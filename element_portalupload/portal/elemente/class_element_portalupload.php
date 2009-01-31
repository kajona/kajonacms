<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/

//base-class
require_once(_portalpath_."/class_elemente_portal.php");
//Interface
require_once(_portalpath_."/interface_portal_element.php");
require_once(_systempath_."/class_modul_filemanager_repo.php");
require_once(_systempath_."/class_modul_downloads_archive.php");

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
	    $objFilemanagerRepo = new class_modul_filemanager_repo($this->arrElementData["char3"]);
	    $objDownloadsRepo = new class_modul_downloads_archive($this->arrElementData["char2"]);
	    
	    if($objFilemanagerRepo->rightRight1()) {

    	    if($objFilemanagerRepo->getStrPath() == $objDownloadsRepo->getPath()) {
    	    
                $strTemplateID = $this->objTemplate->readTemplate("/element_portalupload/".$this->arrElementData["char1"], "portalupload_uploadform");
       	    
                $strDlFolderId = "";
                if($this->getParam("action") == "openDlFolder")
                    $strDlFolderId = $this->getParam("systemid");
               
        		$arrTemplate = array();
                $arrTemplate["portaluploadFileTitle"] = $this->getText("portaluploadFileTitle");
                $arrTemplate["submitTitle"] = $this->getText("portaluploadSubmitTitle");
                $arrTemplate["portaluploadDlfolder"] = $strDlFolderId;
                
        		// check if there was an successfull upload before
        		if($this->getParam("uploadSuccess") == "1")
        			$arrTemplate["portaluploadSuccess"] = $this->getText("portaluploadSuccess");

        	    $arrTemplate["formErrors"] = $formErrors;
        
        	    $arrTemplate["formAction"] = getLinkPortalRaw($this->getPagename(), "", $this->getAction(), "", $strDlFolderId);
        
        		$strReturn .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
    	    }
    	    else {
    	        $strReturn .= $this->getText("portaluploadPathError");
    	    }
	    }
	    else {
	        $strReturn .= $this->getText("portaluploadPermissionsError");
	    }
	    
	    return $strReturn;
	}
	
	
	private function doUpload() {
	    $strReturn = "";
	    
	    //prepare the folder to be used as a target-folder for the upload
	    $objFilemanagerRepo = new class_modul_filemanager_repo($this->arrElementData["char3"]);
	    $objDownloadsRepo = new class_modul_downloads_archive($this->arrElementData["char2"]);
	    
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

            $strTarget = $strPath."/".createFilename(strtolower($arrSource["name"]));

            include_once(_systempath_."/class_filesystem.php");
            $objFilesystem = new class_filesystem();
            if($objFilesystem->isWritable($strPath)) {
            	
                //Check file for correct filters
                $arrAllowed = explode(",", $objFilemanagerRepo->getStrUploadFilter());
                $strSuffix = strtolower(uniSubstr($arrSource["name"], uniStrrpos($arrSource["name"], ".")));
                if($objFilemanagerRepo->getStrUploadFilter() == "" || in_array($strSuffix, $arrAllowed)) {
                    if($objFilesystem->copyUpload($strTarget, $arrSource["tmp_name"])) {
                        
                        //upload was successfull. try to sync the downloads-archive.
                        if($objDownloadsRepo->rightRight1()) {
                            $arrSyncs = class_modul_downloads_file::syncRecursive($objDownloadsRepo->getSystemid(), $objDownloadsRepo->getPath());
                            
                            //reload the site to display the new file
							header("Location: ".str_replace("&amp;", "&", getLinkPortalRaw($this->getPagename(), "", $this->getAction(), "uploadSuccess=1", $this->getSystemid())));
                        }
                        
                        $bitSuccess = true;
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
		    $strReturn .= $this->getText("portaluploadPermissionsError");

	    return $strReturn;
	}

	

}
?>