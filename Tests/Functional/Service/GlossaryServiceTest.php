<?php

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
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\EventDispatcher\ListenerProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

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
     * @var ListenerProvider
     */
    protected $listenerProvider;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var Request|MockObject
     */
    protected $requestMock;

    /**
     * @var StandaloneView|MockObject
     */
    protected $viewMock;

    /**
     * @var string[]
     */
    protected array $testExtensionsToLoad = [
        'jweiland/glossary2',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/tx_glossary2_domain_model_glossary.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/sys_category.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/sys_category_record_mm.csv');

        $this->extConf = GeneralUtility::makeInstance(ExtConf::class);
        $this->listenerProvider = $this->createMock(ListenerProvider::class);
        $this->eventDispatcher = new EventDispatcher($this->listenerProvider);

        GeneralUtility::addInstance(
            CharsetHelper::class,
            new CharsetHelper(
                new CharsetConverter(),
                $this->eventDispatcher,
            ),
        );

        $this->configurationManager = $this->createMock(ConfigurationManager::class);

        $this->requestMock = $this->createMock(Request::class);

        $this->viewMock = $this->createMock(StandaloneView::class);
        $this->viewMock
            ->expects(self::atLeastOnce())
            ->method('setTemplatePathAndFilename');
        $this->viewMock
            ->expects(self::any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->viewMock
            ->expects(self::atLeastOnce())
            ->method('assign');
        $this->viewMock
            ->expects(self::atLeastOnce())
            ->method('render')
            ->willReturn('running some functional tests');
        GeneralUtility::addInstance(StandaloneView::class, $this->viewMock);

    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $this->extConf,
            $this->eventDispatcher,
            $this->viewMock,
        );
        parent::tearDown();
    }

    /**
     * @test
     */
    public function buildGlossaryWillConvertGermanUmlauts(): void
    {
        $this->viewMock
            ->assign('glossary', $this->getGlossary());

        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->eventDispatcher,
            $this->configurationManager,
        );

        $this->subject->buildGlossary($queryBuilder);
    }

    /**
     * As we are working with "EXT:" and getFileAbsFileName() we can only use paths of existing extensions
     * while testing. In that case just "glossary2"
     */
    public function dataProviderForTemplatePath(): array
    {
        return [
            'Default templatePath from ExtConf of glossary2' => [
                [],
                [],
                'EXT:glossary2/Resources/Private/Templates/Glossary.html',
            ],
            'Default templatePath provided by foreign extension' => [
                [
                    'templatePath' => 'EXT:glossary2/Resources/Private/Templates/Yellowpages2.html',
                ],
                [],
                'EXT:glossary2/Resources/Private/Templates/Yellowpages2.html',
            ],
            'Default templatePath provided by TypoScript (string)' => [
                [
                    'templatePath' => 'EXT:glossary2/Resources/Private/Templates/Yellowpages2.html',
                ],
                [
                    'templatePath' => 'EXT:glossary2/Resources/Private/Templates/GlossaryDefault.html',
                ],
                'EXT:glossary2/Resources/Private/Templates/GlossaryDefault.html',
            ],
            'Default templatePath provided by TypoScript (array)' => [
                [
                    'templatePath' => 'EXT:glossary2/Resources/Private/Templates/Yellowpages2.html',
                ],
                [
                    'templatePath' => [
                        'default' => 'EXT:glossary2/Resources/Private/Templates/Default.html',
                    ],
                ],
                'EXT:glossary2/Resources/Private/Templates/Default.html',
            ],
            'ExtKey individual templatePath provided by TypoScript (array)' => [
                [
                    'templatePath' => 'EXT:glossary2/Resources/Private/Templates/Yellowpages2.html',
                    'extensionName' => 'clubdirectory',
                ],
                [
                    'templatePath' => [
                        'default' => 'EXT:glossary2/Resources/Private/Templates/Default.html',
                        'clubdirectory' => 'EXT:glossary2/Resources/Private/Templates/Clubdirectory.html',
                    ],
                ],
                'EXT:glossary2/Resources/Private/Templates/Clubdirectory.html',
            ],
        ];
    }

    /**
     * @test
     *
     * @dataProvider dataProviderForTemplatePath
     */
    public function buildGlossaryWillUseDefaultTemplatePath(array $options, array $settings, string $expectedPath): void
    {
        $this->configurationManager
            ->expects(self::atLeastOnce())
            ->method('getConfiguration')
            ->with(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'Glossary2',
                'Glossary',
            )
            ->willReturn($settings);

        $this->viewMock->setTemplatePathAndFilename(
            GeneralUtility::getFileAbsFileName(
                $expectedPath,
            ),
        );
        $this->viewMock->assign('glossary', $this->getGlossary());

        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->eventDispatcher,
            $this->configurationManager,
        );

        $this->subject->buildGlossary($queryBuilder, $options);
    }

    /**
     * @test
     */
    public function buildGlossaryWillConvertSpecialCharToAsciiByEvent(): void
    {
        // Set link of letter "o" to true
        $expectedGlossary = $this->getGlossary();
        $expectedGlossary[5]['hasLink'] = false;
        $expectedGlossary[15]['hasLink'] = true;

        $this->viewMock
            ->assign('glossary', $expectedGlossary);

        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->eventDispatcher,
            $this->configurationManager,
        );

        $this->subject->buildGlossary($queryBuilder);
    }

    /**
     * @test
     */
    public function buildGlossaryWithModifiedLettersByEvent(): void
    {
        $expectedGlossary = $this->getGlossary();
        // Remove link for letter "a"
        $expectedGlossary[1]['hasLink'] = false;
        // Add link for letter "k"
        $expectedGlossary[11]['hasLink'] = true;

        $this->viewMock
            ->assign('glossary', $expectedGlossary);

        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->eventDispatcher,
            $this->configurationManager,
        );

        $this->subject->buildGlossary($queryBuilder);
    }

    /**
     * @test
     */
    public function buildGlossaryWithIndividualColumnAndAliasWillBuildGlossar(): void
    {
        $this->viewMock
            ->assign('glossary', $this->getGlossary());

        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->eventDispatcher,
            $this->configurationManager,
        );

        $this->subject->buildGlossary(
            $queryBuilder,
            [
                'column' => 'title',
                'columnAlias' => 'Buchstaben',
            ],
        );
    }

    /**
     * @test
     */
    public function buildGlossaryWillAddSettingsToView(): void
    {
        $this->viewMock
            ->assign('settings', ['foo' => 'bar']);

        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->eventDispatcher,
            $this->configurationManager,
        );

        $this->subject->buildGlossary(
            $queryBuilder,
            [
                'settings' => [
                    'foo' => 'bar',
                ],
            ],
        );
    }

    /**
     * @test
     */
    public function buildGlossaryWithDefaultLettersWillNotMergeNumbers(): void
    {
        $expectedGlossary = $this->getGlossary();
        $expectedGlossary[0]['hasLink'] = false;

        $this->viewMock
            ->assign('glossary', $expectedGlossary);

        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->eventDispatcher,
            $this->configurationManager,
        );

        $this->subject->buildGlossary(
            $queryBuilder,
            [
                'mergeNumbers' => false,
            ],
        );
    }

    /**
     * @test
     */
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

        $this->viewMock
            ->assign('glossary', $expectedGlossary);

        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->eventDispatcher,
            $this->configurationManager,
        );

        $this->subject->buildGlossary(
            $queryBuilder,
            [
                'mergeNumbers' => false,
                'possibleLetters' => '0,1,3,a,b,c,d,e,g,h,i,j,k,l,m,n,p,q,r,s,t,u,v,w,x,y,z',
            ],
        );
    }

    /**
     * @test
     */
    public function buildGlossaryWillUseGlossaryRequestForLinkGeneration(): void
    {
        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->eventDispatcher,
            $this->configurationManager,
        );

        $this->subject->buildGlossary($queryBuilder);
    }

    /**
     * @test
     */
    public function buildGlossaryWillUseForeignRequestForLinkGeneration(): void
    {

        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject = new GlossaryService(
            $this->extConf,
            $this->eventDispatcher,
            $this->configurationManager,
        );

        $this->subject->buildGlossary(
            $queryBuilder,
            [
                'extensionName' => 'sync_crop_areas',
                'pluginName' => 'crop',
                'controllerName' => 'Cropping',
                'actionName' => 'view',
            ],
        );
    }

    protected function getGlossary(): array
    {
        return [
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
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
