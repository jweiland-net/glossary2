<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Updater;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * With glossary2 3.0.0 we have changed some FlexForm Settings.
 * This Updater converts existing settings to new version.
 */
class MoveOldFlexFormSettings94Updater implements UpgradeWizardInterface
{
    /**
     * @var MoveOldFlexFormSettingsUpdater
     */
    protected $flexFormUpdate;

    public function __construct(MoveOldFlexFormSettingsUpdater $flexFormUpdate = null)
    {
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
     * @return bool
     */
    public function updateNecessary(): bool
    {
        return $this->flexFormUpdate->updateNecessary();
    }

    /**
     * @return bool
     */
    public function executeUpdate(): bool
    {
        return $this->flexFormUpdate->executeUpdate();
    }

    /**
     * @return string[]
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class
        ];
    }
}
