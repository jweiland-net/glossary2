<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Tests\Functional\PageTitleProvider;

use JWeiland\Glossary2\Domain\Model\Glossary;
use JWeiland\Glossary2\Domain\Repository\GlossaryRepository;
use JWeiland\Glossary2\PageTitleProvider\Glossary2PageTitleProvider;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Functional test for Glossary2PageTitleProvider
 */
class Glossary2PageTitleProviderTest extends FunctionalTestCase
{
    protected Glossary2PageTitleProvider $subject;

    protected array $testExtensionsToLoad = [
        'jweiland/glossary2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $pageId = 15;
        $persistenceManager = GeneralUtility::makeInstance(PersistenceManagerInterface::class);
        $querySettings = GeneralUtility::makeInstance(QuerySettingsInterface::class);
        $querySettings->setStoragePageIds([$pageId]);

        $glossaryRepository = GeneralUtility::makeInstance(GlossaryRepository::class);
        $glossaryRepository->setDefaultQuerySettings($querySettings);

        $this->subject = new Glossary2PageTitleProvider($glossaryRepository);

        $glossary = GeneralUtility::makeInstance(Glossary::class);
        $glossary->setPid($pageId);
        $glossary->setTitle('Nice title for detail page');
        $glossary->setDescription('here is running a functional test case');
        $persistenceManager->add($glossary);
        $persistenceManager->persistAll();
    }

    protected function tearDown(): void
    {
        unset($this->pageTitleProvider);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getGlossaryDetailPageWithAssignedTitleShouldMatch(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = (new \TYPO3\CMS\Core\Http\ServerRequest('https://www.example.com/'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE)
            ->withQueryParams(
                [
                    'tx_glossary2_glossary' => [
                        'action' => 'show',
                        'glossary' => 1,
                    ],
                ],
            );

        self::assertSame(
            'Nice title for detail page',
            $this->subject->getTitle(),
        );
    }
}
