<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_portalupload.php 4085 2011-08-25 14:54:03Z sidler $                              *
********************************************************************************************************/

/**
 * Portal Element to load the login-form, or a small "status" area, providing an logout link
 *
 * @package element_portalupload
 */
class class_element_portalupload_portal extends class_element_portal implements interface_portal_element {

    /**
     * Constructor
     *
     * @param class_module_pages_pageelement $objElementData
     */
    public function __construct($objElementData) {
        parent::__construct($objElementData);
        $this->setArrModuleEntry("table", _dbprefix_."element_universal");
    }


	public function loadData() {
		$strReturn = "";

		if($this->getParam("submitPortaluploadForm") == "1")
		    $strReturn .= $this->doUpload();
		else
		    $strReturn .= $this->uploadForm();


		return $strReturn;
	}



	private function uploadForm($formErrors = "") {
	    $strReturn = "";
	    //validate the rights
        $objFilemanagerRepo =  new class_module_mediamanager_repo($this->arrElementData["char2"]);


	    if($objFilemanagerRepo->rightRight1()) {


            $strTemplateID = $this->objTemplate->readTemplate("/element_portalupload/".$this->arrElementData["char1"], "portalupload_uploadform");

            $strDlFolderId = "";
            if($this->getParam("action") == "openDlFolder")
                $strDlFolderId = $this->getParam("systemid");

            $arrTemplate = array();
            $arrTemplate["portaluploadDlfolder"] = $strDlFolderId;

            // check if there was an successfull upload before
            if($this->getParam("uploadSuccess") == "1")
                $arrTemplate["portaluploadSuccess"] = $this->getLang("portaluploadSuccess");

            $arrTemplate["formErrors"] = $formErrors;

            $arrTemplate["formAction"] = getLinkPortalHref($this->getPagename(), "", $this->getAction(), "", $strDlFolderId);

            $strReturn .= $this->fillTemplate($arrTemplate, $strTemplateID);

	    }
	    else {
	        $strReturn .= $this->getLang("commons_error_permissions");
	    }

	    return $strReturn;
	}


	private function doUpload() {
	    $strReturn = "";

	    //prepare the folder to be used as a target-folder for the upload
        $objFilemanagerRepo = new class_module_mediamanager_repo($this->arrElementData["char2"]);

	    //add a special subfolder?
	    $strPath = $objFilemanagerRepo->getStrPath();
	    if($this->getParam("portaluploadDlfolder") != "") {
	        $objDownloadfolder = new class_module_mediamanager_file($this->getParam("portaluploadDlfolder"));
	        if($objDownloadfolder->getStrFilename() != "") {
	            $strPath = $objDownloadfolder->getStrFilename();
	        }

	    }

	    //upload the file...
	    if($objFilemanagerRepo->rightRight1()) {

	        //Handle the fileupload
            $arrSource = $this->getParam("portaluploadFile");

            $strTarget = $strPath."/".createFilename($arrSource["name"]);

            $objFilesystem = new class_filesystem();
            if($objFilesystem->isWritable($strPath)) {

                //Check file for correct filters
                $arrAllowed = explode(",", $objFilemanagerRepo->getStrUploadFilter());
                $strSuffix = uniStrtolower(uniSubstr($arrSource["name"], uniStrrpos($arrSource["name"], ".")));
                if($objFilemanagerRepo->getStrUploadFilter() == "" || in_array($strSuffix, $arrAllowed)) {
                    if($objFilesystem->copyUpload($strTarget, $arrSource["tmp_name"])) {

                        //upload was successfull. try to sync the downloads-archive.
                        if($objFilemanagerRepo->rightRight1()) {
                            $objFilemanagerRepo->syncRepo();

                            //reload the site to display the new file
							$this->portalReload(getLinkPortalHref($this->getPagename(), "", $this->getAction(), "uploadSuccess=1", $this->getSystemid()));
                        }
                    }
                    else
                        $strReturn .= $this->uploadForm($this->getLang("portaluploadCopyUploadError"));
                }
                else {
                    @unlink($arrSource["tmp_name"]);

            		$strReturn .= $this->uploadForm($this->getLang("portaluploadFilterError"));
                }
            }
            else
                $strReturn .= $this->uploadForm($this->getLang("portaluploadNotWritableError"));

		}
		else
		    $strReturn .= $this->getLang("commons_error_permissions");

	    return $strReturn;
	}



}
