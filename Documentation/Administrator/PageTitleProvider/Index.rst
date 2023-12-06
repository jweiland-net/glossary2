..  include:: /Includes.rst.txt


===================
Page Title Provider
===================

In normal case you only will see something like "detail view" in title of detail page.
If you want to change that title to current glossary title you can make use
of the new TYPO3 page-title providers (since TYPO3 9.4). Luckily glossary2 comes with its own provider to
realize a pretty nice detail page-title for you with following TypoScript:

..  code-block:: typoscript

    config.pageTitleProviders {
      glossary2 {
        provider = JWeiland\Glossary2\PageTitleProvider\Glossary2PageTitleProvider
        # Please add these providers, to be safe loading glossary2 provider before these two.
        before = record, seo
      }
    }
