<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'JWeiland.' . $_EXTKEY,
	'Glossary',
	'LLL:EXT:glossary2/Resources/Private/Language/locallang_db.xlf:plugin.title'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Glossary 2');

// load tt_content to $TCA array and add flexform
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY . '_glossary'] = 'layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY . '_glossary'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($_EXTKEY.'_glossary', 'FILE:EXT:' . $_EXTKEY.'/Configuration/FlexForms/Glossary.xml');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_glossary2_domain_model_glossary', 'EXT:glossary2/Resources/Private/Language/locallang_csh_tx_glossary2_domain_model_glossary.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_glossary2_domain_model_glossary');