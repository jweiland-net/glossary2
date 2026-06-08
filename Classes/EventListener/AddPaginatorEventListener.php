<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\EventListener;

use JWeiland\Glossary2\Event\PostProcessFluidVariablesEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Pagination\PaginationInterface;
use TYPO3\CMS\Core\Pagination\PaginatorInterface;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;

#[AsEventListener(
    identifier: 'glossary2/add-paginator-event-listener',
)]
final readonly class AddPaginatorEventListener extends AbstractControllerEventListener
{
    private const DEFAULT_ITEMS_PER_PAGE = 15;
    private const FLUID_VARIABLE = 'glossaries';
    private const FALLBACK_PAGINATION = SimplePagination::class;

    /**
     * @var array<string, mixed>
     */
    public const ALLOWED_CONTROLLER_ACTIONS = [
        'Glossary' => [
            'list',
        ],
    ];

    public function __invoke(PostProcessFluidVariablesEvent $event): void
    {
        if ($this->isValidRequest($event)) {
            $paginator = new QueryResultPaginator(
                $event->getFluidVariables()[self::FLUID_VARIABLE],
                $this->getCurrentPage($event),
                $this->getItemsPerPage($event),
            );

            $event->addFluidVariable('actionName', $event->getActionName());
            $event->addFluidVariable('paginator', $paginator);
            $event->addFluidVariable(self::FLUID_VARIABLE, $paginator->getPaginatedItems());
            $event->addFluidVariable('pagination', $this->getPagination($event, $paginator));
        }
    }

    private function getCurrentPage(PostProcessFluidVariablesEvent $event): int
    {
        if ($event->getRequest()->hasArgument('currentPage')) {
            return MathUtility::forceIntegerInRange(
                (int)$event->getRequest()->getArgument('currentPage'),
                1,
            );
        }
        return 1;
    }

    private function getItemsPerPage(PostProcessFluidVariablesEvent $event): int
    {
        return (int)($event->getSettings()['pageBrowser']['itemsPerPage'] ?? self::DEFAULT_ITEMS_PER_PAGE);
    }

    private function getPagination(
        PostProcessFluidVariablesEvent $event,
        PaginatorInterface $paginator,
    ): PaginationInterface {
        $paginationClass = $event->getSettings()['pageBrowser']['class'] ?? self::FALLBACK_PAGINATION;

        if (!class_exists($paginationClass)) {
            $paginationClass = self::FALLBACK_PAGINATION;
        }

        if (!is_subclass_of($paginationClass, PaginationInterface::class)) {
            $paginationClass = self::FALLBACK_PAGINATION;
        }

        return new $paginationClass($paginator);
    }
}
