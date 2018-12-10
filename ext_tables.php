<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_glossary2_domain_model_glossary',
    'EXT:glossary2/Resources/Private/Language/locallang_csh_tx_glossary2_domain_model_glossary.xlf'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_glossary2_domain_model_glossary');
