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
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * With glossary2 3.0.0 we have changed some FlexForm Settings.
 * This Updater converts existing settings to new version.
 */
#[UpgradeWizard('glossary2UpdateOldFlexFormFields')]
class MoveOldFlexFormSettingsUpdate implements UpgradeWizardInterface
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {}

    /**
     * Return the speaking name of this wizard
     */
    public function getTitle(): string
    {
        return '[glossary2] Update old FlexForm field settings';
    }

    /**
     * Return the description for this wizard
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
        foreach ($this->getTtContentRecordsWithOutdatedFlexForm() as $record) {
            if ($this->isFlexFormOutdated($this->getFlexFormArray($record))) {
                return true;
            }
        }

        return false;
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    /**
     * Performs the accordant updates.
     *
     * @return bool Whether everything went smoothly or not
     * @throws Exception
     */
    public function executeUpdate(): bool
    {
        foreach ($this->getTtContentRecordsWithOutdatedFlexForm() as $record) {
            $valueFromDatabase = $this->getFlexFormArray($record);
            if (!$this->isFlexFormOutdated($valueFromDatabase)) {
                continue;
            }

            $this->connectionPool->getConnectionForTable('tt_content')->update(
                'tt_content',
                [
                    'pi_flexform' => $this->checkValue_flexArray2Xml($this->migrateFlexForm($valueFromDatabase)),
                ],
                [
                    'uid' => (int)$record['uid'],
                ],
                [
                    'pi_flexform' => Connection::PARAM_STR,
                ],
            );
        }

        return true;
    }

    /**
     * @param array<string, mixed> $valueFromDatabase
     * @return array<string, mixed>
     */
    protected function migrateFlexForm(array $valueFromDatabase): array
    {
        $this->moveSheetDefaultToDef($valueFromDatabase);

        // Move SCA to new field showGlossar
        if (!array_key_exists('settings.showGlossar', $valueFromDatabase['data']['sDEF']['lDEF'] ?? [])) {
            // If SCA is set, it is not default. It was set to showWithoutGlossar.
            // If SCA is not set, it is default and A-Z links should be shown.
            $valueFromDatabase['data']['sDEF']['lDEF']['settings.showGlossar']['vDEF']
                = ($valueFromDatabase['data']['sDEF']['lDEF']['switchableControllerActions']['vDEF'] ?? '') ? '0' : '1';
        }
        unset($valueFromDatabase['data']['sDEF']['lDEF']['switchableControllerActions']);

        // Add showAllLink field, if not already done
        if (!array_key_exists('settings.showAllLink', $valueFromDatabase['data']['sDEF']['lDEF'] ?? [])) {
            // To be backwards compatible, we have to deactivate that option for all existing records.
            // But it is ON by default for new records.
            $valueFromDatabase['data']['sDEF']['lDEF']['settings.showAllLink']['vDEF'] = '0';
        }

        return $valueFromDatabase;
    }

    /**
     * Get all (incl. hidden) tt_content records with plugin glossary2_glossary.
     * Records with the former list_type are respected, too.
     *
     * @return array<int, array<string, mixed>>
     * @throws Exception
     */
    protected function getTtContentRecordsWithOutdatedFlexForm(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder
            ->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return $queryBuilder
            ->select('uid', 'pi_flexform')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter('glossary2_glossary')),
                    $queryBuilder->expr()->and(
                        $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter('list')),
                        $queryBuilder->expr()->eq('list_type', $queryBuilder->createNamedParameter('glossary2_glossary')),
                    ),
                ),
            )
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * @param array<string, mixed> $record
     * @return array<string, mixed>
     */
    protected function getFlexFormArray(array $record): array
    {
        $flexForm = (string)($record['pi_flexform'] ?? '') !== ''
            ? GeneralUtility::xml2array((string)$record['pi_flexform'])
            : [];

        return is_array($flexForm) ? $flexForm : [];
    }

    /**
     * FlexForm is outdated, if it still uses the sheet "sDEFAULT" or the
     * field "switchableControllerActions"
     *
     * @param array<string, mixed> $flexForm
     */
    protected function isFlexFormOutdated(array $flexForm): bool
    {
        if (!is_array($flexForm['data'] ?? null)) {
            return false;
        }

        return array_key_exists('sDEFAULT', $flexForm['data'])
            || array_key_exists('switchableControllerActions', $flexForm['data']['sDEF']['lDEF'] ?? []);
    }

    /**
     * It's not a must-have, but sDEF seems to be more default than sDEFAULT as first sheet name in TYPO3
     *
     * @param array<string, mixed> &$valueFromDatabase
     */
    protected function moveSheetDefaultToDef(array &$valueFromDatabase): void
    {
        if (array_key_exists('sDEFAULT', $valueFromDatabase['data'])) {
            foreach ($valueFromDatabase['data']['sDEFAULT']['lDEF'] as $field => $value) {
                $this->moveFieldFromOldToNewSheet($valueFromDatabase, $field, 'sDEFAULT', 'sDEF');
            }

            // Remove old sheet completely
            unset($valueFromDatabase['data']['sDEFAULT']);
        }
    }

    /**
     * Move field from one sheet to another and remove field from old location
     *
     * @param array<string, mixed> &$valueFromDatabase
     */
    protected function moveFieldFromOldToNewSheet(
        array &$valueFromDatabase,
        string $field,
        string $oldSheet,
        string $newSheet,
    ): void {
        if (array_key_exists($field, $valueFromDatabase['data'][$oldSheet]['lDEF'])) {
            // Create base sheet, if not exist
            if (!array_key_exists($newSheet, $valueFromDatabase['data'])) {
                $valueFromDatabase['data'][$newSheet] = [
                    'lDEF' => [],
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
     * @param array<string, mixed> $array Array with FlexForm data
     * @return string Input array converted to XML
     */
    public function checkValue_flexArray2Xml(array $array): string
    {
        return GeneralUtility::makeInstance(FlexFormTools::class)
            ->flexArray2Xml($array);
    }
}
