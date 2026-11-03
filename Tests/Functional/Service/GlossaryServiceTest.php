<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Tests\Functional\Service;

use JWeiland\Glossary2\Configuration\ExtConf;
use JWeiland\Glossary2\Domain\Repository\GlossaryRepository;
use JWeiland\Glossary2\Event\PostProcessFirstLettersEvent;
use JWeiland\Glossary2\Event\SanitizeValueForCharsetHelperEvent;
use JWeiland\Glossary2\Helper\CharsetHelper;
use JWeiland\Glossary2\Service\GlossaryService;
use JWeiland\Glossary2\Tests\Functional\Fixtures\ProcessFirstLettersEventListener;
use JWeiland\Glossary2\Tests\Functional\Fixtures\SanitizeValueEventListener;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\ListenerProviderInterface;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case
 */
class GlossaryServiceTest extends FunctionalTestCase
{
    private const JSON_TEMPLATE = 'EXT:glossary2/Tests/Functional/Fixtures/Templates/GlossaryAsJson.html';

    private EventDispatcher $eventDispatcher;

    /**
     * @var array<string, array<int, object>>
     */
    private array $listeners = [];

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

        $listenerProvider = $this->createMock(ListenerProviderInterface::class);
        $listenerProvider
            ->method('getListenersForEvent')
            ->willReturnCallback(fn(object $event): array => $this->listeners[$event::class] ?? []);
        $this->eventDispatcher = new EventDispatcher($listenerProvider);

