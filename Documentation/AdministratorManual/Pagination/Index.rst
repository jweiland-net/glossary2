.. include:: ../../Includes.txt

Pagination
==========

EXT:glossary2 comes with its own little pagination to navigate to `first`, `previous`, `next` and `last` page. If
you need something more complex like `1, 2 ... 56, 57, 58 ... 123, 124` you should use another pagination library or
build your own one. In the next steps I explain you how to implement the solution of Georg Ringers
numbered_pagination.

.. rst-class:: bignums

1. Add or modify Configuration/Services.yaml

   .. code-block:: none

      services:
        _defaults:
          autowire: true
          autoconfigure: true
          public: false

        JWeiland\SitePackage\:
          resource: '../Classes/*'

        JWeiland\SitePackage\EventListener\AddPaginatorEventListener:
          tags:
            - name: event.listener
              event: JWeiland\Glossary2\Event\PostProcessFluidVariablesEvent

2. Create EventListener

   Create file `Classes/EventListener/AddPaginatorEventListener.php` with following content:

   .. code-block:: php

      <?php

      declare(strict_types=1);

      /*
       * This file is part of the package jweiland/site-package.
       *
       * For the full copyright and license information, please read the
       * LICENSE file that was distributed with this source code.
       */

      namespace JWeiland\SitePackage\EventListener;

      use GeorgRinger\NumberedPagination\NumberedPagination;
      use JWeiland\Glossary2\Event\PostProcessFluidVariablesEvent;
      use JWeiland\Glossary2\EventListener\AbstractControllerEventListener;
      use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
      use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

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
              if (
                  $this->isValidRequest($event)
                  && $event->getFluidVariables()['glossaries'] instanceof QueryResultInterface
              ) {
                  // Reset $queryResult back to ALL records
                  $queryResult = $event
                      ->getFluidVariables()['glossaries']
                      ->getQuery()
                      ->unsetLimit(0)
                      ->setOffset(0)
                      ->execute();

                  $paginator = new QueryResultPaginator(
                      $queryResult,
                      $this->getCurrentPage($event),
                      $this->getItemsPerPage($event)
                  );

                  $event->addFluidVariable('actionName', $event->getActionName());
                  $event->addFluidVariable('paginator', $paginator);
                  $event->addFluidVariable('glossaries', $paginator->getPaginatedItems());
                  $event->addFluidVariable('pagination', new NumberedPagination($paginator));
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

3. Change path to glossary2 partials

   Set constant `partialRootPath` to a location within your SitePackage:

   .. code-block:: typoscript

      plugin.tx_glossary2.view.partialRootPath = EXT:site_package/Resources/Private/Extensions/Glossary2/Partials/

4. Create Pagination template

   Create file `Resources/Private/Extensions/Glossary2/Partials/Component/Pagination.html` with example content
   from numbered_pagination https://github.com/georgringer/numbered_pagination/blob/master/Resources/Private/Partials/Pagination.html

   .. code-block:: html

      <html lang="en"
            xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
            data-namespace-typo3-fluid="true">

      <ul class="f3-widget-paginator">
         <f:if condition="{pagination.previousPageNumber} && {pagination.previousPageNumber} >= {pagination.firstPageNumber}">
            <li class="previous">
               <a href="{f:uri.action(action:actionName, arguments:{currentPage: pagination.previousPageNumber})}" title="{f:translate(key:'pagination.previous')}">
                  {f:translate(key:'widget.pagination.previous', extensionName: 'fluid')}
               </a>
            </li>
         </f:if>
         <f:if condition="{pagination.hasLessPages}">
            <li>…</li>
         </f:if>
         <f:for each="{pagination.allPageNumbers}" as="page">
            <li class="{f:if(condition: '{page} == {paginator.currentPageNumber}', then:'current')}">
               <a href="{f:uri.action(action:actionName, arguments:{currentPage: page})}">{page}</a>
            </li>
         </f:for>
         <f:if condition="{pagination.hasMorePages}">
            <li>…</li>
         </f:if>
         <f:if condition="{pagination.nextPageNumber} && {pagination.nextPageNumber} <= {pagination.lastPageNumber}">
            <li class="next">
               <a href="{f:uri.action(action:actionName, arguments:{currentPage: pagination.nextPageNumber})}" title="{f:translate(key:'pagination.next')}">
                  {f:translate(key:'widget.pagination.next', extensionName: 'fluid')}
               </a>
            </li>
         </f:if>
      </ul>
      </html>

5. Flush Cache in Installtool

   Needed to activate our new EventListener from Services.yaml. Clear Cache from the upper right (flash) is
   not enough.
