<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$pluginSignature = ExtensionUtility::registerPlugin(
    'glossary2',
    'Glossary',
    'LLL:EXT:glossary2/Resources/Private/Language/locallang_db.xlf:plugin.glossary.title'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['glossary2_glossary'] = 'pi_flexform';

ExtensionManagementUtility::addPiFlexFormValue(
    $pluginSignature,
    'FILE:EXT:glossary2/Configuration/FlexForms/Glossary.xml'
);
