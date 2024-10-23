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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(static function (): void {
    $GLOBALS['TCA']['tx_glossary2_domain_model_glossary']['columns']['categories'] = [
        'config' => [
            'type' => 'category',
        ],
    ];

    ExtensionManagementUtility::addToAllTCAtypes(
        'tx_glossary2_domain_model_glossary',
        'categories',
    );
});
