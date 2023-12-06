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
 * If you need to sanitize more letters like á => a you have to use this event.
 */
class SanitizeValueForCharsetHelperEvent
{
    protected string $value = '';

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}
