<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$						*
********************************************************************************************************/

/**
 * admin-class of the filemananger-module
 * Serves xml-requests, currently to handle uploads
 *
 * @package module_filemanager
 * @author sidler@mulchpro.de
 */
class class_module_filemanager_admin_xml extends class_admin implements interface_xml_admin {


    /**
     * Constructor
     *
     */
	public function __construct() {

        $this->setArrModuleEntry("modul", "filemanager");
        $this->setArrModuleEntry("moduleId", _filemanager_modul_id_);
		parent::__construct();
	}


    /**
     * Deletes the given file from the filesystem
     * @return string
     */
    protected function actionDeleteFile() {
        $strReturn = "";
        $objFmRepo = new class_module_filemanager_repo($this->getSystemid());
        if($objFmRepo->rightDelete()) {
            //create repo-instance
            $strFolder = $this->getParam("folder");
            $strFile = $this->getParam("file");

            //Delete from filesystem
            $objFilesystem = new class_filesystem();
            class_logger::getInstance()->addLogRow("deleted file ".$objFmRepo->getStrPath()."/".$strFolder."/".$strFile, class_logger::$levelInfo);
            if($objFilesystem->fileDelete($objFmRepo->getStrPath()."/".$strFolder."/".$strFile))
                $strReturn .= "<message>".xmlSafeString($this->getLang("datei_loeschen_erfolg"))."</message>";
			else
                $strReturn .= "<error>".xmlSafeString($this->getLang("datei_loeschen_fehler"))."</error>";
        }
        else {
            header(class_http_statuscodes::$strSC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
        }


        return $strReturn;
    }


    /**
     * Create a new folder using the combi of folder & systemid passed
     * @return string
     */
    protected function actionRenameFile() {
        $strReturn = "";
        $objFmRepo = new class_module_filemanager_repo($this->getSystemid());
        if($objFmRepo->rightRight1()) {

            //create repo-instance
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
                        $strReturn = "<error>".xmlSafeString($this->getLang("datei_umbenennen_fehler"))."</error>";
                }
                else
                    $strReturn = "<error>".xmlSafeString($this->getLang("datei_umbenennen_fehler_z"))."</error>";
            }
            else
                $strReturn = "<error>an error occured</error>";
        }
        else {
            header(class_http_statuscodes::$strSC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
        }

        return $strReturn;
    }

