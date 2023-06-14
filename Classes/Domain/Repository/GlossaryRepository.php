<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Domain\Repository;

use JWeiland\Glossary2\Event\ModifyQueryOfSearchGlossariesEvent;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * This class contains all queries to get needed glossary entries from DB
 */
class GlossaryRepository extends Repository
{
    /**
     * @var array
     */
    protected $defaultOrderings = [
        'title' => QueryInterface::ORDER_ASCENDING,
    ];

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    public function injectEventDispatcher(EventDispatcher $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function searchGlossaries(array $categories = [], string $letter = ''): QueryResultInterface
    {
        // Set respectSysLanguage to false, to keep our already translated records
        $query = $this->createQuery();
        // $query->getQuerySettings()->setRespectSysLanguage(false);

        $constraints = [];
        if ($this->checkArgumentsForSearchGlossaries($categories, $letter)) {
            if ($categories !== []) {
                $constraints[] = $query->in('categories.uid', $categories);
            }

            // Add letter to constraint
            if ($letter !== '') {
                if ($letter === '0-9') {
                    $letterConstraint = [];
                    for ($i = 0; $i < 10; $i++) {
                        $letterConstraint[] = $query->like('title', $i . '%');
                    }
                    $constraints[] = $query->logicalOr(...$letterConstraint);
                } else {
                    $constraints[] = $query->like('title', $letter . '%');
                }
            }
        }

        $queryResult = $query->execute();
        if ($constraints !== []) {
            $queryResult = $query->matching($query->logicalAnd(...$constraints))->execute();
        }

        $this->eventDispatcher->dispatch(
            new ModifyQueryOfSearchGlossariesEvent($queryResult, $categories, $letter)
        );

        return $queryResult;
    }

    /**
     * Prepare an Extbase QueryResult for GlossaryService (A-Z navigation)
     */
    public function getExtbaseQueryForGlossary(array $categories = []): QueryResultInterface
    {
        $query = $this->createQuery();

        if (
            $this->checkArgumentsForSearchGlossaries($categories, '')
            && $categories !== []
        ) {
            return $query->matching($query->in('categories.uid', $categories))->execute();
        }

        return $query->execute();
    }

    protected function checkArgumentsForSearchGlossaries(array $categories, string $letter): bool
    {
        // Check categories. Cast category UIDs to int and remove empty values
        $intCategories = GeneralUtility::intExplode(
            ',',
            implode(
                ',',
                $categories
            ),
            true
        );

        if ($intCategories !== $categories) {
            return false;
        }

        if (in_array(0, $intCategories, true)) {
            return false;
        }

        // Check letter
        if ($letter !== '' && !preg_match('@^0-9$|^[a-z]$@', $letter)) {
            return false;
        }

        return true;
    }
}
