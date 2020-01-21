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
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Test case.
 */
class GlossaryTest extends UnitTestCase
{
    /**
     * @var Glossary
     */
    protected $subject;

    /**
     * set up
     */
    public function setUp()
    {
        $this->subject = new Glossary();
    }

    /**
     * tear down
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
    public function getImagesInitiallyReturnsObjectStorage() {
        $this->assertEquals(
            new ObjectStorage(),
            $this->subject->getImages()
        );
    }

    /**
     * @test
     */
    public function setImagesSetsImages() {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setImages($objectStorage);

        $this->assertSame(
            $objectStorage,
            $this->subject->getImages()
        );
    }

    /**
     * @test
     */
    public function addImageAddsOneImage() {
        $objectStorage = new ObjectStorage();
        $this->subject->setImages($objectStorage);

        $object = new FileReference();
        $this->subject->addImage($object);

        $objectStorage->attach($object);

        $this->assertSame(
            $objectStorage,
            $this->subject->getImages()
        );
    }

    /**
     * @test
     */
    public function removeImageRemovesOneImage() {
        $object = new FileReference();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setImages($objectStorage);

        $this->subject->removeImage($object);
        $objectStorage->detach($object);

        $this->assertSame(
            $objectStorage,
            $this->subject->getImages()
        );
    }

    /**
     * @test
     */
    public function getCategoriesInitiallyReturnsObjectStorage() {
        $this->assertEquals(
            new ObjectStorage(),
            $this->subject->getCategories()
        );
    }

    /**
     * @test
     */
    public function setCategoriesSetsCategories() {
        $object = new Category();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setCategories($objectStorage);

        $this->assertSame(
            $objectStorage,
            $this->subject->getCategories()
        );
    }

    /**
     * @test
     */
    public function addCategoryAddsOneCategory() {
        $objectStorage = new ObjectStorage();
        $this->subject->setCategories($objectStorage);

        $object = new Category();
        $this->subject->addCategory($object);

        $objectStorage->attach($object);

        $this->assertSame(
            $objectStorage,
            $this->subject->getCategories()
        );
    }

    /**
     * @test
     */
    public function removeCategoryRemovesOneCategory() {
        $object = new Category();
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object);
        $this->subject->setCategories($objectStorage);

        $this->subject->removeCategory($object);
        $objectStorage->detach($object);

        $this->assertSame(
            $objectStorage,
            $this->subject->getCategories()
        );
    }
}
