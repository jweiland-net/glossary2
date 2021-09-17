<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Service;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use JWeiland\Glossary2\Configuration\ExtConf;
use JWeiland\Glossary2\Event\SanitizeValueForCharsetHelperEvent;
use JWeiland\Glossary2\Event\PostProcessFirstLettersEvent;
use JWeiland\Glossary2\Helper\CharsetHelper;
use JWeiland\Glossary2\Helper\OverlayHelper;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\OrInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Public API to build your glossary (A-Z) for your own Extension
 */
class GlossaryService
{
    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * @var OverlayHelper
     */
    protected $overlayHelper;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * This property contains the settings of the page related TypoScript of plugin.tx_glossary.settings
     * and NOT of the calling extension which uses this API!
     * We need that property to override templatePath on per page basis.
     *
     * @var array
     */
    protected $glossary2Settings;

    public function __construct(
        ExtConf $extConf,
        OverlayHelper $overlayHelper,
        EventDispatcher $eventDispatcher,
        ConfigurationManagerInterface $configurationManager
    ) {
        $this->extConf = $extConf;
        $this->overlayHelper = $overlayHelper;
        $this->eventDispatcher = $eventDispatcher;
        $this->glossary2Settings = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'Glossary2',
            'Glossary'
        ) ?: [];
    }

    public function buildGlossary(QueryBuilder $queryBuilder, array $options = []): string
    {
        $view = $this->getFluidTemplateObject($options);
        $view->assign('glossary', $this->getLinkedGlossary($queryBuilder, $options));
        $view->assign('settings', $options['settings'] ?? []);
        $view->assign('options', $options);

        return $view->render();
    }

    /**
     * Creates a constraint which you can use like that:
     *
     * $query = $this->createQuery();
     * $constraints = [];
     * $constraints[] = $glossary2Service->getLetterConstraintForExtbaseQuery($query, 'title', $letter);
     * return $query->matching($query->logicalAnd($constraints))->execute();
     *
     * @param QueryInterface $extbaseQuery
     * @param string $column
     * @param string $letter
     * @return OrInterface|ComparisonInterface
     */
    public function getLetterConstraintForExtbaseQuery(
        QueryInterface $extbaseQuery,
        string $column,
        string $letter
    ): ConstraintInterface {
        $letterConstraints = [];
        if ($letter === '0-9') {
            for ($i = 0; $i < 10; $i++) {
                $letterConstraints[] = $extbaseQuery->like($column, $i . '%');
            }
        } else {
            $letterConstraints[] = $extbaseQuery->like(
                $column,
                addcslashes($letter, '_%') . '%'
            );
        }
        return $extbaseQuery->logicalOr($letterConstraints);
    }

    /**
     * Creates an expression which you can use like that:
     *
     * $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('my_table');
     * $queryBuilder->andWhere($glossary2Service->getLetterConstraintForDoctrineQuery($queryBuilder, 'title', $letter));
     *
     * @param QueryBuilder $queryBuilder
     * @param string $column
     * @param string $letter
     * @return CompositeExpression
     */
    public function getLetterConstraintForDoctrineQuery(
        QueryBuilder $queryBuilder,
        string $column,
        string $letter
    ): CompositeExpression {
        $letterConstraints = [];
        if ($letter === '0-9') {
            for ($i = 0; $i < 10; $i++) {
                $letterConstraints[] = $queryBuilder->expr()->like(
                    $column,
                    $queryBuilder->createNamedParameter(
                        $i . '%',
                        \PDO::PARAM_STR
                    )
                );
            }
        } else {
            $letterConstraints[] = $queryBuilder->expr()->like(
                $column,
                $queryBuilder->createNamedParameter(
                    $queryBuilder->escapeLikeWildcards($letter) . '%',
                    \PDO::PARAM_STR
                )
            );
        }
        return $queryBuilder->expr()->orX(...$letterConstraints);
    }

    protected function getLinkedGlossary(QueryBuilder $queryBuilder, array $options): array
    {
        // These are the available first letters from Database
        $availableLetters = $this->getAvailableLetters($queryBuilder, $options);

        // These are the configured first letters which are allowed to be visible in frontend by TS configuration
        $possibleLetters = GeneralUtility::trimExplode(
            ',',
            $options['possibleLetters'] ?? $this->extConf->getPossibleLetters(),
            true
        );

        // Mark letter as link (true) or not-linked (false)
        $glossaryLetterHasEntries = [];
        foreach ($possibleLetters as $possibleLetter) {
            $glossaryLetterHasEntries[$possibleLetter] = strpos($availableLetters, $possibleLetter) !== false;
        }

        return $glossaryLetterHasEntries;
    }

    protected function getAvailableLetters($queryBuilder, array $options): string
    {
        $mergeNumbers = (bool)($options['mergeNumbers'] ?? true);

        // These are the available first letters from Database
        $availableLetters = implode(
            '',
            $this->getFirstLettersOfGlossaryRecords(
                $queryBuilder,
                $options['column'] ?? 'title',
                $options['columnAlias'] ?? 'Letter'
            )
        );
        if ($mergeNumbers) {
            // if there are numbers inside, replace them with 0-9
            if (preg_match('~^[[:digit:]]+~', $availableLetters)) {
                $availableLetters = preg_replace('~(^[[:digit:]]+)~', '0-9', $availableLetters);
            }
        }

        return $availableLetters;
    }

    protected function getFirstLettersOfGlossaryRecords(
        QueryBuilder $queryBuilder,
        string $column,
        string $columnAlias
    ): array {
        if ($queryBuilder->getConnection()->getDatabasePlatform() instanceof MySqlPlatform) {
            $statement = $queryBuilder
                ->selectLiteral('uid', $column, sprintf('SUBSTRING(%s, 1, 1) as %s', $column, $columnAlias))
                ->add('groupBy', $columnAlias)
                ->add('orderBy', $columnAlias)
                ->execute();

            $firstLetters = [];
            while ($record = $statement->fetch()) {
                $firstLetter = mb_strtolower($record[$columnAlias]);
                $firstLetters[] = $firstLetter;
            }
        } else {
            // This will collect nearly all records and could be an
            // performance issue, if you have a lot of records
            $statement = $queryBuilder
                ->select('uid', $column . ' AS ' . $columnAlias)
                ->add('groupBy', $columnAlias)
                ->add('orderBy', $columnAlias)
                ->execute();

            $firstLetters = [];
            while ($record = $statement->fetch()) {
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
     * @param array $firstLetters
     * @return array
     */
    protected function cleanUpFirstLetters(array $firstLetters): array
    {
        // Map special chars like Ä => a
        $firstLetters = array_map(static function ($firstLetter) {
            $charsetHelper = GeneralUtility::makeInstance(CharsetHelper::class);
            return $charsetHelper->sanitize($firstLetter);
        }, $firstLetters);

        // Remove all letters which are not numbers or letters. Maybe spaces, tabs, - or others
        $firstLetters = str_split(
            preg_replace('~([[:^alnum:]])~', '', implode('', $firstLetters))
        );

        // Sort and remove duplicate letters
        sort($firstLetters);

        return array_unique($firstLetters);
    }

    protected function getFluidTemplateObject(array $options): StandaloneView
    {
        $extensionName = GeneralUtility::underscoredToUpperCamelCase($options['extensionName'] ?? 'glossary2');
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename($this->getTemplatePath($options));
        $view->getRequest()->setControllerExtensionName($extensionName);
        $view->getRequest()->setPluginName($options['pluginName'] ?? 'glossary');
        $view->getRequest()->setControllerName(ucfirst($options['controllerName'] ?? 'Glossary'));
        $view->getRequest()->setControllerActionName(strtolower($options['actionName'] ?? 'list'));

        return $view;
    }

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
}
