<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                               *
********************************************************************************************************/

namespace Kajona\System\System;

use ZipArchive;


/**
 * This class is a wrapper to phps' integrated zip-archive methods and objects.
 * It depends on the zip-library provided by php.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 3.4.0
 */
class Zip
{

    /**
     * @var ZipArchive ZipArchive
     */
    private $objArchive = null;

    /**
     * Constructor
     *
     * @throws Exception
     */
    public function __construct()
    {
        if (!class_exists("ZipArchive")) {//TODO use namespace
            throw new Exception("current installation has no support for ZipArchive", Exception::$level_ERROR);
        }

        $this->objArchive = new ZipArchive();
    }

    /**
     * Sets and opens the filename of the zip-archive to be used
     *
     * @param string $strFilename
     *
     * @return bool
     */
    public function openArchiveForWriting($strFilename)
    {
        return $this->objArchive->open(_realpath_.$strFilename, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
    }

    /**
     * Adds a file to the zip-archive.
     * The second, optional param indicates the filename inside the archive
     *
     * @param string $strSourceFile
     * @param string $strTargetFile
     *
     * @return bool
     */
    public function addFile($strSourceFile, $strTargetFile = "")
    {

        $strSourceFile = uniStrReplace(_realpath_, "", $strSourceFile);

        if ($strTargetFile == "") {
            $strTargetFile = $strSourceFile;
        }

        $strTargetFile = ltrim($strTargetFile, "/");


        if (file_exists(_realpath_.$strSourceFile)) {
            return $this->objArchive->addFile(_realpath_.$strSourceFile, $strTargetFile);
        }
        else {
            return false;
        }

    }

    /**
     * Traverses the folder and adds the folder recursively.
     *
     * @param string $strFolder
     *
     * @return bool
     */
    public function addFolder($strFolder)
    {
        $bitReturn = true;
        if (is_dir(_realpath_.$strFolder)) {
            $objFilesystem = new Filesystem();
            $arrFiles = $objFilesystem->getCompleteList($strFolder, array(), array(), array(".", ".."));
            foreach ($arrFiles["files"] as $arrOneFile) {
                $bitReturn = $bitReturn && $this->addFile($arrOneFile["filepath"]);
            }

            foreach ($arrFiles["folders"] as $strOneFolder) {
                $bitReturn = $bitReturn && $this->addFolder($strFolder."/".$strOneFolder);
            }

            return $bitReturn;

        }

        return false;
    }

    /**
     * Extracts all files in the archive to the folder given.
     *
     * @param string $strSourceArchive
     * @param string $strTarget
     *
     * @return bool
     */
    public function extractArchive($strSourceArchive, $strTarget)
    {
        $this->objArchive->open(_realpath_.$strSourceArchive);
        $bitReturn = $this->objArchive->extractTo(_realpath_.$strTarget);
        $this->objArchive->close();
        return $bitReturn;
    }

    /**
     * Loads a single file from the passed archive.
     * If not found, false is returned instead.
     *
     * @param string $strSourceArchive
     * @param string $strFilename
     *
     * @return mixed|bool
     */
    public function getFileFromArchive($strSourceArchive, $strFilename)
    {

        if ($strFilename[0] == "/") {
            $strFilename = uniSubstr($strFilename, 1);
        }

        $this->objArchive->open(_realpath_.$strSourceArchive);
        $strReturn = $this->objArchive->getFromName($strFilename);
        $this->objArchive->close();
        return $strReturn;
    }

    /**
     * Finalizes the current archive and closes all file-handles
     *
     * @return bool
     */
    public function closeArchive()
    {
        return $this->objArchive->close();
    }

    /**
     * Checks if the given file is a zip-File
     *
     * @param string $strFilename
     *
     * @return bool, true if the file is a zip-File, an error code if it is not a zip-file (see ZipArchive)
     */
    public function isZipFile($strFilename)
    {
        return $this->objArchive->open(_realpath_.$strFilename, ZipArchive::CHECKCONS);
    }
}

