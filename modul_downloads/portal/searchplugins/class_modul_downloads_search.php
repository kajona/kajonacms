<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                             *
********************************************************************************************************/

include_once(_portalpath_."/class_portal.php");
include_once(_portalpath_."/searchplugins/interface_search_plugin.php");

/**
 * Search plugin of the downloads-module.
 *
 * @package modul_downloads
 */
class class_modul_downloads_search extends class_portal implements interface_search_plugin  {

    private $arrTableConfig = array();
    private $arrSearchterm;
    private $strSearchtermRaw = "";
    private $arrHits = array();

    public function  __construct($arrSearchterm, $strSearchtermRaw) {
        parent::__construct();

        $this->arrSearchterm = $arrSearchterm;
        $this->strSearchtermRaw = $strSearchtermRaw;

        $arrSearch = array();

        //Downloads
        $arrSearch["downloads"] = array();
		$arrSearch["downloads"][_dbprefix_."downloads_file"][] = "downloads_name";
		$arrSearch["downloads"][_dbprefix_."downloads_file"][] = "downloads_description";
		$arrSearch["downloads"][_dbprefix_."downloads_file"][] = "downloads_filename";

		$this->arrTableConfig = $arrSearch;
    }


    public function doSearch() {
        $this->searchDownloads();
        return $this->arrHits;
    }


    /**
     * Searches the downloads
     *
     */
	private function searchDownloads() {
		foreach($this->arrTableConfig["downloads"] as $strTable => $arrColumnConfig) {
			$arrWhere = array();
			//Build an or-statemement out of the columns
			foreach($arrColumnConfig as $strColumn) {
				foreach ($this->arrSearchterm as $strOneSeachterm)
                    $arrWhere[] = $strColumn.$strOneSeachterm;
			}
			$strWhere = "( ".implode(" OR ", $arrWhere). " ) ";

			//Query bauen
			$strQuery =
			"SELECT downloads_name, downloads_description, system_id, system_prev_id
			 FROM ".$strTable.",
			 		"._dbprefix_."system
			 WHERE ".$strWhere."
			 	AND system_id  = downloads_id
			 		AND system_status = 1";

			$arrDownloads = $this->objDB->getArray($strQuery);

			//Register found news
			if(count($arrDownloads) > 0) {
				foreach($arrDownloads as $arrOneDownload) {

				    if(!$this->checkLanguage($arrOneDownload))
				        continue;

					if(isset($this->arrHits[$arrOneDownload["system_id"]]["hits"]))
						$this->arrHits[$arrOneDownload["system_id"]]["hits"]++;
					else {
				    	$this->arrHits[$arrOneDownload["system_id"]]["hits"] = 1;
					    $this->arrHits[$arrOneDownload["system_id"]]["pagelink"] = getLinkPortal(_downloads_suche_seite_, "", "_self", $arrOneDownload["downloads_name"], "", "&highlight=".$this->strSearchtermRaw , $arrOneDownload["system_prev_id"]);
					    $this->arrHits[$arrOneDownload["system_id"]]["pagename"] = _downloads_suche_seite_;
					    $this->arrHits[$arrOneDownload["system_id"]]["description"] = $arrOneDownload["downloads_description"];
					}
				}
			}
		}
	}


	/**
	 * Checks, if the download is available on page using the current language
	 *
	 * @param array $arrOneDownload
	 * @return bool true, if the post is visible
	 */
	private function checkLanguage($arrOneDownload) {
        $bitReturn = true;

        //Loop upwards to find the matching dl-repo
        $strPrevId = $arrOneDownload["system_prev_id"];
        $intCount = 0;
        while($intCount == 0) {
            $strQuery = "SELECT COUNT(*)
                           FROM "._dbprefix_."downloads_archive
                          WHERE archive_id = '".dbsafeString($strPrevId)."'";
            $arrRow = $this->objDB->getRow($strQuery);
            $intCount = $arrRow["COUNT(*)"];
            $intArchiveID = $strPrevId;
            $strPrevId = $this->getPrevId($strPrevId);
        }


        $strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."element_downloads,
                            "._dbprefix_."page_element,
                            "._dbprefix_."system
                      WHERE download_id = '".dbsafeString($intArchiveID)."'
                        AND content_id = page_element_id
                        AND content_id = system_id
                        AND system_status = 1
                        AND page_element_placeholder_language = '".dbsafeString($this->getPortalLanguage())."' " ;

        $arrRow = $this->objDB->getRow($strQuery);


        if(isset($arrRow["COUNT(*)"]) && (int)$arrRow["COUNT(*)"] >= 1)
            return true;

        return false;
	}
}

?>