<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Domain\Repository;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

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
     * @param array $categories
     * @param string $letter
     * @return QueryResultInterface
     */
    public function findEntries(array $categories = [], string $letter = ''): QueryResultInterface
    {
        $query = $this->createQuery();
        if ($this->checkArgumentsForFindEntries($categories, $letter)) {
            $constraint = [];

            // Add category to constraint
            if (!empty($categories)) {
                $categoryConstraints = [];
                foreach ($categories as $category) {
                    $categoryConstraints[] = $query->contains('categories', $category);
                }
                $constraint[] = $query->logicalOr($categoryConstraints);
            }

            // Add letter to constraint
            if (!empty($letter)) {
                $letterConstraints = [];
                if ($letter == '0-9') {
                    for ($i = 0; $i < 10; $i++) {
                        $letterConstraints[] = $query->like('title', $i . '%');
                    }
                } else {
                    $letterConstraints[] = $query->like('title', $letter . '%');
                }
                $constraint[] = $query->logicalOr($letterConstraints);
            }

            if (count($constraint)) {
                $query->matching($query->logicalAnd($constraint));
            }
        }
        $this->emitModifyQueryOfFindEntries($query, $categories, $letter);
        return $query->execute();
    }

    /**
     * Allow modification of query created in findEntities()
     * That way you can add further fields to ORDER BY f.e.
     *
     * @param QueryInterface $query
     * @param array $categories
     * @param string $letter
     * @deprecated will be removed with dropping TYPO3 9 support
     */
    protected function emitModifyQueryOfFindEntries(
        QueryInterface $query,
        array $categories,
        string $letter
    ): void {
        $signalSlotDispatcher = GeneralUtility::makeInstance(ObjectManager::class)
            ->get(Dispatcher::class);
        $signalSlotDispatcher->dispatch(
            self::class,
            'modifyQueryOfFindEntries',
            [$query, $categories, $letter]
        );
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

    public function getQueryBuilderForGlossary(array $categories = []): QueryBuilder
    {
        $table = 'tx_glossary2_domain_model_glossary';
        $query = $this->createQuery();
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($table);
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));

        // Do not set any SELECT statement. It will be set by glossary2 API
        $queryBuilder
            ->from($table, 'glossary')
            ->where(
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
                    'glossary',
                    'sys_category_record_mm',
                    'sc_mm',
                    $queryBuilder->expr()->eq(
                        'glossary.uid',
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

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
