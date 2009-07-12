<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_navigation_tree.php 2353 2008-12-31 15:22:01Z sidler $                              *
********************************************************************************************************/


/**
 * The navigation cache saves the generated portal-tree back to the database.
 * Since the navigation is generated using a recursion, caching can save much time.
 *
 * @package modul_navigation
 */
class class_modul_navigation_cache {

    private $arrModule;
    private $objDB;

    

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct() {
        $this->arrModule = array();
        $this->arrModule["name"] 			= "modul_navigation";
		$this->arrModule["author"] 			= "sidler@mulchprod.de";
		$this->arrModule["moduleId"] 		= _navigation_modul_id_;
		$this->arrModule["table"]       	= _dbprefix_."navigation_cache";
		$this->arrModule["modul"]			= "navigation";

		//We need a db object
		include_once(_systempath_."/class_carrier.php");
		$objCarrier = class_carrier::getInstance();

		$this->objDB = $objCarrier->getObjDB();
    }

    /**
     * Tries to load the cached navigation.
     * If no cached entry was found, null is returned instead
     *
     * @param string $strNavigationId
     * @param string $strElementId
     * @param string $strPagename
     * @return string or null
     */
    public function loadNavigationFromCache($strNavigationId, $strElementId, $strPagename) {

        if(_navigation_use_cache_ == "false")
            return null;

        $strQuery = "SELECT navigation_cache_content
                       FROM ".$this->arrModule["table"]."
                      WHERE navigation_cache_userid = '".dbsafeString(class_carrier::getInstance()->getObjSession()->getUserID())."'
                        AND navigation_cache_checksum = '".dbsafeString($this->generateCacheChecksum($strNavigationId, $strElementId))."'
                        AND navigation_cache_page = '".dbsafeString($strPagename)."'";
        $arrRow = $this->objDB->getRow($strQuery, 0, false);
        if(isset($arrRow["navigation_cache_content"]))
            return $arrRow["navigation_cache_content"];
        else
            return null;
    }

    /**
     * Saves the generated navigation to the cache.
     *
     * @param string $strNavigationId
     * @param string $strElementId
     * @param string $strPagename
     * @param string $strContent
     * @return bool
     */
    public function saveNavigationToCache($strNavigationId, $strElementId, $strPagename, $strContent)  {

        if(_navigation_use_cache_ == "false")
            return true;

        $strQuery = "INSERT INTO ".$this->arrModule["table"]."
                    (navigation_cache_id, navigation_cache_page, navigation_cache_userid, navigation_cache_checksum, navigation_cache_content) VALUES
                    ('".dbsafeString(generateSystemid())."',
                     '".dbsafeString($strPagename)."',
                     '".dbsafeString(class_carrier::getInstance()->getObjSession()->getUserID())."',
                     '".dbsafeString($this->generateCacheChecksum($strNavigationId, $strElementId))."',
                     '".dbsafeString($strContent, false)."')";
        return $this->objDB->_query($strQuery);
    }

    /**
     * Generates a sha1 sum to identify the cache-entry more exactly.
     *
     * @param string $strNavigationId
     * @param string $strElementId
     * @return string
     */
    private function generateCacheChecksum($strNavigationId, $strElementId) {
        return sha1($strNavigationId.$strElementId);
    }

    /**
     * Flushes the cache, so deletes all generated navis saved to the cache
     *
     * @return bool
     */
    public static function flushCache() {
        $strQuery = "DELETE FROM "._dbprefix_."navigation_cache";
        return class_carrier::getInstance()->getObjDB()->_query($strQuery);
    }

    

}
?>