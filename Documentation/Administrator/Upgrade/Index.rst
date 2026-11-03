:navigation-title: Upgrade

..  include:: /Includes.rst.txt
..  _upgrade:

=======
Upgrade
=======

If you update `glossary2` to a newer version, please read this section
carefully!

..  _upgrade-7-1-0:

Upgrade to version 7.1.0
========================

Version 7.1.0 requires TYPO3 13.4 and PHP 8.2 to 8.5. Deprecated API usages
were removed. Integrators should check the TypoScript inclusion and run the
database compare and the upgrade wizards. Developers only need to act if
they implement the event interfaces of glossary2 themselves.

..  _upgrade-7-1-0-integrators:

Changes for integrators
-----------------------

..  rst-class:: bignums

1.  Include the TypoScript only once

    The static template :guilabel:`Glossary 2` works again and imports the
    TypoScript of the site set. Use **either** the site set
    `jweiland/glossary2` **or** the static template, not both. See
    :ref:`installation-typoscript`.

2.  Check the storage PID

    The setting `glossary2.storagePid` is of type `string` now and accepts a
    comma-separated list of folders. See :ref:`site-settings`.

3.  Update the database schema

    The file :file:`ext_tables.sql` was removed. The database schema is
    created by TYPO3 from the TCA now. Run the database compare in
    :guilabel:`Admin Tools > Maintenance > Analyze Database Structure`:
    column `images` changes from `VARCHAR` to `INT UNSIGNED`, column
    `l10n_source` is added and the columns `path_segment` and `description`
    are changed.

4.  Run the upgrade wizards

    Execute the upgrade wizards in
    :guilabel:`Admin Tools > Upgrade > Upgrade Wizard` or with
    `vendor/bin/typo3 upgrade:run`:

    `glossaryPluginUpdate`
        Migrates plugins from `list_type` to the content element (`CType`)
        `glossary2_glossary`. Afterwards check the field
        `explicit_allowdeny` of your backend user groups and restrictions of
        backend layouts (for example `content_defender`) for the new
        `CType` `glossary2_glossary`.

    `glossary2UpdateOldFlexFormFields`
        Migrates old FlexForm settings. The wizard now also finds plugins
        which were already migrated to `CType`.

    `glossary2UpdateSlug`
        Creates missing URL slugs of glossary records.

5.  Check the backend

    The preview of the plugin in the page module works on all pages, also
    without the site set, as the preview is registered in the global
    :file:`Configuration/page.tsconfig`. The hidden toggle of glossary
    records is no longer inverted. The tabs for language, access and notes
    of the plugin are provided by TYPO3 Core and the field `l10n_parent` is
    a select field now.

6.  Check the A-Z navigation

    Umlauts and other multibyte first letters are linked correctly on all
    database systems including MariaDB. A title starting with an umlaut no
    longer adds a link to a second letter (for example "Ä" no longer adds a
    link to "e").

    `Glossary::getSanitizedFirstLetterOfTitle()` returns only the first
    character of the sanitized letter now ("ä" becomes "a" instead of
    "ae"). This changes the `letter` argument of links from the detail view
    back to the list view. Check your templates and route enhancers, if you
    rely on the old value.

..  _upgrade-7-1-0-developers:

Changes for developers
----------------------

*   `ControllerActionEventInterface::getRequest()` and
    `PostProcessFluidVariablesEvent` use
    `TYPO3\CMS\Extbase\Mvc\RequestInterface` instead of
    `TYPO3\CMS\Extbase\Mvc\Request`. Only third-party implementations of
    `ControllerActionEventInterface` have to adapt the return type.
*   New method `PostProcessFirstLettersEvent::setFirstLetters()` to modify
    the first letters of the A-Z navigation. See :ref:`glossary-api-extend`.
*   `GlossaryService` accepts queries and query results of any model. The
    public API uses generic types instead of the glossary2 model.
*   Pass the current request as third argument to
    `GlossaryService::buildGlossary()`. It is needed by `f:link.action` to
    build the A-Z links.
*   The event listeners of glossary2 are registered with the PHP attribute
    `#[AsEventListener]`. Validation and lazy loading use the PHP attributes
    `#[Validate]` and `#[Lazy]` instead of annotations.
*   `GlossaryController` uses constructor injection instead of
    `inject*()` methods. Adapt XClasses of the controller.
