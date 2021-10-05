.. include:: ../Includes.txt


.. _changelog:

=========
ChangeLog
=========

**Version 5.0.2**

Use SimplePaginator instead of our own one

**Version 5.0.1**

Add documentation about how to integrate Georg Ringers numbered_pagination

**Version 5.0.0**

Remove TYPO3 9 compatibility
Add TYPO3 11 compatibility
This version is still TYPO3 10 compatible

**Version 4.3.1**

Move SlugHelper from constructor argument into getSlugHelper()

**Version 4.3.0**

Allow overriding templatePath of glossary2 on page basis via TypoScript

**Version 4.2.0**

Add setting for PID to list/detail view in FlexForm

**Version 4.1.1**

Use unique instead of uniqueInSite for slug

**Version 4.1.0**

- Add 2 new methods to Glossary2 API to simplify your DB queries
- All all API options to Fluid Template
- Replace hard-coded action name in links with action name from API options

**Version 4.0.2**

- Use translation for path_segment from glosssary2 lang files

**Version 4.0.1**

- Make templatePath configurable with Extension Settings

**Version 4.0.0**

- Remove TYPO3 8 compatibility
- Add TYPO3 10 compatibility
- Add Service.yaml for DI
- BUGFIX: Use DEV-Autoloader of glossary2 instead of events2
- Repair UnitTests and FunctionalTests.
- Add many more FunctionalTests
- Add API to build a Glossary for foreign extensions
- Add documentation for Glossary API
- Update DocHeader. Add LICENSE file

**Version 3.0.2**

- Add link to our new Routes documentation in our documentation

**Version 3.0.1**

- Add documentation for Route configuration

**Version 3.0.0**

- Changed all templates. They are using bootstrap classes now by default
- Add SignalSlot to GlossaryRepository to modify Extbase Query
- Removed SwitchableControllerActions from FlexForm. Please start UpdateWizard.
- Add option to enable/disable the A-Z links on top
- Add option to add/remove an ALL-Link in front of A-Z link list.
- Removed action method showWithoutGlossar from Controller.
- Moved ext_icon to new location in Public/Icons
- Add Icon for glossary table
- Little code refactorings like removing @return void
- Update documentation

**Version 2.2.0**

- Add documentation
- Add new ext_icon as SVG

**Version 2.1.1**

Now you can add cropping, alt and title information to images
