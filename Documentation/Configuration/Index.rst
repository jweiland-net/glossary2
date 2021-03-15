.. include:: ../Includes.txt


.. _configuration:

=============
Configuration
=============

Target group: **Developers, Integrators**

How to configure the extension. Try to make it easy to configure the extension.
Give a minimal example or a typical example.


Minimal Example
===============

- It is necessary to include static template `Glossary 2 (glossary2)`

We prefer to set a Storage PID with help of TypoScript Constants:

.. code-block:: none

   plugin.tx_glossary2.persistence {
      # Define Storage PID where glossary records are located
      storagePid = 4
   }

.. _configuration-typoscript:

TypoScript Setup Reference
==========================

.. _pidOfListPage:

pidOfListPage
-------------

Example: plugin.tx_glossary2.settings.pidOfListPage = 23

If you have configured PID of detail page you may also configure a PID back to the list view.


.. _pidOfDetailPage:

pidOfDetailPage
---------------

Example: plugin.tx_glossary2.settings.pidOfDetailPage = 4

Set this value to a PID to modify all links in list view to another detail view. Helpful,
if you have another layout on detail view.


templatePath
------------

Example: plugin.tx_glossary2.settings.templatePath = EXT:events2/Resources/Private/Templates/Glossary2.html

Default by Extension Settings: EXT:glossary2/Resources/Private/Templates/Glossary.html
Default can be overwritten by foreign Extensions (API usage).

With this setting you can override the default templatePath of glossary2 and defined templatePaths coming from
foreign extensions. So TypoScript settings have highest priority.

We also have implemented a more complex setting for templatePath:

plugin.tx_glossary2.settings.templatePath {
  default = EXT:glossary2/Resources/Private/Templates/Glossary2.html
  events2 = EXT:events2/Resources/Private/Templates/Glossary2.html
  yellowpages2 = EXT:yellowpages2/Resources/Private/Templates/Glossary2.html
}

``default`` will be used, if no templatePath for a given ExtensionKey was found.


.. _letters:

letters
-------

Default: 0-9,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z

Example: plugin.tx_glossary2.settings.letters = 0-9,A,E,I,O,U

This is a list of allowed entries within the A-Z navigation above the glossary list in frontend.
0-9 is a special entry which can not be divided into single representations.


.. _list:

list
----

Default: 200c for width and height

Example: plugin.tx_glossary2.settings.list.image.width = 150c

Currently not implemented in Template, but if you want, you can use this
setting to show one or more images with a defined width and height.


.. _show:

show
----

Default: 80c for width and height

Example: plugin.tx_glossary2.settings.show.image.width = 120c

If you want, you can use this setting to show one or more images
with a defined width and height.


.. _pageBrowser:

pageBrowser
-----------

You can fine tuning the page browser

Example: plugin.tx_glossary2.settings.pageBrowser.itemsPerPage = 15
Example: plugin.tx_glossary2.settings.pageBrowser.insertAbove = 1
Example: plugin.tx_glossary2.settings.pageBrowser.insertBelow = 0
Example: plugin.tx_glossary2.settings.pageBrowser.maximumNumberOfLinks = 5

**itemsPerPage**

Reduce result of glossary records to this value for a page

**insertAbove**

Insert page browser above list of glossary records

**insertBelow**

Insert page browser below list of glossary records. I remember a bug in TYPO3 CMS. So I can not guarantee
that this option will work.

**maximumNumberOfLinks**

If you have many glossary records it makes sense to reduce the amount of pages in page browser to a fixed maximum
value. Instead of 1, 2, 3, 4, 5, 6, 7, 8 you will get 1, 2, 3...8, 9 if you have configured this option to 5.
