<?php

/*
 * This file is part of the package jweiland/glossary2.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Tests\Functional\Repository;

use JWeiland\Glossary2\Domain\Model\Glossary;
use JWeiland\Glossary2\Domain\Repository\GlossaryRepository;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

/**
 * Test case.
 */
class GlossaryRepositoryTest extends FunctionalTestCase
{
    /**
     * @var GlossaryRepository
     */
    protected $subject;

    /**
     * @var QuerySettingsInterface
     */
    protected $querySettings;

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/glossary2'
    ];

    public function setUp()
    {
        parent::setUp();

        $this->importDataSet(__DIR__ . '/../Fixtures/tx_glossary2_domain_model_glossary.xml');

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->querySettings = $objectManager->get(QuerySettingsInterface::class);
        $this->querySettings->setStoragePageIds([12]);
        $this->subject = $objectManager->get(GlossaryRepository::class);
    }

    public function tearDown()
    {
        unset(
            $this->subject,
            $this->objectManager
        );
        parent::tearDown();
    }

    /**
     * @test
     */
    public function findAllWillFindGlossariesSorted()
    {
        $glossaries = [];
        /** @var Glossary $glossary */
        foreach ($this->subject->findAll() as $glossary) {
            $glossaries[] = $glossary->getTitle();
        }
        $sortedGlossaries = $glossaries;
        sort($sortedGlossaries);

        self::assertSame(
            $sortedGlossaries,
            $glossaries
        );
    }

    /**
     * @test
     */
    public function findEntriesWillFindAllEntries()
    {
        $this->subject->setDefaultQuerySettings($this->querySettings);
        self::assertSame(
            7,
            count($this->subject->findEntries()->toArray())
        );
    }

    /**
     * @test
     */
    public function findEntriesWithInvalidCategoriesWillFindAllEntries()
    {
        $this->subject->setDefaultQuerySettings($this->querySettings);
        self::assertSame(
            7,
            count($this->subject->findEntries(['0', 'a'])->toArray())
        );
    }

    /**
     * @test
     */
    public function findEntriesWithGivenCategoryWillFindTwoEntries()
    {
        $this->subject->setDefaultQuerySettings($this->querySettings);
        self::assertSame(
            2,
            count($this->subject->findEntries([1])->toArray())
        );
    }

    /**
     * @test
     */
    public function findEntriesWithSomeInvalidCategoriesWillFindAllEntries()
    {
        $this->subject->setDefaultQuerySettings($this->querySettings);
        self::assertSame(
            7,
            count($this->subject->findEntries(['0', 'a', 1])->toArray())
        );
    }

    /**
     * @test
     */
    public function findEntriesWithLetterWillFindTwoEntries()
    {
        // "u" will find records with "u" and "ü"
        $this->subject->setDefaultQuerySettings($this->querySettings);
        self::assertSame(
            2,
            count($this->subject->findEntries([], 'u')->toArray())
        );
    }

    /**
     * @test
     */
    public function findEntriesWithInvalidLetterWillFindAllEntries()
    {
        $this->subject->setDefaultQuerySettings($this->querySettings);
        self::assertSame(
            7,
            count($this->subject->findEntries([], '/')->toArray())
        );
    }

    /**
     * @test
     */
    public function findEntriesWithCategoryAndLetterWillFindOneEntry()
    {
        // "u" will find records with "u" and "ü"
        $this->subject->setDefaultQuerySettings($this->querySettings);
        self::assertSame(
            1,
            count($this->subject->findEntries([2], 'u')->toArray())
        );
    }
}
