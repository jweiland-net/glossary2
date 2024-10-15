# TYPO3 Extension `glossary2`

[![Packagist][packagist-logo-stable]][extension-packagist-url]
[![Latest Stable Version][extension-build-shield]][extension-ter-url]
[![License][LICENSE_BADGE]][extension-packagist-url]
[![Total Downloads][extension-downloads-badge]][extension-packagist-url]
[![Monthly Downloads][extension-monthly-downloads]][extension-packagist-url]
[![TYPO3 13.4][TYPO3-shield]][TYPO3-13-url]

![Build Status](https://github.com/jweiland-net/glossary2/actions/workflows/ci.yml/badge.svg)

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

<!-- MARKDOWN LINKS & IMAGES -->

[extension-build-shield]: https://poser.pugx.org/jweiland/glossary2/v/stable.svg?style=for-the-badge

[extension-downloads-badge]: https://poser.pugx.org/jweiland/glossary2/d/total.svg?style=for-the-badge

[extension-monthly-downloads]: https://poser.pugx.org/jweiland/glossary2/d/monthly?style=for-the-badge

[extension-ter-url]: https://extensions.typo3.org/extension/telephonedirectory/

[extension-packagist-url]: https://packagist.org/packages/jweiland/glossary2/

[packagist-logo-stable]: https://img.shields.io/badge/--grey.svg?style=for-the-badge&logo=packagist&logoColor=white

[TYPO3-13-url]: https://get.typo3.org/version/13

[TYPO3-shield]: https://img.shields.io/badge/TYPO3-13.4-green.svg?style=for-the-badge&logo=typo3

[LICENSE_BADGE]: https://img.shields.io/github/license/jweiland-net/telephonedirectory?label=license&style=for-the-badge
