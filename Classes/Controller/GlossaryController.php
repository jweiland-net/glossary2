<?php
declare(strict_types=1);
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

/**
 * Main controller of glossary2
 */
class GlossaryController extends ActionController
{
    /**
     * glossaryRepository
     *
     * @var \JWeiland\Glossary2\Domain\Repository\GlossaryRepository
     */
    protected $glossaryRepository;

    /**
     * inject method for glossary repository
     *
     * @param \JWeiland\Glossary2\Domain\Repository\GlossaryRepository $glossaryRepository
     * @return void
     */
    public function injectGlossaryRepository(\JWeiland\Glossary2\Domain\Repository\GlossaryRepository $glossaryRepository)
    {
        $this->glossaryRepository = $glossaryRepository;
    }

    /**
     * preprocessing of all actions
     *
     * @return void
     */
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
     * Override this method to solve assign variables common for all actions
     * or prepare the view in another way before the action is called.
     *
     * @param ViewInterface $view The view to be initialized
     * @return void
     */
    protected function initializeView(ViewInterface $view)
    {
        $this->view->assign('data', $this->configurationManager->getContentObject()->data);
    }

    /**
     * action list
     *
     * @param string $letter Show only records starting with this letter
     * @validate $letter String, StringLength(minimum=1,maximum=3)
     * @return void
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
     * action list without glossar
     *
     * @return void
     */
    public function listWithoutGlossarAction()
    {
        $glossaries = $this->glossaryRepository->findEntries(
            GeneralUtility::intExplode(',', $this->settings['categories'], true),
            ''
        );
        $this->view->assign('glossaries', $glossaries);
    }

    /**
     * action show
     *
     * @param \JWeiland\Glossary2\Domain\Model\Glossary $glossary
     * @return void
     */
    public function showAction(\JWeiland\Glossary2\Domain\Model\Glossary $glossary)
    {
        $this->view->assign('glossary', $glossary);
    }

    /**
     * get an array with letters as keys for the glossary
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
