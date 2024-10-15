<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(static function (): void {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Glossary2',
        'Glossary',
        [
            \JWeiland\Glossary2\Controller\GlossaryController::class => 'list, listWithoutGlossar, show',
        ]
    );

    // Update old flex form settings
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['glossary2UpdateOldFlexFormFields']
        = \JWeiland\Glossary2\Updater\MoveOldFlexFormSettingsUpdater::class;
    // Update slugs of glossary records
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['glossary2UpdateSlug']
        = \JWeiland\Glossary2\Updater\GlossarySlugUpdater::class;
});
