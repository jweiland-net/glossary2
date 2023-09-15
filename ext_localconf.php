<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(static function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Glossary2',
        'Glossary',
        [
            \JWeiland\Glossary2\Controller\GlossaryController::class => 'list, listWithoutGlossar, show',
        ]
    );

    // Register SVG Icon Identifier
    $svgIcons = [
        'ext-glossary2-wizard-icon' => 'plugin_wizard.svg',
    ];
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Imaging\IconRegistry::class
    );
    foreach ($svgIcons as $identifier => $fileName) {
    }

    // Add glossary2 plugin to new element wizard
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:glossary2/Configuration/TSconfig/ContentElementWizard.tsconfig">'
    );

    // Update old flex form settings
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['glossary2UpdateOldFlexFormFields']
        = \JWeiland\Glossary2\Updater\MoveOldFlexFormSettingsUpdater::class;
    // Update slugs of glossary records
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['glossary2UpdateSlug']
        = \JWeiland\Glossary2\Updater\GlossarySlugUpdater::class;
});
