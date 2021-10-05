.. include:: ../../Includes.txt

Updating
========

If you update EXT:glossary2 to a newer version, please read this section carefully!

Update to Version 5.0.2
-----------------------

Sorry, we have implemented the wrong Pagination. With this version we use TYPO3's default SimplePagination.
Please adopt changes of Partials/Components/Pagination.html to your own templates, if you have overwritten them.

Update to Version 5.0.0
-----------------------

We have changed some CSS classes in Fluid Templates. Please update them to your needs.

As the Widget system has gone in TYPO3 11 we have implemented a new paginator. Please have a look into our
Fluid Templates and adopt pagination to your own templates.

We have replaced all SignalSlots with EventListeners. Please check your integration and update to EventListeners.

Because of the Pagination change you should also check your RouteEnhancers. If you need help, have a look into
AdministratorManual/Routes for an example here in this documentation.

Update to Version 4.3.1
-----------------------

We have changed some method arguments, please flush cache in InstallTool

Update to Version 4.0.0
-----------------------

As we have only removed TYPO3 8 and added TYPO3 10 compatibility there should be
no problem to upgrade to this version.

Update to Version 3.0.0
-----------------------

We have changed Fluid-Templates a lot. We have removed a lot of CSS classes
and changed them to be compatible with Bootstrap. Further we have moved
Image- and Properties-Partial back to Show-Template in to a f:section.
Please have a look into your templates, if they are still working.

SwitchableControllerAction will be deprecated with TYPO3 10. That's why we have
changed this implementation into two new Checkboxes in FlexForm.
Please use the UpdateWizard in InstallTool to update your FlexForms.
