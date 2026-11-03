:navigation-title: Events

..  include:: /Includes.rst.txt
..  _events:

=============
PSR-14 events
=============

`glossary2` dispatches the following PSR-14 events. Register your event
listeners with the PHP attribute
`#[\TYPO3\CMS\Core\Attribute\AsEventListener]`, see
:ref:`glossary-api-extend` for examples.

..  list-table:: PSR-14 events of glossary2
    :header-rows: 1
    :widths: 40 60

    *   -   Event
        -   Description
    *   -   `JWeiland\Glossary2\Event\PostProcessFluidVariablesEvent`
        -   Add or modify the Fluid variables of the list and show action.
            Implements `ControllerActionEventInterface`, which provides
            `getRequest()`, `getControllerName()`, `getActionName()` and
            `getSettings()`.
    *   -   `JWeiland\Glossary2\Event\ModifyQueryOfSearchGlossariesEvent`
        -   Get the Extbase query result of the glossary records together
            with the selected categories and the letter.
    *   -   `JWeiland\Glossary2\Event\PostProcessFirstLettersEvent`
        -   Add or remove first letters of the A-Z navigation, see
            :ref:`glossary-api-postProcessFirstLetters`.
    *   -   `JWeiland\Glossary2\Event\SanitizeValueForCharsetHelperEvent`
        -   Map special chars of a first letter to their ASCII
            representation, see :ref:`glossary-api-modifyLetterMapping`.

..  versionchanged:: 7.1.0
    `ControllerActionEventInterface::getRequest()` and the constructor of
    `PostProcessFluidVariablesEvent` use
    `TYPO3\CMS\Extbase\Mvc\RequestInterface` instead of
    `TYPO3\CMS\Extbase\Mvc\Request`. Only own implementations of
    `ControllerActionEventInterface` have to adapt the return type.

..  versionchanged:: 7.1.0
    The event listeners of glossary2 are registered with the PHP attribute
    `#[AsEventListener]` instead of :file:`Configuration/Services.yaml`.
