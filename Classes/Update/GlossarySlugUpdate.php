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
use TYPO3\CMS\Core\Attribute\UpgradeWizard;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Upgrades\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Core\Upgrades\UpgradeWizardInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Updater to fill empty slug columns of glossary records
 */
#[UpgradeWizard('glossary2UpdateSlug')]
final readonly class GlossarySlugUpdate implements UpgradeWizardInterface
{
    private const string TABLE_NAME = 'tx_glossary2_domain_model_glossary';

    private const string FIELD_NAME = 'path_segment';

    public function __construct(private readonly ConnectionPool $connectionPool) {}

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
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
        $amountOfRecordsWithEmptySlug = $queryBuilder
            ->count('*')
            ->from(self::TABLE_NAME)
            ->andWhere(
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq(
                        self::FIELD_NAME,
                        $queryBuilder->createNamedParameter('', Connection::PARAM_STR),
                    ),
                    $queryBuilder->expr()->isNull(
                        self::FIELD_NAME,
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
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
        $recordsToUpdate = $queryBuilder
            ->select('uid', 'title')
            ->from(self::TABLE_NAME)
            ->andWhere(
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq(
                        self::FIELD_NAME,
                        $queryBuilder->createNamedParameter('', Connection::PARAM_STR),
                    ),
                    $queryBuilder->expr()->isNull(
                        self::FIELD_NAME,
                    ),
                ),
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $connection = $this->connectionPool->getConnectionForTable(self::TABLE_NAME);
        $slugHelper = $this->getSlugHelper();
        foreach ($recordsToUpdate as $recordToUpdate) {
            if ((string)$recordToUpdate['title'] !== '') {
                $slug = $slugHelper->sanitize((string)$recordToUpdate['title']);
                $connection->update(
                    self::TABLE_NAME,
                    [
                        self::FIELD_NAME => $this->getUniqueValue(
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

    private function getUniqueValue(int $uid, string $slug): string
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

    private function getUniqueCountQueryBuilder(int $uid, string $slug): QueryBuilder
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder
            ->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return $queryBuilder
            ->count('uid')
            ->from(self::TABLE_NAME)
            ->andWhere(
                $queryBuilder->expr()->eq(
                    self::FIELD_NAME,
                    $queryBuilder->createPositionalParameter($slug, Connection::PARAM_STR),
                ),
                $queryBuilder->expr()->neq(
                    'uid',
                    $queryBuilder->createPositionalParameter($uid, Connection::PARAM_INT),
                ),
            );
    }

    private function getSlugHelper(): SlugHelper
    {
        $fieldConfig = $GLOBALS['TCA'][self::TABLE_NAME]['columns']['path_segment']['config'] ?? [];

        // Safe fallback configuration if the wizard runs during a deployment cold cache state
        if (empty($fieldConfig)) {
            $fieldConfig = [
                'type' => 'slug',
                'generatorOptions' => [
                    'fields' => ['title'],
                    'replacements' => ['/' => '-'],
                ],
                'fallbackCharacter' => '-',
            ];
        }

        return GeneralUtility::makeInstance(
            SlugHelper::class,
            self::TABLE_NAME,
            self::FIELD_NAME,
            $fieldConfig,
        );
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
}
