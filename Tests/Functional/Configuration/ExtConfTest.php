<?php

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Tests\Functional\Configuration;

use JWeiland\Glossary2\Configuration\ExtConf;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case
 */
class ExtConfTest extends FunctionalTestCase
{
    protected ExtConf $subject;

    protected array $testExtensionsToLoad = [
        'jweiland/glossary2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = GeneralUtility::makeInstance(ExtConf::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
        );
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getPossibleLettersWillReturnDefaultLetters(): void
    {
        self::assertSame(
            '0-9,a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z',
            $this->subject->getPossibleLetters(),
        );
    }

    /**
     * @test
     */
    public function setPossibleLettersWillSetPossibleLetters(): void
    {
        $this->subject->setPossibleLetters('a,b,c');
        self::assertSame(
            'a,b,c',
            $this->subject->getPossibleLetters(),
        );
    }
}
