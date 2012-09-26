<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                         *
********************************************************************************************************/

/**
 * Class handling communication with the filesystem, e.g. to read directories
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_filesystem {

    /**
     * @var null|resource
     */
    private $objFilePointer = null;

    /**
     * Constructor

     */
    public function __construct() {

    }

    /**
     * Returns all files listed in the passed folder
     *
     * @param string $strFolder
     * @param array $arrSuffix
     *
     * @return mixed
     */
    public function getFilelist($strFolder, $arrSuffix = array()) {
        $arrReturn = array();
        $intCounter = 0;

        if(!is_array($arrSuffix)) {
            $arrSuffix = array($arrSuffix);
        }

        //Deleting the root-folder, if given
        if(uniStrpos($strFolder, _realpath_) !== false)
            $strFolder = str_replace(_realpath_, "", $strFolder);

        //Read files
        if(is_dir(_realpath_.$strFolder)) {
            $handle = opendir(_realpath_.$strFolder);
            if($handle !== false) {
                while(false !== ($strFilename = readdir($handle))) {
                    if(($strFilename != "." && $strFilename != "..") && is_file(_realpath_.$strFolder."/".$strFilename)) {
                        //Wanted Type?
                        if(count($arrSuffix) == 0) {
                            $arrReturn[$intCounter++] = $strFilename;
                        }
                        else {
                            //check, if suffix is in allowed list
                            $strFileSuffix = uniSubstr($strFilename, uniStrrpos($strFilename, "."));
                            if(in_array($strFileSuffix, $arrSuffix)) {
                                $arrReturn[$intCounter++] = $strFilename;
                            }
                        }
                    }
                }
                closedir($handle);
            }
        }
        else {
            return false;
        }

        //sorting
        asort($arrReturn);
        return $arrReturn;
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
    public function getCompleteList($strFolder, $arrTypes = array(), $arrExclude = array(), $arrExcludeFolders = array(".", ".."), $bitFolders = true, $bitFiles = true) {
        $arrReturn = array("nrFiles"        => 0,
                           "nrFolders"      => 0,
                           "files"          => array(),
                           "folders"        => array()
        );


        if(uniStrpos($strFolder, _realpath_) !== false) {
            $strFolder = str_replace(_realpath_, "", $strFolder);
        }


        //Valid dir?
        if(is_dir(_realpath_.$strFolder)) {
            $objFileHandle = opendir(_realpath_.$strFolder);
            if($objFileHandle !== false) {
                while(($strEntry = readdir($objFileHandle)) !== false) {
                    //Folder
                    if(is_dir(_realpath_.$strFolder."/".$strEntry) && $bitFolders == true) {
                        //Folder excluded?
                        if(count($arrExcludeFolders) == 0 || !in_array($strEntry, $arrExcludeFolders)) {
                            $arrReturn["folders"][$arrReturn["nrFolders"]++] = $strEntry;
                        }
                    }

                    //File
                    if(is_file(_realpath_.$strFolder."/".$strEntry) && $bitFiles == true) {
                        $arrTemp = $this->getFileDetails(_realpath_.$strFolder."/".$strEntry);
                        //Excluded?
                        if(count($arrExclude) == 0 || !in_array($arrTemp["filetype"], $arrExclude)) {
                            //Types given?
                            if(count($arrTypes) != 0) {
                                if(in_array($arrTemp["filetype"], $arrTypes)) {

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
    public function getFileDetails($strFile) {
        $arrReturn = array();

        if(strpos($strFile, _realpath_) === false) {
            $strFile = _realpath_.$strFile;
        }

        if(is_file($strFile)) {
            //Filename
            $arrReturn["filename"] = basename($strFile);

            //Type
            $intTemp = uniStrrpos($strFile, ".");
            if($intTemp !== false) {
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
    public function fileRename($strSource, $strTarget, $bitForce = false) {
        $bitReturn = false;

        if(is_file(_realpath_."/".$strSource)) {
            //bitForce: overwrite existing file
            if(!is_file(_realpath_."/".$strTarget) || $bitForce) {
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
    public function fileCopy($strSource, $strTarget, $bitForce = false) {
        $bitReturn = false;

        if(is_file(_realpath_."/".$strSource)) {
            //bitForce: overwrite existing file
            if(!is_file(_realpath_."/".$strTarget) || $bitForce) {
                $bitReturn = copy(_realpath_."/".$strSource, _realpath_."/".$strTarget);
                //set correct rights
                @chmod(_realpath_."/".$strTarget, 0777);
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
    public function fileDelete($strFile) {
        $bitReturn = false;
        if(is_file(_realpath_.$strFile)) {
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
    public function folderDelete($strFolder) {
        $bitReturn = false;

        if(is_dir(_realpath_.$strFolder)) {
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
    public function folderDeleteRecursive($strFolder) {
        $bitReturn = true;

        $arrContents = $this->getCompleteList($strFolder, array(), array(), array(".", ".."));

        foreach($arrContents["folders"] as $strOneFolder) {
            $bitReturn = $bitReturn && $this->folderDeleteRecursive($strFolder."/".$strOneFolder);
        }

        foreach($arrContents["files"] as $strOneFile) {
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
    public function folderCopyRecursive($strSourceDir, $strTargetDir, $bitOverwrite = false) {

        $arrEntries = scandir(_realpath_.$strSourceDir);
        foreach($arrEntries as $strOneEntry) {
            if($strOneEntry == "." || $strOneEntry == "..") {
                continue;
            }

            if(is_file(_realpath_.$strSourceDir."/".$strOneEntry) && ($bitOverwrite || !is_file(_realpath_.$strTargetDir."/".$strOneEntry))) {

                if(!is_dir(_realpath_.$strTargetDir)) {
                    mkdir(_realpath_.$strTargetDir, 0777, true);
                }

                copy(_realpath_.$strSourceDir."/".$strOneEntry, _realpath_.$strTargetDir."/".$strOneEntry);
            }
            else if(is_dir(_realpath_.$strSourceDir."/".$strOneEntry)) {
                if(!is_dir(_realpath_.$strTargetDir."/".$strOneEntry)) {
                    mkdir(_realpath_.$strTargetDir."/".$strOneEntry, 0777, true);
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
     *
     * @return bool
     */
    public function folderCreate($strFolder, $bitRecursive = false) {
        $bitReturn = true;

        if($bitRecursive) {
            $arrRecursiveFolders = explode("/", $strFolder);

            $strFolders = "";
            foreach($arrRecursiveFolders as $strOneFolder) {
                if($bitReturn === true) {
                    $strFolders .= "/".$strOneFolder;
                    if(!is_dir(_realpath_.$strFolders)) {
                        $bitReturn = $this->folderCreate($strFolders, false);
                    }
                }
            }
        }
        else {
            if(!is_dir(_realpath_.$strFolder)) {
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
    public function folderSize($strFolder, $arrTypes = array(), $arrExclude = array(), $arrExcludeFolders = array(".svn", ".", "..")) {
        $intReturn = 0;

        $arrFiles = $this->getCompleteList($strFolder, $arrTypes, $arrExclude, $arrExcludeFolders);

        foreach($arrFiles["files"] as $arrFile) {
            $intReturn += $arrFile["filesize"];
        }

        //Call it recursive
        if(count($arrFiles["folders"]) > 0) {
            foreach($arrFiles["folders"] as $strOneFolder) {
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
    public function copyUpload($strTarget, $strTempfile) {
        $bitReturn = false;
        $strTarget = _realpath_.$strTarget;
        if(is_uploaded_file($strTempfile)) {
            if(@move_uploaded_file($strTempfile, $strTarget)) {
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
    public function openFilePointer($strFilename, $strMode = "w") {
        $this->objFilePointer = @fopen(_realpath_.$strFilename, $strMode);
        if($this->objFilePointer) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Closes the filepointer currently opened and releases the pointer

     */
    public function closeFilePointer() {
        if($this->objFilePointer != null) {
            @fclose($this->objFilePointer);
        }

        $this->objFilePointer = null;
    }

    /**
     * Sets the current filepointer to a given offset
     *
     * @param int $intOffset
     */
    public function setFilePointerOffset($intOffset) {
        if($this->objFilePointer != null) {
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
    public function writeToFile($strContent) {
        if($this->objFilePointer != null) {
            if(@fwrite($this->objFilePointer, $strContent) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Reads a line from the file opened by class_filesystem::openFilePointer(name, "r")
     *
     * @return string or false if eof or error
     */
    public function readLineFromFile() {
        $strContent = false;

        if($this->objFilePointer != null) {
            if(!feof($this->objFilePointer)) {
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
    public function readLastLinesFromFile($intNrOfLines = 10) {
        $strReturn = "";
        $intCursor = -1;
        $intLinesRead = 0;

        if($this->objFilePointer != null) {
            @fseek($this->objFilePointer, $intCursor, SEEK_END);
            $strChar = @fgetc($this->objFilePointer);

            while($strChar !== false && $intLinesRead <= $intNrOfLines) {
                $strReturn = $strChar.$strReturn;

                @fseek($this->objFilePointer, $intCursor--, SEEK_END);
                $strChar = fgetc($this->objFilePointer);

                if($strChar == "\n") {
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
    public function isWritable($strFile) {
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
     * @internal param $strMode
     * @since 4.0
     * @return bool
     */
    public function chmod($strPath, $intModeFile = 0644, $intModeDirectory = 0755, $bitRecursive = false) {

        if(!file_exists(_realpath_.$strPath))
            return false;


        $bitReturn = @chmod(
            _realpath_.$strPath,
            (is_dir(_realpath_.$strPath) ? $intModeDirectory : $intModeFile)
        );

        if($bitRecursive && is_dir(_realpath_.$strPath)) {
            $arrFiles = $this->getCompleteList($strPath);

            foreach($arrFiles["files"] as $strOneFile) {
                $bitReturn = $bitReturn && chmod(_realpath_."/".$strPath."/".$strOneFile, $intModeFile);
            }
            foreach($arrFiles["folders"] as $strOneFolder) {
                $bitReturn = $bitReturn && $this->chmod($strPath."/".$strOneFolder, $intModeFile, $intModeDirectory, $bitRecursive);
            }
        }
        return $bitReturn;
    }
}

