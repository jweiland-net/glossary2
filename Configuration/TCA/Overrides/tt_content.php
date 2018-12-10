<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['glossary2_glossary'] = 'select_key';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['glossary2_glossary'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'glossary2_glossary',
    'FILE:EXT:glossary2/Configuration/FlexForms/Glossary.xml'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JWeiland.glossary2',
    'Glossary',
    'LLL:EXT:glossary2/Resources/Private/Language/locallang_db.xlf:plugin.title'
);
