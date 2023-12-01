<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(static function () {
    $GLOBALS['TCA']['tx_glossary2_domain_model_glossary']['columns']['categories'] = [
        'config' => [
            'type' => 'category'
        ]
    ];

    ExtensionManagementUtility::addToAllTCAtypes(
        'tx_glossary2_domain_model_glossary',
        'categories'
    );
});
