<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;

use PharFileInfo;
use RecursiveIteratorIterator;

/**
 * Class to load and install modules packaged as Phar archives.
 *
 * @package module_system
 * @author ph.wolfer@gmail.com
 * @since 4.8
 */
class PharModule
{
    private $strPharPath;

    /**
     * @param string $strPharPath Path to the Phar file relative to Kajona root.
     */
    public function __construct($strPharPath)
    {
        $this->strPharPath = $strPharPath;
    }


    private function createContentMap()
    {

        if(!BootstrapCache::getInstance()->getCacheRow(BootstrapCache::CACHE_PHARCONTENT, $this->strPharPath)) {

            $arrTemp = array();

            /** @var \PharFileInfo $objFile */
            foreach($this->getFileIterator() as $objFile) {

                if(strpos($objFile->getFilename(), "/vendor/") !== false) {
                    continue;
                }

                $arrTemp[$this->getRelativeFilePath($objFile)] = str_replace("\\", "/", $objFile->getPathname());
            }

            BootstrapCache::getInstance()->addCacheRow(BootstrapCache::CACHE_PHARCONTENT, $this->strPharPath, $arrTemp);
        }

        return BootstrapCache::getInstance()->getCacheRow(BootstrapCache::CACHE_PHARCONTENT, $this->strPharPath);
    }


    public function getContentMap()
    {
        return $this->createContentMap();
    }

    /**
     * Load and initialize a Phar module.
     *
     * @param string[] List of folders supposed to contain code.
     * @return string[] A list of class names and corresponding absolute file paths.
     */
    public function load($arrCodeFoldersBlacklist)
    {
        $arrCodeFiles = [];
        foreach ($this->getContentMap() as $strArchivePath => $strPharPath) {
            // do we have a php file
            if (substr($strArchivePath, -4) !== ".php") {
                continue;
            }

            // check whether path contains a blacklisted path
            foreach ($arrCodeFoldersBlacklist as $strBlacklistName) {
                if (strpos($strArchivePath, $strBlacklistName) !== false) {
                    continue 2;
                }
            }

            $strFullFilename = basename($strArchivePath);
            $strClassname = substr($strFullFilename, 0, -4);

            if (!isset($arrCodeFiles[$strClassname])) {
                $arrCodeFiles[$strClassname] = $strPharPath;
            }
        }

        return $arrCodeFiles;
    }

    public function loadModuleIds()
    {
        foreach ($this->getContentMap() as $strArchivePath => $strPharPath) {

            if(substr($strArchivePath, -4) !== ".php") {
                continue;
            }

            // Include the module ID
            if (preg_match("/module\_([a-z0-9\_])+\_id\.php$/", $strPharPath)) {
                include_once $strPharPath;
            }
        }
    }


    /**
     * Return a file iterator
     *
     * @return RecursiveIteratorIterator
     */
    private function getFileIterator()
    {
        return new \RecursiveIteratorIterator(new \Phar(_realpath_.$this->strPharPath, 0));
    }

    /**
     * Check whether a given path is a Phar file.
     *
     * @param string $strPharFilePath Path to Phar archive file.
     * @return bool
     */
    public static function isPhar($strPharFilePath)
    {
        return StringUtil::indexOf($strPharFilePath, ".phar") !== false;
    }

    /**
     * Returns a Phar file's name withou extension.
     *
     * @param string $strPharFilePath Path to Phar archive file.
     * @return string
     */
    public static function getPharBasename($strPharFilePath)
    {
        $intExtensionPos = StringUtil::indexOf($strPharFilePath, ".phar");
        if ($intExtensionPos !== false) {
            return substr($strPharFilePath, 0, $intExtensionPos);
        }

        return false;
    }

    /**
     * Returns the phar:// path to a file inside a Phar archive
     *
     * @param string $strPharFilePath Absolute path to the Phar archive file.
     * @param string $strContentFilePath Relative path to the file inside the Phar archive
     * @return string
     */
    public static function getPharStreamPath($strPharFilePath, $strContentFilePath)
    {
        return "phar://".$strPharFilePath.$strContentFilePath;
    }

    /**
     * Returns a file's path relative to the Phar archive.
     *
     * @param $objFile PharFileInfo A phar file
     * @return string
     */
    private function getRelativeFilePath($objFile)
    {
        $strPharPath = "phar://".str_replace("//", "/", _realpath_.$this->strPharPath);
        $strArchivePath = substr($objFile->getPathName(), strlen($strPharPath));
        $strArchivePath = str_replace("\\", "/", $strArchivePath);
        return $strArchivePath;
    }
}
