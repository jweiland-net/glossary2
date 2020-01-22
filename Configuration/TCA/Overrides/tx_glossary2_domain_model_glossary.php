<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function () {
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

    if (version_compare(TYPO3_branch, '9.4', '>=')) {
        // Router configuration can not access sanitize() method of slugs, so we have to create our own column
        $GLOBALS['TCA']['tx_glossary2_domain_model_glossary']['columns']['path_segment'] = [
            'exclude' => true,
            'label' => 'LLL:EXT:glossary2/Resources/Private/Language/locallang_db.xlf:tx_glossary2_domain_model_glossary.path_segment',
            'config' => [
                'type' => 'slug',
                'size' => 50,
                'generatorOptions' => [
                    'fields' => ['title'],
                    // Default fieldSeparator is / which is not allowed within path_segments
                    'fieldSeparator' => '-',
                    // As pageSlug may contain slashes, we have to remove page slug
                    'prefixParentPageSlug' => false,
                    'replacements' => [
                        '/' => '-'
                    ],
                ],
                'fallbackCharacter' => '-',
                // Do not add / in path_segments, as they are not allowed in RouteEnhancer configuration
                'prependSlash' => false,
                'eval' => 'uniqueInSite',
                'default' => ''
            ]
        ];
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
            'tx_glossary2_domain_model_glossary',
            'path_segment',
            '',
            'after:title'
        );
        $GLOBALS['TCA']['tx_glossary2_domain_model_glossary']['interface']['showRecordFieldList'] .= ',path_segment';
    }
});
