<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Tests\Functional\Fixtures;

use JWeiland\Glossary2\Event\SanitizeValueForCharsetHelperEvent;

/**
 * Test file to map letter "e" to 0
 */
class SanitizeValueEventListener
{
    public function __invoke(SanitizeValueForCharsetHelperEvent $event): void
    {
        $letterMapping = [
            'e' => 'o',
        ];

        $event->setValue(strtr($event->getValue(), $letterMapping));
    }
}
