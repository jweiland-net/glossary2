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
 * By default we only map a hand full of letters like ä => a.
 * If you need to map more letters like á => a you have to use this event.
 */
class ModifyLetterMappingEvent
{
    /**
     * @var array
     */
    protected $letterMapping = [];

    public function __construct(
        array $letterMapping
    ) {
        $this->letterMapping = $letterMapping;
    }

    public function getLetterMapping(): array
    {
        return $this->letterMapping;
    }

    public function setLetterMapping(array $letterMapping): void
    {
        $this->letterMapping = $letterMapping;
    }
}
