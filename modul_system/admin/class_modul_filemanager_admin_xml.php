<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$						*
********************************************************************************************************/


include_once(_adminpath_."/class_admin.php");
include_once(_adminpath_."/interface_xml_admin.php");

include_once(_systempath_."/class_modul_filemanager_repo.php");
include_once(_systempath_."/class_image.php");


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
        elseif($strAction == "saveCropping")
            $strReturn .= $this->actionSaveCropping();
        elseif($strAction == "rotate")
            $strReturn .= $this->actionRotateImage();           

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
            $objImage->preLoadImage($strFile);
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
            $objImage->preLoadImage($strFile);
            //var_dump($strFile, $this->getParam("intX"), $this->getParam("intY"), $this->getParam("intWidth"), $this->getParam("intHeight"));
            //die();            
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
        
        
        return $strReturn;
    }


	/**
	 * Tries to save the passed file.
	 * Therefore, to following post-params should be given:
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
            $arrSourcesPre = $this->getParam($this->getParam("inputElement"));
            $arrSources = array();
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
	    
		}
		else
		    $strReturn .= "<error>".xmlSafeString($this->getText("xml_error_permissions"))."</error>";

        return $strReturn;
	}


}
?>