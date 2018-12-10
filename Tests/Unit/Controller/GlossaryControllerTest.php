<?php
namespace JWeiland\Glossary2\Tests\Unit\Controller;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case
 */
class GlossaryControllerTest extends UnitTestCase
{
    /**
     * @var \JWeiland\Glossary2\Controller\GlossaryController|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface
     */
    protected $subject;

    /**
     * set up fixure
     *
     * @return void
     */
    public function setUp()
    {
        $this->subject = $this->getAccessibleMock('JWeiland\\Glossary2\\Controller\\GlossaryController', array('dummy'));
    }

    /**
     * tear down fixure
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
    public function injectRepositorySetsRepositoryAsClassAvailable()
    {
        /** @var \JWeiland\Glossary2\Domain\Repository\GlossaryRepository $glossaryRepository */
        $glossaryRepository = $this->getMock('JWeiland\\Glossary2\\Domain\\Repository\\GlossaryRepository', array(), array(), '', false);
        $this->inject($this->subject, 'glossaryRepository', $glossaryRepository);
        $this->assertSame(
            $glossaryRepository,
            $this->subject->_get('glossaryRepository')
        );
    }

    /**
     * @test
     */
    public function initializeActionConvertsPidOfDetailPageToZeroIfEmpty()
    {
        $arrayWithEmptyValues = array(0, '0', null, '');
        foreach ($arrayWithEmptyValues as $emptyValue) {
            $this->subject->_set('settings', array(
                'pidOfDetailPage' => $emptyValue
            ));
            $this->subject->initializeAction();
            $this->assertSame(
                array('pidOfDetailPage' => null),
                $this->subject->_get('settings')
            );
        }
    }

    /**
     * @test
     */
    public function listActionWithLetterResultsInInitializedView()
    {
        $letter = '0-9';
        $glossary = array('a', 'b', 'c');
        $glossaries = array('d', 'e', 'f');
        /** @var \JWeiland\Glossary2\Domain\Repository\GlossaryRepository|\PHPUnit_Framework_MockObject_MockObject $glossaryRepository */
        $glossaryRepository = $this->getMock('JWeiland\\Glossary2\\Domain\\Repository\\GlossaryRepository', array(), array(), '', false);
        $glossaryRepository->expects($this->once())->method('findEntries')->with(array(1, 3, 12), $letter)->will($this->returnValue($glossaries));
        /** @var \TYPO3\CMS\Fluid\View\TemplateView|\PHPUnit_Framework_MockObject_MockObject $view */
        $view = $this->getMock('TYPO3\\CMS\\Fluid\\View\\TemplateView', array(), array(), '', false);
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
    public function showActionWithGlossaryResultsInInitializedView()
    {
        $glossary = new Glossary();
        /** @var \TYPO3\CMS\Fluid\View\TemplateView|\PHPUnit_Framework_MockObject_MockObject $view */
        $view = $this->getMock('TYPO3\\CMS\\Fluid\\View\\TemplateView', array(), array(), '', false);
        $view->expects($this->at(0))->method('assign')->with('glossary')->will($this->returnValue($glossary));
        /** @var \JWeiland\Glossary2\Controller\GlossaryController|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface $glossaryController */
        $glossaryController = $this->getAccessibleMock('JWeiland\\Glossary2\\Controller\\GlossaryController', array('getGlossary'));
        $glossaryController->_set('view', $view);
        $glossaryController->showAction($glossary);
    }

    /**
     * @test
     */
    public function creatingGlossaryResultsInGlossaryWithAllValues()
    {
        // this is the value which might come from database
        $returnValueForGetStartingLetters['letters'] = implode(',', str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789#+?=)(/&%$§"!'));
        $expectedResult = array('0-9' => true);
        foreach (str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ') as $value) {
            $expectedResult[$value] = true;
        }

        /** @var \JWeiland\Glossary2\Domain\Repository\GlossaryRepository|\PHPUnit_Framework_MockObject_MockObject $glossaryRepository */
        $glossaryRepository = $this->getMock('JWeiland\\Glossary2\\Domain\\Repository\\GlossaryRepository', array(), array(), '', false);
        $glossaryRepository
            ->expects($this->once())
            ->method('getStartingLetters')
            ->willReturn($returnValueForGetStartingLetters);
        $this->inject($this->subject, 'glossaryRepository', $glossaryRepository);
        $this->subject->_set('settings', array('letters' => '0-9,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z'));

        $this->assertSame(
            $expectedResult,
            $this->subject->getGlossary()
        );
    }

    /**
     * @test
     */
    public function creatingGlossaryResultsInGlossaryWhereHalfTheValuesAreLinked()
    {
        // this is the value which might come from database
        // each second value was removed (b, d, f, ...) and letters are unsorted
        $returnValueForGetStartingLetters['letters'] = implode(',', str_split('CEIK2MGOQSUAWY0468#+?=)(/&%$§"!'));
        $expectedResult = array('0-9' => true);
        foreach (str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ') as $key => $value) {
            // define that each second letter was not in database. So set them to false
            $expectedResult[$value] = $key % 2 ? false : true;
        }

        /** @var \JWeiland\Glossary2\Domain\Repository\GlossaryRepository|\PHPUnit_Framework_MockObject_MockObject $glossaryRepository */
        $glossaryRepository = $this->getMock('JWeiland\\Glossary2\\Domain\\Repository\\GlossaryRepository', array(), array(), '', false);
        $glossaryRepository
            ->expects($this->once())
            ->method('getStartingLetters')
            ->willReturn($returnValueForGetStartingLetters);
        $this->inject($this->subject, 'glossaryRepository', $glossaryRepository);
        $this->subject->_set('settings', array('letters' => '0-9,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z'));

        $this->assertSame(
            $expectedResult,
            $this->subject->getGlossary()
        );
    }

    /**
     * @test
     */
    public function getGlossaryWithCategoriesResultsInGlossary()
    {
        /** @var \JWeiland\Glossary2\Domain\Repository\GlossaryRepository|\PHPUnit_Framework_MockObject_MockObject $glossaryRepository */
        $glossaryRepository = $this->getAccessibleMock('JWeiland\\Glossary2\\Domain\\Repository\\GlossaryRepository', array('getStartingLetters'), array(), '', false);
        $glossaryRepository->expects($this->once())->method('getStartingLetters')->with(
            $this->callback(function($subject) {
                return $subject === GeneralUtility::intExplode(',', implode(',', $subject), true);
            })
        );
        /** @var \JWeiland\Glossary2\Controller\GlossaryController|\PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface $glossaryController */
        $glossaryController = $this->getAccessibleMock('JWeiland\\Glossary2\\Controller\\GlossaryController', array('dummy'));
        $glossaryController->_set('settings', array('categories' => '1,2,3,4,5'));
        $glossaryController->injectGlossaryRepository($glossaryRepository);
        $glossaryController->getGlossary();
    }
}
