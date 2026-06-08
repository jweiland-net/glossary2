<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */
return [
    'ctrl' => [
        'title' => 'LLL:EXT:glossary2/Resources/Private/Language/locallang_db.xlf:tx_glossary2_domain_model_glossary',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'translationSource' => 'l10n_source',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'iconfile' => 'EXT:glossary2/Resources/Public/Icons/tx_glossary2_domain_model_glossary.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => '--palette--;;language, --palette--;;titleHidden,
            path_segment, description, images, categories,
            --div--;core.form.tabs:access,
            --palette--;;access',
        ],
    ],
    'palettes' => [
        'language' => ['showitem' => 'sys_language_uid, l10n_parent'],
        'titleHidden' => ['showitem' => 'title, hidden'],
        'access' => [
            'showitem' => 'starttime;core.db.general:starttime,endtime;core.db.general:endtime',
        ],
    ],
    'columns' => [
        'title' => [
            'exclude' => true,
            'label' => 'LLL:EXT:glossary2/Resources/Private/Language/locallang_db.xlf:tx_glossary2_domain_model_glossary.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
            ],
        ],
        'path_segment' => [
            'label' => 'LLL:EXT:glossary2/Resources/Private/Language/locallang_db.xlf:tx_glossary2_domain_model_glossary.path_segment',
            'displayCond' => 'VERSION:IS:false',
            'config' => [
                'type' => 'slug',
                'size' => 50,
                'generatorOptions' => [
                    'fields' => ['title'],
                    'prefixParentPageSlug' => false,
                    'replacements' => [
                        '/' => '-',
                    ],
                ],
                'fallbackCharacter' => '-',
                'eval' => 'unique',
                'default' => '',
                'searchable' => false,
            ],
        ],
        'description' => [
            'exclude' => true,
            'label' => 'LLL:EXT:glossary2/Resources/Private/Language/locallang_db.xlf:tx_glossary2_domain_model_glossary.description',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'softref' => 'typolink_tag,email[subst],url',
                'enableRichtext' => true,
            ],
        ],
        'images' => [
            'exclude' => true,
            'label' => 'LLL:EXT:glossary2/Resources/Private/Language/locallang_db.xlf:tx_glossary2_domain_model_glossary.images',
            'config' => [
                'type' => 'file',
                'minitems' => 0,
                'maxitems' => 5,
                'allowed' => 'common-image-types',
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
                'appearance' => [
                    'createNewRelationLinkTitle' => 'LLL:EXT:glossary2/Resources/Private/Language/locallang_db.xlf:tx_glossary2_domain_model_glossary.images.add',
                    'showPossibleLocalizationRecords' => true,
                    'showAllLocalizationLink' => true,
                    'showSynchronizationLink' => true,
                ],
            ],
        ],
        'categories' => [
            'exclude' => true,
            'config' => [
                'type' => 'category',
            ],
        ],
    ],
];