    /**
     * Create a new folder using the combi of folder & systemid passed
     * @return string
     */
    protected function actionCreateFolder() {
        $strReturn = "";
        $objFmRepo = new class_module_filemanager_repo($this->getSystemid());
        if($objFmRepo->rightRight1()) {

            //create repo-instance
            $strFolder = $this->getParam("folder");

            //Create the folder
            $intLastSlashPos = strrpos($strFolder, "/");
            $strFolder = substr($strFolder, 0, $intLastSlashPos)."/". createFilename(substr($strFolder, $intLastSlashPos+1), true);
            //folder already existing?
            if(!is_dir(_realpath_."/".$objFmRepo->getStrPath()."/".$strFolder)) {
                class_logger::getInstance()->addLogRow("creating folder ".$objFmRepo->getStrPath()."/".$strFolder, class_logger::$levelInfo);
                $objFilesystem = new class_filesystem();
                if($objFilesystem->folderCreate($objFmRepo->getStrPath()."/".$strFolder)) {
                    $strReturn = "<message>".xmlSafeString($this->getLang("ordner_anlegen_erfolg"))."</message>";
                }
                else {
                    header(class_http_statuscodes::$strSC_INTERNAL_SERVER_ERROR);
                    $strReturn = "<message><error>".xmlSafeString($this->getLang("order_anlegen_fehler"))."</error></message>";
                }
            }
            else {
                header(class_http_statuscodes::$strSC_INTERNAL_SERVER_ERROR);
                $strReturn = "<message><error>".xmlSafeString($this->getLang("ordner_anlegen_fehler_l"))."</error></message>";
            }
        }
        else {
            header(class_http_statuscodes::$strSC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
        }

        return $strReturn;
    }


    /**
     * Deletes the given file from the filesystem
     * @return string
     */
    protected function actionDeleteFolder() {
        $strReturn = "";
        $objFmRepo = new class_module_filemanager_repo($this->getSystemid());
        if($objFmRepo->rightDelete()) {
            //create repo-instance
            $strFolder = $this->getParam("folder");

            //Delete from filesystem
            $objFilesystem = new class_filesystem();

            //check if folder is empty
            $arrFilesSub = $objFilesystem->getCompleteList($objFmRepo->getStrPath()."/".$strFolder, array(), array(), array(".", ".."));
            if(count($arrFilesSub["files"]) == 0 && count($arrFilesSub["folders"]) == 0) {
                class_logger::getInstance()->addLogRow("deleted folder ".$objFmRepo->getStrPath()."/".$strFolder, class_logger::$levelInfo);
                if($objFilesystem->folderDelete($objFmRepo->getStrPath()."/".$strFolder))
                    $strReturn .= "<message>".xmlSafeString($this->getLang("datei_loeschen_erfolg"))."</message>";
                else {
                    header(class_http_statuscodes::$strSC_INTERNAL_SERVER_ERROR);
                    $strReturn .= "<message><error>".xmlSafeString($this->getLang("datei_loeschen_fehler"))."</error></message>";
                }
            }
            else {
                header(class_http_statuscodes::$strSC_INTERNAL_SERVER_ERROR);
                $strReturn .= "<message><error>".xmlSafeString($this->getLang("ordner_loeschen_fehler_l"))."</error></message>";
            }

        }
        else {
            header(class_http_statuscodes::$strSC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
        }

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
     * @return string
     * @permissions edit
     */
    protected function actionRotate(){
    	$strReturn = "";

        $strFile = $this->getParam("file");

        //pass to the image-class
        $objImage = new class_image();
        if($objImage->preLoadImage($strFile)) {
            if($objImage->rotateImage($this->getParam("angle"))) {
                if($objImage->saveImage($strFile, false, 100)) {
                    class_logger::getInstance()->addLogRow("rotated file ".$strFile, class_logger::$levelInfo);
                    $strReturn .= "<message>".xmlSafeString($this->getLang("xml_rotate_success"))."</message>";
                }
                else
                    class_logger::getInstance()->addLogRow("error rotating file ".$strFile, class_logger::$levelWarning);
            }
        }
        else {
            header(class_http_statuscodes::$strSC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
        }

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
     * @return string
     * @permissions edit
     */
    protected function actionSaveCropping() {
    	$strReturn = "";

        $strFile = $this->getParam("file");

        //pass to the image-class
        $objImage = new class_image();
        if($objImage->preLoadImage($strFile)) {
            if($objImage->cropImage($this->getParam("intX"), $this->getParam("intY"), $this->getParam("intWidth"), $this->getParam("intHeight"))) {
                if($objImage->saveImage($strFile, false, 100)) {
                    class_logger::getInstance()->addLogRow("cropped file ".$strFile, class_logger::$levelInfo);
                    $strReturn .= "<message>".xmlSafeString($this->getLang("xml_cropping_success"))."</message>";
                }
                else
                    class_logger::getInstance()->addLogRow("error cropping file ".$strFile, class_logger::$levelWarning);
            }
        }
        else {
            header(class_http_statuscodes::$strSC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
        }

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
	protected function actionFileupload() {
	    $strReturn = "";

        $objRepo = new class_module_filemanager_repo($this->getSystemid());
	    if($objRepo->rightRight1()) {
	    	//create repo instance
	        $strFolder = $objRepo->getStrPath().$this->getParam("folder");

	        //Handle the fileupload
            $arrSource = $this->getParam($this->getParam("inputElement"));

            $strTarget = $strFolder."/".createFilename($arrSource["name"]);
            $objFilesystem = new class_filesystem();

            if(!file_exists(_realpath_."/".$strFolder))
                $objFilesystem->folderCreate($strFolder, true);

            if($objFilesystem->isWritable($strFolder)) {

                //Check file for correct filters
                $arrAllowed = explode(",", $objRepo->getStrUploadFilter());
                $strSuffix = uniStrtolower(uniSubstr($arrSource["name"], uniStrrpos($arrSource["name"], ".")));
                if($objRepo->getStrUploadFilter() == "" || in_array($strSuffix, $arrAllowed)) {
                    if($objFilesystem->copyUpload($strTarget, $arrSource["tmp_name"])) {
                        $strReturn .= "<message>".$this->getLang("xmlupload_success")."</message>";
                        class_logger::getInstance()->addLogRow("uploaded file ".$strTarget, class_logger::$levelInfo);
                    }
                    else
                        $strReturn .= "<message><error>".$this->getLang("xmlupload_error_copyUpload")."</error></message>";
                }
                else {
                    @unlink($arrSource["tmp_name"]);
                    header(class_http_statuscodes::$strSC_BADREQUEST);
                    $strReturn .= "<message><error>".$this->getLang("xmlupload_error_filter")."</error></message>";
                }
            }
            else {
                header(class_http_statuscodes::$strSC_INTERNAL_SERVER_ERROR);
                $strReturn .= "<message><error>".xmlSafeString($this->getLang("xmlupload_error_notWritable"))."</error></message>";
            }


		}
		else {
            header(class_http_statuscodes::$strSC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
        }

        return $strReturn;
	}


}
