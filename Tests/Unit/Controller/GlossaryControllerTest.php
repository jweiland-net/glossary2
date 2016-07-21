<?php
namespace JWeiland\Glossary2\Tests\Unit\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Stefan Froemken <projects@jweiland.net>, jweiland.net
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
use JWeiland\Glossary2\Controller\GlossaryController;
use JWeiland\Glossary2\Domain\Model\Glossary;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Test case for class Tx_Glossary2_Controller_GlossaryController.
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @package TYPO3
 * @subpackage Glossary 2
 *
 * @author Stefan Froemken <projects@jweiland.net>
 */
class GlossaryControllerTest extends UnitTestCase {

	/**
	 * @var \JWeiland\Glossary2\Controller\GlossaryController|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface
	 */
	protected $subject;

	/**
	 * set up fixure
	 *
	 * @return void
	 */
	public function setUp() {
		$this->subject = $this->getAccessibleMock('JWeiland\\Glossary2\\Controller\\GlossaryController', array('dummy'));
	}

	/**
	 * tear down fixure
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function injectRepositorySetsRepositoryAsClassAvailable() {
		$objectManager = new ObjectManager();
		/** @var \JWeiland\Glossary2\Domain\Repository\GlossaryRepository $glossaryRepository */
		$glossaryRepository = $objectManager->get('JWeiland\\Glossary2\\Domain\\Repository\\GlossaryRepository');
		$this->subject->injectGlossaryRepository($glossaryRepository);
		$this->assertSame(
			$glossaryRepository,
			$this->subject->_get('glossaryRepository')
		);
	}

	/**
	 * @test
	 */
	public function initializeActionConvertsPidOfDetailPageToZeroIfEmpty() {
		$arrayWithEmptyValues = array(0, '0', NULL, '');
		foreach ($arrayWithEmptyValues as $emptyValue) {
			$this->subject->_set('settings', array(
				'pidOfDetailPage' => $emptyValue
			));
			$this->subject->initializeAction();
			$this->assertSame(
				array('pidOfDetailPage' => NULL),
				$this->subject->_get('settings')
			);
		}
	}

	/**
	 * @test
	 */
	public function listActionWithLetterResultsInInitializedView() {
		$letter = '0-9';
		$glossary = array('a', 'b', 'c');
		$glossaries = array('d', 'e', 'f');
		/** @var \JWeiland\Glossary2\Domain\Repository\GlossaryRepository|\PHPUnit_Framework_MockObject_MockObject $glossaryRepository */
		$glossaryRepository = $this->getMock('JWeiland\\Glossary2\\Domain\\Repository\\GlossaryRepository', array(), array(), '', FALSE);
		$glossaryRepository->expects($this->once())->method('findEntries')->with(array(1, 3, 12), $letter)->will($this->returnValue($glossaries));
		/** @var \TYPO3\CMS\Fluid\View\TemplateView|\PHPUnit_Framework_MockObject_MockObject $view */
		$view = $this->getMock('TYPO3\\CMS\\Fluid\\View\\TemplateView');
		$view->expects($this->at(0))->method('assign')->with('glossaries')->will($this->returnValue($glossaries));
		$view->expects($this->at(1))->method('assign')->with('glossary')->will($this->returnValue($glossary));
		/** @var \JWeiland\Glossary2\Controller\GlossaryController|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface $glossaryController */
		$glossaryController = $this->getAccessibleMock('JWeiland\\Glossary2\\Controller\\GlossaryController', array('getGlossary'));
		$glossaryController->injectGlossaryRepository($glossaryRepository);
		$glossaryController->_set('settings', array('categories' => '1, 3, 12'));
		$glossaryController->_set('view', $view);
		$glossaryController->expects($this->once())->method('getGlossary')->will($this->returnValue($glossary));
		$glossaryController->listAction($letter);
	}

	/**
	 * @test
	 */
	public function showActionWithGlossaryResultsInInitializedView() {
		$glossary = new Glossary();
		/** @var \TYPO3\CMS\Fluid\View\TemplateView|\PHPUnit_Framework_MockObject_MockObject $view */
		$view = $this->getMock('TYPO3\\CMS\\Fluid\\View\\TemplateView');
		$view->expects($this->at(0))->method('assign')->with('glossary')->will($this->returnValue($glossary));
		/** @var \JWeiland\Glossary2\Controller\GlossaryController|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface $glossaryController */
		$glossaryController = $this->getAccessibleMock('JWeiland\\Glossary2\\Controller\\GlossaryController', array('getGlossary'));
		$glossaryController->_set('view', $view);
		$glossaryController->showAction($glossary);
	}

	/**
	 * @test
	 */
	public function creatingGlossaryResultsInGlossaryWithAllValues() {
		// this is the value which might come from database
		$returnValueForGetStartingLetters['letters'] = implode(',', str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789#+?=)(/&%$§"!'));
		$expectedResult = array('0-9' => TRUE);
		foreach (str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ') as $value) {
			$expectedResult[$value] = TRUE;
		}

		/** @var \JWeiland\Glossary2\Domain\Repository\GlossaryRepository|\PHPUnit_Framework_MockObject_MockObject $glossaryRepository */
		$glossaryRepository = $this->getMock('JWeiland\\Glossary2\\Domain\\Repository\\GlossaryRepository', array(), array(), '', FALSE);
		$glossaryRepository->expects($this->once())->method('getStartingLetters')->will($this->returnValue($returnValueForGetStartingLetters));
		$glossaryController = new GlossaryController();
		$glossaryController->injectGlossaryRepository($glossaryRepository);

		$this->assertSame(
			$expectedResult,
			$glossaryController->getGlossary()
		);
	}

	/**
	 * @test
	 */
	public function creatingGlossaryResultsInGlossaryWhereHalfTheValuesAreLinked() {
		// this is the value which might come from database
		// each second value was removed (b, d, f, ...) and letters are unsorted
		$returnValueForGetStartingLetters['letters'] = implode(',', str_split('CEIK2MGOQSUAWY0468#+?=)(/&%$§"!'));
		$expectedResult = array('0-9' => TRUE);
		foreach (str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ') as $key => $value) {
			// define that each second letter was not in database. So set them to FALSE
			$expectedResult[$value] = $key % 2 ? FALSE : TRUE;
		}

		/** @var \JWeiland\Glossary2\Domain\Repository\GlossaryRepository|\PHPUnit_Framework_MockObject_MockObject $glossaryRepository */
		$glossaryRepository = $this->getMock('JWeiland\\Glossary2\\Domain\\Repository\\GlossaryRepository', array(), array(), '', FALSE);
		$glossaryRepository->expects($this->once())->method('getStartingLetters')->will($this->returnValue($returnValueForGetStartingLetters));
		$glossaryController = new GlossaryController();
		$glossaryController->injectGlossaryRepository($glossaryRepository);

		$this->assertSame(
			$expectedResult,
			$glossaryController->getGlossary()
		);
	}

	/**
	 * @test
	 */
	public function getGlossaryWithCategoriesResultsInGlossary() {
		/** @var \JWeiland\Glossary2\Domain\Repository\GlossaryRepository|\PHPUnit_Framework_MockObject_MockObject $glossaryRepository */
		$glossaryRepository = $this->getAccessibleMock('JWeiland\\Glossary2\\Domain\\Repository\\GlossaryRepository', array('getStartingLetters'), array(), '', FALSE);
		$glossaryRepository->expects($this->once())->method('getStartingLetters')->with(
			$this->callback(function($subject) {
				return $subject === GeneralUtility::intExplode(',', implode(',', $subject), TRUE);
			})
		);
		/** @var \JWeiland\Glossary2\Controller\GlossaryController|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface $glossaryController */
		$glossaryController = $this->getAccessibleMock('JWeiland\\Glossary2\\Controller\\GlossaryController', array('dummy'));
		$glossaryController->_set('settings', array('categories' => '1,2,3,4,5'));
		$glossaryController->injectGlossaryRepository($glossaryRepository);
		$glossaryController->getGlossary();
	}

}