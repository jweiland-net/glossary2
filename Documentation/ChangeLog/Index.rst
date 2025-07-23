..  include:: /Includes.rst.txt


..  _changelog:

=========
ChangeLog
=========

Version 7.0.2
=============

*   [BUGFIX] Backend plugin CType preview

Version 7.0.1
=============

*   [TASK] Added Glossary2 Site Set
*   [TASK] Modified Update wizard for extending list plugins to CType
*   [BUGFIX] Removed duplicate plugin wizard registered with page.tsconfig.

Version 7.0.0
=============

*   [TASK] Compatibility fixes for TYPO3 13 LTS
*   [TASK] Removed CSH-related method calls from extension

Version 6.0.1
=============

*   [BUGFIX] Due to missing upgrade wizard implementation extension throws
    error in CLI and backend upgrade wizard.

Version 6.0.0
=============

*   [TASK] Compatibility fix for TYPO3 Version 12
*   [TASK] Category in TCA fixed with type `category´
*   [TASK] Migrated testing framework to TYPO3 Testing Framework

Version 5.1.0
=============

*   [FEATURE] Allow QueryBuilder and extbase QueryResult for buildGlossar()
*   [DOCU] Update section about how to use Glossary API
*   [BUGFIX] Use extbase query for better localization support (BREAKING)
*   [TASK] Modify params of ModifyQueryOfSearchGlossariesEvent
*   [TASK] Show only categories in default language in FlexForm
*   [TASK] Remove OverlayHelper class
*   [TASK] Remove unused getGlossaries() method from repo

Version 5.0.10
==============

*   [BUGFIX] Remove exclude from path_segment in TCA
*   [DOCU] Set indents to 4 spaces
*   [DOCU] Streamline the headers
*   [DOCU] Convert Readme.rst to README.md
*   Update .gitignore
*   Update .editorconfig
*   Implement new php-cs-fixer configuration

Version 5.0.9
=============

*   [DOCU] Mistake in storagePid configuration
*   Add .gitattributes

Version 5.0.8
=============

*   Add TS settings for GlossaryService

Version 5.0.7
=============

*   Register GlossaryService as public in Services.yaml

Version 5.0.6
=============

*   Use array type for $availableLetters instead of string
*   Merge 2 conditions

Version 5.0.5
=============

*   Add sortby title for all queries

Version 5.0.4
=============

*   Better support for PHP 8
*   Use ProphecyTrait in functional tests.
*   Reactivate a functional test

Version 5.0.3
=============

*   Use SQL IN to select one or more categories

Version 5.0.2
=============

*   Use SimplePaginator instead of our own one

Version 5.0.1
=============

*   Add documentation about how to integrate Georg Ringers numbered_pagination

Version 5.0.0
=============

*   Remove TYPO3 9 compatibility
*   Add TYPO3 11 compatibility
*   This version is still TYPO3 10 compatible

Version 4.3.1
=============

*   Move SlugHelper from constructor argument into getSlugHelper()

Version 4.3.0
=============

*   Allow overriding templatePath of glossary2 on page basis via TypoScript

Version 4.2.0
=============

*   Add setting for PID to list/detail view in FlexForm

Version 4.1.1
=============

*   Use unique instead of uniqueInSite for slug

Version 4.1.0
=============

*   Add 2 new methods to Glossary2 API to simplify your DB queries
*   All all API options to Fluid Template
*   Replace hard-coded action name in links with action name from API options

Version 4.0.2
=============

*   Use translation for path_segment from glosssary2 lang files

Version 4.0.1
=============

*   Make templatePath configurable with Extension Settings

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

Version 3.0.2
=============

*   Add link to our new Routes documentation in our documentation

Version 3.0.1
=============

*   Add documentation for Route configuration

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

Version 2.2.0
=============

*   Add documentation
*   Add new ext_icon as SVG

Version 2.1.1
=============

*   Now you can add cropping, alt and title information to images
