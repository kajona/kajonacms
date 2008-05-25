<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*                                                                                                       *
*   class_modul_downloads_portal_xml.php                                                                *
*   portalclass of the downloads, xml stuff                                                             *
*-------------------------------------------------------------------------------------------------------*
*   $Id: class_modul_downloads_portal_xml.php 1895 2008-01-12 22:52:56Z sidler $                        *
********************************************************************************************************/

//Include der Mutter-Klasse
include_once(_portalpath_."/class_portal.php");
include_once(_portalpath_."/interface_xml_portal.php");
//model
include_once(_systempath_."/class_modul_downloads_file.php");

/**
 * Portal-class of the downloads-module
 * Serves xml-requests, e.g. saves a sent comment
 *
 * @package modul_downloads
 */
class class_modul_downloads_portal_xml extends class_portal implements interface_xml_portal {
    
    
    /**
     * Constructor
     *
     * @param mixed $arrElementData
     */
    public function __construct() {
        $arrModule["name"]              = "modul_downloads";
        $arrModule["author"]            = "sidler@mulchprod.de";
        $arrModule["moduleId"]          = _downloads_modul_id_;
        $arrModule["modul"]             = "downloads";

        parent::__construct($arrModule, array());
    }


    /**
     * Actionblock. Controls the further behaviour.
     *
     * @param string $strAction
     * @return string
     */
    public function action($strAction) {
        $strReturn = "";
        if($strAction == "saveRating")
            $strReturn .= $this->actionSaveRating();

        return $strReturn;
    }


    /**
     * Saves a rating to a passed downloads-file
     *
     * @return string the new rating for the passed file
     */
    private function actionSaveRating() {
    	$strReturn = "<rating>";
    	
    	$objDownloadFile = new class_modul_downloads_file($this->getSystemid());
    	if($objDownloadFile->getFilename() != "") {
    		$objDownloadFile->saveRating($this->getParam("rating"));
    		$strReturn .= $objDownloadFile->getRating();
    	}
    	
    	
    	$strReturn .= "</rating>";
    	return $strReturn;
    }
    
    
}
?>