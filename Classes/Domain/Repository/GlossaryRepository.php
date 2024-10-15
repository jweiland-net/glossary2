<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Domain\Repository;

use JWeiland\Glossary2\Domain\Model\Glossary;
use JWeiland\Glossary2\Event\ModifyQueryOfSearchGlossariesEvent;
use Psr\EventDispatcher\EventDispatcherInterface as EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * This class contains all queries to get needed glossary entries from DB
 *
 * @extends Repository<Glossary>
 */
class GlossaryRepository extends Repository
{
    protected $defaultOrderings = [
        'title' => QueryInterface::ORDER_ASCENDING,
    ];

    protected EventDispatcher $eventDispatcher;

    public function injectEventDispatcher(EventDispatcher $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /** @phpstan-ignore-next-line */
    public function searchGlossaries(array $categories = [], string $letter = ''): QueryResultInterface
    {
        $query = $this->createQuery();

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
            new ModifyQueryOfSearchGlossariesEvent($queryResult, $categories, $letter),
        );

        return $queryResult;
    }

    /**
     * Prepare an Extbase QueryResult for GlossaryService (A-Z navigation)
     * @param array $categories
     *
     * @throws InvalidQueryException
     */
    /** @phpstan-ignore-next-line */
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

    /**
     * @param array<int> $categories
     */
    protected function checkArgumentsForSearchGlossaries(array $categories, string $letter): bool
    {
        // Check categories. Cast category UIDs to int and remove empty values
        $intCategories = GeneralUtility::intExplode(
            ',',
            implode(
                ',',
                $categories,
            ),
            true,
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
