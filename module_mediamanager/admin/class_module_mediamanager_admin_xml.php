<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_carrier.php 4059 2011-08-09 14:52:41Z sidler $                                            *
********************************************************************************************************/


/**
 * admin-class of the mediamanager-module
 * Serves xml-requests, e.g. syncing a gallery
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 */
class class_module_mediamanager_admin_xml extends class_admin implements interface_xml_admin {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$this->setArrModuleEntry("moduleId", _mediamanager_module_id_);
		$this->setArrModuleEntry("modul", "mediamanager");
		parent::__construct();
	}



//    /**
//     * Deletes the given file from the filesystem
//     * @return string
//     */
//    protected function actionDeleteFile() {
//        $strReturn = "";
//
//        $objFile = new class_module_mediamanager_file($this->getSystemid());
//
//        if($objFile->rightDelete()) {
//
//            //Delete from filesystem
//            $objFilesystem = new class_filesystem();
//            class_logger::getInstance()->addLogRow("deleted file ".$objFile->getStrFilename(), class_logger::$levelInfo);
//            if($objFilesystem->fileDelete($objFile->getStrFilename()))
//                $strReturn .= "<message>".xmlSafeString($this->getLang("file_delete_success"))."</message>";
//            else
//                $strReturn .= "<error>".xmlSafeString($this->getLang("file_delete_error"))."</error>";
//        }
//        else {
//            header(class_http_statuscodes::$strSC_UNAUTHORIZED);
//            $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
//        }
//
//        return $strReturn;
//    }


    /**
     * Create a new folder using the combi of folder & systemid passed
     * @return string
     */
    protected function actionCreateFolder() {
        $strReturn = "";

        $objInstance = class_objectfactory::getInstance()->getObject($this->getSystemid());

        if($objInstance->rightEdit()) {

            $strPrevPath = "";
            if($objInstance instanceof class_module_mediamanager_file && $objInstance->getIntType() == class_module_mediamanager_file::$INT_TYPE_FOLDER)
                $strPrevPath = $objInstance->getStrFilename();

            else if($objInstance instanceof class_module_mediamanager_repo)
                $strPrevPath = $objInstance->getStrPath();

            else
                return "";

            //create repo-instance
            $strFolder = $this->getParam("folder");

            //Create the folder
            $strFolder = createFilename($strFolder, true);
            //folder already existing?
            if(!is_dir(_realpath_."/".$strPrevPath."/".$strFolder)) {

                class_logger::getInstance()->addLogRow("creating folder ".$strPrevPath."/".$strFolder, class_logger::$levelInfo);

                $objFilesystem = new class_filesystem();
                if($objFilesystem->folderCreate($strPrevPath."/".$strFolder)) {
                    $strReturn = "<message>".xmlSafeString($this->getLang("folder_create_success"))."</message>";
                }
                else {
                    header(class_http_statuscodes::$strSC_INTERNAL_SERVER_ERROR);
                    $strReturn = "<message><error>".xmlSafeString($this->getLang("folder_create_error"))."</error></message>";
                }
            }
            else {
                header(class_http_statuscodes::$strSC_INTERNAL_SERVER_ERROR);
                $strReturn = "<message><error>".xmlSafeString($this->getLang("folder_create_error"))."</error></message>";
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

        $objFile = class_objectfactory::getInstance()->getObject($this->getSystemid());
        $strFolder = "";

        $objRepo = null;

        if($objFile instanceof class_module_mediamanager_file) {
            $strFolder = $objFile->getStrFilename();
            if(!$objFile->rightEdit() || $objFile->getIntType() != class_module_mediamanager_file::$INT_TYPE_FOLDER) {
                header(class_http_statuscodes::$strSC_UNAUTHORIZED);
                $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
                return $strReturn;
            }

            $objRepo = class_objectfactory::getInstance()->getObject($objFile->getPrevId());
            while(!$objRepo instanceof class_module_mediamanager_repo)
                $objRepo = class_objectfactory::getInstance()->getObject($objRepo->getPrevId());
        }
        elseif($objFile instanceof class_module_filemanager_repo) {
            $objRepo = $objFile;
            $strFolder = $objFile->getStrPath();
            if(!$objFile->rightEdit()) {
                header(class_http_statuscodes::$strSC_UNAUTHORIZED);
                $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
                return $strReturn;
            }

        }
        else {
            header(class_http_statuscodes::$strSC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
            return $strReturn;
        }

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

                    $objRepo->syncRepo();
                }
                else
                    $strReturn .= "<message><error>".$this->getLang("xmlupload_error_copyUpload")."</error></message>";
            }
            else {
                header(class_http_statuscodes::$strSC_BADREQUEST);
                $strReturn .= "<message><error>".$this->getLang("xmlupload_error_filter")."</error></message>";
            }
        }
        else {
            header(class_http_statuscodes::$strSC_INTERNAL_SERVER_ERROR);
            $strReturn .= "<message><error>".xmlSafeString($this->getLang("xmlupload_error_notWritable"))."</error></message>";
        }

        @unlink($arrSource["tmp_name"]);
        return $strReturn;
    }




