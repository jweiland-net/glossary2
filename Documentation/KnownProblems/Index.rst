.. include:: ../Includes.txt


.. _known-problems:

==============
Known Problems
==============

Special entry for 0-9
=====================

Currently it is not possible to separate glossary entry `0-9` into single values

Database
========

As we use MySQL/MariaDB special keywords LOWER() and SUBSTRING() in our queries
it might be that this extension is not compatible with PostgreSQL and other
DB systems.
