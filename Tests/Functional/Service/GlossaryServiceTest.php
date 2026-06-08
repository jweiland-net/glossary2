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
use JWeiland\Glossary2\Helper\CharsetHelper;
use JWeiland\Glossary2\Service\GlossaryService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\EventDispatcher\ListenerProvider;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case
 */
class GlossaryServiceTest extends FunctionalTestCase
{
    protected GlossaryService $subject;

    protected ExtConf $extConf;

    protected CharsetHelper $charsetHelper;

    protected ListenerProvider $listenerProvider;

    protected EventDispatcher $eventDispatcher;

    protected TypoScriptService $typoScriptService;

    protected ViewFactoryInterface|MockObject $viewFactory;

    protected ServerRequest $serverRequest;

    /**
     * @var string[]
     */
    protected array $testExtensionsToLoad = [
        'jweiland/glossary2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setSetupArray([]);
        $this->serverRequest = (new ServerRequest())
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('frontend.typoscript', $frontendTypoScript);
        $GLOBALS['TYPO3_REQUEST'] = $this->serverRequest;

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/tx_glossary2_domain_model_glossary.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/sys_category.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/sys_category_record_mm.csv');

        $this->extConf = GeneralUtility::makeInstance(ExtConf::class);
        $this->listenerProvider = $this->createMock(ListenerProvider::class);
        $this->eventDispatcher = new EventDispatcher($this->listenerProvider);

        $this->charsetHelper = new CharsetHelper(
            $this->get(CharsetConverter::class),
            $this->eventDispatcher,
        );

        $this->typoScriptService = new TypoScriptService();

        $mockView = $this->createMock(ViewInterface::class);
        $mockView->method('render')->willReturn('');
        $this->viewFactory = $this->createMock(ViewFactoryInterface::class);
        $this->viewFactory->method('create')->willReturn($mockView);
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->extConf,
            $this->charsetHelper,
            $this->eventDispatcher,
            $this->typoScriptService,
            $this->viewFactory,
            $this->serverRequest,
        );
        parent::tearDown();
    }

    #[Test]
    public function buildGlossaryWillConvertGermanUmlauts(): void
    {
        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->eventDispatcher,
            $this->viewFactory,
            $this->charsetHelper,
            $this->typoScriptService,
        );

        $this->subject->buildGlossary($queryBuilder, [], $this->serverRequest);
    }

    #[Test]
    public function buildGlossaryWillConvertSpecialCharToAsciiByEvent(): void
    {
        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->eventDispatcher,
            $this->viewFactory,
            $this->charsetHelper,
            $this->typoScriptService,
        );

        $this->subject->buildGlossary($queryBuilder, [], $this->serverRequest);
    }

    #[Test]
    public function buildGlossaryWithModifiedLettersByEvent(): void
    {
        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->eventDispatcher,
            $this->viewFactory,
            $this->charsetHelper,
            $this->typoScriptService,
        );

        $this->subject->buildGlossary($queryBuilder, [], $this->serverRequest);
    }

    #[Test]
    public function buildGlossaryWithIndividualColumnAndAliasWillBuildGlossar(): void
    {
        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->eventDispatcher,
            $this->viewFactory,
            $this->charsetHelper,
            $this->typoScriptService,
        );

        $this->subject->buildGlossary(
            $queryBuilder,
            [
                'column' => 'title',
                'columnAlias' => 'Buchstaben',
            ],
            $this->serverRequest,
        );
    }

    #[Test]
    public function buildGlossaryWillAddSettingsToView(): void
    {
        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->eventDispatcher,
            $this->viewFactory,
            $this->charsetHelper,
            $this->typoScriptService,
        );

        $this->subject->buildGlossary(
            $queryBuilder,
            [
                'settings' => [
                    'foo' => 'bar',
                ],
            ],
            $this->serverRequest,
        );
    }

    #[Test]
    public function buildGlossaryWithDefaultLettersWillNotMergeNumbers(): void
    {
        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->eventDispatcher,
            $this->viewFactory,
            $this->charsetHelper,
            $this->typoScriptService,
        );

        $this->subject->buildGlossary(
            $queryBuilder,
            [
                'mergeNumbers' => false,
            ],
            $this->serverRequest,
        );
    }

    #[Test]
    public function buildGlossaryWithOwnLettersWillNotMergeNumbers(): void
    {
        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->eventDispatcher,
            $this->viewFactory,
            $this->charsetHelper,
            $this->typoScriptService,
        );

        $this->subject->buildGlossary(
            $queryBuilder,
            [
                'mergeNumbers' => false,
                'possibleLetters' => '0,1,3,a,b,c,d,e,g,h,i,j,k,l,m,n,p,q,r,s,t,u,v,w,x,y,z',
            ],
            $this->serverRequest,
        );
    }

    #[Test]
    public function buildGlossaryWillUseGlossaryRequestForLinkGeneration(): void
    {
        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->eventDispatcher,
            $this->viewFactory,
            $this->charsetHelper,
            $this->typoScriptService,
        );

        $this->subject->buildGlossary($queryBuilder, [], $this->serverRequest);
    }

    #[Test]
    public function buildGlossaryWillUseForeignRequestForLinkGeneration(): void
    {
        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->eventDispatcher,
            $this->viewFactory,
            $this->charsetHelper,
            $this->typoScriptService,
        );

        $this->subject->buildGlossary(
            $queryBuilder,
            [
                'extensionName' => 'sync_crop_areas',
                'pluginName' => 'crop',
                'controllerName' => 'Cropping',
                'actionName' => 'view',
            ],
            $this->serverRequest,
        );
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
