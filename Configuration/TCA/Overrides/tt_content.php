<?php

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

if (!defined('TYPO3')) {
    die('Access denied.');
}

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

call_user_func(static function () {
    $pluginSignature = ExtensionUtility::registerPlugin(
        'glossary2',
        'Glossary',
        'LLL:EXT:glossary2/Resources/Private/Language/locallang_db.xlf:plugin.glossary.title',
        'ext-glossary2-wizard-icon',
        'plugins',
        'LLL:EXT:glossary2/Resources/Private/Language/locallang_db.xlf:plugin.glossary.description',
    );

    ExtensionManagementUtility::addToAllTCAtypes(
        'tt_content',
        '--div--;Configuration,pi_flexform,',
        $pluginSignature,
        'after:subheader',
    );

    ExtensionManagementUtility::addPiFlexFormValue(
        '*',
        'FILE:EXT:glossary2/Configuration/FlexForms/Glossary.xml',
        $pluginSignature,
    );

    $GLOBALS['TCA']['tt_content']['types']['glossary2_glossary']['showitem'] = '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;headers,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.plugin,
            pi_flexform,
            pages,
            recursive,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
            --palette--;;frames,
            --palette--;;appearanceLinks,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
            --palette--;;language,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
            categories,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
            rowDescription,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
    ';
});
