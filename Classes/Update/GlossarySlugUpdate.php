<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Update;

use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Updater to fill empty slug columns of glossary records
 */
#[UpgradeWizard('glossary2UpdateSlug')]
class GlossarySlugUpdate implements UpgradeWizardInterface
{
    protected string $tableName = 'tx_glossary2_domain_model_glossary';

    protected string $fieldName = 'path_segment';

    protected ?SlugHelper $slugHelper = null;

    public function getTitle(): string
    {
        return '[glossary2] Update url slugs of glossary2 records';
    }

    public function getDescription(): string
    {
        return 'Update empty slug column "path_segment" of glossary2 records with an URI compatible version of the title';
    }

    public function updateNecessary(): bool
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($this->tableName);
        $amountOfRecordsWithEmptySlug = $queryBuilder
            ->count('*')
            ->from($this->tableName)
            ->andWhere(
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq(
                        $this->fieldName,
                        $queryBuilder->createNamedParameter('', Connection::PARAM_STR),
                    ),
                    $queryBuilder->expr()->isNull(
                        $this->fieldName,
                    ),
                ),
            )
            ->executeQuery()
            ->fetchOne();

        return (bool)$amountOfRecordsWithEmptySlug;
    }

    /**
     * Performs the accordant updates.
     *
     * @return bool Whether everything went smoothly or not
     * @throws Exception
     */
    public function executeUpdate(): bool
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($this->tableName);
        $recordsToUpdate = $queryBuilder
            ->select('uid', 'title', 'path_segment')
            ->from($this->tableName)
            ->andWhere(
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq(
                        $this->fieldName,
                        $queryBuilder->createNamedParameter('', Connection::PARAM_STR),
                    ),
                    $queryBuilder->expr()->isNull(
                        $this->fieldName,
                    ),
                ),
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $connection = $this->getConnectionPool()->getConnectionForTable($this->tableName);
        foreach ($recordsToUpdate as $recordToUpdate) {
            if ((string)$recordToUpdate['title'] !== '') {
                $slug = $this->getSlugHelper()->sanitize((string)$recordToUpdate['title']);
                $connection->update(
                    $this->tableName,
                    [
                        $this->fieldName => $this->getUniqueValue(
                            (int)$recordToUpdate['uid'],
                            $slug,
                        ),
                    ],
                    [
                        'uid' => (int)$recordToUpdate['uid'],
                    ],
                );
            }
        }

        return true;
    }

    protected function getUniqueValue(int $uid, string $slug): string
    {
        $queryBuilder = $this->getUniqueCountQueryBuilder($uid, $slug);
        $statement = $queryBuilder->prepare();
        $queryResult = $statement->executeQuery();

        if ($queryResult->fetchOne()) {
            for ($counter = 1; $counter <= 100; $counter++) {
                $queryResult->free();
                $newSlug = $slug . '-' . $counter;
                $statement->bindValue(1, $newSlug);
                $resultQuery = $statement->executeQuery();
                if (!$resultQuery->fetchOne()) {
                    break;
                }
            }
        }

        return $newSlug ?? $slug;
    }

    protected function getUniqueCountQueryBuilder(int $uid, string $slug): QueryBuilder
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($this->tableName);
        $queryBuilder
            ->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return $queryBuilder
            ->count('uid')
            ->from($this->tableName)
            ->andWhere(
                $queryBuilder->expr()->eq(
                    $this->fieldName,
                    $queryBuilder->createPositionalParameter($slug, Connection::PARAM_STR),
                ),
                $queryBuilder->expr()->neq(
                    'uid',
                    $queryBuilder->createPositionalParameter($uid, Connection::PARAM_INT),
                ),
            );
    }

    protected function getSlugHelper(): SlugHelper
    {
        if ($this->slugHelper === null) {
            $this->slugHelper = GeneralUtility::makeInstance(
                SlugHelper::class,
                $this->tableName,
                $this->fieldName,
                $GLOBALS['TCA'][$this->tableName]['columns']['path_segment']['config'] ?? [],
            );
        }

        return $this->slugHelper;
    }

    /**
     * @return string[]
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
