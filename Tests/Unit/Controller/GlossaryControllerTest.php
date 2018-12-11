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
use JWeiland\Glossary2\Controller\GlossaryController;
use JWeiland\Glossary2\Domain\Repository\GlossaryRepository;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfigurationService;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Response;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Test case
 */
class GlossaryControllerTest extends UnitTestCase
{
    /**
     * @var GlossaryController
     */
    protected $subject;

    /**
     * @var Request|ObjectProphecy
     */
    protected $requestProphecy;

    /**
     * @var Response|ObjectProphecy
     */
    protected $responseProphecy;

    /**
     * @var ObjectManager|ObjectProphecy
     */
    protected $objectManagerProphecy;

    /**
     * @var Arguments|ObjectProphecy
     */
    protected $argumentsProphecy;

    /**
     * @var UriBuilder|ObjectProphecy
     */
    protected $uriBuilderProphecy;

    /**
     * @var ConfigurationManager|ObjectProphecy
     */
    protected $configurationManagerProphecy;

    /**
     * @var ReflectionService|ObjectProphecy
     */
    protected $reflectionServiceProphecy;

    /**
     * @var ValidatorResolver|ObjectProphecy
     */
    protected $validatorResolverProphecy;

    /**
     * @var MvcPropertyMappingConfigurationService|ObjectProphecy
     */
    protected $mvcPropertyMapperConfigurationServiceProphecy;

    /**
     * @var ControllerContext|ObjectProphecy
     */
    protected $controllerContextProphecy;

    /**
     * @var TemplateView|ObjectProphecy
     */
    protected $templateViewProphecy;

    /**
     * @var ContentObjectRenderer|ObjectProphecy
     */
    protected $contentObjectRendererProphecy;

    /**
     * @var Dispatcher|ObjectProphecy
     */
    protected $signalSlotDispatcherProphecy;

    /**
     * @var GlossaryRepository|ObjectProphecy
     */
    protected $glossaryRepositoryProphecy;

    /**
     * @var PageRenderer|ObjectProphecy
     */
    protected $pageRendererProphecy;

