<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$						*
********************************************************************************************************/

/**
 * admin-class of the filemananger-module
 * Serves xml-requests, currently to handle uploads
 *
 * @package modul_filemanager
 */
class class_modul_filemanager_admin_xml extends class_admin implements interface_xml_admin {


	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 				= "modul_filemanger";
		$arrModule["author"] 			= "sidler@mulchprod.de";
		$arrModule["moduleId"] 			= _filemanager_modul_id_;
		$arrModule["modul"]				= "filemanager";

		parent::__construct($arrModule);
	}


	/**
	 * Actionblock. Controls the further behaviour.
	 *
	 * @param string $strAction
	 * @return string
	 */
	public function action($strAction) {
        $strReturn = "";
        if($strAction == "fileUpload")
            $strReturn .= $this->actionFileupload();
        elseif($strAction == "saveCropping")
            $strReturn .= $this->actionSaveCropping();
        elseif($strAction == "rotate")
            $strReturn .= $this->actionRotateImage();
        elseif($strAction == "deleteFile")
            $strReturn .= $this->actionDeleteFile();
        elseif($strAction == "renameFile")
            $strReturn .= $this->actionRenameFile();
        elseif($strAction == "deleteFolder")
            $strReturn .= $this->actionDeleteFolder();
        elseif($strAction == "createFolder")
            $strReturn .= $this->actionCreateFolder();

        return $strReturn;
	}


    /**
     * Deletes the given file from the filesystem
     * @return string
     */
    private function actionDeleteFile() {
        $strReturn = "";
        if($this->objRights->rightDelete($this->getSystemid())) {
            //create repo-instance
            $objFmRepo = new class_modul_filemanager_repo($this->getSystemid());
            $strFolder = $this->getParam("folder");
            $strFile = $this->getParam("file");

            //Delete from filesystem
            $objFilesystem = new class_filesystem();
            class_logger::getInstance()->addLogRow("deleted file ".$objFmRepo->getStrPath()."/".$strFolder."/".$strFile, class_logger::$levelInfo);
            if($objFilesystem->fileDelete($objFmRepo->getStrPath()."/".$strFolder."/".$strFile))
                $strReturn .= "<message>".xmlSafeString($this->getText("datei_loeschen_erfolg"))."</message>";
			else
                $strReturn .= "<error>".xmlSafeString($this->getText("datei_loeschen_fehler"))."</error>";
        }
        else
            $strReturn .= "<error>".xmlSafeString($this->getText("xml_error_permissions"))."</error>";


        return $strReturn;
    }


    /**
     * Create a new folder using the combi of folder & systemid passed
     * @return string
     */
    private function actionRenameFile() {
        $strReturn = "";
        if($this->objRights->rightRight1($this->getSystemid())) {

            //create repo-instance
            $objFmRepo = new class_modul_filemanager_repo($this->getSystemid());
            $strFolder = $objFmRepo->getStrPath()."/".$this->getParam("folder");


            $strFilename = createFilename($this->getParam("newFilename"));
            //Check existance of old  & new file
            if($strFilename != "" && is_file(_realpath_."/".$strFolder."/".$this->getParam("oldFilename"))) {
                if(!is_file(_realpath_."/".$strFolder."/".$strFilename)) {
                    //Rename File
                    $objFilesystem = new class_filesystem();
                    if($objFilesystem->fileRename($strFolder."/".$this->getParam("oldFilename"), $strFolder."/".$strFilename))
                        $strReturn = "<message></message>";
                    else
                        $strReturn = "<error>".xmlSafeString($this->getText("datei_umbenennen_fehler"))."</error>";

                }
                else
                    $strReturn = "<error>".xmlSafeString($this->getText("datei_umbenennen_fehler_z"))."</error>";
            }
            else
                $strReturn = "<error>an error occured</error>";

        }
        else
            $strReturn .= "<error>".xmlSafeString($this->getText("xml_error_permissions"))."</error>";

        return $strReturn;
    }

    /**
     * Create a new folder using the combi of folder & systemid passed
     * @return string
     */
    private function actionCreateFolder() {
        $strReturn = "";
        if($this->objRights->rightRight1($this->getSystemid())) {

            //create repo-instance
            $objFmRepo = new class_modul_filemanager_repo($this->getSystemid());
            $strFolder = $this->getParam("folder");

            //Create the folder
            $intLastSlashPos = strrpos($strFolder, "/");
            $strFolder = substr($strFolder, 0, $intLastSlashPos)."/". createFilename(substr($strFolder, $intLastSlashPos+1), true);
            //folder already existing?
            if(!is_dir(_realpath_."/".$objFmRepo->getStrPath()."/".$strFolder)) {
                class_logger::getInstance()->addLogRow("creating folder ".$objFmRepo->getStrPath()."/".$strFolder, class_logger::$levelInfo);
                $objFilesystem = new class_filesystem();
                if($objFilesystem->folderCreate($objFmRepo->getStrPath()."/".$strFolder)) {
                    $strReturn = "<message>".xmlSafeString($this->getText("ordner_anlegen_erfolg"))."</message>";
                }
                else
                    $strReturn = "<error>".xmlSafeString($this->getText("order_anlegen_fehler"))."</error>";
            }
            else
                $strReturn = "<error>".xmlSafeString($this->getText("ordner_anlegen_fehler_l"))."</error>";
        }
        else
            $strReturn .= "<error>".xmlSafeString($this->getText("xml_error_permissions"))."</error>";

        return $strReturn;
    }


    /**
     * Deletes the given file from the filesystem
     * @return string
     */
    private function actionDeleteFolder() {
        $strReturn = "";
        if($this->objRights->rightDelete($this->getSystemid())) {
            //create repo-instance
            $objFmRepo = new class_modul_filemanager_repo($this->getSystemid());
            $strFolder = $this->getParam("folder");

            //Delete from filesystem
            $objFilesystem = new class_filesystem();

            //check if folder is empty
            $arrFilesSub = $objFilesystem->getCompleteList($objFmRepo->getStrPath()."/".$strFolder, array(), array(), array(".", ".."));
            if(count($arrFilesSub["files"]) == 0 && count($arrFilesSub["folders"]) == 0) {
                class_logger::getInstance()->addLogRow("deleted folder ".$objFmRepo->getStrPath()."/".$strFolder, class_logger::$levelInfo);
                if($objFilesystem->folderDelete($objFmRepo->getStrPath()."/".$strFolder))
                    $strReturn .= "<message>".xmlSafeString($this->getText("datei_loeschen_erfolg"))."</message>";
                else
                    $strReturn .= "<error>".xmlSafeString($this->getText("datei_loeschen_fehler"))."</error>";
            }
            else {
                $strReturn .= "<error>".xmlSafeString($this->getText("ordner_loeschen_fehler_l"))."</error>";
            }

        }
        else
            $strReturn .= "<error>".xmlSafeString($this->getText("xml_error_permissions"))."</error>";

        return $strReturn;
    }


    /**
     * Tries to rotate the passed imaged.
     * The following params are needed:
     * action = rotateImage
     * folder = the files' location
     * file = the file to crop
     * systemid = the repo-id
     * angle
     */
    private function actionRotateImage(){
    	$strReturn = "";

        if($this->objRights->rightEdit($this->getSystemid())) {
            //create repo instance
            $objRepo = new class_modul_filemanager_repo($this->getSystemid());
            $strFile = $objRepo->getStrPath().$this->getParam("folder")."/".$this->getParam("file");

            //pass to the image-class
            $objImage = new class_image();
            if($objImage->preLoadImage($strFile)) {
                if($objImage->rotateImage($this->getParam("angle"))) {
                    if($objImage->saveImage($strFile, false, 100)) {
                        class_logger::getInstance()->addLogRow("rotated file ".$strFile, class_logger::$levelInfo);
                        $strReturn .= "<message>".xmlSafeString($this->getText("xml_rotate_success"))."</message>";
                    }
                    else
                        class_logger::getInstance()->addLogRow("error rotating file ".$strFile, class_logger::$levelWarning);
                }
            }
            else
                $strReturn .= "<error>".xmlSafeString($this->getText("xml_error_permissions"))."</error>";

        }
        else
            $strReturn .= "<error>".xmlSafeString($this->getText("xml_error_permissions"))."</error>";


        return $strReturn;
    }

    /**
     * Tries to save the passed cropping.
     * The following params are needed:
     * action = saveCropping
     * folder = the files' location
     * file = the file to crop
     * systemid = the repo-id
     * intX
     * intY
     * intWidth
     * intHeight
     */
    private function actionSaveCropping() {
    	$strReturn = "";

        if($this->objRights->rightEdit($this->getSystemid())) {
            //create repo instance
            $objRepo = new class_modul_filemanager_repo($this->getSystemid());
            $strFile = $objRepo->getStrPath().$this->getParam("folder")."/".$this->getParam("file");

            //pass to the image-class
            $objImage = new class_image();
            if($objImage->preLoadImage($strFile)) {
                if($objImage->cropImage($this->getParam("intX"), $this->getParam("intY"), $this->getParam("intWidth"), $this->getParam("intHeight"))) {
                    if($objImage->saveImage($strFile, false, 100)) {
                        class_logger::getInstance()->addLogRow("cropped file ".$strFile, class_logger::$levelInfo);
                        $strReturn .= "<message>".xmlSafeString($this->getText("xml_cropping_success"))."</message>";
                    }
                    else
                        class_logger::getInstance()->addLogRow("error cropping file ".$strFile, class_logger::$levelWarning);
                }
            }
            else
                $strReturn .= "<error>".xmlSafeString($this->getText("xml_error_permissions"))."</error>";

        }
        else
            $strReturn .= "<error>".xmlSafeString($this->getText("xml_error_permissions"))."</error>";


        return $strReturn;
    }


	/**
	 * Tries to save the passed file.
	 * Therefore, the following post-params should be given:
	 * action = fileUpload
	 * folder = the folder to store the file within
	 * systemid = the filemanagers' repo-id
	 * inputElement = name of the inputElement
	 *
	 * @return string
	 */
	private function actionFileupload() {
	    $strReturn = "";

	    if($this->objRights->rightRight1($this->getSystemid())) {
	    	//create repo instance
	        $objRepo = new class_modul_filemanager_repo($this->getSystemid());

	        $strFolder = $objRepo->getStrPath().$this->getParam("folder");

	        //Handle the fileupload
            $arrSource = $this->getParam($this->getParam("inputElement"));

            $strTarget = $strFolder."/".createFilename($arrSource["name"]);
            $objFilesystem = new class_filesystem();
            if($objFilesystem->isWritable($strFolder)) {

                //Check file for correct filters
                $arrAllowed = explode(",", $objRepo->getStrUploadFilter());
                $strSuffix = uniStrtolower(uniSubstr($arrSource["name"], uniStrrpos($arrSource["name"], ".")));
                if($objRepo->getStrUploadFilter() == "" || in_array($strSuffix, $arrAllowed)) {
                    if($objFilesystem->copyUpload($strTarget, $arrSource["tmp_name"])) {
                        $strReturn .= "<message>".$this->getText("xmlupload_success")."</message>";
                        $bitSuccess = true;
                        class_logger::getInstance()->addLogRow("uploaded file ".$strTarget, class_logger::$levelInfo);
                    }
                    else
                        $strReturn .= "<error>".$this->getText("xmlupload_error_copyUpload")."</error>";
                }
                else {
                    @unlink($arrSource["tmp_name"]);
                    $strReturn .= "<error>".$this->getText("xmlupload_error_filter")."</error>";
                }
            }
            else
                $strReturn .= "<error>".xmlSafeString($this->getText("xmlupload_error_notWritable"))."</error>";


		}
		else
		    $strReturn .= "<error>".xmlSafeString($this->getText("xml_error_permissions"))."</error>";

        return $strReturn;
	}


}
?>