//	/**
//	 * Syncs the gallery and creates a small report
//	 *
//	 * @return string
//     * @permissions edit
//	 */
//	protected function actionSyncRepo() {
//		$strReturn = "";
//		$strResult = "";
//
//		$objRepo = new class_module_mediamanager_repo($this->getSystemid());
//        $arrSyncs = $objRepo->syncRepo();
//        $strResult .= $this->getLang("sync_end")."<br />";
//        $strResult .= $this->getLang("sync_add").$arrSyncs["insert"]."<br />".$this->getLang("sync_del").$arrSyncs["delete"];
//
//        $strReturn .= "<repo>".xmlSafeString(strip_tags($strResult))."</repo>";
//
//        class_logger::getInstance()->addLogRow("synced repo ".$this->getSystemid().": ".$strResult, class_logger::$levelInfo);
//
//		return $strReturn;
//	}

//    /**
//     * Syncs the gallery and creates a small report
//     *
//     * @return string
//     */
//    protected function actionMassSyncRepos() {
//        $strReturn = "";
//        $strResult = "";
//
//        $arrRepos = class_module_mediamanager_repo::getAllRepos();
//        $arrSyncs = array( "insert" => 0, "delete" => 0);
//        foreach($arrRepos as $objOneRepo) {
//            if($objOneRepo->rightEdit()) {
//                $arrTemp = $objOneRepo->syncRepo();
//                $arrSyncs["insert"] += $arrTemp["insert"];
//                $arrSyncs["delete"] += $arrTemp["delete"];
//            }
//        }
//        $strResult .= $this->getLang("sync_end")."<br />";
//        $strResult .= $this->getLang("sync_add").$arrSyncs["insert"]."<br />".$this->getLang("sync_del").$arrSyncs["delete"];
//
//        $strReturn .= "<repo>".xmlSafeString(strip_tags($strResult))."</repo>";
//
//        class_logger::getInstance()->addLogRow("mass synced repos: ".$strResult, class_logger::$levelInfo);
//        return $strReturn;
//    }

    /**
     * Syncs the repo partially
     *
     * @return string
     */
    protected function actionPartialSyncRepo() {
        $strReturn = "";
		$strResult = "";

        $objInstance = class_objectfactory::getInstance()->getObject($this->getSystemid());

		if($objInstance instanceof class_module_mediamanager_file)
            $arrSyncs = class_module_mediamanager_file::syncRecursive($objInstance->getSystemid(), $objInstance->getStrFilename());

        else if($objInstance instanceof class_module_mediamanager_repo)
            $arrSyncs = $objInstance->syncRepo();

        else
            return "";


        $strResult .= $this->getLang("sync_end")."<br />";
        $strResult .= $this->getLang("sync_add").$arrSyncs["insert"]."<br />".$this->getLang("sync_del").$arrSyncs["delete"];

        $strReturn .= "<repo>".xmlSafeString(strip_tags($strResult))."</repo>";

        class_logger::getInstance()->addLogRow("synced gallery partially >".$this->getSystemid().": ".$strResult, class_logger::$levelInfo);

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



}

