<?php
namespace JWeiland\Glossary2\Tests\Unit\Domain\Repository;

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
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\Comparison;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\PropertyValue;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Test case.
 *
 * @subpackage Events
 * @author Stefan Froemken <projects@jweiland.net>
 */
class GlossaryRepositoryTest extends UnitTestCase {

	/**
	 * @var \JWeiland\Glossary2\Domain\Repository\GlossaryRepository
	 */
	protected $subject;

	/**
	 * set up
	 *
	 * @return void
	 */
	public function setUp() {
		$objectManager = new ObjectManager();
		$this->subject = $objectManager->get('JWeiland\\Glossary2\\Domain\\Repository\\GlossaryRepository');
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
	public function recordsWillBeSortedByTitleAsDefault() {
		$result = $this->subject->findAll();
		$expectedResult = array(
			'title' => QueryInterface::ORDER_ASCENDING
		);
		$this->assertSame(
			$expectedResult,
			$result->getQuery()->getOrderings()
		);
	}

	/**
	 * @test
	 */
	public function findEntriesWithZeroParametersWillCallFindAll() {
		// both methods creates a new query object
		// so we can only check by "equal" and not "same"
		$this->assertEquals(
			$this->subject->findAll(),
			$this->subject->findEntries()
		);
	}

	/**
	 * DataProvider
	 *
	 * @return array
	 */
	public function dataProviderToCheckArgumentsOfFindEntriesToBeTrue() {
		$arguments = array();
		$arguments['empty categories with empty letter'] = array(array(), '');
		$arguments['numbered categories with empty letter'] = array(array(1, 3, 12), '');
		$arguments['numbered categories with 0-9 as letter'] = array(array(1, 3, 12), '0-9');
		$arguments['numbered categories with z as letter'] = array(array(1, 3, 12), 'Z');
		return $arguments;
	}

	/**
	 * @test
	 *
	 * @param array $categories
	 * @param string $letter
	 * @dataProvider dataProviderToCheckArgumentsOfFindEntriesToBeTrue
	 */
	public function checkArgumentsForFindEntriesResultsInTrue($categories, $letter) {
		$this->assertSame(
			TRUE,
			$this->subject->checkArgumentsForFindEntries($categories, $letter)
		);
	}

	/**
	 * DataProvider
	 *
	 * @return array
	 */
	public function dataProviderToCheckArgumentsOfFindEntriesToBeFalse() {
		$arguments = array();
		$arguments['numbered categories casted to string with empty letter'] = array(array('1', '3', '12'), '');
		$arguments['numbered categories casted to string with 0-9 as letter'] = array(array('1', '3', '12'), '0-9');
		$arguments['numbered categories casted to string with r as letter'] = array(array('1', '3', '12'), 'R');
		$arguments['null as category with 0-9 as letter'] = array(array(1, NULL, 12), '0-9');
		$arguments['numbered categories with invalid letter'] = array(array(1, NULL, 12), 'r');
		$arguments['a string within a list of numbered categories and with 0-9 as letter'] = array(array(1, '3', 12), '0-9');
		$arguments['multi dim array as category with 0-9 as letter'] = array(array(1, array(2, 5, 8), 12), '0-9');
		return $arguments;
	}

	/**
	 * @test
	 *
	 * @param array $categories
	 * @param string $letter
	 * @dataProvider dataProviderToCheckArgumentsOfFindEntriesToBeFalse
	 */
	public function checkArgumentsForFindEntriesResultsInFalse($categories, $letter) {
		$this->assertSame(
			FALSE,
			$this->subject->checkArgumentsForFindEntries($categories, $letter)
		);
	}

	/**
	 * DataProvider
	 *
	 * @return array
	 */
	public function dataProviderToCheckArgumentsOfFindEntriesToThrowAnException() {
		$arguments = array();
		$arguments['string'] = array('lorem ipsum', '');
		$arguments['integer'] = array(432, '');
		$arguments['null'] = array(NULL, '');
		return $arguments;
	}

	/**
	 * @test
	 *
	 * @param array $categories
	 * @expectedException \Exception
	 * @expectedExceptionCode 4096
	 * @dataProvider dataProviderToCheckArgumentsOfFindEntriesToThrowAnException
	 */
	public function checkArgumentsForFindEntriesResultsInAnException($categories) {
		$this->subject->checkArgumentsForFindEntries($categories, '');
	}

	/**
	 * We can't use $this->subject as we have to count the calls to $query->like
	 *
	 * @test
	 */
	public function findEntriesWithNumbersAsLetterResultsInManyLikes() {
		/** @var \TYPO3\CMS\Extbase\Persistence\Generic\Query|\PHPUnit_Framework_MockObject_MockObject $query */
		$query = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Query', array('like', 'contains', 'logicalOr', 'logicalAnd', 'execute'), array('type' => 'JWeiland\\Glossary2\\Domain\\Model\\Glossary'));
		for ($i = 0; $i < 10; $i++) {
			$property = new PropertyValue('title', 'title');
			$comparison = new Comparison($property, 7, $i . '%');
			$query->expects($this->at($i))->method('like')->with($this->equalTo('title'), $this->equalTo($i . '%'))->will($this->returnValue($comparison));
		}
		$query->expects($this->once())->method('logicalOr')->with($this->arrayHasKey(9));
		$query->expects($this->once())->method('logicalAnd')->with($this->arrayHasKey(0));
		$query->expects($this->once())->method('execute')->with();
		$query->expects($this->never())->method('contains');
		/** @var \JWeiland\Glossary2\Domain\Repository\GlossaryRepository|\PHPUnit_Framework_MockObject_MockObject $glossaryRepository */
		$glossaryRepository = $this->getMock('JWeiland\\Glossary2\\Domain\\Repository\\GlossaryRepository', array('createQuery', 'findAll'), array(), '', FALSE);
		$glossaryRepository->expects($this->once())->method('createQuery')->will($this->returnValue($query));
		$glossaryRepository->expects($this->never())->method('findAll');
		$glossaryRepository->findEntries(array(), '0-9');
	}

	/**
	 * We can't use $this->subject as we have to count the calls to $query->like
	 *
	 * @test
	 */
	public function findEntriesWithGivenLetterResultsInOneLike() {
		/** @var \TYPO3\CMS\Extbase\Persistence\Generic\Query|\PHPUnit_Framework_MockObject_MockObject $query */
		$query = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Query', array('like', 'contains', 'logicalOr', 'logicalAnd', 'execute'), array('type' => 'JWeiland\\Glossary2\\Domain\\Model\\Glossary'));
		$query->expects($this->once())->method('like')->with($this->equalTo('title'), $this->equalTo('D%'));
		$query->expects($this->once())->method('logicalOr')->with($this->arrayHasKey(0));
		$query->expects($this->once())->method('logicalAnd')->with($this->arrayHasKey(0));
		$query->expects($this->once())->method('execute');
		$query->expects($this->never())->method('contains');
		/** @var \JWeiland\Glossary2\Domain\Repository\GlossaryRepository|\PHPUnit_Framework_MockObject_MockObject $glossaryRepository */
		$glossaryRepository = $this->getMock('JWeiland\\Glossary2\\Domain\\Repository\\GlossaryRepository', array('createQuery'), array(), '', FALSE);
		$glossaryRepository->expects($this->once()) ->method('createQuery')->will($this->returnValue($query));
		$glossaryRepository->expects($this->never())->method('findAll');
		$glossaryRepository->findEntries(array(), 'D');
	}

	/**
	 * DataProvider
	 *
	 * @return array
	 */
	public function dataProviderToGenerateInvalidLettersToReturnFullListOfRecords() {
		$invalidLetters = array();
		$invalidLetters['german umlauts'] = array('ä');
		$invalidLetters['special chars'] = array('<');
		$invalidLetters['long string'] = array('lorem ipsum');
		$invalidLetters['integer'] = array(4);
		$invalidLetters['long integer'] = array(238745);
		$invalidLetters['null'] = array(NULL);
		$invalidLetters['array'] = array(array(0 => 123));
		return $invalidLetters;
	}

	/**
	 * @test
	 *
	 * @param string $letter
	 * @dataProvider dataProviderToGenerateInvalidLettersToReturnFullListOfRecords
	 */
	public function findEntriesWithInvalidLetterResultsInFullListOfRecords($letter) {
		$this->assertEquals(
			$this->subject->findAll(),
			$this->subject->findEntries(array(), $letter)
		);
	}

	/**
	 * We can't use $this->subject as we have to count the calls to $query->like
	 *
	 * @test
	 */
	public function findEntriesWithNumericCategories() {
		$categories = array(1, 3, 12);
		/** @var \TYPO3\CMS\Extbase\Persistence\Generic\Query|\PHPUnit_Framework_MockObject_MockObject $query */
		$query = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Query', array('contains', 'logicalOr', 'logicalAnd', 'execute'), array('type' => 'JWeiland\\Glossary2\\Domain\\Model\\Glossary'));
		foreach ($categories as $key => $category) {
			$query->expects($this->at($key))->method('contains')->with($this->equalTo('categories'), $this->equalTo($category));
		}
		$query->expects($this->once())->method('logicalOr')->with($this->arrayHasKey(0));
		$query->expects($this->once())->method('logicalAnd')->with($this->arrayHasKey(0));
		$query->expects($this->once())->method('execute');
		/** @var \JWeiland\Glossary2\Domain\Repository\GlossaryRepository|\PHPUnit_Framework_MockObject_MockObject $glossaryRepository */
		$glossaryRepository = $this->getMock('JWeiland\\Glossary2\\Domain\\Repository\\GlossaryRepository', array('createQuery'), array(), '', FALSE);
		$glossaryRepository->expects($this->once()) ->method('createQuery')->will($this->returnValue($query));
		$glossaryRepository->expects($this->never())->method('findAll');
		$glossaryRepository->findEntries($categories);
	}

	/**
	 * DataProvider
	 *
	 * @return array
	 */
	public function dataProviderForFindEntriesThrowsAnException() {
		$arguments = array();
		$arguments['string'] = array('lorem ipsum', '');
		$arguments['integer'] = array(432, '');
		$arguments['null'] = array(NULL, '');
		return $arguments;
	}

	/**
	 * @test
	 *
	 * @param array $categories
	 * @expectedException \Exception
	 * @expectedExceptionCode 4096
	 * @dataProvider dataProviderForFindEntriesThrowsAnException
	 */
	public function findEntriesResultsInAnException($categories) {
		$this->subject->checkArgumentsForFindEntries($categories, '');
	}

	/**
	 * @test
	 */
	public function getStartingLettersWithNoCategoryResultsInQueryWithoutJoin() {
		$querySettings = new Typo3QuerySettings();
		$querySettings->setStoragePageIds(array(1, 3, 12));
		/** @var \TYPO3\CMS\Extbase\Persistence\Generic\Query|\PHPUnit_Framework_MockObject_MockObject $query */
		$query = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Query', array('getQuerySettings', 'statement', 'execute'), array('type' => 'JWeiland\\Glossary2\\Domain\\Model\\Glossary'));
		$query->expects($this->once())->method('getQuerySettings')->will($this->returnValue($querySettings));
		$query->expects($this->once())->method('statement')->with(
			$this->logicalAnd(
				$this->stringContains('SELECT GROUP_CONCAT(DISTINCT UPPER(LEFT(title, 1))) as letters'),
				$this->stringContains('FIND_IN_SET(tx_glossary2_domain_model_glossary.pid, "1,3,12")'),
				$this->stringContains('tx_glossary2_domain_model_glossary.hidden=0'),
				$this->stringEndsWith('tx_glossary2_domain_model_glossary.deleted=0'),
				$this->logicalNot(
					$this->stringContains('LEFT JOIN sys_category_record_mm')
				)
			)
		)->will($this->returnSelf());
		$query->expects($this->once())->method('execute');
		/** @var \JWeiland\Glossary2\Domain\Repository\GlossaryRepository|\PHPUnit_Framework_MockObject_MockObject $glossaryRepository */
		$glossaryRepository = $this->getMock('JWeiland\\Glossary2\\Domain\\Repository\\GlossaryRepository', array('createQuery'), array(), '', FALSE);
		$glossaryRepository->expects($this->once()) ->method('createQuery')->will($this->returnValue($query));
		$glossaryRepository->getStartingLetters(array());
	}

	/**
	 * @test
	 */
	public function getStartingLettersWithCategoriesResultsInQueryWithJoin() {
		$querySettings = new Typo3QuerySettings();
		$querySettings->setStoragePageIds(array(1, 3, 12));
		/** @var \TYPO3\CMS\Extbase\Persistence\Generic\Query|\PHPUnit_Framework_MockObject_MockObject $query */
		$query = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Query', array('getQuerySettings', 'statement', 'execute'), array('type' => 'JWeiland\\Glossary2\\Domain\\Model\\Glossary'));
		$query->expects($this->once())->method('getQuerySettings')->will($this->returnValue($querySettings));
		$query->expects($this->once())->method('statement')->with(
			$this->logicalAnd(
				$this->stringContains('SELECT GROUP_CONCAT(DISTINCT UPPER(LEFT(title, 1))) as letters'),
				$this->stringContains('LEFT JOIN sys_category_record_mm'),
				$this->stringContains('FIND_IN_SET(tx_glossary2_domain_model_glossary.pid, "1,3,12")'),
				$this->stringContains('FIND_IN_SET(sys_category_record_mm.uid_local, "32,47,2456")'),
				$this->stringContains('tx_glossary2_domain_model_glossary.hidden=0'),
				$this->stringEndsWith('tx_glossary2_domain_model_glossary.deleted=0')
			)
		)->will($this->returnSelf());
		$query->expects($this->once())->method('execute');
		/** @var \JWeiland\Glossary2\Domain\Repository\GlossaryRepository|\PHPUnit_Framework_MockObject_MockObject $glossaryRepository */
		$glossaryRepository = $this->getMock('JWeiland\\Glossary2\\Domain\\Repository\\GlossaryRepository', array('createQuery'), array(), '', FALSE);
		$glossaryRepository->expects($this->once()) ->method('createQuery')->will($this->returnValue($query));
		$glossaryRepository->getStartingLetters(array(32, 47, 2456));
	}

	/**
	 * DataProvider
	 *
	 * @return array
	 */
	public function dataProviderForGetStartingLettersReturnsEmptyArray() {
		$arguments = array();
		$arguments['numbered array casted to string'] = array(array('1', '3', '12'));
		$arguments['null array'] = array(array('1', NULL, '12'));
		$arguments['integer array'] = array(array(1, '3', 12));
		$arguments['multi dim array'] = array(array(1, array(3, 7, 34), 12));
		$arguments['string array'] = array(array('lorem', 'ipsum'));
		return $arguments;
	}

	/**
	 * @test
	 *
	 * @param array $categories
	 * @dataProvider dataProviderForGetStartingLettersReturnsEmptyArray
	 */
	public function getStartingLettersResultsInEmptyArray($categories) {
		$this->assertSame(
			array(),
			$this->subject->getStartingLetters($categories)
		);
	}

	/**
	 * DataProvider
	 *
	 * @return array
	 */
	public function dataProviderForGetStartingLettersThrowsAnException() {
		$arguments = array();
		$arguments['string'] = array('lorem ipsum', '');
		$arguments['integer'] = array(432, '');
		$arguments['null'] = array(NULL, '');
		return $arguments;
	}

	/**
	 * @test
	 *
	 * @param array $categories
	 * @expectedException \Exception
	 * @expectedExceptionCode 4096
	 * @dataProvider dataProviderForGetStartingLettersThrowsAnException
	 */
	public function getStartingLettersResultsInAnException($categories) {
		$this->subject->getStartingLetters($categories);
	}

}