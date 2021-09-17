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
 * Use this event, if you want to modify the query of GlossaryRepository::getGlossaries.
 */
class ModifyQueryOfGetGlossariesEvent
{
    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }
}
