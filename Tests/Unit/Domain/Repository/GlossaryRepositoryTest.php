<?php
namespace JWeiland\Glossary2\Tests\Unit\Domain\Repository;

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
use JWeiland\Glossary2\Domain\Repository\GlossaryRepository;
use JWeiland\Glossary2\Service\DatabaseService;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\Comparison;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\PropertyValue;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Test case.
 */
class GlossaryRepositoryTest extends UnitTestCase
{
    /**
     * @var GlossaryRepository
     */
    protected $subject;

    /**
     * @var ObjectManager|ObjectProphecy
     */
    protected $objectManagerProphecy;

    /**
     * @var PersistenceManager|ObjectProphecy
     */
    protected $persistenceManagerProphecy;

    /**
     * @var Query|ObjectProphecy
     */
    protected $queryProphecy;

    /**
     * set up
     *
     * @return void
     */
    public function setUp()
    {
        $this->objectManagerProphecy = $this->prophesize(ObjectManager::class);
        $this->persistenceManagerProphecy = $this->prophesize(PersistenceManager::class);
        $this->queryProphecy = $this->prophesize(Query::class);

        $this->subject = new GlossaryRepository($this->objectManagerProphecy->reveal());
        $this->subject->injectPersistenceManager($this->persistenceManagerProphecy->reveal());
    }

    /**
     * tear down
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->subject);
        unset(
            $this->objectManager
        );
    }

    /**
     * @test
     */
    public function createQueryWillSortGlossaryEntriesByTitle()
    {
        $this->persistenceManagerProphecy
            ->createQueryForType(Argument::any())
            ->shouldBeCalled()
            ->willReturn($this->queryProphecy->reveal());

        $this->queryProphecy
            ->setOrderings([
                'title' => 'ASC'
            ])
            ->shouldBeCalled();

        $this->subject->createQuery();
    }

    /**
     * @test
     */
    public function getStartingLettersWithInvalidCategoryUidsReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getStartingLetters([1,'b','c'])
        );
    }

    /**
     * @test
     */
    public function getStartingLettersWithInvalidCategoriesReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getStartingLetters([0])
        );
    }

    /**
     * @test
     */
    public function getStartingLettersWithEmptyCategoriesReturnsEmptyString() {
        $this->assertSame(
            '',
            $this->subject->getStartingLetters([1, '', 3])
        );
    }

    /**
     * @test
     */
    public function getStartingLettersWithoutCategoriesReturnsGlossary() {
        $this->persistenceManagerProphecy
            ->createQueryForType(Argument::any())
            ->shouldBeCalled()
            ->willReturn($this->queryProphecy->reveal());

        /** @var DatabaseService|ObjectProphecy $databaseServiceProphecy */
        $databaseServiceProphecy = $this->prophesize(DatabaseService::class);
        $databaseServiceProphecy
            ->getGroupedFirstLetters(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn(
                [
                    0 => [
                        'Letter' => 'A'
                    ],
                    1 => [
                        'Letter' => 'B'
                    ],
                    2 => [
                        'Letter' => 'C'
                    ],
                ]
            );

        GeneralUtility::addInstance(DatabaseService::class, $databaseServiceProphecy->reveal());

        $this->assertSame(
            'A,B,C',
            $this->subject->getStartingLetters()
        );
    }

    /**
     * @test
     */
    public function getStartingLettersWithoutCategoriesReturnsConvertsGermanUmlauts() {
        $this->persistenceManagerProphecy
            ->createQueryForType(Argument::any())
            ->shouldBeCalled()
            ->willReturn($this->queryProphecy->reveal());

        /** @var DatabaseService|ObjectProphecy $databaseServiceProphecy */
        $databaseServiceProphecy = $this->prophesize(DatabaseService::class);
        $databaseServiceProphecy
            ->getGroupedFirstLetters(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn(
                [
                    0 => [
                        'Letter' => 'Ä'
                    ],
                    1 => [
                        'Letter' => 'Ö'
                    ],
                    2 => [
                        'Letter' => 'Ü'
                    ],
                ]
            );

        GeneralUtility::addInstance(DatabaseService::class, $databaseServiceProphecy->reveal());

        $this->assertSame(
            'A,O,U',
            $this->subject->getStartingLetters()
        );
    }
}
