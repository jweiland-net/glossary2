<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

$pluginSignature = ExtensionUtility::registerPlugin(
    'glossary2',
    'Glossary',
    'LLL:EXT:glossary2/Resources/Private/Language/locallang_db.xlf:plugin.glossary.title',
    'ext-glossary2-wizard-icon',
    'plugins',
    'LLL:EXT:glossary2/Resources/Private/Language/locallang_db.xlf:plugin.glossary.description',
    'FILE:EXT:glossary2/Configuration/FlexForms/Glossary.xml',
);
