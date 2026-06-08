<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Event;

/**
 * This is an event that allows post-processing of the first letters.
 * This class is designed to handle a collection of first letters, providing
 * mechanisms to manipulate or retrieve them as needed in the context of
 * post-processing operations.
 */
final class PostProcessFirstLettersEvent
{
    /**
     * @param array<string> $firstLetters
     */
    public function __construct(private array $firstLetters) {}

    /**
     * @return array<string, mixed>
     */
    public function getFirstLetters(): array
    {
        return $this->firstLetters;
    }

    /**
     * @param array<string, mixed> $firstLetters
     */
    public function setFirstLetters(array $firstLetters): void
    {
        $this->firstLetters = $firstLetters;
    }
}