*   The non-existing action `listWithoutGlossar` was removed from the plugin
    configuration.
*   The Composer package requires `typo3/cms-extbase`, `typo3/cms-fluid`
    and `typo3/cms-frontend` explicitly. The requirement of `ext-pdo` was
    dropped.
*   The CI tests PHP 8.2 to 8.5 and runs PHPStan on level 6.

..  _upgrade-7-0-1:

Upgrade to version 7.0.1
========================

Version 7.0.1 introduced the site set `jweiland/glossary2`. The TypoScript
constants were renamed and the default for items per page was reduced from
`15` to `10`. Rename the constants in your site package to the new names
listed below or move the values to the
:ref:`site settings <site-settings>`. Values must be set with the new names
to take effect:

..  list-table:: Renamed TypoScript constants
    :header-rows: 1

    *   -   Old constant
        -   New constant or site setting
    *   -   `plugin.tx_glossary2.view.templateRootPath`
        -   `glossary2.templateRootPath`
    *   -   `plugin.tx_glossary2.view.partialRootPath`
        -   `glossary2.partialRootPath`
    *   -   `plugin.tx_glossary2.view.layoutRootPath`
        -   `glossary2.layoutRootPath`
    *   -   `plugin.tx_glossary2.persistence.storagePid`
        -   `glossary2.storagePid`
    *   -   `plugin.tx_glossary2.settings.pidOfDetailPage`
        -   `glossary2.pidOfDetailPage`
    *   -   `plugin.tx_glossary2.settings.pageBrowser.itemsPerPage`
        -   `glossary2.pageBrowser.itemsPerPage`

..  _upgrade-6-0-0:

Upgrade to version 6.0.0
========================

We enhanced the extension's compatibility to TYPO3 version 12 while
establishing a minimum requirement of version 11. As part of this upgrade,
a new page title provider was introduced within the extension.
In addition, comprehensive documentation was provided to guide users in
seamlessly implementing the page title using the newly added provider.
Furthermore, the testing framework for the extension was successfully migrated
to the TYPO3 Testing framework, ensuring robust and efficient testing practices
in line with the latest TYPO3 standards. These upgrades collectively contribute
to an improved and up-to-date extension experience for users within
the TYPO3 ecosystem.

..  _upgrade-5-1-0:

Update to version 5.1.0
=======================

If you make use of `ModifyQueryOfSearchGlossariesEvent` event:
We have changed the strict type of TYPO3 QueryBuilder to extbase
QueryResult. Yes, this is a breaking change, but it seems that no one
makes use of it. Please adopt your code to use QueryResult. You can retrieve
the extbase query with method `getQuery()`.

We have reduced the list of categories in Plugin FlexForm to categories in
default language only. Please check, if your selected categories are
still valid.

..  _upgrade-5-0-2:

Update to version 5.0.2
=======================

Sorry, we have implemented the wrong Pagination. With this version we use
TYPO3's default SimplePagination. Please adopt changes
of Partials/Components/Pagination.html to your own templates, if you have
overwritten them.

..  _upgrade-5-0-0:

Upgrade to version 5.0.0
========================

We have changed some CSS classes in Fluid Templates. Please update them to
your needs.

As the Widget system has gone in TYPO3 11 we have implemented a new paginator.
Please have a look into our Fluid Templates and adopt pagination to your
own templates.

We have replaced all SignalSlots with EventListeners. Please check your
integration and update to EventListeners.

Because of the Pagination change you should also check your RouteEnhancers. If
you need help, have a look into :ref:`routes` for an example
here in this documentation.

..  _upgrade-4-3-1:

Update to version 4.3.1
=======================

We have changed some method arguments, please flush cache in InstallTool

..  _upgrade-4-0-0:

Upgrade to version 4.0.0
========================

As we have only removed TYPO3 8 and added TYPO3 10 compatibility there should
be no problem to upgrade to this version.

..  _upgrade-3-0-0:

Upgrade to version 3.0.0
========================

We have changed Fluid-Templates a lot. We have removed a lot of CSS classes
and changed them to be compatible with Bootstrap. Further we have moved
Image- and Properties-Partial back to Show-Template in to a f:section.
Please have a look into your templates, if they are still working.

SwitchableControllerAction will be deprecated with TYPO3 10. That's why we have
changed this implementation into two new Checkboxes in FlexForm.
Please use the UpdateWizard in InstallTool to update your FlexForms.
