<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                           *
********************************************************************************************************/

//Include der Mutter-Klasse
include_once(_portalpath_."/class_portal.php");
include_once(_portalpath_."/interface_xml_portal.php");
//model
include_once(_systempath_."/class_modul_rating_rate.php");

/**
 * Portal-class of the rating-module
 * Serves xml-requests, e.g. saves a sent rating
 *
 * @package modul_rating
 */
class class_modul_rating_portal_xml extends class_portal implements interface_xml_portal {
    
    
    /**
     * Constructor
     *
     * @param mixed $arrElementData
     */
    public function __construct() {
        $arrModule["name"]              = "modul_rating";
        $arrModule["author"]            = "sidler@mulchprod.de";
        $arrModule["moduleId"]          = _rating_modul_id_;
        $arrModule["modul"]             = "rating";

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
     * Saves a rating to a passed rating-file
     *
     * @return string the new rating for the passed file
     */
    private function actionSaveRating() {
    	$strReturn = "<rating>";
    	
    	//rating already existing?
    	$objRating = class_modul_rating_rate::getRating($this->getSystemid());
    	if($objRating == null) {
    		$objRating = new class_modul_rating_rate();
    		$objRating->setStrRatingSystemid($this->getSystemid());
    		$objRating->saveObjectToDb();
    	}
    	
    	$objRating->saveRating($this->getParam("rating"));
  		$strReturn .= $objRating->getFloatRating();
    	
    	$strReturn .= "</rating>";
    	return $strReturn;
    }
    
    
}
?>