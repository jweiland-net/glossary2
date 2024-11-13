<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Controller;

use JWeiland\Glossary2\Domain\Model\Glossary;
use JWeiland\Glossary2\Domain\Repository\GlossaryRepository;
use JWeiland\Glossary2\Event\PostProcessFluidVariablesEvent;
use JWeiland\Glossary2\Service\GlossaryService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Main controller of glossary2 to list and show glossary records
 */
class GlossaryController extends ActionController
{
    protected GlossaryRepository $glossaryRepository;

    protected GlossaryService $glossaryService;

    public function injectGlossaryRepository(GlossaryRepository $glossaryRepository): void
    {
        $this->glossaryRepository = $glossaryRepository;
    }

    public function injectGlossaryService(GlossaryService $glossaryService): void
    {
        $this->glossaryService = $glossaryService;
    }

    public function initializeAction(): void
    {
        // If this value was not set, then it will be filled with 0, but this is bad as
        // UriBuilder accepts 0 as pid. So it's better to set it to null
        if (empty($this->settings['pidOfDetailPage'])) {
            $this->settings['pidOfDetailPage'] = null;
        }
    }

    protected function initializeView(ViewInterface $view): void
    {
        $view->assign('data', $this->getContentObjectData());
    }

    /**
     * @param string $letter Show only records starting with this letter
     * @Extbase\Validate("StringLength", options={"minimum": 1, "maximum": 3}, param="letter")
     */
    public function listAction(string $letter = ''): ResponseInterface
    {
        $this->postProcessAndAssignFluidVariables([
            'letter' => $letter,
            'glossaries' => $this->glossaryRepository->searchGlossaries(
                GeneralUtility::intExplode(',', $this->settings['categories'], true),
                $letter,
            ),
        ]);
        return $this->htmlResponse();
    }

    public function showAction(Glossary $glossary): ResponseInterface
    {
        $this->postProcessAndAssignFluidVariables([
            'glossary' => $glossary,
            'letter' => $glossary->getSanitizedFirstLetterOfTitle(),
        ]);
        return $this->htmlResponse();
    }

    /**
     * @param array<string, mixed> $variables
     */
    protected function postProcessAndAssignFluidVariables(array $variables = []): void
    {
        /** @var PostProcessFluidVariablesEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new PostProcessFluidVariablesEvent(
                /** @phpstan-ignore-next-line */
                $this->request,
                $this->settings,
                $variables,
            ),
        );

        $this->view->assignMultiple($event->getFluidVariables());
    }

    /**
     * @return array<string, mixed>
     */
    protected function getContentObjectData(): array
    {
        $data = [];
        $contentObjectRenderer = $this->request->getAttribute('currentContentObject');
        if ($contentObjectRenderer instanceof ContentObjectRenderer && is_array($contentObjectRenderer->data)) {
            $data = $contentObjectRenderer->data;
        }

        return $data;
    }
}
