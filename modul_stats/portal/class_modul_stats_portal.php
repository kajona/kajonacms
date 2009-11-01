<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                *
********************************************************************************************************/

/**
 * Portal-Class of the stats module. Invokes the logging of requests to the database
 *
 * @package modul_stats
 */
class class_modul_stats_portal extends class_portal {
	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$arrModule["name"] 				= "modul_stats";
		$arrModule["author"] 			= "sidler@mulchprod.de";
		$arrModule["table"] 			= _dbprefix_."stats_data";
		$arrModule["moduleId"] 			= _stats_modul_id_;
		$arrModule["modul"] 			= "stats";

		//Mutter-Klasse rufen
		parent::__construct($arrModule);

	}

	public function insertStat() {
		//Collect Data
		$strIp = getServer("REMOTE_ADDR");
		$intDate = time();
		$strPage = $this->getParam("page");
		if($strPage == "")
		  $strPage = $this->getParam("seite");
		$strReferer = getServer("HTTP_REFERER");
		$strBrowser = getServer("HTTP_USER_AGENT");
		$strLanguage = $this->getPortalLanguage();

		$objStats = new class_modul_stats_worker();
        $objStats->createStatsEntry($strIp, $intDate, $strPage, $strReferer, $strBrowser, $strLanguage);
	}

}
?>