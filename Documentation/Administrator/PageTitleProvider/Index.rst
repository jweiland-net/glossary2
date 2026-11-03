:navigation-title: Page title provider

..  include:: /Includes.rst.txt
..  _page-title-provider:

===================
Page title provider
===================

In normal case you only will see something like "detail view" in title of
detail page. If you want to change that title to current glossary title you
can make use of the TYPO3 page title providers. Luckily glossary2 comes with
its own provider to realize a pretty nice detail page-title for you with
following TypoScript:

..  code-block:: typoscript
    :caption: TypoScript setup

    config.pageTitleProviders {
      glossary2 {
        provider = JWeiland\Glossary2\PageTitleProvider\Glossary2PageTitleProvider
        # Load the glossary2 provider before the providers "record" and "seo"
        before = record, seo
      }
    }

The page title is only changed on the detail view, which means the request
contains the argument `tx_glossary2_glossary[glossary]`. On all other pages
the provider returns an empty title and the next provider of the chain is
used.

..  versionchanged:: 7.1.0
    The provider gets the current request from the TYPO3
    `PageTitleProviderManager`. Pages without glossary2 arguments do not
    throw a `TypeError` anymore. The configuration via
    `config.pageTitleProviders` is unchanged.
