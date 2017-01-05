<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'JWeiland.' . $_EXTKEY,
    'Glossary',
    array(
        'Glossary' => 'list, listWithoutGlossar, show',
        
    ),
    // non-cacheable actions
    array(
        'Glossary' => '',
    )
);
