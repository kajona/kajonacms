<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

/**
 * Search plugin of the gallery-module.
 *
 * @package module_mediamanager
 */
class class_module_mediamanager_search_portal implements interface_search_plugin_portal  {

    private $arrTableConfig = array();
    private $strSearchterm = "";
    private $arrHits = array();

    private $objDB;

    public function  __construct($strSearchterm) {

        $this->strSearchterm = $strSearchterm;
        $arrSearch = array();

        $arrSearch["mediamanager"] = array();
		$arrSearch["mediamanager"][_dbprefix_."mediamanager_file"][] = "file_name LIKE ?";
		$arrSearch["mediamanager"][_dbprefix_."mediamanager_file"][] = "file_filename LIKE ?";
		$arrSearch["mediamanager"][_dbprefix_."mediamanager_file"][] = "file_description LIKE ?";
		$arrSearch["mediamanager"][_dbprefix_."mediamanager_file"][] = "file_subtitle LIKE ?";

		$this->arrTableConfig = $arrSearch;
        $this->objDB = class_carrier::getInstance()->getObjDB();
    }


    public function doSearch() {
        if(class_module_system_module::getModuleByName("mediamanager") !== null)
            $this->searchMediamanager();

        return $this->arrHits;
    }


   /**
    * Searches the images in galleries
    *
    */
	private function searchMediamanager() {
		foreach($this->arrTableConfig["mediamanager"] as $strTable => $arrColumnConfig) {
			$arrWhere = array();
            $arrParams = array();

            //Build an or-statemement out of the columns
            foreach($arrColumnConfig as $strColumn) {
                $arrWhere[] = $strColumn;
                $arrParams[] = "%".$this->strSearchterm."%";
            }
            $strWhere = "( ".implode(" OR ", $arrWhere). " ) ";

			//Query bauen
			$strQuery ="SELECT system_id
			              FROM ".$strTable.",
			 		           "._dbprefix_."system
                         WHERE ".$strWhere."
                           AND system_id = file_id
                           AND system_status = 1";

			$arrFiles = $this->objDB->getPArray($strQuery, $arrParams);
			//register found pics
			if(count($arrFiles) > 0) {
				foreach($arrFiles as $arrOneFile) {
                    $objFile = new class_module_mediamanager_file($arrOneFile["system_id"]);
                    $arrDetails = $this->getElementData($objFile);

                    foreach($arrDetails as $arrOnePage) {

                        if(!isset($arrOnePage["page_name"]) || $arrOnePage["page_name"] == "" || !$objFile->rightView())
                            continue;

                        if(isset($this->arrHits[$objFile->getSystemid().$arrOnePage["page_id"]]["hits"]))
                            $this->arrHits[$objFile->getSystemid().$arrOnePage["page_id"]]["hits"]++;
                        else {
                            $this->arrHits[$objFile->getSystemid().$arrOnePage["page_id"]]["hits"] = 1;
                            $this->arrHits[$objFile->getSystemid().$arrOnePage["page_id"]]["pagelink"] = getLinkPortal($arrOnePage["page_name"], "", "_self", $objFile->getStrDisplayName(), "mediaFolder", "&highlight=".html_entity_decode($this->strSearchterm, ENT_QUOTES, "UTF-8"), $objFile->getPrevId(), "", "", $objFile->getStrDisplayName());
                            $this->arrHits[$objFile->getSystemid().$arrOnePage["page_id"]]["pagename"] = $arrOnePage["page_name"];
                            $this->arrHits[$objFile->getSystemid().$arrOnePage["page_id"]]["description"] = uniStrTrim($objFile->getStrDescription(), 150);
					    }

                    }
				}
			}
		}
	}




    private function getElementData(class_module_mediamanager_file $objFile) {
        $objLanguages = new class_module_languages_language();

        $strQuery =  "SELECT page_name, page_id
                       FROM "._dbprefix_."element_downloads,
                            "._dbprefix_."page_element,
                            "._dbprefix_."page,
                            "._dbprefix_."system
                      WHERE download_id = ?
                        AND content_id = page_element_id
                        AND content_id = system_id
                        AND system_prev_id = page_id
                        AND system_status = 1
                        AND page_element_ph_language = ? " ;

        $arrRows = $this->objDB->getPArray($strQuery, array($objFile->getRepositoryId(), $objLanguages->getStrPortalLanguage()));

        $strQuery =  "SELECT page_name, page_id
                       FROM "._dbprefix_."element_gallery,
                            "._dbprefix_."page_element,
                            "._dbprefix_."page,
                            "._dbprefix_."system
                      WHERE gallery_id = ?
                        AND content_id = page_element_id
                        AND content_id = system_id
                        AND system_prev_id = page_id
                        AND system_status = 1
                        AND page_element_ph_language = ? " ;

        $arrRows = array_merge($arrRows, $this->objDB->getPArray($strQuery, array($objFile->getRepositoryId(), $objLanguages->getStrPortalLanguage())));

        return $arrRows;
    }

}

