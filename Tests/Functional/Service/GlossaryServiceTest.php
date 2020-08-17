<?php

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Tests\Functional\Service;

use JWeiland\Glossary2\Configuration\ExtConf;
use JWeiland\Glossary2\Service\GlossaryService;
use JWeiland\Glossary2\Tests\Functional\Fixtures\GlossaryServiceSignalSlot;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
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
     * @var StandaloneView|ObjectProphecy
     */
    protected $viewProphecy;

    /**
     * @var Request|ObjectProphecy
     */
    protected $requestProphecy;

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

        $this->subject = new GlossaryService($this->extConf);
    }

    public function tearDown()
    {
        unset(
            $this->subject,
            $this->extConf,
            $this->viewProphecy
        );
        parent::tearDown();
    }

    /**
     * @test
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

        $this->subject->buildGlossary($queryBuilder);
    }

    /**
     * @test
     */
    public function buildGlossaryWillUseDefaultTemplatePath()
    {
        $this->viewProphecy
            ->setTemplatePathAndFilename(
                GeneralUtility::getFileAbsFileName(
                    'EXT:glossary2/Resources/Private/Templates/Glossary.html'
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

        $this->subject->buildGlossary($queryBuilder);
    }

    /**
     * @test
     */
    public function buildGlossaryWithOwnTemplatePath()
    {
        $this->viewProphecy
            ->setTemplatePathAndFilename(
                GeneralUtility::getFileAbsFileName(
                    'EXT:events2/Resources/Private/Templates/Glossary.html'
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

        $this->subject->buildGlossary(
            $queryBuilder,
            [
                'templatePath' => 'EXT:events2/Resources/Private/Templates/Glossary.html'
            ]
        );
    }

    /**
     * @test
     */
    public function buildGlossaryWillConvertFrenchUmlautsBySignalSlot()
    {
        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $signalSlotDispatcher->connect(
            GlossaryService::class,
            'modifyLetterMapping',
            GlossaryServiceSignalSlot::class,
            'modifyLetterMapping'
        );

        $expectedGlossary = $this->getGlossary();
        $expectedGlossary['e'] = true;

        $this->viewProphecy
            ->assign('glossary', $expectedGlossary)
            ->shouldBeCalled();

        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

        $this->subject->buildGlossary($queryBuilder);
    }

    /**
     * @test
     */
    public function buildGlossaryWithModifiedLettersBySignalSlot()
    {
        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $signalSlotDispatcher->connect(
            GlossaryService::class,
            'postProcessFirstLetters',
            GlossaryServiceSignalSlot::class,
            'postProcessFirstLetters'
        );

        $expectedGlossary = $this->getGlossary();
        $expectedGlossary['a'] = false;
        $expectedGlossary['k'] = true;

        $this->viewProphecy
            ->assign('glossary', $expectedGlossary)
            ->shouldBeCalled();

        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

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
        $expectedGlossary['0-9'] = false;

        $this->viewProphecy
            ->assign('glossary', $expectedGlossary)
            ->shouldBeCalled();

        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

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
        $expectedGlossary['0'] = false;
        $expectedGlossary['1'] = true;
        $expectedGlossary['3'] = false;
        unset($expectedGlossary['0-9']);
        unset($expectedGlossary['f']);
        unset($expectedGlossary['o']);

        $this->viewProphecy
            ->assign('glossary', $expectedGlossary)
            ->shouldBeCalled();

        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable('tx_glossary2_domain_model_glossary');
        $queryBuilder->from('tx_glossary2_domain_model_glossary');

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
            '0-9' => true,
            'a' => true,
            'b' => false,
            'c' => false,
            'd' => false,
            'e' => false,
            'f' => false,
            'g' => false,
            'h' => false,
            'i' => false,
            'j' => false,
            'k' => false,
            'l' => false,
            'm' => false,
            'n' => false,
            'o' => false,
            'p' => true,
            'q' => false,
            'r' => false,
            's' => false,
            't' => false,
            'u' => true,
            'v' => false,
            'w' => false,
            'x' => false,
            'y' => false,
            'z' => false
        ];
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
