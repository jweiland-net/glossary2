<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Updater;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Controller\Action\Tool\UpgradeWizard;
use TYPO3\CMS\Install\Updates\AbstractUpdate;

/**
 * With glossary2 3.0.0 we have changed some FlexForm Settings.
 * This Updater converts existing settings to new version.
 */
class MoveOldFlexFormSettings87Updater extends AbstractUpdate
{
    /**
     * @var MoveOldFlexFormSettingsUpdater
     */
    protected $flexFormUpdate;

    public function __construct(
        string $identifier,
        int $versionAsInt,
        string $userInput = null,
        UpgradeWizard $parentObject = null,
        MoveOldFlexFormSettingsUpdater $flexFormUpdate = null
    ) {
        if ($flexFormUpdate === null) {
            $flexFormUpdate = GeneralUtility::makeInstance(MoveOldFlexFormSettingsUpdater::class);
        }
        $this->flexFormUpdate = $flexFormUpdate;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->flexFormUpdate->getIdentifier();
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->flexFormUpdate->getTitle();
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->flexFormUpdate->getDescription();
    }

    /**
     * Checks whether updates are required.
     *
     * @param string &$description The description for the update
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     */
    public function checkForUpdate(&$description): bool
    {
        $description = $this->getDescription();
        return $this->flexFormUpdate->updateNecessary();
    }

    /**
     * Performs the accordant updates.
     *
     * @param array &$dbQueries Queries done in this update
     * @param string &$customMessage Custom message
     * @return bool Whether everything went smoothly or not
     */
    public function performUpdate(array &$dbQueries, &$customMessage): bool
    {
        return $this->flexFormUpdate->executeUpdate();
    }
}
