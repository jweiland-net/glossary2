:navigation-title: Glossary API

..  include:: /Includes.rst.txt
..  _glossary-api:

============
Glossary API
============

Since glossary2 4.0.0 we deliver a new Glossary API which you can use to
implement a Glossary Index (A-Z list) into your own extension. All the magic
you need you'll find in GlossaryService class.

..  _glossary-api-build:

Build a glossary with the API
=============================

In class `GlossaryService` you will find public method called `buildGlossary`
which you have to use. As our API does not know the table to use and does not
know further WHERE conditions, it is up to you to deliver a TYPO3 QueryBuilder
or QueryResult instance as first argument.

We prefer creating a new method into your Repository and return an Extbase
QueryResult object:

..  code-block:: php
    :caption: EXT:my_extension/Classes/Domain/Repository/MyRepository.php

    public function getQueryBuilderToFindAllEntries(): QueryBuilder
    {
        return $this->createQuery()->execute();
    }

Alternative you can also return a TYPO3 QueryBuilder instance. BUT: It's up
to you now to respect storage PIDs, translation and workspaces:

..  code-block:: php
    :caption: EXT:my_extension/Classes/Domain/Repository/MyRepository.php

    public function getQueryBuilderToFindAllEntries(): QueryBuilder
    {
        $table = 'tx_myext_domain_model_whatever';
        $query = $this->createQuery();
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($table);
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));

        // Do not set any SELECT, ORDER BY, GROUP BY statement. It will be set by glossary2 API
        $queryBuilder
            ->from($table)
            ->andWhere(
                $queryBuilder->expr()->in(
                    'pid',
                    $queryBuilder->createNamedParameter(
                        $query->getQuerySettings()->getStoragePageIds(),
                        Connection::PARAM_INT_ARRAY
                    )
                )
            );

        return $queryBuilder;
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }

Within your controller you can call our API that way:

..  code-block:: php
    :caption: EXT:my_extension/Classes/Controller/MyController.php

    /**
     * @param string $letter Show only records starting with this letter
     * @Extbase\Validate("String", param="letter")
     * @Extbase\Validate("StringLength", param="letter", options={"minimum": 0, "maximum": 3})
     */
    public function listAction(string $letter = ''): void
    {
        $companies = $this->companyRepository->findByStartingLetter($letter, $this->settings);

        $this->view->assign('companies', $companies);
        $this->view->assign(
            'glossar',
            $this->glossaryService->buildGlossary(
                $this->myRepository->getQueryBuilderToFindAllEntries(),
                [],
                $this->request,
            )
        );
    }

This will transfer the fully rendered HTML Glossar to View.

Please always pass the current Extbase request as third argument. It is
needed to build the A-Z links and to read the TypoScript settings of
glossary2 (`plugin.tx_glossary2.settings.templatePath`).
Use `f:format.raw()` in Fluid Template:

..  code-block:: html

    {glossar -> f:format.raw()}

..  _glossary-api-configure:

Configure the glossary API
==========================

If you want, you can configure our API with second `options` argument:

..  code-block:: php
    :caption: EXT:my_extension/Classes/Controller/MyController.php

    $this->view->assign(
        'glossar',
        $this->glossaryService->buildGlossary(
            $this->myRepository->getQueryBuilderToFindAllEntries(),
            [
                'settings' => $this->settings,
                'templatePath' => 'EXT:myext:/Resources/Private/Templates/Glossary.html',
            ],
            $this->request,
        )
    );

..  confval-menu::
    :display: table
    :type:
    :default:

..  confval:: templatePath
    :name: glossary-api-option-templatePath
    :type: string
    :default: EXT:glossary2/Resources/Private/Templates/Glossary.html

    All rendering of the Glossary is in one file. There is no configuration for
    partial-, template- nor for layoutRootPaths.

..  confval:: settings
    :name: glossary-api-option-settings
    :type: array
    :default: empty

    If you have your own Template defined, it may be useful to have your own
    settings inside of your template. Assign your own settings or use settings of
    your controller (see example above).

..  confval:: extensionName
    :name: glossary-api-option-extensionName
    :type: string
    :default: glossary2

    Please change default value to extension name of your extension. This is
    needed for correct linking of the A-Z list. It will be used within the Plugin
    namespace in URI: `tx_extensionname_pluginname`. Underscores will
    automatically be converted to UpperCamelCase.

    It's your part to check the GET parameters (letter) and adapt your queries
    to show filtered records.

..  confval:: pluginName
    :name: glossary-api-option-pluginName
    :type: string
    :default: glossar

    Please change default value to plugin name of your extension. This is needed
    for correct linking of the A-Z list. It will be used within the Plugin
    namespace in URI: `tx_extensionname_pluginname`.

    It's your part to check the GET parameters (letter) and adapt your queries
    to show filtered records.

..  confval:: controllerName
    :name: glossary-api-option-controllerName
    :type: string
    :default: Glossar

    Please change default value to controller name which should be used for links.
    It will be used as controller part
    in URI: `tx_extensionname_pluginname[controllerName]`.

    It's your part to check the GET parameters (letter) and adapt your queries
    to show filtered records.

