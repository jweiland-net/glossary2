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
use JWeiland\Glossary2\Pagination\GlossaryPagination;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;

class AddPaginatorEventListener extends AbstractControllerEventListener
{
    /**
     * @var int
     */
    protected $itemsPerPage = 15;

    protected $allowedControllerActions = [
        'Glossary' => [
            'list'
        ]
    ];

    public function __invoke(PostProcessFluidVariablesEvent $event): void
    {
        if ($this->isValidRequest($event)) {
            $paginator = new QueryResultPaginator(
                $event->getFluidVariables()['glossaries'],
                $this->getCurrentPage($event),
                $this->getItemsPerPage($event)
            );

            $event->addFluidVariable('actionName', $event->getActionName());
            $event->addFluidVariable('paginator', $paginator);
            $event->addFluidVariable('glossaries', $paginator->getPaginatedItems());
            $event->addFluidVariable('pagination', new GlossaryPagination($paginator));
        }
    }

    protected function getCurrentPage(PostProcessFluidVariablesEvent $event): int
    {
        $currentPage = 1;
        if ($event->getRequest()->hasArgument('currentPage')) {
            $currentPage = $event->getRequest()->getArgument('currentPage');
        }
        return (int)$currentPage;
    }

    protected function getItemsPerPage(PostProcessFluidVariablesEvent $event): int
    {
        $itemsPerPage = $this->itemsPerPage;
        if (isset($event->getSettings()['pageBrowser']['itemsPerPage'])) {
            $itemsPerPage = $event->getSettings()['pageBrowser']['itemsPerPage'];
        }
        return (int)$itemsPerPage;
    }
}
