<?php
declare(strict_types=1);
namespace JWeiland\Glossary2\Domain\Repository;

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
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * This class contains all queries to get needed glossary entries from DB
 */
class GlossaryRepository extends Repository
{
    /**
     * @var array
     */
    protected $defaultOrderings = [
        'title' => QueryInterface::ORDER_ASCENDING
    ];

    /**
     * find entries
     *
     * @param array $categories
     * @param string $letter
     * @return QueryResultInterface
     */
    public function findEntries(array $categories = [], $letter = '')
    {
        // return full list, if arguments are not valid
        if (!$this->checkArgumentsForFindEntries($categories, $letter)) {
            return $this->findAll();
        }

        $query = $this->createQuery();
        $constraint = [];

        // add category to constraint
        if (count($categories)) {
            $categoryConstraints = [];
            foreach ($categories as $category) {
                $categoryConstraints[] = $query->contains('categories', $category);
            }
            $constraint[] = $query->logicalOr($categoryConstraints);
        }

        // add letter to constraint
        if (!empty($letter)) {
            $letterConstraints = [];
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
    public function checkArgumentsForFindEntries(array $categories, $letter)
    {
        // check categories
        if (is_array($categories)) {
            $intCategories = GeneralUtility::intExplode(',', implode(',', $categories), true);
            if ($intCategories !== $categories) {
                return false;
            }
            if (in_array(0, $intCategories)) {
                return false;
            }
        } else {
            return false;
        }

        // check letter
        if (!is_string($letter) || ($letter !== '' && !preg_match('@^0-9$|^[A-Z]$@', $letter))) {
            return false;
        }

        return true;
    }

    /**
     * get an array with available starting letters
     *
     * @param array $categories
     * @return array
     */
    public function getStartingLetters(array $categories = array())
    {
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
            )->execute(true);
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
                BackendUtility::BEenableFields('tx_glossary2_domain_model_glossary') .
                BackendUtility::deleteClause('tx_glossary2_domain_model_glossary')
            )->execute(true);
        }
        return $availableLetters;
    }
}