    /**
     * set up fixure
     *
     * @return void
     */
    public function setUp()
    {
        $this->subject = new GlossaryController();

        $this->requestProphecy = $this->prophesize(Request::class);
        $this->responseProphecy = $this->prophesize(Response::class);
        $this->objectManagerProphecy = $this->prophesize(ObjectManager::class);
        $this->argumentsProphecy = $this->prophesize(Arguments::class);
        $this->uriBuilderProphecy = $this->prophesize(UriBuilder::class);
        $this->configurationManagerProphecy = $this->prophesize(ConfigurationManager::class);
        $this->reflectionServiceProphecy = $this->prophesize(ReflectionService::class);
        $this->validatorResolverProphecy = $this->prophesize(ValidatorResolver::class);
        $this->mvcPropertyMapperConfigurationServiceProphecy = $this->prophesize(MvcPropertyMappingConfigurationService::class);
        $this->controllerContextProphecy = $this->prophesize(ControllerContext::class);
        $this->templateViewProphecy = $this->prophesize(TemplateView::class);
        $this->contentObjectRendererProphecy = $this->prophesize(ContentObjectRenderer::class);
        $this->signalSlotDispatcherProphecy = $this->prophesize(Dispatcher::class);
        $this->glossaryRepositoryProphecy = $this->prophesize(GlossaryRepository::class);
        $this->pageRendererProphecy = $this->prophesize(PageRenderer::class);

        $this->requestProphecy
            ->setDispatched(true)
            ->shouldBeCalled();
        $this->requestProphecy
            ->getControllerVendorName()
            ->shouldBeCalled()
            ->willReturn('JWeiland');
        $this->requestProphecy
            ->getControllerExtensionName()
            ->shouldBeCalled()
            ->willReturn('Gallery2');
        $this->requestProphecy
            ->getControllerName()
            ->shouldBeCalled()
            ->willReturn('Gallery');
        $this->requestProphecy
            ->getFormat()
            ->shouldBeCalled()
            ->willReturn('html');

        $this->argumentsProphecy
            ->getIterator()
            ->shouldBeCalled()
            ->willReturn(new ObjectStorage());
        $this->argumentsProphecy
            ->getValidationResults()
            ->shouldBeCalled()
            ->willReturn(new Result());

        $this->configurationManagerProphecy
            ->getConfiguration(Argument::cetera())
            ->willReturn([]);
        $this->configurationManagerProphecy
            ->getContentObject()
            ->willReturn($this->contentObjectRendererProphecy->reveal());

        $this->objectManagerProphecy
            ->get(Arguments::class)
            ->shouldBeCalled()
            ->willReturn($this->argumentsProphecy->reveal());
        $this->objectManagerProphecy
            ->get(UriBuilder::class)
            ->shouldBeCalled()
            ->willReturn($this->uriBuilderProphecy->reveal());
        $this->objectManagerProphecy
            ->get(ReflectionService::class)
            ->shouldBeCalled()
            ->willReturn($this->reflectionServiceProphecy->reveal());
        $this->objectManagerProphecy
            ->get(ControllerContext::class)
            ->shouldBeCalled()
            ->willReturn($this->controllerContextProphecy->reveal());
        $this->objectManagerProphecy
            ->get(TemplateView::class)
            ->shouldBeCalled()
            ->willReturn($this->templateViewProphecy->reveal());
        $this->objectManagerProphecy
            ->get(PageRenderer::class)
            ->shouldBeCalled()
            ->willReturn($this->pageRendererProphecy->reveal());

        $this->uriBuilderProphecy
            ->setRequest($this->requestProphecy->reveal())
            ->shouldBeCalled();

        $this->reflectionServiceProphecy
            ->getMethodParameters(Argument::cetera())
            ->willReturn([]);

        $this->validatorResolverProphecy
            ->buildMethodArgumentsValidatorConjunctions(Argument::cetera())
            ->willReturn([]);

        $this->mvcPropertyMapperConfigurationServiceProphecy
            ->initializePropertyMappingConfigurationFromRequest($this->requestProphecy->reveal(), Argument::any())
            ->shouldBeCalled();

        $this->signalSlotDispatcherProphecy
            ->dispatch(Argument::cetera())
            ->shouldBeCalled();

        $this->templateViewProphecy
            ->canRender(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn(true);
        $this->templateViewProphecy
            ->setControllerContext($this->controllerContextProphecy->reveal())
            ->shouldBeCalled();
        $this->templateViewProphecy
            ->initializeView()
            ->shouldBeCalled();
        $this->templateViewProphecy
            ->assign('data', Argument::any())
            ->shouldBeCalled();
        $this->templateViewProphecy
            ->render(Argument::cetera())
            ->shouldBeCalled();
        $this->templateViewProphecy
            ->renderSection(Argument::cetera())
            ->shouldBeCalled();

        $this->subject->injectConfigurationManager($this->configurationManagerProphecy->reveal());
        $this->subject->injectReflectionService($this->reflectionServiceProphecy->reveal());
        $this->subject->injectObjectManager($this->objectManagerProphecy->reveal());
        $this->subject->injectValidatorResolver($this->validatorResolverProphecy->reveal());
        $this->subject->injectMvcPropertyMappingConfigurationService($this->mvcPropertyMapperConfigurationServiceProphecy->reveal());
        $this->subject->injectSignalSlotDispatcher($this->signalSlotDispatcherProphecy->reveal());
        $this->subject->injectGlossaryRepository($this->glossaryRepositoryProphecy->reveal());
    }

    /**
     * tear down fixure
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->subject);
        unset(
            $this->requestProphecy,
            $this->responseProphecy,
            $this->objectManagerProphecy,
            $this->argumentsProphecy,
            $this->uriBuilderProphecy,
            $this->configurationManagerProphecy,
            $this->reflectionServiceProphecy,
            $this->validatorResolverProphecy,
            $this->mvcPropertyMapperConfigurationServiceProphecy,
            $this->controllerContextProphecy,
            $this->templateViewProphecy,
            $this->contentObjectRendererProphecy,
            $this->signalSlotDispatcherProphecy,
            $this->glossaryRepositoryProphecy,
            $this->pageRendererProphecy
        );
    }

    /**
     * @test
     */
    public function processRequestConvertsEmptyPidOfDetailPageToNull()
    {
        $queryResultProphecy = $this->prophesize(QueryResult::class);
        $this->glossaryRepositoryProphecy
            ->findEntries(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn($queryResultProphecy->reveal());
        $this->glossaryRepositoryProphecy
            ->getStartingLetters(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn('A,B,C');

        $this->requestProphecy
            ->getControllerActionName()
            ->shouldBeCalled()
            ->willReturn('list');
        $this->configurationManagerProphecy
            ->getConfiguration('Settings')
            ->shouldBeCalled()
            ->willReturn([
                'letters' => '',
                'pidOfDetailPage' => 0
            ]);

        $this->templateViewProphecy
            ->assign('settings', [
                'letters' => '',
                'pidOfDetailPage' => null
            ])
            ->shouldBeCalled();
        $this->templateViewProphecy
            ->assign('glossaries', Argument::any())
            ->shouldBeCalled();
        $this->templateViewProphecy
            ->assign('glossary', Argument::any())
            ->shouldBeCalled();

        $this->subject->injectConfigurationManager($this->configurationManagerProphecy->reveal());
        $this->subject->processRequest($this->requestProphecy->reveal(), $this->responseProphecy->reveal());
    }

    /**
     * @test
     */
    public function processRequestCallsFindEntriesInListActionWithoutCategories()
    {
        $queryResultProphecy = $this->prophesize(QueryResult::class);
        $this->glossaryRepositoryProphecy
            ->findEntries([], '')
            ->shouldBeCalled()
            ->willReturn($queryResultProphecy->reveal());
        $this->glossaryRepositoryProphecy
            ->getStartingLetters(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn('A,B,C');

        $this->requestProphecy
            ->getControllerActionName()
            ->shouldBeCalled()
            ->willReturn('list');

        $this->templateViewProphecy
            ->assign(Argument::cetera())
            ->shouldBeCalled();

        $this->subject->processRequest($this->requestProphecy->reveal(), $this->responseProphecy->reveal());
    }

    /**
     * @test
     */
    public function processRequestGeneratesGlossaryInListAction()
    {
        $queryResultProphecy = $this->prophesize(QueryResult::class);
        $this->glossaryRepositoryProphecy
            ->findEntries(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn($queryResultProphecy->reveal());
        $this->glossaryRepositoryProphecy
            ->getStartingLetters(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn('A,B,C');

        $this->requestProphecy
            ->getControllerActionName()
            ->shouldBeCalled()
            ->willReturn('list');
        $this->configurationManagerProphecy
            ->getConfiguration('Settings')
            ->shouldBeCalled()
            ->willReturn([
                'categories' => '',
                'letters' => '0-9,A,B,C,D',
                'pidOfDetailPage' => 0
            ]);

        $this->templateViewProphecy
            ->assign('settings', [
                'categories' => '',
                'letters' => '0-9,A,B,C,D',
                'pidOfDetailPage' => null
            ])
            ->shouldBeCalled();
        $this->templateViewProphecy
            ->assign('glossaries', Argument::any())
            ->shouldBeCalled();
        $this->templateViewProphecy
            ->assign('glossary', [
                '0-9' => false,
                'A' => true,
                'B' => true,
                'C' => true,
                'D' => false,
            ])
            ->shouldBeCalled();

        $this->subject->injectConfigurationManager($this->configurationManagerProphecy->reveal());
        $this->subject->processRequest($this->requestProphecy->reveal(), $this->responseProphecy->reveal());
    }
}
