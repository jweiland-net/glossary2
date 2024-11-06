<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Event;

/*
 * Use this event, if you want to modify the query of GlossaryRepository::findEntries.
 */
class PostProcessFirstLettersEvent
{
    /**
     * @var array<string, mixed>
     */
    protected array $firstLetters = [];

    /**
     * @param array<string> $firstLetters
     */
    public function __construct(array $firstLetters)
    {
        $this->firstLetters = $firstLetters;
    }

    /**
     * @return array<string, mixed>
     */
    public function getFirstLetters(): array
    {
        return $this->firstLetters;
    }
}
