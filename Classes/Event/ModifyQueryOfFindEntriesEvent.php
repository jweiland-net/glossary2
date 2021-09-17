<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Event;

use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/*
 * Use this event, if you want to modify the query of GlossaryRepository::findEntries.
 */
class ModifyQueryOfFindEntriesEvent
{
    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var array
     */
    protected $categories = [];

    /**
     * @var string
     */
    protected $letter = '';

    public function __construct(
        QueryBuilder $queryBuilder,
        array $categories,
        string $letter
    ) {
        $this->queryBuilder = $queryBuilder;
        $this->categories = $categories;
        $this->letter = $letter;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
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
