<?php
namespace JWeiland\Glossary2\Tests\Unit\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Stefan Froemken <projects@jweiland.net>, jweiland.net
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Core\Tests\UnitTestCase;
use JWeiland\Glossary2\Domain\Model\Glossary;

/**
 * Test case.
 *
 * @subpackage Events
 * @author Stefan Froemken <projects@jweiland.net>
 */
class GlossaryTest extends UnitTestCase {

	/**
	 * @var \JWeiland\Glossary2\Domain\Model\Glossary
	 */
	protected $subject;

	/**
	 * set up
	 *
	 * @return void
	 */
	public function setUp() {
		$this->subject = new Glossary();
	}

	/**
	 * tear down
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function getTitleInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function setTitleSetsTitle() {
		$this->subject->setTitle('foo bar');

		$this->assertSame(
			'foo bar',
			$this->subject->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function setTitleWithIntegerResultsInString() {
		$this->subject->setTitle(123);
		$this->assertSame('123', $this->subject->getTitle());
	}

	/**
	 * @test
	 */
	public function setTitleWithBooleanResultsInString() {
		$this->subject->setTitle(TRUE);
		$this->assertSame('1', $this->subject->getTitle());
	}

	/**
	 * @test
	 */
	public function getDescriptionInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->subject->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function setDescriptionSetsDescription() {
		$this->subject->setDescription('foo bar');

		$this->assertSame(
			'foo bar',
			$this->subject->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function setDescriptionWithIntegerResultsInString() {
		$this->subject->setDescription(123);
		$this->assertSame('123', $this->subject->getDescription());
	}

	/**
	 * @test
	 */
	public function setDescriptionWithBooleanResultsInString() {
		$this->subject->setDescription(TRUE);
		$this->assertSame('1', $this->subject->getDescription());
	}

	/**
	 * @test
	 */
	public function getImagesInitiallyReturnsNull() {
		$this->assertNull($this->subject->getImages());
	}

	/**
	 * @test
	 */
	public function setImagesSetsImages() {
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
	public function getCategoriesInitiallyReturnsNull() {
		$this->assertNull($this->subject->getCategories());
	}

	/**
	 * @test
	 */
	public function setCategoriesSetsCategories() {
		$instance = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$this->subject->setCategories($instance);

		$this->assertSame(
			$instance,
			$this->subject->getCategories()
		);
	}

}