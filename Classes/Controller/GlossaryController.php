<?php
declare(strict_types = 1);
namespace JWeiland\Glossary2\Controller;

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

use JWeiland\Glossary2\Domain\Model\Glossary;
use JWeiland\Glossary2\Domain\Repository\GlossaryRepository;
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
     * @param GlossaryRepository $glossaryRepository
     */
    public function injectGlossaryRepository(GlossaryRepository $glossaryRepository)
    {
        $this->glossaryRepository = $glossaryRepository;
    }

    public function initializeAction()
    {
        // if this value was not set, then it will be filled with 0
        // but that is not good, because UriBuilder accepts 0 as pid, so it's better to set it to null
        if (empty($this->settings['pidOfDetailPage'])) {
            $this->settings['pidOfDetailPage'] = null;
        }
    }

    /**
     * Initializes the view before invoking an action method.
     *
     * @param ViewInterface $view The view to be initialized
     */
    protected function initializeView(ViewInterface $view)
    {
        $view->assign('data', $this->configurationManager->getContentObject()->data);
    }

    /**
     * @param string $letter Show only records starting with this letter
     * @validate $letter String, StringLength(minimum=1,maximum=3)
     */
    public function listAction($letter = '')
    {
        $glossaries = $this->glossaryRepository->findEntries(
            GeneralUtility::intExplode(',', $this->settings['categories'], true),
            $letter
        );
        $this->view->assign('letter', $letter);
        $this->view->assign('glossaries', $glossaries);
        $this->view->assign('glossary', $this->getGlossary());
    }

    /**
     * @param Glossary $glossary
     */
    public function showAction(Glossary $glossary)
    {
        $letter = strtr(mb_strtolower($glossary->getTitle(){0}), "äöü", "aou");
        $this->view->assign('glossary', $glossary);
        $this->view->assign('letter', $letter);
    }

    /**
     * Get an array with letters as keys for the glossary
     *
     * @return array Array with starting letters as keys
     */
    public function getGlossary(): array
    {
        $possibleLetters = GeneralUtility::trimExplode(',', $this->settings['letters'], true);

        // get available first letters from database
        $availableLetters = $this->glossaryRepository->getStartingLetters(
            GeneralUtility::intExplode(',', $this->settings['categories'], true)
        );
        // remove all letters which are not numbers or letters. Maybe spaces, tabs, - or others
        $availableLetters = str_split(preg_replace('~([[:^alnum:]])~', '', $availableLetters));
        sort($availableLetters);
        $availableLetters = implode('', $availableLetters);

        // if there are numbers inside, replace them with 0-9
        if (preg_match('~^[[:digit:]]+~', $availableLetters)) {
            $availableLetters = preg_replace('~(^[[:digit:]]+)~', '0-9', $availableLetters);
        }

        // mark letter as link (true) or not-linked (false)
        $glossary = [];
        foreach ($possibleLetters as $possibleLetter) {
            $glossary[$possibleLetter] = (strpos($availableLetters, $possibleLetter) !== false) ? true : false;
        }

        return $glossary;
    }
}
