<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tt_content.pi_flexform.glossary2_glossary.list',
    'EXT:glossary2/Resources/Private/Language/locallang_csh_flexform.xlf'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_glossary2_domain_model_glossary',
    'EXT:glossary2/Resources/Private/Language/locallang_csh_tx_glossary2_domain_model_glossary.xlf'
);
