<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * Class handling communication with the filesystem, e.g. to read directories
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class Filesystem
{

    /**
     * @var null|resource
     */
    private $objFilePointer = null;


    /**
     * Returns all files listed in the passed folder
     *
     * @param string $strFolder
     * @param array $arrSuffix
     * @param bool $bitRecursive
     *
     * @return string[]
     */
    public function getFilelist($strFolder, $arrSuffix = array(), $bitRecursive = false)
    {

        if (!is_array($arrSuffix)) {
            $arrSuffix = array($arrSuffix);
        }

        //Deleting the root-folder, if given
        if (uniStrpos($strFolder, _realpath_) !== false) {
            $strFolder = str_replace(_realpath_, "", $strFolder);
        }

        $arrReturn = array();
        $this->getFilelistHelper($strFolder, $arrSuffix, $bitRecursive, $arrReturn);

        //sorting
        asort($arrReturn);
        return $arrReturn;
    }

    /**
     * Internal helper to load folder contents recursively
     *
     * @param string $strFolder
     * @param string[] $arrSuffix
     * @param bool $bitRecursive
     * @param array &$arrReturn
     *
     * @return void
     */
    private function getFilelistHelper($strFolder, $arrSuffix, $bitRecursive, &$arrReturn)
    {
        if (!is_dir(_realpath_.$strFolder)) {
            return;
        }

        $arrFiles = scandir(_realpath_.$strFolder);
        foreach ($arrFiles as $strFilename) {
            if ($strFilename == "." || $strFilename == "..") {
                continue;
            }

            if (is_file(_realpath_.$strFolder."/".$strFilename)) {
                //Wanted Type?
                if (count($arrSuffix) == 0) {
                    $arrReturn[$strFolder."/".$strFilename] = $strFilename;
                }
                else {
                    //check, if suffix is in allowed list
                    $strFileSuffix = uniSubstr($strFilename, uniStrrpos($strFilename, "."));
                    if (in_array($strFileSuffix, $arrSuffix)) {
                        $arrReturn[$strFolder."/".$strFilename] = $strFilename;
                    }
                }
            }
            elseif (is_dir(_realpath_.$strFolder."/".$strFilename) && $bitRecursive) {
                $this->getFilelistHelper($strFolder."/".$strFilename, $arrSuffix, $bitRecursive, $arrReturn);
            }
        }

    }


    /**
     * Returns all files an folders in the passed folder
     *
     * @param string $strFolder
     * @param mixed $arrTypes
     * @param mixed $arrExclude
     * @param mixed $arrExcludeFolders
     * @param bool $bitFolders
     * @param bool $bitFiles
     *
     * @return mixed
     */
    public function getCompleteList($strFolder, $arrTypes = array(), $arrExclude = array(), $arrExcludeFolders = array(".", ".."), $bitFolders = true, $bitFiles = true)
    {
        $arrReturn = array("nrFiles"   => 0,
                           "nrFolders" => 0,
                           "files"     => array(),
                           "folders"   => array()
        );


        if (uniStrpos($strFolder, _realpath_) !== false) {
            $strFolder = str_replace(_realpath_, "", $strFolder);
        }


        //Valid dir?
        if (is_dir(_realpath_.$strFolder)) {
            $objFileHandle = opendir(_realpath_.$strFolder);
            if ($objFileHandle !== false) {
                while (($strEntry = readdir($objFileHandle)) !== false) {
                    //Folder
                    if (is_dir(_realpath_.$strFolder."/".$strEntry) && $bitFolders == true) {
                        //Folder excluded?
                        if (count($arrExcludeFolders) == 0 || !in_array($strEntry, $arrExcludeFolders)) {
                            $arrReturn["folders"][$arrReturn["nrFolders"]++] = $strEntry;
                        }
                    }

                    //File
                    if (is_file(_realpath_.$strFolder."/".$strEntry) && $bitFiles == true) {
                        $arrTemp = $this->getFileDetails(_realpath_.$strFolder."/".$strEntry);
                        //Excluded?
                        if (count($arrExclude) == 0 || !in_array($arrTemp["filetype"], $arrExclude)) {
                            //Types given?
                            if (count($arrTypes) != 0) {
                                if (in_array($arrTemp["filetype"], $arrTypes)) {

                                    $arrReturn["files"][$arrReturn["nrFiles"]++] = $arrTemp;
                                }
                            }
                            else {
                                $arrReturn["files"][$arrReturn["nrFiles"]++] = $arrTemp;
                            }
                        }
                    }
                }

            }
            closedir($objFileHandle);
        }

        //sort array
        asort($arrReturn["files"]);
        asort($arrReturn["folders"]);
        return $arrReturn;
    }

    /**
     * Returns detailed info about a file
     *
     * @param string $strFile
     *
     * @return mixed
     */
    public function getFileDetails($strFile)
    {
        $arrReturn = array();

        if (strpos($strFile, _realpath_) === false) {
            $strFile = _realpath_.$strFile;
        }

        if (is_file($strFile)) {
            //Filename
            $arrReturn["filename"] = basename($strFile);

            //Type
            $intTemp = uniStrrpos($strFile, ".");
            if ($intTemp !== false) {
                $arrReturn["filetype"] = uniSubstr($strFile, $intTemp);
            }
            else {
                $arrReturn["filetype"] = $strFile;
            }
            $arrReturn["filetype"] = uniStrtolower($arrReturn["filetype"]);
            //Size
            $arrReturn["filesize"] = filesize($strFile);
            //creatipn
            $arrReturn["filecreation"] = filemtime($strFile);
            //change
            $arrReturn["filechange"] = filectime($strFile);
            //access
            $arrReturn["fileaccess"] = fileatime($strFile);
            //path
            $arrReturn["filepath"] = $strFile;
        }

        return $arrReturn;
    }

    /**
     * Renames a file
     *
     * @param string $strSource
     * @param string $strTarget
     * @param bool $bitForce
     *
     * @return bool
     */
    public function fileRename($strSource, $strTarget, $bitForce = false)
    {
        $bitReturn = false;

        if (is_file(_realpath_."/".$strSource)) {
            //bitForce: overwrite existing file
            if (!is_file(_realpath_."/".$strTarget) || $bitForce) {
                $bitReturn = rename(_realpath_."/".$strSource, _realpath_."/".$strTarget);
            }
        }
        return $bitReturn;
    }

    /**
     * Copies a file
     *
     * @param string $strSource
     * @param string $strTarget
     * @param bool $bitForce
     *
     * @return bool
     */
    public function fileCopy($strSource, $strTarget, $bitForce = false)
    {
        $bitReturn = false;

        if (\Kajona\System\System\StringUtil::indexOf($strSource, _realpath_) === false) {
            $strSource = _realpath_.$strSource;
        }
        if (\Kajona\System\System\StringUtil::indexOf($strTarget, _realpath_) === false) {
            $strTarget = _realpath_.$strTarget;
        }

        if (is_file($strSource)) {
            //bitForce: overwrite existing file
            if (!is_file($strTarget) || $bitForce) {
                $bitReturn = copy($strSource, $strTarget);
                //set correct rights
                @chmod($strTarget, 0777);
            }
        }
        return $bitReturn;
    }

    /**
     * Deletes a file from the filesystem
     *
     * @param string $strFile
     *
     * @return bool
     */
    public function fileDelete($strFile)
    {
        $strFile = uniStrReplace(_realpath_, "", $strFile);
        $bitReturn = false;
        if (is_file(_realpath_.$strFile)) {
            $bitReturn = unlink(_realpath_.$strFile);
        }
        return $bitReturn;
    }

    /**
     * Deletes a folder from the filesystem
     *
     * @param string $strFolder
     *
     * @return bool
     */
    public function folderDelete($strFolder)
    {
        $bitReturn = false;
        $strFolder = uniStrReplace(_realpath_, "", $strFolder);

        if (is_dir(_realpath_.$strFolder)) {
            $bitReturn = rmdir(_realpath_.$strFolder);
        }

        return $bitReturn;
    }

    /**
     * Deletes a folder and all its contents
     *
     * @param string $strFolder
     *
     * @return bool
     */
    public function folderDeleteRecursive($strFolder)
    {
        $bitReturn = true;
        $strFolder = uniStrReplace(_realpath_, "", $strFolder);

        $arrContents = $this->getCompleteList($strFolder, array(), array(), array(".", ".."));

        foreach ($arrContents["folders"] as $strOneFolder) {
            $bitReturn = $bitReturn && $this->folderDeleteRecursive($strFolder."/".$strOneFolder);
        }

        foreach ($arrContents["files"] as $strOneFile) {
            $bitReturn = $bitReturn && $this->fileDelete($strFolder."/".$strOneFile["filename"]);
        }

        $bitReturn = $bitReturn && $this->folderDelete($strFolder);


        return $bitReturn;
    }

    /**
     * Copies a folder recursive, including all files and folders
     *
     * @param $strSourceDir
     * @param $strTargetDir
     * @param bool $bitOverwrite
     *
     * @since 4.0
     */
    public function folderCopyRecursive($strSourceDir, $strTargetDir, $bitOverwrite = false)
    {

        if (\Kajona\System\System\StringUtil::indexOf($strSourceDir, _realpath_) === false) {
            $strSourceDir = _realpath_.$strSourceDir;
        }

        if (\Kajona\System\System\StringUtil::indexOf($strTargetDir, _realpath_) === false) {
            $strTargetDir = _realpath_.$strTargetDir;
        }


        $arrEntries = scandir($strSourceDir);
        foreach ($arrEntries as $strOneEntry) {
            if ($strOneEntry == "." || $strOneEntry == "..") {
                continue;
            }

            if (is_file($strSourceDir."/".$strOneEntry) && ($bitOverwrite || !is_file($strTargetDir."/".$strOneEntry))) {

                if (!is_dir($strTargetDir)) {
                    mkdir($strTargetDir, 0777, true);
                }

                copy($strSourceDir."/".$strOneEntry, $strTargetDir."/".$strOneEntry);
            }
            elseif (is_dir($strSourceDir."/".$strOneEntry)) {
                if (!is_dir($strTargetDir."/".$strOneEntry)) {
                    mkdir($strTargetDir."/".$strOneEntry, 0777, true);
                }

                $this->folderCopyRecursive($strSourceDir."/".$strOneEntry, $strTargetDir."/".$strOneEntry, $bitOverwrite);
            }
        }
    }

    /**
     * Creates a folder in the filesystem. Use $bitRecursive if you want to create a whole folder tree
     *
     * @param string $strFolder
     * @param bool $bitRecursive
     * @param bool $bitThrowExceptionOnError
     *
     * @return bool
     * @throws Exception
     */
    public function folderCreate($strFolder, $bitRecursive = false, $bitThrowExceptionOnError = false)
    {
        $strFolder = uniStrReplace(_realpath_, "", $strFolder);
        $bitReturn = true;

        if ($bitRecursive) {
            $arrRecursiveFolders = explode("/", $strFolder);

            $strFolders = "";
            foreach ($arrRecursiveFolders as $strOneFolder) {
                if ($bitReturn === true) {

                    $strTestfolder = $strFolders;

                    $strFolders .= "/".$strOneFolder;
                    if (!is_dir(_realpath_.$strFolders)) {

                        if ($bitThrowExceptionOnError && !is_writable(_realpath_.$strTestfolder)) {
                            throw new Exception("Folder "._realpath_.$strTestfolder." is not writable", Exception::$level_FATALERROR);
                        }

                        $bitReturn = $this->folderCreate($strFolders, false);
                    }
                }
            }
        }
        else {
            if (!is_dir(_realpath_.$strFolder)) {
                $bitReturn = mkdir(_realpath_.$strFolder, 0777);
            }
        }

        return $bitReturn;
    }

    /**
     * Fetches the size of a folder recursively
     *
     * @param string $strFolder
     * @param mixed $arrTypes
     * @param mixed $arrExclude
     * @param mixed $arrExcludeFolders
     *
     * @return int
     */
    public function folderSize($strFolder, $arrTypes = array(), $arrExclude = array(), $arrExcludeFolders = array(".svn", ".", ".."))
    {
        $intReturn = 0;

        $arrFiles = $this->getCompleteList($strFolder, $arrTypes, $arrExclude, $arrExcludeFolders);

        foreach ($arrFiles["files"] as $arrFile) {
            $intReturn += $arrFile["filesize"];
        }

        //Call it recursive
        if (count($arrFiles["folders"]) > 0) {
            foreach ($arrFiles["folders"] as $strOneFolder) {
                $intReturn += $this->folderSize($strFolder."/".$strOneFolder, $arrTypes, $arrExclude, $arrExcludeFolders);
            }
        }
        return $intReturn;
    }

    /**
     * Moves an uploaded file
     *
     * @param string $strTarget
     * @param string $strTempfile
     *
     * @return bool
     */
    public function copyUpload($strTarget, $strTempfile)
    {
        $bitReturn = false;
        $strTarget = _realpath_.$strTarget;
        if (is_uploaded_file($strTempfile)) {
            if (@move_uploaded_file($strTempfile, $strTarget)) {
                @unlink($strTempfile);
                //set correct rights
                @chmod($strTarget, 0777);
                $bitReturn = true;
            }
            else {
                @unlink($strTempfile);
            }
        }
        return $bitReturn;
    }

    /**
     * Opens the pointer to a file, used to read from it ot to write to this file
     *
     * @param string $strFilename
     * @param string $strMode w = write, r = read
     *
     * @return bool
     */
    public function openFilePointer($strFilename, $strMode = "w")
    {
        $this->objFilePointer = @fopen(_realpath_.$strFilename, $strMode);
        if ($this->objFilePointer) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Closes the filepointer currently opened and releases the pointer
     */
    public function closeFilePointer()
    {
        if ($this->objFilePointer != null) {
            @fclose($this->objFilePointer);
        }

        $this->objFilePointer = null;
    }

    /**
     * Sets the current filepointer to a given offset
     *
     * @param int $intOffset
     */
    public function setFilePointerOffset($intOffset)
    {
        if ($this->objFilePointer != null) {
            @fseek($this->objFilePointer, $intOffset);
        }
    }

    /**
     * Tries to write the content passed to the file opened before
     *
     * @param string $strContent
     *
     * @return bool
     */
    public function writeToFile($strContent)
    {
        if ($this->objFilePointer != null) {
            if (@fwrite($this->objFilePointer, $strContent) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Reads a line from the file opened by Filesystem::openFilePointer(name, "r")
     *
     * @return string or false if eof or error
     */
    public function readLineFromFile()
    {
        $strContent = false;

        if ($this->objFilePointer != null) {
            if (!feof($this->objFilePointer)) {
                $strContent = trim(fgets($this->objFilePointer));
            }
        }

        return $strContent;
    }

    /**
     * Reads a section from the end of a file.
     * This is done with pointers, reducing the amount of memory consumed.
     * Open the file by openFilePointer() before.
     *
     * @param int $intNrOfLines
     *
     * @return string
     */
    public function readLastLinesFromFile($intNrOfLines = 10)
    {
        $strReturn = "";
        $intCursor = -1;
        $intLinesRead = 0;

        if ($this->objFilePointer != null) {
            @fseek($this->objFilePointer, $intCursor, SEEK_END);
            $strChar = @fgetc($this->objFilePointer);

            while ($strChar !== false && $intLinesRead <= $intNrOfLines) {
                $strReturn = $strChar.$strReturn;

                @fseek($this->objFilePointer, $intCursor--, SEEK_END);
                $strChar = fgetc($this->objFilePointer);

                if ($strChar == "\n") {
                    $intLinesRead++;
                }
            }
        }

        return $strReturn;
    }

    /**
     * Checks if a file or folder is writable
     *
     * @param string $strFile
     *
     * @return bool
     */
    public function isWritable($strFile)
    {
        return is_writable(_realpath_."/".$strFile);
    }

    /**
     * Wrapper to phps' chmod function. Provides an optional recursion.
     * When called with no other param then the path, a default set of
     *  0644 for files and
     *  0755 for directories
     * is set.
     *
     * @param $strPath
     * @param int $intModeFile
     * @param int $intModeDirectory
     * @param bool $bitRecursive
     *
     * @since 4.0
     * @return bool
     */
    public function chmod($strPath, $intModeFile = 0644, $intModeDirectory = 0755, $bitRecursive = false)
    {

        if (!file_exists(_realpath_.$strPath)) {
            return false;
        }


        $bitReturn = @chmod(
            _realpath_.$strPath,
            (is_dir(_realpath_.$strPath) ? $intModeDirectory : $intModeFile)
        );

        if ($bitRecursive && is_dir(_realpath_.$strPath)) {
            $arrFiles = $this->getCompleteList($strPath);

            foreach ($arrFiles["files"] as $strOneFile) {
                $bitReturn = $bitReturn && chmod(_realpath_."/".$strPath."/".$strOneFile, $intModeFile);
            }
            foreach ($arrFiles["folders"] as $strOneFolder) {
                $bitReturn = $bitReturn && $this->chmod($strPath."/".$strOneFolder, $intModeFile, $intModeDirectory, $bitRecursive);
            }
        }
        return $bitReturn;
    }

    /**
     * Streams the file directly to the client.
     * Make sure to die() the process afterwards, this is not done by this method!
     *
     * @param $strSourceFile
     */
    public function streamFile($strSourceFile)
    {
        if (StringUtil::indexOf($strSourceFile, _realpath_) === false) {
            $strSourceFile = _realpath_.$strSourceFile;
        }


        //Send the data to the browser
        $strBrowser = getServer("HTTP_USER_AGENT");
        //Check the current browsertype
        if (StringUtil::indexOf($strBrowser, "IE") !== false) {
            //Internet Explorer
            ResponseObject::getInstance()->addHeader("Content-type: application/x-ms-download");
            ResponseObject::getInstance()->addHeader("Content-type: x-type/subtype\n");
            ResponseObject::getInstance()->addHeader("Content-type: application/force-download");
            ResponseObject::getInstance()->addHeader(
                "Content-Disposition: attachment; filename=".preg_replace(
                    '/\./', '%2e',
                    saveUrlEncode(trim(basename($strSourceFile))), substr_count(basename($strSourceFile), '.') - 1
                )
            );
        }
        else {
            //Good: another browser vendor
            ResponseObject::getInstance()->addHeader("Content-Type: application/octet-stream");
            ResponseObject::getInstance()->addHeader("Content-Disposition: attachment; filename=".saveUrlEncode(trim(basename($strSourceFile))));
        }
        //Common headers
        ResponseObject::getInstance()->addHeader("Expires: Mon, 01 Jan 1995 00:00:00 GMT");
        ResponseObject::getInstance()->addHeader("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
        ResponseObject::getInstance()->addHeader("Pragma: no-cache");
        ResponseObject::getInstance()->addHeader("Content-description: JustThum-Generated Data\n");
        ResponseObject::getInstance()->addHeader("Content-Length: ".filesize($strSourceFile));

        //End Session
        Carrier::getInstance()->getObjSession()->sessionClose();

        ob_clean();
        ResponseObject::getInstance()->sendHeaders();

        //Loop the file
        $ptrFile = @fopen($strSourceFile, 'rb');
        fpassthru($ptrFile);
        @fclose($ptrFile);
        ob_flush();
        flush();
    }
}

