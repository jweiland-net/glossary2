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
use JWeiland\Glossary2\Service\GlossaryService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

/**
 * Main controller of glossary2
 */
class GlossaryController extends ActionController
{
    /**
     * @var GlossaryRepository
     */
    protected $glossaryRepository;

    /**
     * @var GlossaryService
     */
    protected $glossaryService;

    public function __construct(
        GlossaryRepository $glossaryRepository,
        GlossaryService $glossaryService
    ) {
        $this->glossaryRepository = $glossaryRepository;
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
        $view->assign('data', $this->configurationManager->getContentObject()->data);
    }

    /**
     * @param string $letter Show only records starting with this letter
     * @TYPO3\CMS\Extbase\Annotation\Validate("StringLength", options={"minimum": 1, "maximum": 3}, param="letter")
     */
    public function listAction(string $letter = ''): void
    {
        $categories = GeneralUtility::intExplode(',', $this->settings['categories'], true);
        $glossaries = $this->glossaryRepository->findEntries($categories, $letter);

        $this->view->assign('letter', $letter);
        $this->view->assign('glossaries', $glossaries);
        $this->view->assign(
            'glossary',
            $this->glossaryService->buildGlossary(
                $this->glossaryRepository->getQueryBuilderForGlossary($categories),
                ['settings' => $this->settings]
            )
        );
    }

    /**
     * @param Glossary $glossary
     */
    public function showAction(Glossary $glossary): void
    {
        $letter = strtr(mb_strtolower($glossary->getTitle()[0]), 'äöü', 'aou');
        $this->view->assign('glossary', $glossary);
        $this->view->assign('letter', $letter);
    }
}
