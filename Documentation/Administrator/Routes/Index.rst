..  include:: /Includes.rst.txt


======
Routes
======

With TYPO3 9 you have the possibility to configure RouteEnhancers

Example Configuration
=====================

..  code-block:: none

    routeEnhancers:
      Glossary2Plugin:
        type: 'Extbase'
        extension: 'Glossary2'
        plugin: 'Glossary'
        routes:
          -
            routePath: '/first-glossary-page'
            _controller: 'Glossary::list'
          -
            routePath: '/glossary-page-{page}'
            _controller: 'Glossary::list'
            _arguments:
              page: 'currentPage'
          -
            routePath: '/glossary-by-letter/{letter}'
            _controller: 'Glossary::list'
          -
            routePath: '/show/{glossary_title}'
            _controller: 'Glossary::show'
            _arguments:
              glossary_title: 'glossary'
        defaults:
          page: '0'
        requirements:
          letter: '^(0-9|[a-z])$'
          glossary_title: '^[a-zA-Z0-9\-]+$'
        defaultController: 'Glossary::list'
        aspects:
          glossary_title:
            type: 'PersistedAliasMapper'
            tableName: 'tx_glossary2_domain_model_glossary'
            routeFieldName: 'path_segment'
          page:
            type: StaticRangeMapper
            start: '1'
            end: '10'
          letter:
            type: StaticValueMapper
            map:
              '0-9': '0-9'
              'a': 'a'
              'b': 'b'
              'c': 'c'
              'd': 'd'
              'e': 'e'
              'f': 'f'
              'g': 'g'
              'h': 'h'
              'i': 'i'
              'j': 'j'
              'k': 'k'
              'l': 'l'
              'm': 'm'
              'n': 'n'
              'o': 'o'
              'p': 'p'
              'q': 'q'
              'r': 'r'
              's': 's'
              't': 't'
              'u': 'u'
              'v': 'v'
              'w': 'w'
              'x': 'x'
              'y': 'y'
              'z': 'z'
              '': ''
