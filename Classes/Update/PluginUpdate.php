<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Update;

use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\AbstractListTypeToCTypeUpdate;

/**
 * Updater to fill empty slug columns of glossary records
 */
#[UpgradeWizard('glossaryPluginUpdate')]
class PluginUpdate extends AbstractListTypeToCTypeUpdate
{
    protected function getListTypeToCTypeMapping(): array
    {
        return [
            'glossary2_glossary' => 'glossary2_glossary',
        ];
    }

    public function getTitle(): string
    {
        return 'EXT:glossary2: Migrate plugins list types to CType glossary2_glossary';
    }

    public function getDescription(): string
    {
        return 'This update wizard migrates all existing plugin with list_type to CType';
    }
}
