<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
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
    private $objPhar;

//    private static $arrPharContent = null;
    private static $arrPharContent = array();

    /**
     * @param string $strPharPath Path to the Phar file relative to Kajona root.
     */
    public function __construct($strPharPath)
    {
        $this->strPharPath = $strPharPath;
        $this->objPhar = new \Phar(_realpath_."/".$this->strPharPath, 0);

        $this->createContentMap();
    }


    private function createContentMap()
    {

//        if(self::$arrPharContent == null) {
//            self::$arrPharContent = array();
//
//            if(is_file(_realpath_."/project/temp/pharcontent.cache")) {
//                self::$arrPharContent = unserialize(file_get_contents(_realpath_."/project/temp/pharcontent.cache"));
//            }
//            else
//                self::$arrPharContent = array();
//        }

        if(!isset(self::$arrPharContent[$this->strPharPath])) {

            self::$arrPharContent[$this->strPharPath] = array();

            /** @var \SplFileInfo $objFile */
            foreach($this->getFileIterator() as $objFile) {
                self::$arrPharContent[$this->strPharPath][$this->getRelativeFilePath($objFile)] = $objFile->getPathname();
            }

        }
    }

    /**
     * @inheritDoc
     */
    function __destruct()
    {
//        if(self::$arrPharContent != null) {
//            file_put_contents(_realpath_."/project/temp/pharcontent.cache", serialize(self::$arrPharContent));
//            self::$arrPharContent = null;
//        }
    }


    public function getContentMap()
    {
        return self::$arrPharContent[$this->strPharPath];
    }

    /**
     * Load and initialize a Phar module.
     *
     * @param string[] List of folders supposed to contain code.
     * @return string[] A list of class names and corresponding absolute file paths.
     */
    public function load($arrCodeFolders)
    {
        $arrCodeFiles = [];
        foreach ($this->getContentMap() as $strArchivePath => $strPharPath) {

            if(substr($strArchivePath, -4) !== ".php") {
                continue;
            }


            $strFullFilename = basename($strPharPath);
            foreach ($arrCodeFolders as $strFolder) {
                $strFolder = str_replace("\\", "/", $strFolder).basename($strArchivePath);

                if (substr($strArchivePath, -4) === ".php" && $strArchivePath == $strFolder) {
                    $strClassname = substr($strFullFilename, 0, -4);

                    if (!isset($arrCodeFiles[$strClassname])) {
                        $arrCodeFiles[$strClassname] = $strPharPath;
                        break;
                    }
                }
            }

            // Include the module ID
            if (preg_match("/module\_([a-z0-9\_])+\_id\.php/", $strFullFilename)) {
                include_once $strPharPath;
            }
        }

        return $arrCodeFiles;
    }

    /**
     * Return a file iterator
     *
     * @return RecursiveIteratorIterator
     */
    private function getFileIterator()
    {
        return new \RecursiveIteratorIterator($this->objPhar);
    }

    /**
     * Check whether a given path is a Phar file.
     *
     * @param string $strPharFilePath Path to Phar archive file.
     * @return bool
     */
    public static function isPhar($strPharFilePath)
    {
        return uniStrpos($strPharFilePath, ".phar") !== false;
    }

    /**
     * Returns a Phar file's name withou extension.
     *
     * @param string $strPharFilePath Path to Phar archive file.
     * @return string
     */
    public static function getPharBasename($strPharFilePath)
    {
        $intExtensionPos = uniStrpos($strPharFilePath, ".phar");
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
        $strArchivePath = DIRECTORY_SEPARATOR.substr($objFile->getPathName(),
            strlen("phar://"._realpath_."/".$this->strPharPath));
        $strArchivePath = str_replace("\\", "/", $strArchivePath);
        return $strArchivePath;
    }
}
