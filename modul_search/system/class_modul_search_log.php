<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

include_once(_systempath_."/class_model.php");
include_once(_systempath_."/interface_model.php");

/**
 * This class contains a few methods used by the search as little helpers
 *
 * @package modul_search
 */
class class_modul_search_log extends class_model implements interface_model  {

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_search";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _suche_modul_id_;
		$arrModul["table"]       		= _dbprefix_."search_log";
		$arrModul["modul"]				= "search";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }

    /**
     * Initalises the current object, if a systemid was given
     * NOT YET IMPLEMENTED
     *
     */
    public function initObject() {

    }

    /**
     * Updates the current object to the database
     * NOT YET IMPLEMENTED
     *
     */
    public function updateObjectToDb() {

    }

    /**
     * Generates a new entry in the log-table
     *
     * @param string $strSeachterm
     * @return bool
     * @static 
     */
    public static function generateLogEntry($strSeachterm) {
    	
    	include_once(_systempath_."/class_modul_system_common.php");
        $objCommon = new class_modul_system_common();
        $strLanguage = $objCommon->getStrPortalLanguage();
    	
        $strQuery = "INSERT INTO "._dbprefix_."search_log 
                    (search_log_id, search_log_date, search_log_query, search_log_language) VALUES
                    ('".dbsafeString(generateSystemid())."', ".(int)time().", '".dbsafeString($strSeachterm)."', '".dbsafeString($strLanguage)."'  )";
        
        return class_carrier::getInstance()->getObjDB()->_query($strQuery);
    }
    
    /**
     * Loads a list of logbook-entries
     *
     * @return unknown
     */
    public function getLogBookEntries() {
        return $this->objDB->getArray("SELECT search_log_date, search_log_query 
                                         FROM ".$this->arrModule["table"]."
                                     GROUP BY search_log_date 
                                     ORDER BY search_log_date DESC");
    }

}
?>