<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                          *
********************************************************************************************************/

/**
 * This class provides the mechanism, to save and load already generated pages from / to the database.
 * This results in a better performance when loading pages in the portal
 *
 * @package modul_pages
 */
class class_modul_pages_pagecache {

    private $arrModule;
    private $objDB;

    /**
	 * Constructor
	 *
	 */
	public function __construct() 	{
		$this->arrModule["name"] 		= "pagecache";
		$this->arrModule["author"] 		= "sidler@mulchprod.de";

		//We need a db object
		include_once(_systempath_."/class_carrier.php");
		$objCarrier = class_carrier::getInstance();

		$this->objDB = $objCarrier->getObjDB();
	}


	/**
	 * Trys to load a page from the cache
	 *
	 * @param string $strPagename
	 * @param string $strUserid
	 * @return string
	 */
	public function loadPageFromCache($strPagename, $strUserid) {
	    $strQuery = "SELECT page_cache_content as page
	                 FROM "._dbprefix_."page_cache
	                 WHERE page_cache_name='".$this->objDB->dbsafeString($strPagename)."'
	                   AND page_cache_checksum = '".$this->objDB->dbsafeString($this->generateCacheChecksum())."'
	                   AND page_cache_releasetime > ".(int)time()."
	                   AND page_cache_userid = '".$this->objDB->dbsafeString($strUserid)."'";

	    $arrPage = $this->objDB->getRow($strQuery, 0, false);
	    if(isset($arrPage["page"]))
	       return $arrPage["page"];
	    else
	       return "";

	}


	/**
	 * saves the passed page to the cache
	 *
	 * @param string $strPagename
	 * @param int $intCachetime
	 * @param string $strUserid
	 * @param string $strPage
	 * @return bool
	 */
	public function savePageToCache($strPagename, $intCachetime, $strUserid, $strPage) {

	    $strQuery = "INSERT INTO "._dbprefix_."page_cache
	       (page_cache_id, page_cache_name, page_cache_checksum, page_cache_createtime, page_cache_releasetime, page_cache_userid, page_cache_content) VALUES
	       ('".$this->objDB->dbsafeString(generateSystemid())."', '".$this->objDB->dbsafeString($strPagename)."', '".$this->objDB->dbsafeString($this->generateCacheChecksum())."',
	        '".(int)time()."', '".(int)(time()+$intCachetime)."', '".$this->objDB->dbsafeString($strUserid)."', '".$this->objDB->dbsafeString($strPage, false)."')";

	    return $this->objDB->_query($strQuery);
	}


	/**
	 * Generates an id to identify the page correctly in the cache.
	 * Therefore, the passed systemid and action is used to create an md5-checksum
	 *
	 * @return string
	 */
	private function generateCacheChecksum() {
	    $strAction = getGet("action");
	    $strSystemid = getGet("systemid");
	    //collection to react on "invisible" commands
	    $strMixed = getGet("pv").getPost("searchterm").getGet("highlight");
	    include_once(_systempath_."/class_modul_system_common.php");
	    $objCommon = new class_modul_system_common();
	    $strLanguage = $objCommon->getStrPortalLanguage();
        $strKey = md5($strAction.$strSystemid.$strLanguage.$strMixed);
        return $strKey;
	}


	/**
	 * This method cleans up the cache. Therefor, it deletes all
	 * pages that are invalid (out of date).
	 *
	 */
	public function cacheCleanup() {
        $strQuery = "DELETE FROM "._dbprefix_."page_cache
                     WHERE page_cache_releasetime < ".(int)time()."";
        $this->objDB->_query($strQuery);
	}

	/**
	 * Deletes the complete Pages-Cache
	 *
	 * @return bool
	 */
	public function flushCompletePagesCache() {
        $strQuery = "DELETE FROM "._dbprefix_."page_cache";
        return $this->objDB->_query($strQuery);
	}

	/**
	 * Removes one page from the cache
	 *
	 * @param string $strPagename
	 * @return bool
	 */
	public function flushPageFromPagesCache($strPagename) {
        $strQuery = "DELETE FROM "._dbprefix_."page_cache
                     WHERE page_cache_name = '".$this->objDB->dbsafeString($strPagename)."'";
        return $this->objDB->_query($strQuery);
	}
}


?>