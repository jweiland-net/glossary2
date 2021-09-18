<?php

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Tests\Functional\Service;

use JWeiland\Glossary2\Configuration\ExtConf;
use JWeiland\Glossary2\Event\PostProcessFirstLettersEvent;
use JWeiland\Glossary2\Event\SanitizeValueForCharsetHelperEvent;
use JWeiland\Glossary2\Helper\CharsetHelper;
use JWeiland\Glossary2\Helper\OverlayHelper;
use JWeiland\Glossary2\Service\GlossaryService;
use JWeiland\Glossary2\Tests\Functional\Fixtures\ProcessFirstLettersEventListener;
use JWeiland\Glossary2\Tests\Functional\Fixtures\SanitizeValueEventListener;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\EventDispatcher\ListenerProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Test case
 */
class GlossaryServiceTest extends FunctionalTestCase
{
    /**
     * @var GlossaryService
     */
    protected $subject;

    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * @var OverlayHelper
     */
    protected $overlayHelper;

    /**
     * @var ListenerProvider|ObjectProphecy
     */
    protected $listenerProviderProphecy;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var ConfigurationManagerInterface|ObjectProphecy
     */
    protected $configurationManagerProphecy;

    /**
     * @var Request|ObjectProphecy
     */
    protected $requestProphecy;

    /**
     * @var StandaloneView|ObjectProphecy
     */
    protected $viewProphecy;

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

        $this->extConf = new ExtConf();
        $this->overlayHelper = GeneralUtility::makeInstance(OverlayHelper::class);
        $this->listenerProviderProphecy = $this->prophesize(ListenerProvider::class);
        $this->listenerProviderProphecy
            ->getListenersForEvent(Argument::any())
            ->willReturn([]);
        $this->eventDispatcher = new EventDispatcher($this->listenerProviderProphecy->reveal());
        GeneralUtility::addInstance(
            CharsetHelper::class,
            new CharsetHelper(
                new CharsetConverter(),
                $this->eventDispatcher
            )
        );

        $this->configurationManagerProphecy = $this->prophesize(ConfigurationManager::class);
        $this->configurationManagerProphecy
            ->getConfiguration(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn([]);

        $this->requestProphecy = $this->prophesize(Request::class);

        $this->viewProphecy = $this->prophesize(StandaloneView::class);
        $this->viewProphecy
            ->setTemplatePathAndFilename(Argument::any())
            ->shouldBeCalled();
        $this->viewProphecy
            ->getRequest()
            ->shouldBeCalled()
            ->willReturn($this->requestProphecy->reveal());
        $this->viewProphecy->assign(Argument::cetera())->shouldBeCalled();
        $this->viewProphecy->render()->shouldBeCalled()->willReturn('');
        GeneralUtility::addInstance(StandaloneView::class, $this->viewProphecy->reveal());
    }

    public function tearDown()
    {
        unset(
            $this->subject,
            $this->extConf,
            $this->overlayHelper,
            $this->eventDispatcher,
            $this->viewProphecy
        );
        parent::tearDown();
    }

    /**
     * @tester
     */
    public function buildGlossaryWillConvertGermanUmlauts()
    {
        $this->viewProphecy
            ->assign('glossary', $this->getGlossary())
            ->shouldBeCalled();

        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->overlayHelper,
            $this->eventDispatcher,
            $this->configurationManagerProphecy->reveal()
        );

