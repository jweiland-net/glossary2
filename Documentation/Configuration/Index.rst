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

.. container:: ts-properties

   =========================== ===================================== ======================= ====================
   Property                    Data type                             :ref:`t3tsref:stdwrap`  Default
   =========================== ===================================== ======================= ====================
   pidOfDetailPage_            Comma separated list of page UIDs     no
   letters_                    Comma separated list of letters       no                      0-9, A-Z
   show_                       Array
   list_                       Array
   pageBrowser_                Array
   =========================== ===================================== ======================= ====================


Property details
================

.. only:: html

   .. contents::
      :local:
      :depth: 1


.. _pidOfDetailPage:

pidOfDetailPage
---------------

Example: plugin.tx_glossary2.settings.pidOfDetailPage = 4

Here you can add one or a comma separated list of Storage PIDs where your glossary
records are located.


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
