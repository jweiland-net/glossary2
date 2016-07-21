<?php
namespace JWeiland\Glossary2\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Stefan Froemken <projects@jweiland.net>, jweiland.net
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @package glossary2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GlossaryController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

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
    public function injectGlossaryRepository(\JWeiland\Glossary2\Domain\Repository\GlossaryRepository $glossaryRepository) {
        $this->glossaryRepository = $glossaryRepository;
    }

    /**
     * preprocessing of all actions
     *
     * @return void
     */
    public function initializeAction() {
        // if this value was not set, then it will be filled with 0
        // but that is not good, because UriBuilder accepts 0 as pid, so it's better to set it to NULL
        if (empty($this->settings['pidOfDetailPage'])) {
            $this->settings['pidOfDetailPage'] = NULL;
        }
    }

    /**
     * action list
     *
     * @param string $letter Show only records starting with this letter
     * @validate $letter String, StringLength(minimum=1,maximum=3)
     * @return void
     */
    public function listAction($letter = '') {
        $glossaries = $this->glossaryRepository->findEntries(
            GeneralUtility::intExplode(',', $this->settings['categories'], TRUE),
            $letter
        );
        $this->view->assign('glossaries', $glossaries);
        $this->view->assign('glossary', $this->getGlossary());
    }

    /**
     * action show
     *
     * @param \JWeiland\Glossary2\Domain\Model\Glossary $glossary
     * @return array
     */
    public function showAction(\JWeiland\Glossary2\Domain\Model\Glossary $glossary) {
        $this->view->assign('glossary', $glossary);
    }

    /**
     * get an array with letters as keys for the glossary
     *
     * @return array Array with starting letters as keys
     */
    public function getGlossary() {
        $possibleLetters = GeneralUtility::trimExplode(',', $this->settings['letters']);

        // get available first letters from database
        $availableLetters = $this->glossaryRepository->getStartingLetters(
            GeneralUtility::intExplode(',', $this->settings['categories'], TRUE)
        );
        // remove all letters which are not numbers or letters. Maybe spaces, tabs, - or others
        $availableLetters = str_split(preg_replace('~([[:^alnum:]])~', '', $availableLetters['letters']));
        sort($availableLetters);
        $availableLetters = implode('', $availableLetters);

        // if there are numbers inside, replace them with 0-9
        if (preg_match('~^[[:digit:]]+~', $availableLetters)) {
            $availableLetters = preg_replace('~(^[[:digit:]]+)~', '0-9', $availableLetters);
        }

        // mark letter as link (TRUE) or not-linked (FALSE)
        $glossary = array();
        foreach ($possibleLetters as $possibleLetter) {
            $glossary[$possibleLetter] = (strpos($availableLetters, $possibleLetter) !== FALSE) ? TRUE : FALSE;
        }

        return $glossary;
    }

}