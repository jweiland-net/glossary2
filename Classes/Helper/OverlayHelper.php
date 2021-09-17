<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Helper;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/**
 * Helper to add where clause for translations and workspaes to QueryBuider
 */
class OverlayHelper
{
    /**
     * @var Context
     */
    protected $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function addWhereForOverlay(QueryBuilder $queryBuilder, string $tableName, string $tableAlias): void
    {
        $this->addWhereForWorkspaces($queryBuilder, $tableName, $tableAlias);
        $this->addWhereForTranslation($queryBuilder, $tableName, $tableAlias);
    }

    protected function addWhereForWorkspaces(QueryBuilder $queryBuilder, string $tableName, string $tableAlias): void
    {
        if ($GLOBALS['TCA'][$tableName]['ctrl']['versioningWS']) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq($tableAlias . '.t3ver_oid', 0)
            );
        }
    }

    protected function addWhereForTranslation(QueryBuilder $queryBuilder, string $tableName, string $tableAlias): void
    {
        // Column: sys_language_uid
        $languageField = $GLOBALS['TCA'][$tableName]['ctrl']['languageField'];
        // Column: l10n_parent
        $transOrigPointerField = $GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'] ?? '';


        if ($this->getLanguageAspect()->doOverlays()) {
            // Get default language
            // sys_language_uid = 0
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq($tableAlias . '.' . $languageField, 0)
            );
        } else {
            // strict mode
            // sys_language_uid = {requestedLanguageUid} AND l10n_parent = 0
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    $tableAlias . '.' . $languageField,
                    $this->getLanguageAspect()->getContentId()
                ),
                $queryBuilder->expr()->eq(
                    $tableAlias . '.' . $transOrigPointerField,
                    0
                )
            );
        }
    }

    protected function getLanguageAspect(): LanguageAspect
    {
        return $this->context->getAspect('language');
    }
}
