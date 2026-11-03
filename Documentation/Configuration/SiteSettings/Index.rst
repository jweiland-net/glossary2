:navigation-title: Site settings

..  include:: /Includes.rst.txt
..  _site-settings:

======================================
Site settings and TypoScript constants
======================================

The site set `jweiland/glossary2` provides the following settings. Adjust
them in module :guilabel:`Site Management > Settings` or in the file
:file:`config/sites/<identifier>/settings.yaml` of your site.

If you include the static template instead of the site set, set the
values as TypoScript constants with the names listed below (for example
`glossary2.storagePid`). The :guilabel:`Constant Editor` lists the
constants under their names used before version 7.0.1 (for example
`plugin.tx_glossary2.persistence.storagePid`). A constant with the new
name takes precedence over the old one.

..  versionchanged:: 7.0.1
    The TypoScript constants were renamed. They do not start with
    `plugin.tx_glossary2` anymore, for example
    `plugin.tx_glossary2.persistence.storagePid` is now
    `glossary2.storagePid`.

..  versionchanged:: 7.1.0
    The old constant names work again as a fallback, see
    :ref:`upgrade-7-0-1`.

..  confval-menu::
    :display: table
    :type:
    :default:

..  confval:: glossary2.templateRootPath
    :name: site-settings-templateRootPath
    :type: string
    :default: `EXT:glossary2/Resources/Private/Templates/`

    Path to your own template root. It is added to
    `plugin.tx_glossary2.view.templateRootPaths` with key `1`.

..  confval:: glossary2.partialRootPath
    :name: site-settings-partialRootPath
    :type: string
    :default: `EXT:glossary2/Resources/Private/Partials/`

    Path to your own partial root. It is added to
    `plugin.tx_glossary2.view.partialRootPaths` with key `1`.

..  confval:: glossary2.layoutRootPath
    :name: site-settings-layoutRootPath
    :type: string
    :default: `EXT:glossary2/Resources/Private/Layouts/`

    Path to your own layout root. It is added to
    `plugin.tx_glossary2.view.layoutRootPaths` with key `1`.

..  confval:: glossary2.storagePid
    :name: site-settings-storagePid
    :type: string
    :default: `0`

    Comma-separated list of the storage folders (PIDs) which contain the
    glossary records, for example `21,45,3234`. The value is used for
    `plugin.tx_glossary2.persistence.storagePid`.

    ..  versionchanged:: 7.1.0
        The setting is of type `string` now and accepts a comma-separated
        list of folders. Before, only a single folder (`int`) was possible.

..  confval:: glossary2.pidOfDetailPage
    :name: site-settings-pidOfDetailPage
    :type: int
    :default: `0`

    Page which contains the plugin to show the detail view. Leave it at `0`
    to show the details on the current page. The value is used for
    `plugin.tx_glossary2.settings.pidOfDetailPage`.

..  confval:: glossary2.pageBrowser.itemsPerPage
    :name: site-settings-pageBrowser-itemsPerPage
    :type: int
    :default: `10`

    Number of glossary records shown on one page of the list view. The value
    is used for `plugin.tx_glossary2.settings.pageBrowser.itemsPerPage`.

    ..  versionchanged:: 7.0.1
        The default value was reduced from `15` to `10`.

..  _site-settings-example:

Example site settings
=====================

..  code-block:: yaml
    :caption: config/sites/<identifier>/settings.yaml

    glossary2:
      storagePid: '21,45'
      pidOfDetailPage: 84
      pageBrowser:
        itemsPerPage: 20
