<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(static function () {
    $GLOBALS['TCA']['tx_glossary2_domain_model_glossary']['columns']['categories'] = [
        'config' => [
            'type' => 'category',
            'relationship' => 'oneToOne'
        ]
    ];
});
