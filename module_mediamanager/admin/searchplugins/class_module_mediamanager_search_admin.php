<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

/**
 * Search plugin of the mediamanger-module.
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 */
class class_module_mediamanager_search_admin implements interface_search_plugin  {

    private $strSearchterm = "";

    /**
     * @var class_search_result
     */
    private $arrHits = array();

    private $objDB;

    public function  __construct($strSearchterm) {

        $this->strSearchterm = $strSearchterm;
        $this->objDB = class_carrier::getInstance()->getObjDB();
    }


    public function doSearch() {
        if(class_module_system_module::getModuleByName("mediamanager") !== null)
            $this->searchMediamanager();

        return array_values($this->arrHits);
    }


   /**
    * Searches the list of entries in the mediamanager
    *
    */
	private function searchMediamanager() {
			$arrWhere = array(
                "file_name LIKE ?",
                "file_filename LIKE ?",
                "file_description LIKE ?",
                "file_subtitle LIKE ?"
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
                           AND system_id = file_id";

			$arrFiles = $this->objDB->getPArray($strQuery, $arrParams);
			//register found pics
			if(count($arrFiles) > 0) {
				foreach($arrFiles as $arrOneFile) {
                    $objFile = new class_module_mediamanager_file($arrOneFile["system_id"]);

                    if(isset($this->arrHits[$objFile->getSystemid()])) {
                        $objResult = $this->arrHits[$objFile->getSystemid()];
                        $objResult->setIntHits($objResult->getIntHits()+1);
                    }
                    else {
                        $objResult = new class_search_result();
                        $objResult->setStrResultId($objFile->getSystemid());
                        $objResult->setStrSystemid($objFile->getSystemid());
                        $objResult->setObjObject($objFile);
                        $objResult->setStrDescription($objFile->getStrDisplayName());

                        $this->arrHits[$objFile->getSystemid()] = $objResult;
                    }

				}
			}
	}



}

