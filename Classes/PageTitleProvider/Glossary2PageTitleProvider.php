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
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\PageTitle\PageTitleProviderInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * Instead of just setting the PageTitle to DetailView on Detail Page,
 * we think it would be much cooler to see the Glossary title in Browser-Tab.
 *
 * Please use config.pageTitleProviders.* to use our PageTitleProvider.
 */
class Glossary2PageTitleProvider implements PageTitleProviderInterface
{
    private ?ServerRequestInterface $request = null;

    public function __construct(
        protected readonly GlossaryRepository $glossaryRepository,
    ) {}

    /**
     * Called by PageTitleProviderManager with the current frontend request
     */
    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    public function getTitle(): string
    {
        $gp = $this->getValidPluginArguments();

        if ($gp !== null) {
            $glossaryRecord = $this->glossaryRepository->findByUid((int)$gp['glossary']);

            if ($glossaryRecord instanceof Glossary) {
                return trim($glossaryRecord->getTitle());
            }
        }

        return '';
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getValidPluginArguments(): ?array
    {
        if ($this->request === null) {
            return null;
        }

        $gp = $this->getPluginArgumentsFromRequest($this->request);
        if (is_array($gp) && $this->isValidRequest($gp)) {
            return $gp;
        }

        return null;
    }

    /**
     * This PageTitleProvider will only work on detail page of glossary2.
     * glossary have to be given. Else: Page title will not be overwritten.
     *
     * @param array<string, mixed> $gp
     */
    protected function isValidRequest(array $gp): bool
    {
        if (!isset($gp['action'], $gp['glossary'])) {
            return false;
        }

        return (int)$gp['glossary'] > 0;
    }

    /**
     * @param ServerRequestInterface $requestObject
     * @return string|array<string, mixed>|null
     */
    protected function getPluginArgumentsFromRequest(ServerRequestInterface $requestObject): string|array|null
    {
        $queryParams = $requestObject->getQueryParams();

        return ArrayUtility::isValidPath($queryParams, 'tx_glossary2_glossary')
            ? ArrayUtility::getValueByPath($queryParams, 'tx_glossary2_glossary')
            : null;
    }
}
