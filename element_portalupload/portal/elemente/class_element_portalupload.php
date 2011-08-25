<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/

/**
 * Portal Element to load the login-form, or a small "status" area, providing an logout link
 *
 * @package modul_pages
 */
class class_element_portalupload extends class_element_portal implements interface_portal_element {

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($objElementData) {
        $arrModule = array();
		$arrModule["name"] 			= "element_portalupload";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]		    = _dbprefix_."element_universal";
		$arrModule["modul"]		    = "elemente";

		parent::__construct($arrModule, $objElementData);
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
	    $objDownloadsRepo =  new class_modul_downloads_archive($this->arrElementData["char2"]);
	    $objFilemanagerRepo = class_modul_filemanager_repo::getRepoForForeignId($objDownloadsRepo->getSystemid());
        

	    if($objFilemanagerRepo->rightRight1()) {

    	    if($objFilemanagerRepo->getStrPath() == $objDownloadsRepo->getPath()) {

                $strTemplateID = $this->objTemplate->readTemplate("/element_portalupload/".$this->arrElementData["char1"], "portalupload_uploadform");

                $strDlFolderId = "";
                if($this->getParam("action") == "openDlFolder")
                    $strDlFolderId = $this->getParam("systemid");

        		$arrTemplate = array();
                $arrTemplate["portaluploadDlfolder"] = $strDlFolderId;

        		// check if there was an successfull upload before
        		if($this->getParam("uploadSuccess") == "1")
        			$arrTemplate["portaluploadSuccess"] = $this->getText("portaluploadSuccess");

        	    $arrTemplate["formErrors"] = $formErrors;

        	    $arrTemplate["formAction"] = getLinkPortalHref($this->getPagename(), "", $this->getAction(), "", $strDlFolderId);

        		$strReturn .= $this->fillTemplate($arrTemplate, $strTemplateID);
    	    }
    	    else {
    	        $strReturn .= $this->getText("portaluploadPathError");
    	    }
	    }
	    else {
	        $strReturn .= $this->getText("commons_error_permissions");
	    }

	    return $strReturn;
	}


	private function doUpload() {
	    $strReturn = "";

	    //prepare the folder to be used as a target-folder for the upload
	    $objDownloadsRepo = new class_modul_downloads_archive($this->arrElementData["char2"]);
	    $objFilemanagerRepo = class_modul_filemanager_repo::getRepoForForeignId($objDownloadsRepo->getSystemid());

	    //add a special subfolder?
	    $strPath = $objDownloadsRepo->getPath();
	    if($this->getParam("portaluploadDlfolder") != "") {
	        $objDownloadfolder = new class_modul_downloads_file($this->getParam("portaluploadDlfolder"));
	        if($objDownloadfolder->getFilename() != "") {
	            $strPath = $objDownloadfolder->getFilename();
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
                        if($objDownloadsRepo->rightRight1()) {
                            class_modul_downloads_file::syncRecursive($objDownloadsRepo->getSystemid(), $objDownloadsRepo->getPath());
                            
                            //reload the site to display the new file
							$this->portalReload(getLinkPortalHref($this->getPagename(), "", $this->getAction(), "uploadSuccess=1", $this->getSystemid()));
                        }
                    }
                    else
                        $strReturn .= $this->uploadForm($this->getText("portaluploadCopyUploadError"));
                }
                else {
                    @unlink($arrSource["tmp_name"]);

            		$strReturn .= $this->uploadForm($this->getText("portaluploadFilterError"));
                }
            }
            else
                $strReturn .= $this->uploadForm($this->getText("portaluploadNotWritableError"));

		}
		else
		    $strReturn .= $this->getText("commons_error_permissions");

	    return $strReturn;
	}



}
?>