<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Helper;

use JWeiland\Glossary2\Event\SanitizeValueForCharsetHelperEvent;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;

/**
 * Helper to convert chars like ä, á, ß to its ASCII representation a, a, s
 */
class CharsetHelper
{
    protected CharsetConverter $charsetConverter;

    protected EventDispatcher $eventDispatcher;

    public function __construct(CharsetConverter $charsetConverter, EventDispatcher $eventDispatcher)
    {
        $this->charsetConverter = $charsetConverter;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Sanitize value by an automatism.
     * If you need, you can implement further sanitizing for chars the automatism does not respect.
     */
    public function sanitize(string $value): string
    {
        // This should sanitize the most values to ASCII
        $preSanitizedValue = $this->charsetConverter->specCharsToASCII(
            'utf-8',
            mb_strtolower($value, 'utf-8'),
        );

        /** @var SanitizeValueForCharsetHelperEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new SanitizeValueForCharsetHelperEvent($preSanitizedValue),
        );

        return $event->getValue();
    }
}
