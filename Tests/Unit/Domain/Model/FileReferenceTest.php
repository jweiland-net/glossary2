<?php
namespace JWeiland\Glossary2\Tests\Unit\Domain\Model;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use JWeiland\Glossary2\Domain\Model\FileReference;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Test case.
 *
 * @subpackage Events
 * @author Stefan Froemken <projects@jweiland.net>
 */
class FileReferenceTest extends UnitTestCase
{
    /**
     * @var \JWeiland\Glossary2\Domain\Model\FileReference
     */
    protected $subject;

    /**
     * set up
     *
     * @return void
     */
    public function setUp()
    {
        $this->subject = new FileReference();
    }

    /**
     * tear down
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->subject);
    }

    /**
     * @test
     */
    public function getTitleInitiallyReturnsEmptyString()
    {
        $this->assertSame(
            '',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function setTitleSetsTitle()
    {
        $this->subject->setTitle('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function setTitleWithIntegerResultsInString()
    {
        $this->subject->setTitle(123);
        $this->assertSame('123', $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function setTitleWithBooleanResultsInString()
    {
        $this->subject->setTitle(true);
        $this->assertSame('1', $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function getCruserIdInitiallyReturnsZero()
    {
        $this->assertSame(
            0,
            $this->subject->getCruserId()
        );
    }

    /**
     * @test
     */
    public function setCruserIdSetsCruserId()
    {
        $this->subject->setCruserId(123456);

        $this->assertSame(
            123456,
            $this->subject->getCruserId()
        );
    }

    /**
     * @test
     */
    public function getUidLocalInitiallyReturnsZero()
    {
        $this->assertSame(
            0,
            $this->subject->getUidLocal()
        );
    }

    /**
     * @test
     */
    public function setUidLocalSetsUidLocal()
    {
        $this->subject->setUidLocal(123456);

        $this->assertSame(
            123456,
            $this->subject->getUidLocal()
        );
    }

    /**
     * @test
     */
    public function getTablenamesInitiallyReturnsEmptyString()
    {
        $this->assertSame(
            '',
            $this->subject->getTablenames()
        );
    }

    /**
     * @test
     */
    public function setTablenamesSetsTablenames()
    {
        $this->subject->setTablenames('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getTablenames()
        );
    }

    /**
     * @test
     */
    public function setTablenamesWithIntegerResultsInString()
    {
        $this->subject->setTablenames(123);
        $this->assertSame('123', $this->subject->getTablenames());
    }

    /**
     * @test
     */
    public function setTablenamesWithBooleanResultsInString()
    {
        $this->subject->setTablenames(true);
        $this->assertSame('1', $this->subject->getTablenames());
    }
}
