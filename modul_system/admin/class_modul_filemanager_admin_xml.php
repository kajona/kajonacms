<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_modul_filemanager_admin_xml.php  																*
* 	adminclass of the filemanager, xml stuff															*
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_filemanager_admin_xml.php 1884 2007-12-26 15:04:48Z sidler $						*
********************************************************************************************************/


include_once(_adminpath_."/class_admin.php");
include_once(_adminpath_."/interface_xml_admin.php");

include_once(_systempath_."/class_modul_filemanager_repo.php");


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
		$arrModule["name"] 				= "modul_filemanger";
		$arrModule["author"] 			= "sidler@mulchprod.de";
		$arrModule["moduleId"] 			= _dashboard_modul_id_;
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

        return $strReturn;
	}


	/**
	 * Tries to save the passed file.
	 * Therefore, to following post-params should be given:
	 * action = fileUpload
	 * folder = the folder to store the file within
	 * systemid = the filemanagers' repo-id
	 * filemanager_upload = array of file details
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
            $arrSourcesPre = $this->getParam("filemanager_upload");
            foreach ($arrSourcesPre["name"] as $intKey => $strName) {
                if($strName != "") {
                    $arrSources[$intKey] = array();
                    $arrSources[$intKey]["name"] = $arrSourcesPre["name"][$intKey];
                    $arrSources[$intKey]["tmp_name"] = $arrSourcesPre["tmp_name"][$intKey];
                }
            }

            foreach ($arrSources as $arrSource) {
	            $strTarget = $strFolder."/".createFilename(strtolower($arrSource["name"]));
	            include_once(_systempath_."/class_filesystem.php");
	            $objFilesystem = new class_filesystem();
	            if($objFilesystem->isWritable($strFolder)) {
	            	
	                //Check file for correct filters
	                $arrAllowed = explode(",", $objRepo->getStrUploadFilter());
	                $strSuffix = strtolower(uniSubstr($arrSource["name"], uniStrrpos($arrSource["name"], ".")));
	                if($objRepo->getStrUploadFilter() == "" || in_array($strSuffix, $arrAllowed)) {
	                    if($objFilesystem->copyUpload($strTarget, $arrSource["tmp_name"])) {
	                        $strReturn .= "<message>".$this->getText("xmlupload_success")."</message>";
	                        $bitSuccess = true;
	                    }
	                    else
	                        $strReturn .= $this->getText("xmlupload_error_copyUpload");
	                }
	                else {
	                    @unlink($arrSource["tmp_name"]);
	                    $strReturn .= $this->getText("xmlupload_error_filter");
	                }
	            }
	            else
		            $strReturn .= "<error>".xmlSafeString($this->getText("xmlupload_error_notWritable"))."</error>";
            }
	    
		}
		else
		    $strReturn .= "<error>".xmlSafeString($this->getText("xmlupload_error_permissions"))."</error>";

        return $strReturn;
	}


}
?>