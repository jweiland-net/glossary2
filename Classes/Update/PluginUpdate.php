<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Update;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Updater to fill empty slug columns of glossary records
 */
#[UpgradeWizard('glossaryPluginUpdate')]
class PluginUpdate implements UpgradeWizardInterface
{
    public function getTitle(): string
    {
        return 'EXT:glossary2: Migrate plugins list types to CType glossary2_glossary';
    }

    public function getDescription(): string
    {
        return 'This update wizard migrates all existing plugin with list_type to CType';
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    public function updateNecessary(): bool
    {
        return $this->checkIfWizardIsRequired();
    }

    public function executeUpdate(): bool
    {
        return $this->performMigration();
    }

    public function checkIfWizardIsRequired(): bool
    {
        return count($this->getMigrationRecords()) > 0;
    }

    public function performMigration(): bool
    {
        $targetListType = 'glossary2_glossary';
        $records = $this->getMigrationRecords();
        foreach ($records as $record) {
            $this->updateContentElement($record['uid'], $targetListType);
        }

        return true;
    }

    /**
     * @return array<int, mixed>
     */
    protected function getMigrationRecords(): array
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return $queryBuilder
            ->select('uid', 'pid', 'CType', 'list_type', 'pi_flexform')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'CType',
                    $queryBuilder->createNamedParameter('list'),
                ),
                $queryBuilder->expr()->eq(
                    'list_type',
                    $queryBuilder->createNamedParameter('glossary2_glossary'),
                ),
            )
            ->executeQuery()
            ->fetchAllAssociative();
    }

    protected function updateContentElement(int $uid, string $newCtype): void
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->update('tt_content')
            ->set('CType', $newCtype)
            ->set('list_type', '')
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT),
                ),
            )
            ->executeStatement();
    }
}
