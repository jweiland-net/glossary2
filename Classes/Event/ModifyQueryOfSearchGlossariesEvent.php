<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Event;

use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/*
 * Use this event, if you want to modify the query of GlossaryRepository::searchGlossaries.
 */
class ModifyQueryOfSearchGlossariesEvent
{
    /**
     * @var QueryResultInterface
     */
    protected $queryResult;

    /**
     * @var array
     */
    protected $categories = [];

    /**
     * @var string
     */
    protected $letter = '';

    public function __construct(
        QueryResultInterface $extbaseQuery,
        array $categories,
        string $letter
    ) {
        $this->queryResult = $extbaseQuery;
        $this->categories = $categories;
        $this->letter = $letter;
    }

    public function getQueryResult(): QueryResultInterface
    {
        return $this->queryResult;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function getLetter(): string
    {
        return $this->letter;
    }
}
