<?php
declare(strict_types = 1);
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
use JWeiland\Glossary2\Service\DatabaseService;
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
    public function findEntries(array $categories = [], string $letter = ''): QueryResultInterface
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
     * Check arguments of method findEntries
     *
     * @param array $categories
     * @param string $letter
     * @return bool
     */
    protected function checkArgumentsForFindEntries(array $categories, string $letter): bool
    {
        // check categories as they can also be set by TypoScript
        $intCategories = GeneralUtility::intExplode(
            ',',
            implode(
                ',',
                $categories
            ),
            true
        );
        if ($intCategories !== $categories) {
            return false;
        }
        if (in_array(0, $intCategories)) {
            return false;
        }

        // check letter
        if (!is_string($letter) || ($letter !== '' && !preg_match('@^0-9$|^[A-Z]$@', $letter))) {
            return false;
        }

        return true;
    }

    /**
     * Get an array with available starting letters
     *
     * @param array $categories
     * @return string
     */
    public function getStartingLetters(array $categories = []): string
    {
        // return empty array, if argument is not valid
        if (!$this->checkArgumentsForFindEntries($categories, '')) {
            return '';
        }

        $databaseService = GeneralUtility::makeInstance(DatabaseService::class);
        $rows = $databaseService->getGroupedFirstLetters($this->createQuery(), $categories);

        $letters = [];
        foreach ($rows as $row) {
            $letters[] = strtr($row['Letter'], [
                'Ä' => 'A',
                'Ö' => 'O',
                'Ü' => 'U',
            ]);
        }

        return implode(',', array_unique($letters));
    }
}
