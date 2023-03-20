<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(static function () {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
        'glossary2',
        'tx_glossary2_domain_model_glossary',
        'categories',
        [
            'fieldConfiguration' => [
                'foreign_table_where' => ' AND sys_category.sys_language_uid IN (-1, 0) ORDER BY sys_category.title ASC',
            ],
        ]
    );
});
