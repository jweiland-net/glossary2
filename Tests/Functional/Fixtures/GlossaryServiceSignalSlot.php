<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Tests\Functional\Fixtures;

/**
 * Test file to map french letters in glossary
 */
class GlossaryServiceSignalSlot
{
    public function modifyLetterMapping(array $letterMapping): array
    {
        $letterMapping['à'] = 'a';
        $letterMapping['è'] = 'e';
        $letterMapping['ù'] = 'u';

        return [$letterMapping];
    }

    public function postProcessFirstLetters(array &$firstLetters, $queryBuilder): void
    {
        $key = array_search('a', $firstLetters);
        unset($firstLetters[$key]);
        $firstLetters[] = 'k';
    }
}
