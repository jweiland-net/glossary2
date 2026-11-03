:navigation-title: Changelog

..  include:: /Includes.rst.txt
..  _changelog:

=========
Changelog
=========

..  _changelog-7-1-0:

Version 7.1.0
=============

*   [TASK] Add assertions to functional tests of glossary
*   [BUGFIX] Allow to modify first letters by event
*   [TASK] Remove unused CSH language files
*   [TASK] Declare all required packages and PHP version
*   [TASK] Use generic types in public API of glossary
*   [TASK] Use constructor injection and PHP attributes
*   [BUGFIX] Find plugins with CType in FlexForm wizard
*   [BUGFIX] Remove system fields from showitem of plugin
*   [TASK] Rely on TCA columns and schema created by core
*   [BUGFIX] Use request given to the page title provider
*   [BUGFIX] Determine first letters with portable SQL. The first letter
    of a glossary title is reduced to one character ("ä" is "a", no longer
    "ae"), which changes the `letter` argument of show-to-list links.
*   [BUGFIX] Repair TypoScript of static template and site set
*   [TASK] Test PHP 8.4 and 8.5 in CI
*   [BUGFIX] Allow PHPStan 1.12 to run on PHP 8.5
*   [TASK] Support PHP 8.5 in runTests.sh
*   [BUGFIX] Keep renamed TypoScript constants as fallback. Constants
    used before version 7.0.1 apply only if no `glossary2.*` constant or
    site setting is defined.
*   [BUGFIX] Force at least one item per page in paginator
*   [TASK] Exclude documentation from CGL checks
*   [DOCS] Update documentation to TYPO3 13 and render-guides

See :ref:`upgrade-7-1-0` for the required upgrade steps.

..  _changelog-7-0-7:

Version 7.0.7
=============

*   [BUGFIX] Remove obsolete internal_type from FlexForm group fields
*   [BUGFIX] Use explicit nullable request parameters in GlossaryService
*   [BUGFIX] Do not access request or ConfigurationManager in constructor
    of GlossaryService. TypoScript settings of glossary2 are now read
    from the request given to `buildGlossary()`.

..  _changelog-7-0-6:

Version 7.0.6
=============

*   [BUGFIX] Filter glossary items by categories in AddGlossaryEventListener

..  _changelog-7-0-5:

Version 7.0.5
=============

*   [TASK] Tailor TER release configuration updates

..  _changelog-7-0-4:

Version 7.0.4
=============

*   [BUGFIX] Pass glossary 'letter' argument to pagination links

..  _changelog-7-0-3:

Version 7.0.3
=============

*   [BUGFIX] SiteSet identifier renamed by removing default

..  _changelog-7-0-2:

Version 7.0.2
=============

*   [BUGFIX] Backend plugin CType preview

..  _changelog-7-0-1:

Version 7.0.1
=============

*   [TASK] Added Glossary2 Site Set
*   [TASK] Modified Update wizard for extending list plugins to CType
*   [BUGFIX] Removed duplicate plugin wizard registered with page.tsconfig.

..  _changelog-7-0-0:

Version 7.0.0
=============

*   [TASK] Compatibility fixes for TYPO3 13 LTS
*   [TASK] Removed CSH-related method calls from extension

..  _changelog-6-0-1:

Version 6.0.1
=============

*   [BUGFIX] Due to missing upgrade wizard implementation extension throws
    error in CLI and backend upgrade wizard.

..  _changelog-6-0-0:

Version 6.0.0
=============

