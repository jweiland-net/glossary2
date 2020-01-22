.. include:: ../../Includes.txt

Routes
======

With TYPO3 9 you have the possibility to configure RouteEnhancers

Example Configuration
---------------------

.. code-block:: none

   routeEnhancers:
     Glossary2Plugin:
       type: Extbase
       extension: Glossary2
       plugin: Glossary
       routes:
         -
           routePath: '/first-glossary-page'
           _controller: 'Glossary::list'
         -
           routePath: '/glossary-page-{page}'
           _controller: 'Glossary::list'
           _arguments:
             page: '@widget_0/currentPage'
         -
           routePath: '/glossary-by-letter/{letter}'
           _controller: 'Glossary::list'
         -
           routePath: '/show/{glossary_title}'
           _controller: 'Glossary::show'
           _arguments:
             glossary_title: glossary
       requirements:
         letter: '^(0-9|[a-z])$'
         glossary_title: '^[a-zA-Z0-9]+\-[0-9]+$'
       defaultController: 'Glossary::list'
       aspects:
         glossary_title:
           type: PersistedAliasMapper
           tableName: tx_glossary2_domain_model_glossary
           routeFieldName: path_segment
