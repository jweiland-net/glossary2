<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Updater;

use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * With glossary2 3.0.0 we have changed some FlexForm Settings.
 * This Updater converts existing settings to new version.
 */
class MoveOldFlexFormSettingsUpdater
{
    /**
     * Return the identifier for this wizard
     * This should be the same string as used in the ext_localconf class registration
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'glossary2UpdateOldFlexFormFields';
    }

    /**
     * Return the speaking name of this wizard
     *
     * @return string
     */
    public function getTitle(): string
    {
        return '[glossary2] Update old FlexForm field settings';
    }

    /**
     * Return the description for this wizard
     *
     * @return string
     */
    public function getDescription(): string
    {
        return 'It seems that some FlexForm fields of glossary2 are using old SwitchableControllerActions. ' .
            'As these fields are outdated you should update them to new FlexForm fields.';
    }

    /**
     * Checks whether updates are required.
     *
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     */
    public function updateNecessary(): bool
    {
        $records = $this->getTtContentRecordsWithOutdatedFlexForm();
        foreach ($records as $record) {
            $valueFromDatabase = (string)$record['pi_flexform'] !== '' ? GeneralUtility::xml2array($record['pi_flexform']) : [];
            if (!is_array($valueFromDatabase) || empty($valueFromDatabase)) {
                continue;
            }

            if (array_key_exists('sDEFAULT', $valueFromDatabase['data'])) {
                return true;
            }

            if (
                array_key_exists('switchableControllerActions', $valueFromDatabase['data']['sDEF']['lDEF'] ?? [])
                || array_key_exists('switchableControllerActions', $valueFromDatabase['data']['sDEFAULT']['lDEF'] ?? [])
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Performs the accordant updates.
     *
     * @return bool Whether everything went smoothly or not
     */
    public function executeUpdate(): bool
    {
        $records = $this->getTtContentRecordsWithOutdatedFlexForm();
        foreach ($records as $record) {
            $valueFromDatabase = (string)$record['pi_flexform'] !== '' ? GeneralUtility::xml2array($record['pi_flexform']) : [];
            if (!is_array($valueFromDatabase) || empty($valueFromDatabase)) {
                continue;
            }
            $this->moveSheetDefaultToDef($valueFromDatabase);

            // Move SCA to new field showGlossar
            if (!array_key_exists('settings.showGlossar', $valueFromDatabase['data']['sDEF']['lDEF'])) {
                if ($valueFromDatabase['data']['sDEF']['lDEF']['switchableControllerActions']['vDEF']) {
                    // if set, SCA is not default. It was set to showWithoutGlossar. So set to 0
                    $valueFromDatabase['data']['sDEF']['lDEF']['settings.showGlossar']['vDEF'] = '0';
                } else {
                    // if not set, SCA is default. A-Z links should be shown. So set to 1
                    $valueFromDatabase['data']['sDEF']['lDEF']['settings.showGlossar']['vDEF'] = '1';
                }
            }
            unset($valueFromDatabase['data']['sDEF']['lDEF']['switchableControllerActions']);

            // Add showAllLink field, if not already done
            if (!array_key_exists('settings.showAllLink', $valueFromDatabase['data']['sDEF']['lDEF'])) {
                // To be backwards compatible, we have to deactivate that option for all existing records.
                // But it is ON by default for new records.
                $valueFromDatabase['data']['sDEF']['lDEF']['settings.showAllLink']['vDEF'] = '0';
            }

            $connection = $this->getConnectionPool()->getConnectionForTable('tt_content');
            $connection->update(
                'tt_content',
                [
                    'pi_flexform' => $this->checkValue_flexArray2Xml($valueFromDatabase)
                ],
                [
                    'uid' => (int)$record['uid']
                ],
                [
                    'pi_flexform' => \PDO::PARAM_STR
                ]
            );
        }

        return true;
    }

    /**
     * Get all (incl. deleted/hidden) tt_content records with plugin glossary2_glossary
     *
     * @return array
     */
    protected function getTtContentRecordsWithOutdatedFlexForm(): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();
        $records = $queryBuilder
            ->select('uid', 'pi_flexform')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'CType',
                    $queryBuilder->createNamedParameter('list', \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->eq(
                    'list_type',
                    $queryBuilder->createNamedParameter('glossary2_glossary', \PDO::PARAM_STR)
                )
            )
            ->execute()
            ->fetchAll();

        if ($records === false) {
            $records = [];
        }

        return $records;
    }

    /**
     * It's not a must have, but sDEF seems to be more default than sDEFAULT as first sheet name in TYPO3
     *
     * @param array $valueFromDatabase
     */
    protected function moveSheetDefaultToDef(array &$valueFromDatabase)
    {
        if (array_key_exists('sDEFAULT', $valueFromDatabase['data'])) {
            foreach ($valueFromDatabase['data']['sDEFAULT']['lDEF'] as $field => $value) {
                $this->moveFieldFromOldToNewSheet($valueFromDatabase, $field, 'sDEFAULT', 'sDEF');
            }

            // remove old sheet completely
            unset($valueFromDatabase['data']['sDEFAULT']);
        }
    }

    /**
     * Move field from one sheet to another and remove field from old location
     *
     * @param array $valueFromDatabase
     * @param string $field
     * @param string $oldSheet
     * @param string $newSheet
     */
    protected function moveFieldFromOldToNewSheet(array &$valueFromDatabase, string $field, string $oldSheet, string $newSheet)
    {
        if (array_key_exists($field, $valueFromDatabase['data'][$oldSheet]['lDEF'])) {
            // Create base sheet, if not exist
            if (!array_key_exists($newSheet, $valueFromDatabase['data'])) {
                $valueFromDatabase['data'][$newSheet] = [
                    'lDEF' => []
                ];
            }

            // Move field to new location, if not already done
            if (!array_key_exists($field, $valueFromDatabase['data'][$newSheet]['lDEF'])) {
                $valueFromDatabase['data'][$newSheet]['lDEF'][$field] = $valueFromDatabase['data'][$oldSheet]['lDEF'][$field];
            }

            // Remove old reference
            unset($valueFromDatabase['data'][$oldSheet]['lDEF'][$field]);
        }
    }

    /**
     * Converts an array to FlexForm XML
     *
     * @param array $array Array with FlexForm data
     * @return string Input array converted to XML
     */
    public function checkValue_flexArray2Xml($array): string
    {
        $flexObj = GeneralUtility::makeInstance(FlexFormTools::class);
        return $flexObj->flexArray2Xml($array, true);
    }

    /**
     * Get TYPO3s Connection Pool
     *
     * @return ConnectionPool
     */
    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
