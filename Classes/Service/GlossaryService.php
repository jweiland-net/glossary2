<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Service;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use JWeiland\Glossary2\Configuration\ExtConf;
use JWeiland\Glossary2\Domain\Model\Glossary;
use JWeiland\Glossary2\Event\PostProcessFirstLettersEvent;
use JWeiland\Glossary2\Helper\CharsetHelper;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Public API to build your glossary (A-Z) for your own Extension
 */
class GlossaryService
{
    protected ExtConf $extConf;

    protected EventDispatcher $eventDispatcher;

    /**
     * This property contains the settings of the page related TypoScript of plugin.tx_glossary.settings
     * and NOT of the calling extension which uses this API!
     * We need that property to override templatePath on per page basis.
     *
     * @var array<string, mixed>
     */
    protected array $glossary2Settings;

    public function __construct(
        ExtConf $extConf,
        EventDispatcher $eventDispatcher,
        ConfigurationManagerInterface $configurationManager,
        private readonly ViewFactoryInterface $viewFactory,
    ) {
        $this->extConf = $extConf;
        $this->eventDispatcher = $eventDispatcher;
        $this->glossary2Settings = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'Glossary2',
            'Glossary',
        ) ?: [];
    }

    /**
     * @param QueryBuilder|QueryResultInterface<int, Glossary> $queryBuilder
     * @param array<string, mixed> $options
     * @param ServerRequestInterface|null $request
     * @return string
     * @throws Exception
     */
    public function buildGlossary(
        QueryResultInterface|QueryBuilder $queryBuilder,
        array $options = [],
        ServerRequestInterface $request = null,
    ): string {
        $view = $this->getFluidTemplateObject($options, $request);
        $view->assign('glossary', $this->getLinkedGlossary($queryBuilder, $options));
        $view->assign('settings', $options['settings'] ?? []);
        $view->assign('variables', $options['variables'] ?? []);
        $view->assign('options', $options);

        return $view->render();
    }

    /**
     * Creates a constraint which you can use like that:
     * $query = $this->createQuery();
     * $constraints = [];
     * $constraints[] = $glossary2Service->getLetterConstraintForExtbaseQuery($query, 'title', $letter);
     * return $query->matching($query->logicalAnd($constraints))->execute();
     *
     * @param QueryInterface<Glossary> $extbaseQuery
     * @throws InvalidQueryException
     */
    public function getLetterConstraintForExtbaseQuery(
        QueryInterface $extbaseQuery,
        string $column,
        string $letter,
    ): ConstraintInterface {
        $letterConstraints = [];
        if ($letter === '0-9') {
            for ($i = 0; $i < 10; $i++) {
                $letterConstraints[] = $extbaseQuery->like($column, $i . '%');
            }
        } else {
            $letterConstraints[] = $extbaseQuery->like(
                $column,
                addcslashes($letter, '_%') . '%',
            );
        }

        return $extbaseQuery->logicalOr(...$letterConstraints);
    }

    /**
     * Creates an expression which you can use like that:
     *
     * $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('my_table');
     * $queryBuilder->andWhere($glossary2Service->getLetterConstraintForDoctrineQuery($queryBuilder, 'title', $letter));
     */
    public function getLetterConstraintForDoctrineQuery(
        QueryBuilder $queryBuilder,
        string $column,
        string $letter,
    ): CompositeExpression {
        $letterConstraints = [];
        if ($letter === '0-9') {
            for ($i = 0; $i < 10; $i++) {
                $letterConstraints[] = $queryBuilder->expr()->like(
                    $column,
                    $queryBuilder->createNamedParameter($i . '%'),
                );
            }
        } else {
            $letterConstraints[] = $queryBuilder->expr()->like(
                $column,
                $queryBuilder->createNamedParameter(
                    $queryBuilder->escapeLikeWildcards($letter) . '%',
                ),
            );
        }

        return $queryBuilder->expr()->or(...$letterConstraints);
    }

    /**
     * @param QueryBuilder|QueryResultInterface<int, Glossary> $queryBuilder
     * @param array<string, mixed> $options
     * @return array<int, array<string, bool|string>>
     * @throws Exception
     */
    protected function getLinkedGlossary(QueryResultInterface|QueryBuilder $queryBuilder, array $options): array
    {
        // These are the available first letters from Database
        $availableLetters = $this->getAvailableLetters($queryBuilder, $options);

        // These are the configured first letters which are allowed to be visible in frontend by TS configuration
        $possibleLetters = GeneralUtility::trimExplode(
            ',',
            $options['possibleLetters'] ?? $this->extConf->getPossibleLetters(),
            true,
        );

        // Mark letter as link (true) or not-linked (false)
        $glossaryLetterHasEntries = [];
        foreach ($possibleLetters as $possibleLetter) {
            $glossaryLetterHasEntries[] = [
                'letter' => $possibleLetter,
                'hasLink' => in_array($possibleLetter, $availableLetters, true),
                'isRequestedLetter' => ($options['variables']['letter'] ?? '') === $possibleLetter,
            ];
        }

        return $glossaryLetterHasEntries;
    }

    /**
     * @param QueryBuilder|QueryResultInterface<int, Glossary> $queryBuilder
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     * @throws Exception
     */
    protected function getAvailableLetters(QueryResultInterface|QueryBuilder $queryBuilder, array $options): array
    {
        $mergeNumbers = (bool)($options['mergeNumbers'] ?? true);

        // These are the available first letters from Database
        $availableChars = $this->getFirstLettersOfGlossaryRecords(
            $queryBuilder,
            $options['column'] ?? 'title',
            $options['columnAlias'] ?? 'Letter',
        );

        $availableNumbers = array_filter($availableChars, static function ($letter) {
            return is_numeric($letter);
        });

        $availableLetters = array_diff($availableChars, $availableNumbers);

        // If merge is activated, merge all numbers to 0-9
        if ($mergeNumbers && $availableNumbers !== []) {
            $availableNumbers = ['0-9'];
        }

        return array_merge($availableNumbers, $availableLetters);
    }

    /**
     * @param QueryBuilder|QueryResultInterface<int, Glossary> $queryBuilder
     * @return array<string, mixed>
     * @throws Exception
     */
    protected function getFirstLettersOfGlossaryRecords(
        QueryResultInterface|QueryBuilder $queryBuilder,
        string $column,
        string $columnAlias,
    ): array {
        $firstLetters = [];

        if ($queryBuilder instanceof QueryResultInterface) {
            // As we can not modify SELECT part, we have to loop through all records
            $propertyGetter = 'get' . GeneralUtility::underscoredToUpperCamelCase($column);
            foreach ($queryBuilder as $record) {
                if (method_exists($record, $propertyGetter)) {
                    $firstLetter = mb_strtolower(mb_substr(call_user_func([$record, $propertyGetter]), 0, 1));
                    $firstLetters[$firstLetter] = $firstLetter;
                }
            }
        } elseif ($queryBuilder->getConnection()->getDatabasePlatform() instanceof MySQLPlatform) {
            $queryResult = $queryBuilder
                ->selectLiteral(sprintf('SUBSTRING(%s, 1, 1) as %s', $column, $columnAlias))
                ->groupBy($columnAlias)
                ->orderBy($columnAlias)
                ->executeQuery();

            while ($record = $queryResult->fetchAssociative()) {
                $firstLetter = mb_strtolower($record[$columnAlias]);
                $firstLetters[] = $firstLetter;
            }
        } else {
            // This will collect nearly all records and could be an
            // performance issue, if you have a lot of records
            $queryResult = $queryBuilder
                ->select($column . ' AS ' . $columnAlias)
                ->groupBy($columnAlias)
                ->orderBy($columnAlias)
                ->executeQuery();

            while ($record = $queryResult->fetchAssociative()) {
                $firstLetter = mb_strtolower($record[$columnAlias][0]);
                $firstLetters[$firstLetter] = $firstLetter;
            }
        }

        $firstLetters = array_unique($this->cleanUpFirstLetters($firstLetters));

        /** @var PostProcessFirstLettersEvent $event */
        $event = $this->eventDispatcher->dispatch(new PostProcessFirstLettersEvent($firstLetters));

        return $event->getFirstLetters();
    }

    /**
     * GROUP BY of DB will group all "a" letters like a, á, â, à to ONE of them. If grouped letter
     * is "a", everything is fine, but in case of "á" we have to convert this letter to ASCII "a" representation.
     *
     * @param array<string, mixed> $firstLetters
     * @return array<int, mixed>
     */
    protected function cleanUpFirstLetters(array $firstLetters): array
    {
        // Map special chars like Ä => a
        $charsetHelper = GeneralUtility::makeInstance(CharsetHelper::class);
        foreach ($firstLetters as $key => $firstLetter) {
            $firstLetters[$key] = $charsetHelper->sanitize($firstLetter);
        }

        // Remove all letters which are not numbers or letters. Maybe spaces, tabs, - or others
        $firstLetters = str_split(
            preg_replace('~([[:^alnum:]])~', '', implode('', $firstLetters)),
        );

        // Sort and remove duplicate letters
        sort($firstLetters);

        return array_unique($firstLetters);
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function getFluidTemplateObject(array $options, ServerRequestInterface $request = null): ViewInterface
    {
        $viewFactoryData = new ViewFactoryData(
            templatePathAndFilename: $this->getTemplatePath($options),
            request: $request,
        );

        return $this->viewFactory->create($viewFactoryData);
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function getTemplatePath(array $options): string
    {
        // Priority 4. Use path from ExtConf of glossary2
        $templatePath = $this->extConf->getTemplatePath();

        // Priority 3. Use path of foreign extension
        if (array_key_exists('templatePath', $options) && !empty($options['templatePath'])) {
            $templatePath = $options['templatePath'];
        }

        // Priority 2. Use path from TypoScript of glossary2
        // plugin.tx_glossary2.settings.templatePath = EXT:site_package/.../Glossary2.html
        if (
            array_key_exists('templatePath', $this->glossary2Settings)
            && is_string($this->glossary2Settings['templatePath'])
            && !empty($this->glossary2Settings['templatePath'])
        ) {
            $templatePath = $this->glossary2Settings['templatePath'];
        }

        // Priority 1. Use extKey individual path from TypoScript of glossary2
        // plugin.tx_glossary2.settings.templatePath.default = EXT:site_package/.../Glossary2.html
        // plugin.tx_glossary2.settings.templatePath.yellowpages2 = EXT:site_package/.../GlossaryForYellowpages.html
        // plugin.tx_glossary2.settings.templatePath.clubdirectory = EXT:site_package/.../GlossaryForClubdirectory.html
        if (
            array_key_exists('templatePath', $this->glossary2Settings)
            && is_array($this->glossary2Settings['templatePath'])
            && !empty($this->glossary2Settings['templatePath'])
        ) {
            $extKey = GeneralUtility::camelCaseToLowerCaseUnderscored($options['extensionName'] ?? 'glossary2');

            // Override with default template path for all extensions
            if (
                array_key_exists('default', $this->glossary2Settings['templatePath'])
                && !empty($this->glossary2Settings['templatePath']['default'])
            ) {
                $templatePath = $this->glossary2Settings['templatePath']['default'];
            }

            // Override with extKey specific template path
            if (
                array_key_exists($extKey, $this->glossary2Settings['templatePath'])
                && !empty($this->glossary2Settings['templatePath'][$extKey])
            ) {
                $templatePath = $this->glossary2Settings['templatePath'][$extKey];
            }
        }

        return GeneralUtility::getFileAbsFileName($templatePath);
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }

    protected function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? ServerRequestFactory::fromGlobals();
    }
}
