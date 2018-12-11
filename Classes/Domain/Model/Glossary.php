<?php
declare(strict_types=1);
namespace JWeiland\Glossary2\Domain\Model;

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

use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * This class contains all getters and setters for a glossary entry
 */
class Glossary extends AbstractEntity
{
    /**
     * Title
     *
     * @var string
     */
    protected $title = '';

    /**
     * Description
     *
     * @var string
     */
    protected $description = '';

    /**
     * Images
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     * @lazy
     */
    protected $images;

    /**
     * categories
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category>
     * @lazy
     */
    protected $categories;

    /**
     * Constructor of this class.
     */
    public function __construct()
    {
        $this->initStorageObjects();
    }

    /**
     * Initializes all ObjectStorage properties.
     */
    protected function initStorageObjects()
    {
        $this->images = new ObjectStorage();
        $this->categories = new ObjectStorage();
    }

    /**
     * Returns the title
     *
     * @return string $title
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Sets the title
     *
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * Returns the description
     *
     * @return string $description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Sets the description
     *
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * Returns the images
     *
     * @return ObjectStorage
     */
    public function getImages(): ObjectStorage
    {
        return $this->images;
    }

    /**
     * Sets the images
     *
     * @param ObjectStorage $images
     */
    public function setImages(ObjectStorage $images)
    {
        $this->images = $images;
    }

    /**
     * Add an image
     *
     * @param FileReference $image
     */
    public function addImage(FileReference $image)
    {
        $this->images->attach($image);
    }

    /**
     * Remove an image
     *
     * @param FileReference $image
     */
    public function removeImage(FileReference $image)
    {
        $this->images->detach($image);
    }

    /**
     * Returns the categories
     *
     * @return ObjectStorage
     */
    public function getCategories(): ObjectStorage
    {
        return $this->categories;
    }

    /**
     * Sets the categories
     *
     * @param ObjectStorage $categories
     */
    public function setCategories(ObjectStorage $categories)
    {
        $this->categories = $categories;
    }

    /**
     * Add a category
     *
     * @param Category $category
     */
    public function addCategory(Category $category)
    {
        $this->categories->attach($category);
    }

    /**
     * Remove a category
     *
     * @param Category $category
     */
    public function removeCategory(Category $category)
    {
        $this->categories->detach($category);
    }
}
