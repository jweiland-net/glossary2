:navigation-title: Installation

..  include:: /Includes.rst.txt
..  _installation:

============
Installation
============

..  _installation-composer:

Installation with Composer
==========================

If your TYPO3 installation works in composer mode, please execute following
command:

..  code-block:: bash

    composer req jweiland/glossary2
    vendor/bin/typo3 extension:setup --extension=glossary2

If you work with DDEV please execute this command:

..  code-block:: bash

    ddev composer req jweiland/glossary2
    ddev exec vendor/bin/typo3 extension:setup --extension=glossary2

..  _installation-extension-manager:

Installation with the Extension Manager
=======================================

On non composer based TYPO3 installations you can install `glossary2` still
over the ExtensionManager:

..  rst-class:: bignums

1.  Login

    Login to backend of your TYPO3 installation as an administrator or system
    maintainer.

2.  Open ExtensionManager

    Click on `Extensions` from the left menu to open the ExtensionManager.

3.  Update Extensions

    Choose `Get Extensions` from the upper selectbox and click on
    the `Update now` button at the upper right.

4.  Install `glossary2`

    Use the search field to find `glossary2`. Choose the `glossary2` line from
    the search result and click on the cloud icon to install `glossary2`.

..  _installation-typoscript:

Include the TypoScript of glossary2
===================================

`glossary2` delivers its TypoScript as site set and as static TypoScript
template. Use **either** the site set **or** the static template, not both.
The static template imports the TypoScript of the site set, so including
both would load the same TypoScript twice.

..  _installation-site-set:

Add the site set (recommended)
------------------------------

Add the site set `jweiland/glossary2` (:guilabel:`Glossary2 Set`) as
dependency to the site set of your site package:

..  code-block:: yaml
    :caption: EXT:site_package/Configuration/Sets/SitePackage/config.yaml

    name: my-vendor/site-package
    label: Site package
    dependencies:
      - jweiland/glossary2

Alternatively, add the set in module :guilabel:`Site Management > Sites`
to the sets of your site configuration.

The settings of the site set can be adjusted in module
:guilabel:`Site Management > Settings`, see
:ref:`Site settings and TypoScript constants <site-settings>`.

..  _installation-static-template:

Include the static template (alternative)
-----------------------------------------

If your site does not use site sets, edit your TypoScript record in module
:guilabel:`Site Management > TypoScript` and include the static template
:guilabel:`Glossary 2 (glossary2)`.

The static template provides its constants under the names used before
version 7.0.1 (for example `plugin.tx_glossary2.persistence.storagePid`).
Adjust them in the :guilabel:`Constant Editor`. Constants with the names of
the site settings (for example `glossary2.storagePid`) take precedence.

..  _installation-next-step:

Next step
=========

:ref:`Configure glossary2 <configuration>`.
