:navigation-title: TypoScript

..  include:: /Includes.rst.txt
..  _typoscript:

==========
TypoScript
==========

..  confval-menu::
    :display: table
    :type:
    :default:

..  _typoscript-view:

View
====

..  confval:: view.templateRootPaths
    :name: typoscript-view-templateRootPaths
    :type: array
    :default: Value from Constants `EXT:glossary2/Resources/Private/Templates/`

    You can override our Templates with your own SitePackage extension. We
    prefer to change this value in TS Constants.

..  confval:: view.partialRootPaths
    :name: typoscript-view-partialRootPaths
    :type: array
    :default: Value from Constants `EXT:glossary2/Resources/Private/Partials/`

    You can override our Partials with your own SitePackage extension. We
    prefer to change this value in TS Constants.

..  confval:: view.layoutsRootPaths
    :name: typoscript-view-layoutRootPaths
    :type: array
    :default: Value from Constants `EXT:glossary2/Resources/Layouts/Templates/`

    You can override our Layouts with your own SitePackage extension. We
    prefer to change this value in TS Constants.

..  _typoscript-persistence:

Persistence
===========

..  confval:: persistence.storagePid
    :name: typoscript-persistence-storagePid
    :type: string

    Set this value to a Storage Folder (PID) where you have stored the
    records.

    Example: `plugin.tx_glossary2.persistence.storagePid = 21,45,3234`

..  _typoscript-settings:

Settings
========

..  confval:: setting.pidOfDetailPage
    :name: typoscript-settings-pidOfDetailPage
    :type: int
    :default: 0

    Example: `plugin.tx_glossary2.settings.pidOfDetailPage = 84`

    Often it is useful to move the detail view onto a separate page
    for design/layout reasons.

..  confval:: setting.templatePath
    :name: typoscript-settings-templatePath
    :type: string or array
    :default: empty (Use value of Extension Settings as fallback)

    Example:
    `plugin.tx_glossary2.settings.templatePath = EXT:events2/Resources/Private/Templates/Glossary2.html`

    With this setting you can override the default templatePath of glossary2
    and defined templatePaths coming from foreign extensions. So TypoScript
    settings have highest priority.

    We also have implemented a more complex setting for `templatePath`:

    ..  code-block:: typoscript
        :caption: TypoScript setup

        plugin.tx_glossary2.settings.templatePath {
          default = EXT:glossary2/Resources/Private/Templates/Glossary2.html
          events2 = EXT:events2/Resources/Private/Templates/Glossary2.html
          yellowpages2 = EXT:yellowpages2/Resources/Private/Templates/Glossary2.html
        }

    `default` will be used, if no templatePath for a given ExtensionKey was
    found.

..  confval:: settings.glossary.mergeNumbers
    :name: typoscript-settings-glossary-mergeNumbers
    :type: bool
    :default: 1

    Example: `plugin.tx_glossary2.settings.glossary.mergeNumbers = 0`

    Merge record titles starting with numbers to `0-9` in glossary.

..  confval:: settings.glossary.showAllLink
    :name: typoscript-settings-glossary-showAllLink
    :type: bool
    :default: 1

    Example: `plugin.tx_glossary2.settings.glossary.showAllLink = 0`

    Prepend an additional button in front of the glossary to show all records
    again.

..  confval:: settings.pageBrowser.itemsPerPage
    :name: typoscript-settings-pageBrowser-itemsPerPage
    :type: int
    :default: 15

    Example: `plugin.tx_glossary2.settings.pageBrowser.itemsPerPage = 20`

    Reduce result of records to this value for a page
