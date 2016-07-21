<?php
namespace JWeiland\Glossary2\Domain\Repository;

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
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @package glossary2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GlossaryRepository extends Repository {

	/**
	 * @var array
	 */
	protected $defaultOrderings = array(
		'title' => QueryInterface::ORDER_ASCENDING
	);

	/**
	 * find entries
	 *
	 * @param array $categories
	 * @param string $letter
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function findEntries(array $categories = array(), $letter = '') {
		// return full list, if arguments are not valid
		if (!$this->checkArgumentsForFindEntries($categories, $letter)) {
			return $this->findAll();
		}

		$query = $this->createQuery();
		$constraint = array();

		// add category to constraint
		if (count($categories)) {
			$categoryConstraints = array();
			foreach ($categories as $category) {
				$categoryConstraints[] = $query->contains('categories', $category);
			}
			$constraint[] = $query->logicalOr($categoryConstraints);
		}

		// add letter to constraint
		if (!empty($letter)) {
			$letterConstraints = array();
			if ($letter == '0-9') {
				for ($i = 0; $i < 10; $i++) {
					$letterConstraints[] = $query->like('title', $i . '%');
				}
			} else {
				$letterConstraints[] = $query->like('title', $letter . '%');
			}
			$constraint[] = $query->logicalOr($letterConstraints);
		}

		if (count($constraint)) {
			return $query->matching(
				$query->logicalAnd($constraint)
			)->execute();
		} else {
			return $this->findAll();
		}
	}

	/**
	 * check arguments of method findEntries
	 *
	 * @param array $categories
	 * @param string $letter
	 * @return bool
	 */
	public function checkArgumentsForFindEntries(array $categories, $letter) {
		// check categories
		if (is_array($categories)) {
			$intCategories = GeneralUtility::intExplode(',', implode(',', $categories), TRUE);
			if ($intCategories !== $categories) {
				return FALSE;
			}
			if (in_array(0, $intCategories)) {
				return FALSE;
			}
		} else {
			return FALSE;
		}

		// check letter
		if (!is_string($letter) || ($letter !== '' && !preg_match('@^0-9$|^[A-Z]$@', $letter))) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * get an array with available starting letters
	 *
	 * @param array $categories
	 * @return array
	 */
	public function getStartingLetters(array $categories = array()) {
		// return empty array, if argument is not valid
		if (!$this->checkArgumentsForFindEntries($categories, '')) {
			return array();
		}
		/** @var \TYPO3\CMS\Extbase\Persistence\Generic\Query $query */
		$query = $this->createQuery();
		if ($categories === array()) {
			list($availableLetters) = $query->statement('
				SELECT GROUP_CONCAT(DISTINCT REPLACE(REPLACE(REPLACE(LEFT(UPPER(title), 1), \'Ä\', \'A\'), \'Ö\', \'O\'), \'Ü\', \'U\')) as letters
				FROM tx_glossary2_domain_model_glossary
				WHERE FIND_IN_SET(tx_glossary2_domain_model_glossary.pid, "' . implode(',', $query->getQuerySettings()->getStoragePageIds()) . '")' .
				BackendUtility::BEenableFields('tx_glossary2_domain_model_glossary')	.
				BackendUtility::deleteClause('tx_glossary2_domain_model_glossary')
			)->execute(TRUE);
		} else {
			list($availableLetters) = $query->statement('
				SELECT GROUP_CONCAT(DISTINCT REPLACE(REPLACE(REPLACE(LEFT(UPPER(title), 1), \'Ä\', \'A\'), \'Ö\', \'O\'), \'Ü\', \'U\')) as letters
				FROM tx_glossary2_domain_model_glossary
				LEFT JOIN sys_category_record_mm
				ON sys_category_record_mm.uid_foreign=tx_glossary2_domain_model_glossary.uid
				WHERE FIND_IN_SET(tx_glossary2_domain_model_glossary.pid, "' . implode(',', $query->getQuerySettings()->getStoragePageIds()) . '")
				AND sys_category_record_mm.tablenames="tx_glossary2_domain_model_glossary"
				AND sys_category_record_mm.fieldname="categories"
				AND FIND_IN_SET(sys_category_record_mm.uid_local, "' . implode(',', $categories) . '")' .
				BackendUtility::BEenableFields('tx_glossary2_domain_model_glossary')	.
				BackendUtility::deleteClause('tx_glossary2_domain_model_glossary')
			)->execute(TRUE);
		}
		return $availableLetters;
	}

}