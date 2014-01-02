<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                  *
********************************************************************************************************/

/**
 * Search plugin of the gallery-module.
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 */
class class_module_mediamanager_search_portal implements interface_search_plugin  {

    private $strSearchterm = "";

    /**
     * @var class_search_result
     */
    private $arrHits = array();

    private $objDB;

    public function  __construct(class_module_search_search $objSearch) {

        $this->strSearchterm = $objSearch->getStrQuery();
        $this->objDB = class_carrier::getInstance()->getObjDB();
    }


    public function doSearch() {
        if(class_module_system_module::getModuleByName("mediamanager") !== null)
            $this->searchMediamanager();

        return array_values($this->arrHits);
    }


   /**
    * Searches the images in galleries
    *
    */
	private function searchMediamanager() {
        $arrWhere = array(
            "file_name LIKE ?",
            "file_filename LIKE ?",
            "file_description LIKE ?",
            "file_subtitle LIKE ? "
        );
        $arrParams = array(
            "%".$this->strSearchterm."%",
            "%".$this->strSearchterm."%",
            "%".$this->strSearchterm."%",
            "%".$this->strSearchterm."%"
        );

        $strWhere = "( ".implode(" OR ", $arrWhere). " ) ";

        //Query bauen
        $strQuery ="SELECT system_id
                      FROM "._dbprefix_."mediamanager_file,
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

                    if(isset($this->arrHits[$objFile->getSystemid().$arrOnePage["page_id"]])) {
                        $objResult = $this->arrHits[$objFile->getSystemid().$arrOnePage["page_id"]];
                        $objResult->setIntHits($objResult->getIntHits()+1);
                    }
                    else {

                        $objResult = new class_search_result();
                        $objResult->setStrResultId($objFile->getSystemid().$arrOnePage["page_id"]);
                        $objResult->setStrSystemid($objFile->getSystemid());
                        $objResult->setStrPagelink(getLinkPortal($arrOnePage["page_name"], "", "_self", $objFile->getStrDisplayName(), "mediaFolder", "&highlight=".urlencode(html_entity_decode($this->strSearchterm, ENT_QUOTES, "UTF-8")), $objFile->getPrevId(), "", "", $objFile->getStrDisplayName()));
                        $objResult->setStrPagename($arrOnePage["page_name"]);
                        $objResult->setStrDescription($objFile->getStrDescription());

                        $this->arrHits[$objFile->getSystemid().$arrOnePage["page_id"]] = $objResult;
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

