<?php

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

if (!defined('TYPO3')) {
    die('Access denied.');
}

use JWeiland\Glossary2\Controller\GlossaryController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

call_user_func(static function (): void {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Glossary2',
        'Glossary',
        [
            GlossaryController::class => 'list, listWithoutGlossar, show',
        ],
        [],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
    );
});
