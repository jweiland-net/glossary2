# TYPO3 Extension `glossary2`

![Build Status](https://github.com/jweiland-net/glossary2/workflows/CI/badge.svg)

`glossary2` is an extension for TYPO3 CMS. It shows you a list of glossary
entries incl. detail view. Above the list you will see an A-Z navigation.

## 1 Features

* Create and manage glossar records

## 2 Usage

### 2.1 Installation

#### Installation using Composer

The recommended way to install the extension is using Composer.

Run the following command within your Composer based TYPO3 project:

```
composer require jweiland/glossary2
```

#### Installation as extension from TYPO3 Extension Repository (TER)

Download and install `glossary2` with the extension manager module.

### 2.2 Minimal setup

1) Include the static TypoScript of the extension.
2) Create glossary2 records on a sysfolder.
3) Add glossary2 plugin on a page and select at least the sysfolder as startingpoint.