*   [TASK] Compatibility fix for TYPO3 Version 12
*   [TASK] Category in TCA fixed with type `category´
*   [TASK] Migrated testing framework to TYPO3 Testing Framework

..  _changelog-5-1-0:

Version 5.1.0
=============

*   [FEATURE] Allow QueryBuilder and extbase QueryResult for buildGlossar()
*   [DOCU] Update section about how to use Glossary API
*   [BUGFIX] Use extbase query for better localization support (BREAKING)
*   [TASK] Modify params of ModifyQueryOfSearchGlossariesEvent
*   [TASK] Show only categories in default language in FlexForm
*   [TASK] Remove OverlayHelper class
*   [TASK] Remove unused getGlossaries() method from repo

..  _changelog-5-0-10:

Version 5.0.10
==============

*   [BUGFIX] Remove exclude from path_segment in TCA
*   [DOCU] Set indents to 4 spaces
*   [DOCU] Streamline the headers
*   [DOCU] Convert Readme.rst to README.md
*   Update .gitignore
*   Update .editorconfig
*   Implement new php-cs-fixer configuration

..  _changelog-5-0-9:

Version 5.0.9
=============

*   [DOCU] Mistake in storagePid configuration
*   Add .gitattributes

..  _changelog-5-0-8:

Version 5.0.8
=============

*   Add TS settings for GlossaryService

..  _changelog-5-0-7:

Version 5.0.7
=============

*   Register GlossaryService as public in Services.yaml

..  _changelog-5-0-6:

Version 5.0.6
=============

*   Use array type for $availableLetters instead of string
*   Merge 2 conditions

..  _changelog-5-0-5:

Version 5.0.5
=============

*   Add sortby title for all queries

..  _changelog-5-0-4:

Version 5.0.4
=============

*   Better support for PHP 8
*   Use ProphecyTrait in functional tests.
*   Reactivate a functional test

..  _changelog-5-0-3:

Version 5.0.3
=============

*   Use SQL IN to select one or more categories

..  _changelog-5-0-2:

Version 5.0.2
=============

*   Use SimplePaginator instead of our own one

..  _changelog-5-0-1:

Version 5.0.1
=============

*   Add documentation about how to integrate Georg Ringers numbered_pagination

..  _changelog-5-0-0:

Version 5.0.0
=============

*   Remove TYPO3 9 compatibility
*   Add TYPO3 11 compatibility
*   This version is still TYPO3 10 compatible

..  _changelog-4-3-1:

Version 4.3.1
=============

*   Move SlugHelper from constructor argument into getSlugHelper()

..  _changelog-4-3-0:

Version 4.3.0
=============

*   Allow overriding templatePath of glossary2 on page basis via TypoScript

..  _changelog-4-2-0:

Version 4.2.0
=============

*   Add setting for PID to list/detail view in FlexForm

..  _changelog-4-1-1:

Version 4.1.1
=============

*   Use unique instead of uniqueInSite for slug

..  _changelog-4-1-0:

Version 4.1.0
=============

*   Add 2 new methods to Glossary2 API to simplify your DB queries
*   All all API options to Fluid Template
*   Replace hard-coded action name in links with action name from API options

..  _changelog-4-0-2:

Version 4.0.2
=============

*   Use translation for path_segment from glosssary2 lang files

..  _changelog-4-0-1:

Version 4.0.1
=============

*   Make templatePath configurable with Extension Settings

..  _changelog-4-0-0:

Version 4.0.0
=============

*   Remove TYPO3 8 compatibility
*   Add TYPO3 10 compatibility
*   Add Service.yaml for DI
*   BUGFIX: Use DEV-Autoloader of glossary2 instead of events2
*   Repair UnitTests and FunctionalTests.
*   Add many more FunctionalTests
*   Add API to build a Glossary for foreign extensions
*   Add documentation for Glossary API
*   Update DocHeader. Add LICENSE file

..  _changelog-3-0-2:

Version 3.0.2
=============

*   Add link to our new Routes documentation in our documentation

..  _changelog-3-0-1:

Version 3.0.1
=============

*   Add documentation for Route configuration

..  _changelog-3-0-0:

Version 3.0.0
=============

*   Changed all templates. They are using bootstrap classes now by default
*   Add SignalSlot to GlossaryRepository to modify Extbase Query
*   Removed SwitchableControllerActions from FlexForm. Please start UpdateWizard.
*   Add option to enable/disable the A-Z links on top
*   Add option to add/remove an ALL-Link in front of A-Z link list.
*   Removed action method showWithoutGlossar from Controller.
*   Moved ext_icon to new location in Public/Icons
*   Add Icon for glossary table
*   Little code refactorings like removing @return void
*   Update documentation

..  _changelog-2-2-0:

Version 2.2.0
=============

*   Add documentation
*   Add new ext_icon as SVG

..  _changelog-2-1-1:

Version 2.1.1
=============

*   Now you can add cropping, alt and title information to images
