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
 * Use this event, if you want to add or remove first letters, which were
 * found in the database, before the A-Z navigation is built.
 */
class PostProcessFirstLettersEvent
{
    /**
     * @var array<int, string>
     */
    protected array $firstLetters = [];

    /**
     * @param array<int, string> $firstLetters
     */
    public function __construct(array $firstLetters)
    {
        $this->firstLetters = $firstLetters;
    }

    /**
     * @return array<int, string>
     */
    public function getFirstLetters(): array
    {
        return $this->firstLetters;
    }

    /**
     * @param array<int, string> $firstLetters
     */
    public function setFirstLetters(array $firstLetters): void
    {
        $this->firstLetters = $firstLetters;
    }
}