        GeneralUtility::addInstance(
            CharsetHelper::class,
            new CharsetHelper(
                new CharsetConverter(),
                $this->eventDispatcher,
            ),
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->eventDispatcher,
            $this->listeners,
        );
        parent::tearDown();
    }

    #[Test]
    public function buildGlossaryWithQueryBuilderLinksFirstLettersOfRecords(): void
    {
        self::assertSame(
            $this->getGlossary(),
            $this->buildGlossaryAsArray($this->getQueryBuilder())['glossary'],
        );
    }

    #[Test]
    public function buildGlossaryWithQueryResultLinksFirstLettersOfRecords(): void
    {
        self::assertSame(
            $this->getGlossary(),
            $this->buildGlossaryAsArray($this->getQueryResult())['glossary'],
        );
    }

    #[Test]
    public function buildGlossaryWithQueryBuilderWillConvertGermanUmlauts(): void
    {
        // Only record "Ärger"
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->where($queryBuilder->expr()->eq('uid', 5));

        $expectedGlossary = $this->getGlossary(false);
        $expectedGlossary[1]['hasLink'] = true;

        self::assertSame(
            $expectedGlossary,
            $this->buildGlossaryAsArray($queryBuilder)['glossary'],
        );
    }

    #[Test]
    public function buildGlossaryWithQueryBuilderWillConvertSpecialCharsOfDifferentLetters(): void
    {
        // Only records "Ärger" and "èmily"
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->where($queryBuilder->expr()->in('uid', [5, 7]));

        $expectedGlossary = $this->getGlossary(false);
        $expectedGlossary[1]['hasLink'] = true;
        $expectedGlossary[5]['hasLink'] = true;

        self::assertSame(
            $expectedGlossary,
            $this->buildGlossaryAsArray($queryBuilder)['glossary'],
        );
    }

    #[Test]
    public function buildGlossaryWillConvertSpecialCharToAsciiByEvent(): void
    {
        $this->listeners[SanitizeValueForCharsetHelperEvent::class] = [new SanitizeValueEventListener()];

        // Set link of letter "o" instead of "e" to true
        $expectedGlossary = $this->getGlossary();
        $expectedGlossary[5]['hasLink'] = false;
        $expectedGlossary[15]['hasLink'] = true;

        self::assertSame(
            $expectedGlossary,
            $this->buildGlossaryAsArray($this->getQueryBuilder())['glossary'],
        );
    }

    #[Test]
    public function buildGlossaryWithModifiedLettersByEvent(): void
    {
        $this->listeners[PostProcessFirstLettersEvent::class] = [new ProcessFirstLettersEventListener()];

        $expectedGlossary = $this->getGlossary();
        // Remove link for letter "a"
        $expectedGlossary[1]['hasLink'] = false;
        // Add link for letter "k"
        $expectedGlossary[11]['hasLink'] = true;

        self::assertSame(
            $expectedGlossary,
            $this->buildGlossaryAsArray($this->getQueryBuilder())['glossary'],
        );
    }

    #[Test]
    public function buildGlossaryWithIndividualColumnAndAliasWillBuildGlossary(): void
    {
        self::assertSame(
            $this->getGlossary(),
            $this->buildGlossaryAsArray(
                $this->getQueryBuilder(),
                [
                    'column' => 'title',
                    'columnAlias' => 'Buchstaben',
                ],
            )['glossary'],
        );
    }

    #[Test]
    public function buildGlossaryWillAddSettingsToView(): void
    {
        self::assertSame(
            [
                'foo' => 'bar',
            ],
            $this->buildGlossaryAsArray(
                $this->getQueryBuilder(),
                [
                    'settings' => [
                        'foo' => 'bar',
                    ],
                ],
            )['settings'],
        );
    }

    #[Test]
    public function buildGlossaryWillAddOptionsToView(): void
    {
        $options = $this->buildGlossaryAsArray(
            $this->getQueryBuilder(),
            [
                'extensionName' => 'sync_crop_areas',
                'pluginName' => 'crop',
                'controllerName' => 'Cropping',
                'actionName' => 'view',
            ],
        )['options'];

        self::assertSame('sync_crop_areas', $options['extensionName']);
        self::assertSame('crop', $options['pluginName']);
        self::assertSame('Cropping', $options['controllerName']);
        self::assertSame('view', $options['actionName']);
    }

    #[Test]
    public function buildGlossaryWillMarkRequestedLetter(): void
    {
        $expectedGlossary = $this->getGlossary();
        $expectedGlossary[16]['isRequestedLetter'] = true;

        self::assertSame(
            $expectedGlossary,
            $this->buildGlossaryAsArray(
                $this->getQueryBuilder(),
                [
                    'variables' => [
                        'letter' => 'p',
                    ],
                ],
            )['glossary'],
        );
    }

    #[Test]
    public function buildGlossaryWithDefaultLettersWillNotMergeNumbers(): void
    {
        // Letter "1" of "110 Police" is not part of the default letters
        $expectedGlossary = $this->getGlossary();
        $expectedGlossary[0]['hasLink'] = false;

        self::assertSame(
            $expectedGlossary,
            $this->buildGlossaryAsArray(
                $this->getQueryBuilder(),
                [
                    'mergeNumbers' => false,
                ],
            )['glossary'],
        );
    }

    #[Test]
    public function buildGlossaryWithOwnLettersWillNotMergeNumbers(): void
    {
        $expectedGlossary = $this->getGlossary();
        // Remove 0-9
        unset($expectedGlossary[0]);
        // Remove f
        unset($expectedGlossary[6]);
        // Remove o
        unset($expectedGlossary[15]);
        // Add 0, 1, 3
        array_unshift(
            $expectedGlossary,
            [
                'letter' => '0',
                'hasLink' => false,
                'isRequestedLetter' => false,
            ],
            [
                'letter' => '1',
                'hasLink' => true,
                'isRequestedLetter' => false,
            ],
            [
                'letter' => '3',
                'hasLink' => false,
                'isRequestedLetter' => false,
            ],
        );

        self::assertSame(
            $expectedGlossary,
            $this->buildGlossaryAsArray(
                $this->getQueryBuilder(),
                [
                    'mergeNumbers' => false,
                    'possibleLetters' => '0,1,3,a,b,c,d,e,g,h,i,j,k,l,m,n,p,q,r,s,t,u,v,w,x,y,z',
                ],
            )['glossary'],
        );
    }

    #[Test]
    public function buildGlossaryRendersDefaultTemplateWithAllPossibleLetters(): void
    {
        $content = $this->getSubject()->buildGlossary($this->getQueryBuilder(), [], $this->getExtbaseRequest());

        self::assertStringContainsString('<nav class="nav">', $content);
        self::assertSame(27, substr_count($content, '<li'));
    }

    #[Test]
    public function serviceCanBeBuiltWithoutRequest(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);

        self::assertInstanceOf(
            GlossaryService::class,
            $this->get(GlossaryService::class),
        );
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function templatePathTypoScriptDataProvider(): array
    {
        $fixture = 'EXT:glossary2/Tests/Functional/Fixtures/Templates/Glossary.html';

        return [
            'No TypoScript uses default template' => [[], 'clubdirectory', 'class="nav"'],
            'String template path' => [['templatePath' => $fixture], 'clubdirectory', 'Fixture template'],
            'Default template path' => [['templatePath.' => ['default' => $fixture]], 'clubdirectory', 'Fixture template'],
            'Extension specific template path' => [
                ['templatePath.' => ['default' => 'EXT:foo/bar.html', 'clubdirectory' => $fixture]],
                'clubdirectory',
                'Fixture template clubdirectory',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $settings
     */
    #[Test]
    #[DataProvider('templatePathTypoScriptDataProvider')]
    public function buildGlossaryUsesTemplatePathFromTypoScriptOfGivenRequest(
        array $settings,
        string $extensionName,
        string $expectedContent,
    ): void {
        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setSetupArray([
            'plugin.' => [
                'tx_glossary2.' => [
                    'settings.' => $settings,
                ],
            ],
        ]);

        self::assertStringContainsString(
            $expectedContent,
            $this->getSubject()->buildGlossary(
                $this->getQueryBuilder(),
                ['extensionName' => $extensionName],
                $this->getExtbaseRequest()->withAttribute('frontend.typoscript', $frontendTypoScript),
            ),
        );
    }

    /**
     * @return array<int, array<string, bool|string>>
     */
    private function getGlossary(bool $withLinks = true): array
    {
        $glossary = [
            0 => [
                'letter' => '0-9',
                'hasLink' => true,
                'isRequestedLetter' => false,
            ],
            1 => [
                'letter' => 'a',
                'hasLink' => true,
                'isRequestedLetter' => false,
            ],
            2 => [
                'letter' => 'b',
                'hasLink' => false,
                'isRequestedLetter' => false,
            ],
            3 => [
                'letter' => 'c',
                'hasLink' => false,
                'isRequestedLetter' => false,
            ],
            4 => [
                'letter' => 'd',
                'hasLink' => false,
                'isRequestedLetter' => false,
            ],
            5 => [
                'letter' => 'e',
                'hasLink' => true,
                'isRequestedLetter' => false,
            ],
            6 => [
                'letter' => 'f',
                'hasLink' => false,
                'isRequestedLetter' => false,
            ],
            7 => [
                'letter' => 'g',
                'hasLink' => false,
                'isRequestedLetter' => false,
            ],
            8 => [
                'letter' => 'h',
                'hasLink' => false,
                'isRequestedLetter' => false,
            ],
            9 => [
                'letter' => 'i',
                'hasLink' => false,
                'isRequestedLetter' => false,
            ],
            10 => [
                'letter' => 'j',
                'hasLink' => false,
                'isRequestedLetter' => false,
            ],
            11 => [
                'letter' => 'k',
                'hasLink' => false,
                'isRequestedLetter' => false,
            ],
            12 => [
                'letter' => 'l',
                'hasLink' => false,
                'isRequestedLetter' => false,
            ],
            13 => [
                'letter' => 'm',
                'hasLink' => false,
                'isRequestedLetter' => false,
            ],
            14 => [
                'letter' => 'n',
                'hasLink' => false,
                'isRequestedLetter' => false,
            ],
            15 => [
                'letter' => 'o',
                'hasLink' => false,
                'isRequestedLetter' => false,
            ],
            16 => [
                'letter' => 'p',
                'hasLink' => true,
                'isRequestedLetter' => false,
            ],
            17 => [
                'letter' => 'q',
                'hasLink' => false,
                'isRequestedLetter' => false,
            ],
            18 => [
                'letter' => 'r',
                'hasLink' => false,
                'isRequestedLetter' => false,
            ],
            19 => [
                'letter' => 's',
                'hasLink' => false,
                'isRequestedLetter' => false,
            ],
            20 => [
                'letter' => 't',
                'hasLink' => false,
                'isRequestedLetter' => false,
            ],
            21 => [
                'letter' => 'u',
                'hasLink' => true,
                'isRequestedLetter' => false,
            ],
            22 => [
                'letter' => 'v',
                'hasLink' => false,
                'isRequestedLetter' => false,
            ],
            23 => [
                'letter' => 'w',
                'hasLink' => false,
                'isRequestedLetter' => false,
            ],
            24 => [
                'letter' => 'x',
                'hasLink' => false,
                'isRequestedLetter' => false,
            ],
            25 => [
                'letter' => 'y',
                'hasLink' => false,
                'isRequestedLetter' => false,
            ],
            26 => [
                'letter' => 'z',
                'hasLink' => false,
                'isRequestedLetter' => false,
            ],
        ];

        if (!$withLinks) {
            foreach ($glossary as $key => $_) {
                $glossary[$key]['hasLink'] = false;
            }
        }

        return $glossary;
    }

    /**
     * Renders the glossary with a JSON template and returns the decoded
     * variables glossary, settings, options and variables
     *
     * @param QueryBuilder|QueryResultInterface<int, object> $queryBuilder
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function buildGlossaryAsArray(QueryBuilder|QueryResultInterface $queryBuilder, array $options = []): array
    {
        $options['templatePath'] = self::JSON_TEMPLATE;

        $json = $this->getSubject()->buildGlossary($queryBuilder, $options, $this->getExtbaseRequest());

        return json_decode(trim($json), true, 512, JSON_THROW_ON_ERROR);
    }

    private function getSubject(): GlossaryService
    {
        return new GlossaryService(
            GeneralUtility::makeInstance(ExtConf::class),
            $this->eventDispatcher,
            $this->get(ViewFactoryInterface::class),
            new TypoScriptService(),
        );
    }

    private function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        return $queryBuilder;
    }

    /**
     * @return QueryResultInterface<int, object>
     */
    private function getQueryResult(): QueryResultInterface
    {
        $querySettings = GeneralUtility::makeInstance(QuerySettingsInterface::class);
        $querySettings->setStoragePageIds([12]);

        $glossaryRepository = GeneralUtility::makeInstance(GlossaryRepository::class);
        $glossaryRepository->setDefaultQuerySettings($querySettings);

        return $glossaryRepository->getExtbaseQueryForGlossary();
    }

    private function getExtbaseRequest(): RequestInterface
    {
        $extbaseRequestParameters = new ExtbaseRequestParameters();
        $serverRequest = new ServerRequest();
        $serverRequest = $serverRequest->withAttribute('extbase', $extbaseRequestParameters)
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        return new Request($serverRequest);
    }
}
