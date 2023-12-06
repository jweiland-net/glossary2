<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/glossary2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Glossary2\Configuration;

use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;

/*
 * This class streamlines all settings from extension manager
 */
class ExtConf implements SingletonInterface
{
    protected string $possibleLetters = '';

    protected string $templatePath = '';

    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        try {
            $extConf = $extensionConfiguration->get('glossary2');
            if (is_array($extConf)) {
                // call setter method foreach configuration entry
                foreach ($extConf as $key => $value) {
                    $methodName = 'set' . ucfirst($key);
                    if (method_exists($this, $methodName)) {
                        $this->$methodName($value);
                    }
                }
            }
        } catch (ExtensionConfigurationExtensionNotConfiguredException | ExtensionConfigurationPathDoesNotExistException $e) {
            // Use default values of this class
        }
    }

    public function getPossibleLetters(): string
    {
        if ($this->possibleLetters === '') {
            return '0-9,a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z';
        }
        return $this->possibleLetters;
    }

    public function setPossibleLetters(string $possibleLetters): void
    {
        $this->possibleLetters = trim($possibleLetters);
    }

    public function getTemplatePath(): string
    {
        if ($this->templatePath === '') {
            return 'EXT:glossary2/Resources/Private/Templates/Glossary.html';
        }
        return $this->templatePath;
    }

    public function setTemplatePath(string $templatePath): void
    {
        $this->templatePath = trim($templatePath);
    }
}
