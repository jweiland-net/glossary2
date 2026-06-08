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
use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\Exception\MissingArrayPathException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Public API to build your glossary (A-Z) for your own Extension
 */
final readonly class GlossaryService
{
    public function __construct(
        private ExtConf $extConf,
        private EventDispatcher $eventDispatcher,
        private ViewFactoryInterface $viewFactory,
        private CharsetHelper $charsetHelper,
        private TypoScriptService $typoScriptService,
    ) {}

    public function buildGlossary(
        QueryResultInterface|QueryBuilder $queryBuilder,
        ServerRequestInterface $request,
        array $options = [],
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
    private function getLinkedGlossary(QueryResultInterface|QueryBuilder $queryBuilder, array $options): array
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
    private function getAvailableLetters(QueryResultInterface|QueryBuilder $queryBuilder, array $options): array
    {
        $mergeNumbers = (bool)($options['mergeNumbers'] ?? true);

        // These are the available first letters from Database
        $availableChars = $this->getFirstLettersOfGlossaryRecords(
            $queryBuilder,
            $options['column'] ?? 'title',
            $options['columnAlias'] ?? 'Letter',
        );

        $availableNumbers = array_filter($availableChars, is_numeric(...));

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
    private function getFirstLettersOfGlossaryRecords(
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
    private function cleanUpFirstLetters(array $firstLetters): array
    {
        // Map special chars like Ä => a
        foreach ($firstLetters as $key => $firstLetter) {
            $firstLetters[$key] = $this->charsetHelper->sanitize($firstLetter);
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
    private function getFluidTemplateObject(array $options, ServerRequestInterface $request): ViewInterface
    {
        $viewFactoryData = new ViewFactoryData(
            templatePathAndFilename: $this->getTemplatePath($options, $request),
            request: $request,
        );

        return $this->viewFactory->create($viewFactoryData);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function getTemplatePath(array $options, ServerRequestInterface $request): string
    {
        $glossary2TypoScriptSettings = $this->getTypoScriptByPath('plugin./tx_glossary2.', $request);
        if ($glossary2TypoScriptSettings === []) {
            return 'ERROR: Path at plugin.tx_glossary2 not found. Missing TypoScript include? Cached request?';
        }

        $settings = $glossary2TypoScriptSettings['settings'] ?? [];
        if ($settings === []) {
            return 'ERROR: Cannot find any plugin settings!';
        }

        $siteSettings = $this->getSiteSettings($request);
        if ($siteSettings === []) {
            return 'Error: Missing site settings. Missing glossary2 Site Set dependencies?';
        }

        // Priority 4. Use path from ExtConf of glossary2
        $templatePath = $this->extConf->getTemplatePath();

        // Priority 3. Use path of foreign extension
        if (array_key_exists('templatePath', $options) && !empty($options['templatePath'])) {
            $templatePath = $options['templatePath'];
        }

        // Priority 2. Use path from TypoScript of glossary2
        // plugin.tx_glossary2.settings.templatePath = EXT:site_package/.../Glossary2.html
        if (
            isset($settings['templatePath'])
            && is_string($settings['templatePath'])
            && $settings['templatePath'] !== ''
            && $settings['templatePath'] !== '0'
        ) {
            $templatePath = $settings['templatePath'];
        }

        // Priority 1. Use extKey individual path from TypoScript of glossary2
        // plugin.tx_glossary2.settings.templatePath.default = EXT:site_package/.../Glossary2.html
        // plugin.tx_glossary2.settings.templatePath.yellowpages2 = EXT:site_package/.../GlossaryForYellowpages.html
        // plugin.tx_glossary2.settings.templatePath.clubdirectory = EXT:site_package/.../GlossaryForClubdirectory.html
        if (
            isset($settings['templatePath'])
            && is_array($settings['templatePath'])
            && $settings['templatePath'] !== []
        ) {
            $extKey = GeneralUtility::camelCaseToLowerCaseUnderscored($options['extensionName'] ?? 'glossary2');

            // Override with default template path for all extensions
            if (!empty($settings['templatePath']['default'])) {
                $templatePath = $settings['templatePath']['default'];
            }

            // Override with extKey specific template path
            if (!empty($settings['templatePath'][$extKey])) {
                $templatePath = $settings['templatePath'][$extKey];
            }
        }

        // Main Priority from SiteSettings
        if (!empty($siteSettings['templatePath'])) {
            $templatePath = $siteSettings['templatePath'];
        }

        return GeneralUtility::getFileAbsFileName($templatePath);
    }

    private function getSiteSettings(ServerRequestInterface $request): array
    {
        $siteSettings = $this->getCurrentSite($request)->getSettings();

        if (!$siteSettings->has('glossary2')) {
            return [];
        }

        return $siteSettings->get('glossary2');
    }

    private function getCurrentSite(ServerRequestInterface $request): Site
    {
        return $request->getAttribute('site');
    }

    private function getTypoScriptByPath(string $path, ServerRequestInterface $request): array
    {
        try {
            $rawTypoScriptSetup = $this->getTypoScriptSetup($request);
            $rawPluginSettingsByPath = ArrayUtility::getValueByPath($this->getTypoScriptSetup($request), $path);

            return $this->typoScriptService->convertTypoScriptArrayToPlainArray($rawPluginSettingsByPath);
        } catch (\RuntimeException|MissingArrayPathException) {
        }

        return [];
    }

    private function getTypoScriptSetup(ServerRequestInterface $request): array
    {
        return $this->getFrontendTypoScript($request)->getSetupArray();
    }

    /**
     * The middleware calling this service is loaded after prepare TSFE, so TypoScript is defined at that point.
     */
    private function getFrontendTypoScript(ServerRequestInterface $request): FrontendTypoScript
    {
        return $request->getAttribute('frontend.typoscript');
    }
}
