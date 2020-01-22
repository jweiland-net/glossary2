<?php
declare(strict_types = 1);
namespace JWeiland\Glossary2\Service;

/*
 * This file is part of the glossary2 project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * A little helper to organize our DB queries
 */
class DatabaseService
{
    /**
     * Get an array with grouped first letters of all found glossary entries in database
     *
     * @param QueryInterface $query
     * @param array $categories
     * @return array
     */
    public function getGroupedFirstLetters(QueryInterface $query, array $categories = []): array
    {
        $table = 'tx_glossary2_domain_model_glossary';
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($table);
        $queryBuilder = $queryBuilder
            ->selectLiteral('LOWER(SUBSTRING(title, 1, 1)) as Letter')
            ->from($table, 'glossary');

        $constraint = [
            $queryBuilder->expr()->in(
                'pid',
                $queryBuilder->createNamedParameter(
                    $query->getQuerySettings()->getStoragePageIds(),
                    Connection::PARAM_INT_ARRAY
                )
            )
        ];

        // Add additional JOIN and WHERE to QueryBuilder, if categories are set
        if (!empty($categories)) {
            $queryBuilder = $queryBuilder
                ->leftJoin(
                    'glossary',
                    'sys_category_record_mm',
                    'category_mm',
                    $queryBuilder->expr()->eq(
                        'glossary.uid',
                        $queryBuilder->quoteIdentifier('category_mm.uid_foreign')
                    )
                );

            $constraint[] = $queryBuilder->expr()->eq(
                'category_mm.tablenames',
                $queryBuilder->createNamedParameter(
                    $table,
                    \PDO::PARAM_STR
                )
            );
            $constraint[] = $queryBuilder->expr()->eq(
                'category_mm.fieldname',
                $queryBuilder->createNamedParameter(
                    'categories',
                    \PDO::PARAM_STR
                )
            );
        }

        return $queryBuilder
            ->where($queryBuilder->expr()->andX(...$constraint))
            ->groupBy('Letter')
            ->orderBy('Letter')
            ->execute()
            ->fetchAll();
    }

    /**
     * Get TYPO3s Connection Pool
     *
     * @return ConnectionPool
     */
    protected function getConnectionPool()
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
