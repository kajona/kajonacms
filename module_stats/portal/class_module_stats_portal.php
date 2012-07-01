<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_stats_portal.php 3823 2011-05-13 13:16:35Z sidler $	                            *
********************************************************************************************************/

/**
 * Portal-Class of the stats module. Invokes the logging of requests to the database
 *
 * @package module_stats
 * @author sidler@mulchprod.de
 */
class class_module_stats_portal extends class_portal {

    /**
     * @param array $arrElementData
     */
    public function __construct($arrElementData) {
        $this->setArrModuleEntry("moduleId", _stats_modul_id_);
        $this->setArrModuleEntry("modul", "stats");
        parent::__construct($arrElementData);

	}

	public function insertStat() {
		//Collect Data
		$strIp = getServer("REMOTE_ADDR");
		$intDate = time();
		$strPage = $this->getParam("page");
        if($strPage == "")
            $strPage = $this->getParam("seite");

		$strReferer = rtrim(getServer("HTTP_REFERER"), "/");
		$strBrowser = getServer("HTTP_USER_AGENT");
		$strLanguage = $this->getStrPortalLanguage();

		$objStats = new class_module_stats_worker();
        $objStats->createStatsEntry($strIp, $intDate, $strPage, $strReferer, $strBrowser, $strLanguage);
	}

}
