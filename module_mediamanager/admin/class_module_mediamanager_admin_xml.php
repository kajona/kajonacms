<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/


/**
 * admin-class of the mediamanager-module
 * Serves xml-requests, e.g. syncing a gallery
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 *
 * @module mediamanager
 * @moduleId _mediamanager_module_id_
 */
class class_module_mediamanager_admin_xml extends class_admin_controller implements interface_xml_admin {

    /**
     * Create a new folder using the combination of passed folder & systemid
     * @return string
     * @permissions edit
     */
    protected function actionCreateFolder() {
        $strReturn = "";

        /** @var class_module_mediamanager_repo|class_module_mediamanager_file $objInstance */
        $objInstance = class_objectfactory::getInstance()->getObject($this->getSystemid());

        if($objInstance->rightEdit()) {

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
                    class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_INTERNAL_SERVER_ERROR);
                    $strReturn = "<message><error>".xmlSafeString($this->getLang("folder_create_error"))."</error></message>";
                }
            }
            else {
                class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_INTERNAL_SERVER_ERROR);
                $strReturn = "<message><error>".xmlSafeString($this->getLang("folder_create_error"))."</error></message>";
            }
        }
        else {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_UNAUTHORIZED);
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
     * @permissions right1
     */
    protected function actionFileupload() {
        $strReturn = "";

        /** @var class_module_mediamanager_repo|class_module_mediamanager_file $objFile */
        $objFile = class_objectfactory::getInstance()->getObject($this->getSystemid());

        /**
         * @var class_module_mediamanager_repo
         */
        $objRepo = null;

        if($objFile instanceof class_module_mediamanager_file) {
            $strFolder = $objFile->getStrFilename();
            if(!$objFile->rightEdit() || $objFile->getIntType() != class_module_mediamanager_file::$INT_TYPE_FOLDER) {
                class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_UNAUTHORIZED);
                $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
                return $strReturn;
            }

            $objRepo = class_objectfactory::getInstance()->getObject($objFile->getPrevId());
            while(!$objRepo instanceof class_module_mediamanager_repo)
                $objRepo = class_objectfactory::getInstance()->getObject($objRepo->getPrevId());
        }
        elseif($objFile instanceof class_module_mediamanager_repo) {
            $objRepo = $objFile;
            $strFolder = $objFile->getStrPath();
            if(!$objFile->rightEdit()) {
                class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_UNAUTHORIZED);
                $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
                return $strReturn;
            }

        }
        else {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
            return $strReturn;
        }

        //Handle the fileupload
        $arrSource = $this->getParam($this->getParam("inputElement"));


        $bitJsonResponse = $this->getParam("jsonResponse") != "";

        $bitPostData = false;
        if(is_array($arrSource)) {
            $strFilename = $arrSource["name"];
        }
        else {
            $bitPostData = getPostRawData() != "";
            $strFilename = $arrSource;
        }

        $strTarget = $strFolder."/".createFilename($strFilename);
        $objFilesystem = new class_filesystem();

        if(!file_exists(_realpath_."/".$strFolder))
            $objFilesystem->folderCreate($strFolder, true);

        if($objFilesystem->isWritable($strFolder)) {

            //Check file for correct filters
            $arrAllowed = explode(",", $objRepo->getStrUploadFilter());

            $strSuffix = uniStrtolower(uniSubstr($strFilename, uniStrrpos($strFilename, ".")));
            if($objRepo->getStrUploadFilter() == "" || in_array($strSuffix, $arrAllowed)) {

                if($bitPostData) {
                    $objFilesystem = new class_filesystem();
                    $objFilesystem->openFilePointer($strTarget);
                    $bitCopySuccess = $objFilesystem->writeToFile(getPostRawData());
                    $objFilesystem->closeFilePointer();
                }
                else {
                    $bitCopySuccess = $objFilesystem->copyUpload($strTarget, $arrSource["tmp_name"]);
                }
                if($bitCopySuccess) {
                    if($bitJsonResponse)
                        $strReturn = json_encode(array('success' => true));
                    else
                        $strReturn .= "<message>".$this->getLang("xmlupload_success")."</message>";

                    class_logger::getInstance()->addLogRow("uploaded file ".$strTarget, class_logger::$levelInfo);

                    $objRepo->syncRepo();
                }
                else {
                    if($bitJsonResponse)
                        $strReturn .= json_encode(array('error' => $this->getLang("xmlupload_error_copyUpload")));
                    else
                        $strReturn .= "<message><error>".$this->getLang("xmlupload_error_copyUpload")."</error></message>";
                }
            }
            else {
                class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_BADREQUEST);

                if($bitJsonResponse)
                    $strReturn .= json_encode(array('error' => $this->getLang("xmlupload_error_filter")));
                else
                    $strReturn .= "<message><error>".$this->getLang("xmlupload_error_filter")."</error></message>";
            }
        }
        else {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_INTERNAL_SERVER_ERROR);

            if($bitJsonResponse)
                $strReturn .= json_encode(array('error' => $this->getLang("xmlupload_error_notWritable")));
            else
                $strReturn .= "<message><error>".xmlSafeString($this->getLang("xmlupload_error_notWritable"))."</error></message>";
        }


        if($bitJsonResponse) {
            //disabled for ie. otherwise the upload won't work due to the headers.
            class_response_object::getInstance()->setStrResponseType(class_http_responsetypes::STR_TYPE_HTML);
            //class_response_object::getInstance()->setStResponseType(class_http_responsetypes::STR_TYPE_JSON);
        }
        @unlink($arrSource["tmp_name"]);
        return $strReturn;
    }

    /**
     * Syncs the repo partially
     *
     * @return string
     * @permissions edit
     */
    protected function actionPartialSyncRepo() {
        $strReturn = "";
		$strResult = "";

        /** @var class_module_mediamanager_repo|class_module_mediamanager_file $objInstance */
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
     * Syncs the repo partially
     *
     * @return string
     * @permissions edit
     */
    protected function actionSyncRepo() {
        $strReturn = "";
        $strResult = "";

        /** @var class_module_mediamanager_repo|class_module_mediamanager_file $objInstance */
        $objInstance = class_objectfactory::getInstance()->getObject($this->getSystemid());
        //close the session to avoid a blocking behaviour
        $this->objSession->sessionClose();
        if($objInstance instanceof class_module_mediamanager_repo)
            $arrSyncs = $objInstance->syncRepo();

        else
            return "<error>mediamanager repo could not be loaded</error>";

        $strResult = 0;

        $strResult += $arrSyncs["insert"]+$arrSyncs["delete"];
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

        $objImage = new class_image2();
        $objImage->setUseCache(false);
        $objImage->load($strFile);
        $objImage->addOperation(new class_image_rotate($this->getParam("angle")));
        if ($objImage->save($strFile)) {
            class_logger::getInstance()->addLogRow("rotated file ".$strFile, class_logger::$levelInfo);
            $strReturn .= "<message>".xmlSafeString($this->getLang("xml_rotate_success"))."</message>";
        }
        else {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_UNAUTHORIZED);
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

        $objImage = new class_image2();
        $objImage->setUseCache(false);
        $objImage->load($strFile);
        $objImage->addOperation(new class_image_crop($this->getParam("intX"), $this->getParam("intY"), $this->getParam("intWidth"), $this->getParam("intHeight")));
        if ($objImage->save($strFile)) {
            class_logger::getInstance()->addLogRow("cropped file ".$strFile, class_logger::$levelInfo);
            $strReturn .= "<message>".xmlSafeString($this->getLang("xml_cropping_success"))."</message>";
        }
        else {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
        }

        return $strReturn;
    }



}

