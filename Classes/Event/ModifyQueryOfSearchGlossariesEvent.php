<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Event;

use JWeiland\Glossary2\Domain\Model\Glossary;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/*
 * Use this event, if you want to modify the query of GlossaryRepository::searchGlossaries.
 */
class ModifyQueryOfSearchGlossariesEvent
{
    /**
     * @var QueryResultInterface<int, Glossary>
     */
    protected QueryResultInterface $queryResult;

    /**
     * @var array<int>
     */
    protected array $categories = [];

    protected string $letter = '';

    /**
     * @param QueryResultInterface<int, Glossary> $extbaseQuery
     * @param array<int> $categories
     */
    public function __construct(
        QueryResultInterface $extbaseQuery,
        array $categories,
        string $letter,
    ) {
        $this->queryResult = $extbaseQuery;
        $this->categories = $categories;
        $this->letter = $letter;
    }

    /**
     * @return QueryResultInterface<int, Glossary>
     */
    public function getQueryResult(): QueryResultInterface
    {
        return $this->queryResult;
    }

    /**
     * @return array<int>
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    public function getLetter(): string
    {
        return $this->letter;
    }
}
