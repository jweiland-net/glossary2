<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function ($extKey) {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'JWeiland.' . $extKey,
        'Glossary',
        array(
            'Glossary' => 'list, listWithoutGlossar, show',

        ),
        // non-cacheable actions
        array(
            'Glossary' => '',
        )
    );

    // Register SVG Icon Identifier
    $svgIcons = [
        'ext-glossary2-wizard-icon' => 'plugin_wizard.svg',
    ];
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    foreach ($svgIcons as $identifier => $fileName) {
        $iconRegistry->registerIcon(
            $identifier,
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:glossary2/Resources/Public/Icons/' . $fileName]
        );
    }

    // add glossary2 plugin to new element wizard
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:glossary2/Configuration/TSconfig/ContentElementWizard.txt">');
}, $_EXTKEY);
