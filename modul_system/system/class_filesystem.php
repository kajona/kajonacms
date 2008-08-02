<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_filesystem.php																				*
* 	Class handling access to the filesystem																*
*																										*
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                         *
********************************************************************************************************/

/**
 * Class handling communication with the filesystem, e.g. to read directories
 *
 * @package modul_system
 */
class class_filesystem {
	private $arrModul;

	private $objFilePointer = null;

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$this->arrModul["name"] 		= "class_filesystem";
		$this->arrModul["author"] 		= "sidler@mulchprod.de";
		$this->arrModul["moduleId"]		= _filesystem_modul_id_;

		$objCarrier = class_carrier::getInstance();
		//$this->objConfig = $objCarrier->getObjConfig();
	}

	/**
	 * Returns all files listed in the passed folder
	 *
	 * @param string $strFolder
	 * @param array $arrSuffix
	 * @return mixed
	 */
	public function getFilelist($strFolder, $arrSuffix=array()) 	{
		$arrReturn = array();
		$intCounter = 0;

		if(!is_array($arrSuffix)) {
		    $arrSuffix = array($arrSuffix);
		}

		//Deleting the root-folder, if given
		if(uniStrpos($strFolder, _realpath_) !== false)
			$strFolder = str_replace(_realpath_, "", $strFolder);

		//Read files
		if(is_dir(_realpath_ . $strFolder)) {
			$handle = opendir(_realpath_ . $strFolder);
			if($handle !== false) {
				while(false !== ($strFilename = readdir($handle))) 	{
					if(($strFilename != "." && $strFilename != "..") && is_file(_realpath_ . $strFolder . "/".$strFilename)) {
						//Wanted Type?
						if(count($arrSuffix)==0) {
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
		else
			return false;

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
	 * @return mixed
	 */
	public function getCompleteList($strFolder, $arrTypes = array(), $arrExclude = array(), $arrExcludeFolders = array(), $bitFolders = true, $bitFiles = true) {
		$arrReturn =  array( "nrFiles"  	=>  0,
							 "nrFolders"	=>	0,
							 "files"		=>	array(),
							 "folders"		=>	array()
						    );


		if(uniStrpos($strFolder, _realpath_) !== false)
			$strFolder = str_replace(_realpath_, "", $strFolder);


		//Valid dir?
		if(is_dir(_realpath_ . $strFolder)) {
			$handler = opendir(_realpath_ . $strFolder);
			if($handler !== false) {
				while(($strEntry = readdir($handler)) !== false) {
					//Folder
					if(is_dir(_realpath_ . $strFolder ."/". $strEntry) && $bitFolders == true) {
						//Folder Excluded?
						if(count($arrExcludeFolders) == 0 || !in_array($strEntry, $arrExcludeFolders)) 	{
							$arrReturn["folders"][$arrReturn["nrFolders"]++] = $strEntry;
						}
					}

					//File
					if(is_file(_realpath_ . $strFolder ."/". $strEntry) && $bitFiles == true) {
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
		}

		//sort Array
		asort($arrReturn["files"]);
		asort($arrReturn["folders"]);
		return $arrReturn;
	}

	/**
	 * Returns detailed infos about a file
	 *
	 * @param string $strFile
	 * @return mixed
	 */
	public function getFileDetails($strFile) {
		$arrReturn = array();
		if(is_file($strFile)) {
			//Filename
			$intTemp = uniStrrpos($strFile, "/");
			if($intTemp !== false)
				$arrReturn["filename"] = uniSubstr($strFile, $intTemp+1);
			else
				$arrReturn["filename"] = $strFile;
			//Type
			$intTemp = uniStrrpos($strFile, ".");
			if($intTemp !== false)
				$arrReturn["filetype"] = uniSubstr($strFile, $intTemp);
			else
				$arrReturn["filetype"] = $strFile;
			$arrReturn["filetype"] = strtolower($arrReturn["filetype"]);
			//Size
			$arrReturn["filesize"] = filesize($strFile);
			//creatopn
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
	 * Deletes a file from the filesystem
	 *
	 * @param string $strFile
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
	 * @return bool
	 */
	public function folderDeleteRecursive($strFolder) {
        $bitReturn = true;

        $arrContents = $this->getCompleteList($strFolder, array(), array(), array(".", ".."));

        foreach($arrContents["folders"] as $strOneFolder) {
            $bitReturn = $bitReturn && $this->folderDeleteRecursive($strFolder."/".$strOneFolder);
        }

        foreach ($arrContents["files"] as $strOneFile) {
            $bitReturn = $bitReturn && $this->fileDelete($strFolder."/".$strOneFile["filename"]);
        }

        $bitReturn = $bitReturn && $this->folderDelete($strFolder) ;


        return $bitReturn;
	}

	/**
	 * Creates a folder in the filesystem
	 *
	 * @param string $strFolder
	 * @return bool
	 */
	public function folderCreate($strFolder) {
		return mkdir(_realpath_.$strFolder, 0777);
	}

	/**
	 * Moves a uploded file
	 *
	 * @param string $strTarget
	 * @param string $strTempfile
	 * @return bool
	 */
	public function copyUpload($strTarget, $strTempfile) {
		$bitReturn = false;
		$strTarget = _realpath_.$strTarget;
		if(is_uploaded_file($strTempfile)) 	{
			if(@move_uploaded_file($strTempfile, $strTarget)) {
				@unlink($strTempfile);
				//Noch ein chmod absetzen
				@chmod($strTarget, 0777);
				$bitReturn = true;
			}
			else
				@unlink($strTempfile);
		}
		return $bitReturn;
	}

	/**
	 * Opens the pointer to a file, used to read from it ot to write to this file
	 *
	 * @param string $strFilename
	 * @return bool
	 */
	public function openFilePointer($strFilename) {
	    $this->objFilePointer = @fopen(_realpath_.$strFilename, "w");
	    if($this->objFilePointer)
	        return true;
	    else
	        return false;
	}

	/**
	 * Closes the filepointer currently opened and releases the pointer
	 *
	 */
	public function closeFilePointer() {
	    if($this->objFilePointer != null)
	       @fclose($this->objFilePointer);

	    $this->objFilePointer = null;
	}

	/**
	 * Tries to write the content passed to the file opened before
	 *
	 * @param stirng $strContent
	 * @return bool
	 */
	public function writeToFile($strContent) {
	    if($this->objFilePointer != null) {
	        if(@fwrite($this->objFilePointer, $strContent) !== false)
	           return true;
	    }
	    return false;
	}
	
	/**
	 * Checks if a file or folder is writeable
	 *
	 * @param string $strFile
	 * @return bool
	 */
	public function isWritable($strFile) {
		return is_writable(_realpath_."/".$strFile);
	}	
}

?>