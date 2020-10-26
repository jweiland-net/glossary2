<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Service;

use JWeiland\Glossary2\Configuration\ExtConf;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\OrInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
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

    public function __construct(?ExtConf $extConf = null)
    {
        $this->extConf = $extConf ?? GeneralUtility::makeInstance(ExtConf::class);
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
        $queryBuilder
            ->selectLiteral(sprintf('LOWER(SUBSTRING(%s, 1, 1)) as %s', $column, $columnAlias))
            ->add('groupBy', $columnAlias)
            ->add('orderBy', $columnAlias);

        $statement = $queryBuilder->execute();

        $firstLetters = [];
        while ($firstLetter = $statement->fetch()) {
            $firstLetters[] = $firstLetter[$columnAlias];
        }

        $firstLetters = array_unique($this->cleanUpFirstLetters($firstLetters));
        $this->emitPostProcessFirstLettersSignal($firstLetters, $queryBuilder);

        return $firstLetters;
    }

    protected function cleanUpFirstLetters(array $firstLetters): array
    {
        // Map special chars like Ä => a
        $firstLetters = array_map(function ($firstLetter) {
            return strtr($firstLetter, $this->getLetterMapping());
        }, $firstLetters);

        // Remove all letters which are not numbers or letters. Maybe spaces, tabs, - or others
        $firstLetters = str_split(
            preg_replace('~([[:^alnum:]])~', '', implode('', $firstLetters))
        );

        // Sort and remove duplicate letters
        sort($firstLetters);

        return array_unique($firstLetters);
    }

    protected function getLetterMapping(): array
    {
        $letterMapping = [
            'ä' => 'a',
            'ö' => 'o',
            'ü' => 'u',
        ];

        return $this->emitModifyLetterMappingSignal($letterMapping);
    }

    /**
     * Use this signal, if you want to modify the first letters of glossary records.
     *
     * @param array $firstLetters
     * @param QueryBuilder $queryBuilder
     */
    protected function emitPostProcessFirstLettersSignal(
        array &$firstLetters,
        QueryBuilder $queryBuilder
    ): void {
        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $signalSlotDispatcher->dispatch(
            self::class,
            'postProcessFirstLetters',
            [&$firstLetters, $queryBuilder]
        );
    }

    protected function emitModifyLetterMappingSignal(array $letterMapping): array
    {
        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        return $signalSlotDispatcher->dispatch(
            self::class,
            'modifyLetterMapping',
            [$letterMapping]
        )[0];
    }

    protected function getFluidTemplateObject(array $options): StandaloneView
    {
        $extensionName = GeneralUtility::underscoredToUpperCamelCase($options['extensionName'] ?? 'glossary2');
        $templatePath = GeneralUtility::getFileAbsFileName(
            $options['templatePath'] ?? $this->extConf->getTemplatePath()
        );
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename($templatePath);
        $view->getRequest()->setControllerExtensionName($extensionName);
        $view->getRequest()->setPluginName($options['pluginName'] ?? 'glossary');
        $view->getRequest()->setControllerName(ucfirst($options['controllerName'] ?? 'Glossary'));
        $view->getRequest()->setControllerActionName(strtolower($options['actionName'] ?? 'list'));

        return $view;
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
