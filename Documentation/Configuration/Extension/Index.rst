:navigation-title: Extension settings

..  include:: /Includes.rst.txt
..  _extensionSettings:

==================
Extension settings
==================

Some general settings for `glossary2` can be configured
in :guilabel:`Admin Tools > Settings`.

..  _extensionSettings-basic:

Extension settings of tab "Basic"
=================================

..  confval-menu::
    :display: table
    :type:
    :default:

..  confval:: templatePath
    :name: extension-templatePath
    :type: string
    :default: `EXT:glossary2/Resources/Private/Templates/Glossary.html`

    Which template should be used to render the A-Z list. `EXT:` as prefix is
    possible. This is more a fallback, as you can override this option at
    various places.

..  confval:: possibleLetters
    :name: extension-possibleLetters
    :type: string
    :default: `0-9,a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z`

    Example: `plugin.tx_glossary2.settings.letters = 0-9,a,e,i,o,u`

    Define the letters you want see in the glossary.
    This is more a fallback, as you can override this option at various
    places.

    If you deactivate `mergeNumbers` in TypoScript you should override `0-9`
    in this option to individual numbers: `0,1,2,3,4,5,6,7,8,9`. Of cause you
    can override this option with help of TypoScript, too.
