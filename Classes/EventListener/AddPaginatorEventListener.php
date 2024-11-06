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
use TYPO3\CMS\Core\Pagination\PaginationInterface;
use TYPO3\CMS\Core\Pagination\PaginatorInterface;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;

class AddPaginatorEventListener extends AbstractControllerEventListener
{
    protected int $itemsPerPage = 15;

    /**
     * Fluid variable name for paginated records
     */
    protected string $fluidVariableForPaginatedRecords = 'glossaries';

    protected string $fallbackPaginationClass = SimplePagination::class;

    /**
     * @var array<string, mixed>
     */
    protected array $allowedControllerActions = [
        'Glossary' => [
            'list',
        ],
    ];

    public function __invoke(PostProcessFluidVariablesEvent $event): void
    {
        if ($this->isValidRequest($event)) {
            $paginator = new QueryResultPaginator(
                $event->getFluidVariables()[$this->fluidVariableForPaginatedRecords],
                $this->getCurrentPage($event),
                $this->getItemsPerPage($event),
            );

            $event->addFluidVariable('actionName', $event->getActionName());
            $event->addFluidVariable('paginator', $paginator);
            $event->addFluidVariable($this->fluidVariableForPaginatedRecords, $paginator->getPaginatedItems());
            $event->addFluidVariable('pagination', $this->getPagination($event, $paginator));
        }
    }

    protected function getCurrentPage(PostProcessFluidVariablesEvent $event): int
    {
        $currentPage = 1;
        if ($event->getRequest()->hasArgument('currentPage')) {
            // $currentPage have to be positive and greater than 0
            // See: AbstractPaginator::setCurrentPageNumber()
            $currentPage = MathUtility::forceIntegerInRange(
                (int)$event->getRequest()->getArgument('currentPage'),
                1,
            );
        }

        return $currentPage;
    }

    protected function getItemsPerPage(PostProcessFluidVariablesEvent $event): int
    {
        return (int)($event->getSettings()['pageBrowser']['itemsPerPage'] ?? $this->itemsPerPage);
    }

    protected function getPagination(
        PostProcessFluidVariablesEvent $event,
        PaginatorInterface $paginator,
    ): PaginationInterface {
        $paginationClass = $event->getSettings()['pageBrowser']['class'] ?? $this->fallbackPaginationClass;

        if (!class_exists($paginationClass)) {
            $paginationClass = $this->fallbackPaginationClass;
        }

        if (!is_subclass_of($paginationClass, PaginationInterface::class)) {
            $paginationClass = $this->fallbackPaginationClass;
        }

        // Explicitly tell PHPStan that the result is an instance of PaginationInterface
        /** @phpstan-ignore-next-line */
        return new $paginationClass($paginator);
    }
}
