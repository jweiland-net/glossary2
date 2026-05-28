<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Backend\Preview;

use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Collection\LazyRecordCollection;
use TYPO3\CMS\Core\Collection\RecordCollectionInterface;
use TYPO3\CMS\Core\Domain\RecordInterface;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

final class PluginPreview extends StandardContentPreviewRenderer
{
    private const PREVIEW_TEMPLATE = 'EXT:glossary2/Resources/Private/Templates/PluginPreview/GlossaryPluginPreview.fluid.html';

    private const ALLOWED_PLUGINS = [
        'glossary2_glossary',
    ];

    public function __construct(
        private readonly ViewFactoryInterface $viewFactory,
    ) {}

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $ttContentRecord = $item->getRecord();
        if (!$this->isValidPlugin($ttContentRecord)) {
            return '';
        }

        $view = $this->viewFactory->create(
            new ViewFactoryData(
                templatePathAndFilename: self::PREVIEW_TEMPLATE,
            ),
        );
        $view->assignMultiple($ttContentRecord->toArray());

        $this->addPluginName($view, $ttContentRecord->toArray());

        // Add data from column pi_flexform
        $piFlexformData = $this->getPiFlexformData($ttContentRecord->toArray());
        if ($piFlexformData !== []) {
            $view->assign('pi_flexform_transformed', $piFlexformData);
        }

        return $view->render();
    }

    private function isValidPlugin(RecordInterface $ttContentRecord): bool
    {
        $rawRecord = $ttContentRecord->toArray();

        if (!isset($rawRecord['CType'])) {
            return false;
        }
        return in_array($rawRecord['CType'], self::ALLOWED_PLUGINS, true);
    }

    /**
     * @param array<string, mixed> $ttContentRecord
     */
    private function addPluginName(ViewInterface $view, array $ttContentRecord): void
    {
        $langKey = sprintf(
            'plugin.%s.title',
            str_replace('glossary2_', '', $ttContentRecord['CType']),
        );

        $view->assign(
            'pluginName',
            LocalizationUtility::translate('LLL:EXT:glossary2/Resources/Private/Language/locallang_db.xlf:' . $langKey),
        );
    }

    /**
     * @param array<string, mixed> $ttContentRecord
     * @return array<string, mixed>
     */
    private function getPiFlexformData(array $ttContentRecord): array
    {
        $data = [];

        $flexformArray = [];
        if (isset($ttContentRecord['pi_flexform']) && method_exists($ttContentRecord['pi_flexform'], 'toArray')) {
            $flexformArray = $ttContentRecord['pi_flexform']->toArray();
        } elseif (isset($ttContentRecord['pi_flexform']) && is_array($ttContentRecord['pi_flexform'])) {
            $flexformArray = $ttContentRecord['pi_flexform'];
        }

        $rawSettings = $flexformArray['sDEF']['settings'] ?? [];
        $cleanedSettings = [];

        if ($rawSettings !== []) {
            foreach ($rawSettings as $key => $value) {
                if ($value instanceof LazyRecordCollection || $value instanceof RecordCollectionInterface) {
                    $uids = [];
                    foreach ($value as $record) {
                        if (method_exists($record, 'getUid')) {
                            $uids[] = $record->getUid();
                        }
                    }
                    $cleanedSettings[$key] = $uids === [] ? $this->extractFieldValueFromCollection($value) : implode(',', $uids);
                } else {
                    $cleanedSettings[$key] = $value;
                }
            }
        }

        if ($cleanedSettings !== []) {
            $data['settings'] = $cleanedSettings;
        }

        return $data;
    }

    private function extractFieldValueFromCollection(object $collection): string
    {
        try {
            $reflection = new \ReflectionClass($collection);
            if ($reflection->hasProperty('fieldValue')) {
                $property = $reflection->getProperty('fieldValue');

                return (string)$property->getValue($collection);
            }
        } catch (\ReflectionException) {
        }

        return '';
    }
}
