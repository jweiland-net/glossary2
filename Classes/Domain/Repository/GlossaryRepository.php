<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Domain\Repository;

use JWeiland\Glossary2\Event\ModifyQueryOfGetGlossariesEvent;
use JWeiland\Glossary2\Event\ModifyQueryOfSearchGlossariesEvent;
use JWeiland\Glossary2\Helper\OverlayHelper;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * This class contains all queries to get needed glossary entries from DB
 */
class GlossaryRepository extends Repository
{
    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var OverlayHelper
     */
    protected $overlayHelper;

    public function injectOverlayHelper(OverlayHelper $overlayHelper): void
    {
        $this->overlayHelper = $overlayHelper;
    }

    public function injectEventDispatcher(EventDispatcher $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function searchGlossaries(array $categories = [], string $letter = ''): QueryResultInterface
    {
        // Set respectSysLanguage to false, to keep our already translated records
        $extbaseQuery = $this->createQuery();
        $extbaseQuery->getQuerySettings()->setRespectSysLanguage(false);

        $queryBuilder = $this->getQueryBuilderForTable(
            'tx_glossary2_domain_model_glossary',
            'g',
            true
        );
        $queryBuilder->select('*');

        if ($this->checkArgumentsForSearchGlossaries($categories, $letter)) {
            $andConstraints = [];

            if ($categories !== []) {
                $this->addCategoryConstraintToQueryBuilder(
                    $queryBuilder,
                    'tx_glossary2_domain_model_glossary',
                    $categories
                );
            }

            // Add letter to constraint
            if ($letter !== '') {
                if ($letter === '0-9') {
                    $letterConstraint = [];
                    for ($i = 0; $i < 10; $i++) {
                        $letterConstraint[] = $queryBuilder->expr()->like(
                            'title',
                            $queryBuilder->createNamedParameter($i . '%', \PDO::PARAM_STR)
                        );
                    }
                    $andConstraints[] = $queryBuilder->expr()->orX(...$letterConstraint);
                } else {
                    $andConstraints[] = $queryBuilder->expr()->like(
                        'title',
                        $queryBuilder->createNamedParameter($letter . '%', \PDO::PARAM_STR)
                    );
                }

                $queryBuilder->andWhere(...$andConstraints);
            }
        }

        $this->eventDispatcher->dispatch(
            new ModifyQueryOfSearchGlossariesEvent($queryBuilder, $categories, $letter)
        );

        $extbaseQuery->statement($queryBuilder);

        return $extbaseQuery->execute();
    }

    protected function checkArgumentsForSearchGlossaries(array $categories, string $letter): bool
    {
        // check categories as they can also be set by TypoScript
        $intCategories = GeneralUtility::intExplode(
            ',',
            implode(
                ',',
                $categories
            ),
            true
        );
        if ($intCategories !== $categories) {
            return false;
        }
        if (in_array(0, $intCategories, true)) {
            return false;
        }

        // check letter
        if ($letter !== '' && !preg_match('@^0-9$|^[a-z]$@', $letter)) {
            return false;
        }

        return true;
    }

    /**
     * Prepare a QueryBuilder for glossary (A-Z navigation)
     */
    public function getQueryBuilderForGlossary(array $categories = []): QueryBuilder
    {
        $queryBuilder = $this->getQueryBuilderForTable('tx_glossary2_domain_model_glossary', 'g');

        if ($categories !== []) {
            $this->addCategoryConstraintToQueryBuilder(
                $queryBuilder,
                'tx_glossary2_domain_model_glossary',
                $categories
            );
        }

        return $queryBuilder;
    }

    protected function addCategoryConstraintToQueryBuilder(
        QueryBuilder $queryBuilder,
        string $table,
        array $categories
    ): void {
        $queryBuilder
            ->leftJoin(
                'g',
                'sys_category_record_mm',
                'sc_mm',
                $queryBuilder->expr()->eq(
                    'g.uid',
                    $queryBuilder->quoteIdentifier('sc_mm.uid_foreign')
                )
            )
            ->andWhere(
                $queryBuilder->expr()->eq(
                    'sc_mm.tablenames',
                    $queryBuilder->createNamedParameter($table, \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->eq(
                    'sc_mm.fieldname',
                    $queryBuilder->createNamedParameter('categories', \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->in(
                    'sc_mm.uid_local',
                    $queryBuilder->createNamedParameter($categories, Connection::PARAM_INT_ARRAY)
                )
            );
    }

    protected function getQueryBuilderForTable(string $table, string $alias, bool $useLangStrict = false): QueryBuilder
    {
        $extbaseQuery = $this->createQuery();

        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($table);
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        $queryBuilder
            ->from($table, $alias)
            ->andWhere(
                $queryBuilder->expr()->in(
                    'pid',
                    $queryBuilder->createNamedParameter(
                        $extbaseQuery->getQuerySettings()->getStoragePageIds(),
                        Connection::PARAM_INT_ARRAY
                    )
                )
            )
            ->orderBy('title', 'ASC');

        $this->overlayHelper->addWhereForOverlay($queryBuilder, $table, $alias, $useLangStrict);

        return $queryBuilder;
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
