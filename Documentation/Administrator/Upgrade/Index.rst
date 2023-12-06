..  include:: /Includes.rst.txt


=======
Upgrade
=======

If you update `glossary2` to a newer version, please read this section
carefully!

Upgrade to Version 6.0.0
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

Update to Version 5.1.0
=======================

If you make use of `ModifyQueryOfSearchGlossariesEvent` event:
We have changed the strict type of TYPO3 QueryBuilder to extbase
QueryResult. Yes, this is a breaking change, but it seems that no one
makes use of it. Please adopt your code to use QueryResult. You can retrieve
the extbase query with method `getQuery()`.

We have reduced the list of categories in Plugin FlexForm to categories in
default language only. Please check, if your selected categories are
still valid.

Update to Version 5.0.2
=======================

Sorry, we have implemented the wrong Pagination. With this version we use
TYPO3's default SimplePagination. Please adopt changes
of Partials/Components/Pagination.html to your own templates, if you have
overwritten them.

Upgrade to Version 5.0.0
========================

We have changed some CSS classes in Fluid Templates. Please update them to
your needs.

As the Widget system has gone in TYPO3 11 we have implemented a new paginator.
Please have a look into our Fluid Templates and adopt pagination to your
own templates.

We have replaced all SignalSlots with EventListeners. Please check your
integration and update to EventListeners.

Because of the Pagination change you should also check your RouteEnhancers. If
you need help, have a look into AdministratorManual/Routes for an example
here in this documentation.

Update to Version 4.3.1
=======================

We have changed some method arguments, please flush cache in InstallTool

Upgrade to Version 4.0.0
========================

As we have only removed TYPO3 8 and added TYPO3 10 compatibility there should
be no problem to upgrade to this version.

Upgrade to Version 3.0.0
========================

We have changed Fluid-Templates a lot. We have removed a lot of CSS classes
and changed them to be compatible with Bootstrap. Further we have moved
Image- and Properties-Partial back to Show-Template in to a f:section.
Please have a look into your templates, if they are still working.

SwitchableControllerAction will be deprecated with TYPO3 10. That's why we have
changed this implementation into two new Checkboxes in FlexForm.
Please use the UpdateWizard in InstallTool to update your FlexForms.
