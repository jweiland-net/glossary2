<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['glossary2_glossary'] = 'pi_flexform';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'glossary2_glossary',
    'FILE:EXT:glossary2/Configuration/FlexForms/Glossary.xml'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'glossary2',
    'Glossary',
    'LLL:EXT:glossary2/Resources/Private/Language/locallang_db.xlf:plugin.glossary.title'
);
