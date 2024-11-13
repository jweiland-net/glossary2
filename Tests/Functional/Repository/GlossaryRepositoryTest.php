<?php

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Tests\Functional\Repository;

use JWeiland\Glossary2\Domain\Model\Glossary;
use JWeiland\Glossary2\Domain\Repository\GlossaryRepository;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class GlossaryRepositoryTest extends FunctionalTestCase
{
    protected GlossaryRepository $subject;

    protected QuerySettingsInterface $querySettings;

    protected array $testExtensionsToLoad = [
        'jweiland/glossary2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setSetupArray([]);
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest())
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('frontend.typoscript', $frontendTypoScript);

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/tx_glossary2_domain_model_glossary.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/sys_category.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/sys_category_record_mm.csv');

        $this->querySettings = GeneralUtility::makeInstance(QuerySettingsInterface::class);
        $this->querySettings->setStoragePageIds([12]);
        $this->subject = GeneralUtility::makeInstance(GlossaryRepository::class);
    }

    protected function tearDown(): void
    {
        unset($this->subject);
        parent::tearDown();
    }

    #[Test]
    public function findAllWillFindGlossariesSorted(): void
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
            $glossaries,
        );
    }

    #[Test]
    public function searchGlossariesWithInvalidCategoriesWillFindAllEntries(): void
    {
        $this->subject->setDefaultQuerySettings($this->querySettings);
        self::assertCount(
            7,
            $this->subject->searchGlossaries(['0', 'a'])->toArray(),
        );
    }

    #[Test]
    public function searchGlossariesWithGivenCategoryWillFindTwoEntries(): void
    {
        $this->subject->setDefaultQuerySettings($this->querySettings);
        self::assertCount(
            2,
            $this->subject->searchGlossaries([1])->toArray(),
        );
    }

    #[Test]
    public function searchGlossariesWithSomeInvalidCategoriesWillFindAllEntries(): void
    {
        $this->subject->setDefaultQuerySettings($this->querySettings);
        self::assertCount(
            7,
            $this->subject->searchGlossaries(['0', 'a', 1])->toArray(),
        );
    }

    #[Test]
    public function searchGlossariesWithLetterWillFindTwoEntries(): void
    {
        // "u" will find records with "u" and "ü"
        $this->subject->setDefaultQuerySettings($this->querySettings);
        self::assertCount(
            2,
            $this->subject->searchGlossaries([], 'u')->toArray(),
        );
    }

    #[Test]
    public function searchGlossariesWithInvalidLetterWillFindAllEntries(): void
    {
        $this->subject->setDefaultQuerySettings($this->querySettings);
        self::assertCount(
            7,
            $this->subject->searchGlossaries([], '/')->toArray(),
        );
    }

    #[Test]
    public function searchGlossariesWithCategoryAndLetterWillFindOneEntry(): void
    {
        // "u" will find records with "u" and "ü"
        $this->subject->setDefaultQuerySettings($this->querySettings);
        self::assertCount(
            1,
            $this->subject->searchGlossaries([2], 'u')->toArray(),
        );
    }
}