..  confval:: actionName
    :name: glossary-api-option-actionName
    :type: string
    :default: list

    Please change default value to action name of given controller name above,
    which should be used for links. It will be used as action part
    in URI: `tx_extensionname_pluginname[actionName]`.

    It's your part to check the GET parameters (letter) and adapt your queries
    to show filtered records.

..  confval:: mergeNumbers
    :name: glossary-api-option-mergeNumbers
    :type: bool
    :default: true

    By default the numbers will be represented in A-Z list as 0-9 instead
    of 0 1 2 3 4 5 6 7 8 9.

..  confval:: column
    :name: glossary-api-option-column
    :type: string
    :default: title

    The column of your QueryBuilder to extract the first letters from.

..  confval:: columnAlias
    :name: glossary-api-option-columnAlias
    :type: string
    :default: Letter

    To prevent duplicate usage of column in GROUP BY and ORDER BY we are working
    with an column alias. If you already have a column called `Letter` in your
    table you should change that property to something unique.

..  confval:: possibleLetters
    :name: glossary-api-option-possibleLetters
    :type: string
    :default: 0-9,a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z

    If empty it uses the default from ExtensionSettings

    These are the allowed letters to be shown in frontend. So, if you remove for
    example the letter `r`, it will not be shown in frontend, regardless if a
    record starting with `r` is in ResultSet or not.

    If you disable `mergeNumbers` you have to add each individual number here
    instead of using 0-9.

    It is not allowed to use a combination of numbers and a range like: 0, 1-3, 4.
    It is not allowed to use ranges other that 0-9 like: 0-3, 4-9.

..  _glossary-api-controller:

Extend your controller
======================

It is up to you to process the letter in your controller. In most cases you
may extend your listAction:

..  code-block:: php
    :caption: EXT:my_extension/Classes/Controller/MyController.php

    /**
     * @param string $letter
     */
    public function listAction(string $letter = '')
    {
        if ($letter) {
            $myRecords = $this->myRepo->findByLetter($letter);
        } else {
            $myRecords = $this->myRepo->findAll();
        }
        $this->view->assign('myRecords', $myRecords);
    }

..  _glossary-api-repository:

Extend your repository
======================

Above we have used a new method `findByLetter`. With glossary2 4.1.0 you can
use our API with Extbase Query or Doctrine.

..  _glossary-api-repository-extbase:

Example for an Extbase query
----------------------------

..  code-block:: php
    :caption: EXT:my_extension/Classes/Domain/Repository/MyRepository.php

    public function findByLetter(string $letter): QueryResultInterface
    {
        $glossaryService = GeneralUtility::makeInstance(GlossaryService::class);
        $query = $this->createQuery();

        $constraints = [];
        $constraints[] = $glossaryService->getLetterConstraintForExtbaseQuery($query, 'myColumnName', $letter);

        return $query->matching($query->logicalAnd($constraints))->execute();
    }

..  _glossary-api-repository-doctrine:

Example for a Doctrine query
----------------------------

..  code-block:: php
    :caption: EXT:my_extension/Classes/Domain/Repository/MyRepository.php

    public function findByLetter(string $letter): QueryResultInterface
    {
        $glossaryService = GeneralUtility::makeInstance(GlossaryService::class);
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('my_table');
        $queryBuilder
            ->select('*')
            ->from('my_table')
            ->andWhere($glossaryService->getLetterConstraintForDoctrineQuery($queryBuilder, 'my_colum_name', $letter));

        $query = $this->createQuery();
        return $query->statement($queryBuilder)->execute();
    }

..  _glossary-api-extend:

Extend the glossary function
============================

We have added two SignalSlots to extend functionality of glossary API

..  _glossary-api-postProcessFirstLetters:

postProcessFirstLetters
-----------------------

After retrieving the possible first letters from database, we clean, sort and
remove duplicates. The result will then be sent to this SignalSlot including
the currently used QueryBuilder. It's a simple array as reference:

..  code-block:: php
    :caption: EXT:my_extension/Classes/EventListener/MyEventListener.php

    $firstLetters = [
        0 => '0',
        1 => 'a',
        2 => 'b',
        3 => 'c',
        ...
    ];

Add or remove letters as you like.

..  _glossary-api-modifyLetterMapping:

modifyLetterMapping
-------------------

After retrieving the possible first letters from database, we start a
cleaning process of each individual letter. Here, we will map all german
umlauts ÄÖÜ to its AOU representation.

If you need further mappings like for french or spain you can use this
SignalSlot. You will get the $letterMapping array which you have to return within SignalSlot (not reference).

..  code-block:: php
    :caption: EXT:my_extension/Classes/EventListener/MyEventListener.php

    $letterMapping = [
        // default entries for germany
        'ä' => 'a',
        'ö' => 'e',
        'ü' => 'u',

        // new entries of your extension
        'à' => 'a',
        'è' => 'e',
        'ù' => 'u',
        ...
    ];
