.. include:: ../../Includes.txt

Updating
========

If you update EXT:glossary2 to a newer version, please read this section carefully!

Update to Version 3.0.0
-----------------------

We have changed Fluid-Templates a lot. We have removed a lot of CSS classes
and changed them to be compatible with Bootstrap. Further we have moved
Image- and Properties-Partial back to Show-Template in to a f:section.
Please have a look into your templates, if they are still working.

SwitchableControllerAction will be deprecated with TYPO3 10. That's why we have
changed this implementation into two new Checkboxes in FlexForm.
Please use the UpdateWizard in InstallTool to update your FlexForms.