        $this->subject->buildGlossary($queryBuilder);
    }

    /**
     * As we are working with "EXT:" and getFileAbsFileName() we can only use paths of existing extensions
     * while testing. In that case just "glossary2"
     */
    public function dataProviderForTemplatePath()
    {
        return [
            'Default templatePath from ExtConf of glossary2' => [
                [],
                [],
                'EXT:glossary2/Resources/Private/Templates/Glossary.html'
            ],
            'Default templatePath provided by foreign extension' => [
                [
                    'templatePath' => 'EXT:glossary2/Resources/Private/Templates/Yellowpages2.html'
                ],
                [],
                'EXT:glossary2/Resources/Private/Templates/Yellowpages2.html'
            ],
            'Default templatePath provided by TypoScript (string)' => [
                [
                    'templatePath' => 'EXT:glossary2/Resources/Private/Templates/Yellowpages2.html'
                ],
                [
                    'templatePath' => 'EXT:glossary2/Resources/Private/Templates/GlossaryDefault.html'
                ],
                'EXT:glossary2/Resources/Private/Templates/GlossaryDefault.html'
            ],
            'Default templatePath provided by TypoScript (array)' => [
                [
                    'templatePath' => 'EXT:glossary2/Resources/Private/Templates/Yellowpages2.html'
                ],
                [
                    'templatePath' => [
                        'default' => 'EXT:glossary2/Resources/Private/Templates/Default.html'
                    ]
                ],
                'EXT:glossary2/Resources/Private/Templates/Default.html'
            ],
            'ExtKey individual templatePath provided by TypoScript (array)' => [
                [
                    'templatePath' => 'EXT:glossary2/Resources/Private/Templates/Yellowpages2.html',
                    'extensionName' => 'clubdirectory'
                ],
                [
                    'templatePath' => [
                        'default' => 'EXT:glossary2/Resources/Private/Templates/Default.html',
                        'clubdirectory' => 'EXT:glossary2/Resources/Private/Templates/Clubdirectory.html'
                    ]
                ],
                'EXT:glossary2/Resources/Private/Templates/Clubdirectory.html'
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider dataProviderForTemplatePath
     */
    public function buildGlossaryWillUseDefaultTemplatePath(array $options, array $settings, string $expectedPath)
    {
        $this->configurationManagerProphecy
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'Glossary2',
                'Glossary'
            )
            ->shouldBeCalled()
            ->willReturn($settings);

        $this->viewProphecy
            ->setTemplatePathAndFilename(
                GeneralUtility::getFileAbsFileName(
                    $expectedPath
                )
            )
            ->shouldBeCalled();
        $this->viewProphecy
            ->assign('glossary', $this->getGlossary())
            ->shouldBeCalled();

        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->overlayHelper,
            $this->eventDispatcher,
            $this->configurationManagerProphecy->reveal()
        );

        $this->subject->buildGlossary($queryBuilder, $options);
    }

    /**
     * @test
     */
    public function buildGlossaryWillConvertSpecialCharToAsciiByEvent()
    {
        $this->listenerProviderProphecy
            ->getListenersForEvent(Argument::type(SanitizeValueForCharsetHelperEvent::class))
            ->shouldBeCalled()
            ->willReturn([new SanitizeValueEventListener()]);

        // Set link of letter "o" to true
        $expectedGlossary = $this->getGlossary();
        $expectedGlossary[5]['hasLink'] = false;
        $expectedGlossary[15]['hasLink'] = true;

        $this->viewProphecy
            ->assign('glossary', $expectedGlossary)
            ->shouldBeCalled();

        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->overlayHelper,
            $this->eventDispatcher,
            $this->configurationManagerProphecy->reveal()
        );

        $this->subject->buildGlossary($queryBuilder);
    }

    /**
     * @test
     */
    public function buildGlossaryWithModifiedLettersByEvent()
    {
        $this->listenerProviderProphecy
            ->getListenersForEvent(Argument::type(PostProcessFirstLettersEvent::class))
            ->shouldBeCalled()
            ->willReturn([new ProcessFirstLettersEventListener()]);

        $expectedGlossary = $this->getGlossary();
        // Remove link for letter "a"
        $expectedGlossary[1]['hasLink'] = false;
        // Add link for letter "k"
        $expectedGlossary[11]['hasLink'] = true;

        $this->viewProphecy
            ->assign('glossary', $expectedGlossary)
            ->shouldBeCalled();

        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->overlayHelper,
            $this->eventDispatcher,
            $this->configurationManagerProphecy->reveal()
        );

        $this->subject->buildGlossary($queryBuilder);
    }

    /**
     * @test
     */
    public function buildGlossaryWithIndividualColumnAndAliasWillBuildGlossar()
    {
        $this->viewProphecy
            ->assign('glossary', $this->getGlossary())
            ->shouldBeCalled();

        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->overlayHelper,
            $this->eventDispatcher,
            $this->configurationManagerProphecy->reveal()
        );

        $this->subject->buildGlossary(
            $queryBuilder,
            [
                'column' => 'title',
                'columnAlias' => 'Buchstaben'
            ]
        );
    }

    /**
     * @test
     */
    public function buildGlossaryWillAddSettingsToView()
    {
        $this->viewProphecy
            ->assign('settings', ['foo' => 'bar'])
            ->shouldBeCalled();

        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->overlayHelper,
            $this->eventDispatcher,
            $this->configurationManagerProphecy->reveal()
        );

        $this->subject->buildGlossary(
            $queryBuilder,
            [
                'settings' => [
                    'foo' => 'bar'
                ]
            ]
        );
    }

    /**
     * @test
     */
    public function buildGlossaryWithDefaultLettersWillNotMergeNumbers()
    {
        $expectedGlossary = $this->getGlossary();
        $expectedGlossary[0]['hasLink'] = false;

        $this->viewProphecy
            ->assign('glossary', $expectedGlossary)
            ->shouldBeCalled();

        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->overlayHelper,
            $this->eventDispatcher,
            $this->configurationManagerProphecy->reveal()
        );

        $this->subject->buildGlossary(
            $queryBuilder,
            [
                'mergeNumbers' => false
            ]
        );
    }

    /**
     * @test
     */
    public function buildGlossaryWithOwnLettersWillNotMergeNumbers()
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
                'isRequestedLetter' => false
            ],
            [
                'letter' => '1',
                'hasLink' => true,
                'isRequestedLetter' => false
            ],
            [
                'letter' => '3',
                'hasLink' => false,
                'isRequestedLetter' => false
            ]
        );

        $this->viewProphecy
            ->assign('glossary', $expectedGlossary)
            ->shouldBeCalled();

        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->overlayHelper,
            $this->eventDispatcher,
            $this->configurationManagerProphecy->reveal()
        );

        $this->subject->buildGlossary(
            $queryBuilder,
            [
                'mergeNumbers' => false,
                'possibleLetters' => '0,1,3,a,b,c,d,e,g,h,i,j,k,l,m,n,p,q,r,s,t,u,v,w,x,y,z'
            ]
        );
    }

    /**
     * @test
     */
    public function buildGlossaryWillUseGlossaryRequestForLinkGeneration()
    {
        $this->requestProphecy
            ->setControllerExtensionName('Glossary2')
            ->shouldBeCalled();
        $this->requestProphecy
            ->setPluginName('glossary')
            ->shouldBeCalled();
        $this->requestProphecy
            ->setControllerName('Glossary')
            ->shouldBeCalled();
        $this->requestProphecy
            ->setControllerActionName('list')
            ->shouldBeCalled();

        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->overlayHelper,
            $this->eventDispatcher,
            $this->configurationManagerProphecy->reveal()
        );

        $this->subject->buildGlossary($queryBuilder);
    }

    /**
     * @test
     */
    public function buildGlossaryWillUseForeignRequestForLinkGeneration()
    {
        $this->requestProphecy
            ->setControllerExtensionName('SyncCropAreas')
            ->shouldBeCalled();
        $this->requestProphecy
            ->setPluginName('crop')
            ->shouldBeCalled();
        $this->requestProphecy
            ->setControllerName('Cropping')
            ->shouldBeCalled();
        $this->requestProphecy
            ->setControllerActionName('view')
            ->shouldBeCalled();

        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->overlayHelper,
            $this->eventDispatcher,
            $this->configurationManagerProphecy->reveal()
        );

        $this->subject->buildGlossary(
            $queryBuilder,
            [
                'extensionName' => 'sync_crop_areas',
                'pluginName' => 'crop',
                'controllerName' => 'Cropping',
                'actionName' => 'view'
            ]
        );
    }

    protected function getGlossary(): array
    {
        return [
            0 => [
                'letter' => '0-9',
                'hasLink' => true,
                'isRequestedLetter' => false
            ],
            1 => [
                'letter' => 'a',
                'hasLink' => true,
                'isRequestedLetter' => false
            ],
            2 => [
                'letter' => 'b',
                'hasLink' => false,
                'isRequestedLetter' => false
            ],
            3 => [
                'letter' => 'c',
                'hasLink' => false,
                'isRequestedLetter' => false
            ],
            4 => [
                'letter' => 'd',
                'hasLink' => false,
                'isRequestedLetter' => false
            ],
            5 => [
                'letter' => 'e',
                'hasLink' => true,
                'isRequestedLetter' => false
            ],
            6 => [
                'letter' => 'f',
                'hasLink' => false,
                'isRequestedLetter' => false
            ],
            7 => [
                'letter' => 'g',
                'hasLink' => false,
                'isRequestedLetter' => false
            ],
            8 => [
                'letter' => 'h',
                'hasLink' => false,
                'isRequestedLetter' => false
            ],
            9 => [
                'letter' => 'i',
                'hasLink' => false,
                'isRequestedLetter' => false
            ],
            10 => [
                'letter' => 'j',
                'hasLink' => false,
                'isRequestedLetter' => false
            ],
            11 => [
                'letter' => 'k',
                'hasLink' => false,
                'isRequestedLetter' => false
            ],
            12 => [
                'letter' => 'l',
                'hasLink' => false,
                'isRequestedLetter' => false
            ],
            13 => [
                'letter' => 'm',
                'hasLink' => false,
                'isRequestedLetter' => false
            ],
            14 => [
                'letter' => 'n',
                'hasLink' => false,
                'isRequestedLetter' => false
            ],
            15 => [
                'letter' => 'o',
                'hasLink' => false,
                'isRequestedLetter' => false
            ],
            16 => [
                'letter' => 'p',
                'hasLink' => true,
                'isRequestedLetter' => false
            ],
            17 => [
                'letter' => 'q',
                'hasLink' => false,
                'isRequestedLetter' => false
            ],
            18 => [
                'letter' => 'r',
                'hasLink' => false,
                'isRequestedLetter' => false
            ],
            19 => [
                'letter' => 's',
                'hasLink' => false,
                'isRequestedLetter' => false
            ],
            20 => [
                'letter' => 't',
                'hasLink' => false,
                'isRequestedLetter' => false
            ],
            21 => [
                'letter' => 'u',
                'hasLink' => true,
                'isRequestedLetter' => false
            ],
            22 => [
                'letter' => 'v',
                'hasLink' => false,
                'isRequestedLetter' => false
            ],
            23 => [
                'letter' => 'w',
                'hasLink' => false,
                'isRequestedLetter' => false
            ],
            24 => [
                'letter' => 'x',
                'hasLink' => false,
                'isRequestedLetter' => false
            ],
            25 => [
                'letter' => 'y',
                'hasLink' => false,
                'isRequestedLetter' => false
            ],
            26 => [
                'letter' => 'z',
                'hasLink' => false,
                'isRequestedLetter' => false
            ],
        ];
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
