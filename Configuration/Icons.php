<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */
return [
    'ext-glossary2-wizard-icon' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:glossary2/Resources/Public/Icons/plugin_wizard.svg',
    ],
];
