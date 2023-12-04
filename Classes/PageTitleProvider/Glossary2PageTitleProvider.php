<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\PageTitleProvider;

use JWeiland\Glossary2\Domain\Model\Glossary;
use JWeiland\Glossary2\Domain\Repository\GlossaryRepository;
use TYPO3\CMS\Core\PageTitle\PageTitleProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Instead of just setting the PageTitle to DetailView on Detail Page,
 * we think it would be much cooler to see the Glossary title in Browser-Tab.
 *
 * Please use config.pageTitleProviders.* to use our PageTitleProvider.
 */
class Glossary2PageTitleProvider implements PageTitleProviderInterface
{
    protected GlossaryRepository $glossaryRepository;

    public function __construct(GlossaryRepository $glossaryRepository)
    {
        $this->glossaryRepository = $glossaryRepository;
    }

    public function getTitle(): string
    {
        $pageTitle = '';
        $gp = $this->getMergedRequestParameters();

        if ($this->isValidRequest($gp)) {
            $glossaryRecord = $this->glossaryRepository->findByUid((int)$gp['glossary']);
            if ($glossaryRecord instanceof Glossary) {
                $pageTitle = sprintf(
                    '%s',
                    trim($glossaryRecord->getTitle())
                );
            }
        }

        return $pageTitle;
    }

    protected function getMergedRequestParameters(): array
    {
        return GeneralUtility::_GPmerged('tx_glossary2_glossary');
    }

    /**
     * This PageTitleProvider will only work on detail page of glossary2.
     * glossary have to be given. Else: Page title will not be overwritten.
     */
    protected function isValidRequest(array $gp): bool
    {
        if (!isset($gp['action'], $gp['glossary'])) {
            return false;
        }

        return (int)$gp['glossary'] > 0;
    }
}
