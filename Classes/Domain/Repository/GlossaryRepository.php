<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Domain\Repository;

use JWeiland\Glossary2\Event\ModifyQueryOfFindEntriesEvent;
use JWeiland\Glossary2\Helper\OverlayHelper;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * This class contains all queries to get needed glossary entries from DB
 */
class GlossaryRepository extends Repository
{
    /**
     * @var array
     */
    protected $defaultOrderings = [
        'title' => QueryInterface::ORDER_ASCENDING
    ];

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var OverlayHelper
     */
    protected $overlayHelper;

    public function __construct(
        ObjectManagerInterface $objectManager,
        OverlayHelper $overlayHelper,
        EventDispatcher $eventDispatcher
    ) {
        parent::__construct($objectManager);

        $this->overlayHelper = $overlayHelper;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function findEntries(array $categories = [], string $letter = ''): QueryResultInterface
    {
        $extbaseQuery = $this->createQuery();
        $queryBuilder = $this->getQueryBuilderForTable('tx_glossary2_domain_model_glossary', 'g');
        $queryBuilder->select('*');

        if ($this->checkArgumentsForFindEntries($categories, $letter)) {
            if ($categories !== []) {
                $queryBuilder->leftJoin(
                    'g',
                    'sys_category_record_mm',
                    'sc_mm',
                    $queryBuilder->expr()->eq(
                        'g.uid',
                        $queryBuilder->quoteIdentifier('sc_mm.uid_foreign')
                    )
                );

                $queryBuilder->expr()->eq(
                    'sc_mm.uid_local',
                    $queryBuilder->createNamedParameter($categories, \PDO::PARAM_INT)
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
                    $queryBuilder->expr()->orX(...$letterConstraint);
                } else {
                    $queryBuilder->expr()->like(
                        'title',
                        $queryBuilder->createNamedParameter($letter . '%', \PDO::PARAM_STR)
                    );
                }
            }
        }

        $this->eventDispatcher->dispatch(
            new ModifyQueryOfFindEntriesEvent($queryBuilder, $categories, $letter)
        );

        $extbaseQuery->statement($queryBuilder);

        return $extbaseQuery->execute();
    }

    /**
     * Check arguments of method findEntries
     *
     * @param array $categories
     * @param string $letter
     * @return bool
     */
    protected function checkArgumentsForFindEntries(array $categories, string $letter): bool
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
        if (!is_string($letter) || ($letter !== '' && !preg_match('@^0-9$|^[a-z]$@', $letter))) {
            return false;
        }

        return true;
    }

    /**
     * Prepare a QueryBuilder for glossary (A-Z navigation)
     *
     * @param array $categories
     * @return QueryBuilder
     */
    public function getQueryBuilderForGlossary(array $categories = []): QueryBuilder
    {
        $table = 'tx_glossary2_domain_model_glossary';
        $query = $this->createQuery();
        $queryBuilder = $this->getQueryBuilderForTable($table, 'g');

        // Do not set any SELECT statement. It will be set by glossary2 API
        $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->in(
                    'pid',
                    $queryBuilder->createNamedParameter(
                        $query->getQuerySettings()->getStoragePageIds(),
                        Connection::PARAM_INT_ARRAY
                    )
                )
            );

        // Add additional JOIN to sys_category_record_mm if needed
        if (!empty($categories)) {
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
                    $queryBuilder->expr()->eq(
                        'sc_mm.uid_local',
                        $queryBuilder->createNamedParameter($categories, Connection::PARAM_INT_ARRAY)
                    )
                );
        }

        return $queryBuilder;
    }

    protected function getQueryBuilderForTable(string $table, string $alias): QueryBuilder
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($table);
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        $queryBuilder->from($table, $alias);

        $this->overlayHelper->addWhereForOverlay($queryBuilder, $table, $alias);

        return $queryBuilder;
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
