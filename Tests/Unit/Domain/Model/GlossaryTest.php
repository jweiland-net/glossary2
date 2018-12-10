<?php
namespace JWeiland\Glossary2\Tests\Unit\Domain\Model;

/*
 * This file is part of the glossary2 project.
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
use JWeiland\Glossary2\Domain\Model\Glossary;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test case.
 */
class GlossaryTest extends UnitTestCase
{
    /**
     * @var \JWeiland\Glossary2\Domain\Model\Glossary
     */
    protected $subject;

    /**
     * set up
     *
     * @return void
     */
    public function setUp()
    {
        $this->subject = new Glossary();
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
    public function getDescriptionInitiallyReturnsEmptyString()
    {
        $this->assertSame(
            '',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function setDescriptionSetsDescription()
    {
        $this->subject->setDescription('foo bar');

        $this->assertSame(
            'foo bar',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function setDescriptionWithIntegerResultsInString()
    {
        $this->subject->setDescription(123);
        $this->assertSame('123', $this->subject->getDescription());
    }

    /**
     * @test
     */
    public function setDescriptionWithBooleanResultsInString()
    {
        $this->subject->setDescription(true);
        $this->assertSame('1', $this->subject->getDescription());
    }

    /**
     * @test
     */
    public function getImagesInitiallyReturnsNull()
    {
        $this->assertNull($this->subject->getImages());
    }

    /**
     * @test
     */
    public function setImagesSetsImages()
    {
        $instance = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->subject->setImages($instance);

        $this->assertSame(
            $instance,
            $this->subject->getImages()
        );
    }

    /**
     * @test
     */
    public function getCategoriesInitiallyReturnsNull()
    {
        $this->assertNull($this->subject->getCategories());
    }

    /**
     * @test
     */
    public function setCategoriesSetsCategories()
    {
        $instance = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->subject->setCategories($instance);

        $this->assertSame(
            $instance,
            $this->subject->getCategories()
        );
    }
}
