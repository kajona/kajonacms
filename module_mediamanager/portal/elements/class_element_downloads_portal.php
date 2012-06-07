<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * Portal-part of the downloads-element
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 */
class class_element_downloads_portal extends class_element_portal implements interface_portal_element {

    /**
     * Contructor
     *
     * @param $objElementData
     */
	public function __construct($objElementData) {
		$this->setArrModuleEntry("name", "element_downloads");
		$this->setArrModuleEntry("table", _dbprefix_."element_downloads");
		parent::__construct($objElementData);

        //we support ratings, so add cache-busters
        $this->setStrCacheAddon(getCookie("kj_ratingHistory"));
	}


    /**
     * Loads the downloads-class and passes control
     *
     * @return string
     */
	public function loadData() {
		$strReturn = "";

        $objDownloadsModule = class_module_system_module::getModuleByName("mediamanager");
		if($objDownloadsModule != null) {

            $this->arrElementData["repo_id"] = $this->arrElementData["download_id"];
            $this->arrElementData["repo_elementsperpage"] = $this->arrElementData["download_amount"];
            $this->arrElementData["repo_template"] = $this->arrElementData["download_template"];


    		$objDownloads = $objDownloadsModule->getPortalInstanceOfConcreteModule($this->arrElementData);
            $strReturn = $objDownloads->action();
		}

		return $strReturn;
	}

    public function getNavigationEntries() {
        $arrData = $this->getElementContent($this->getSystemid());

        $arrData["repo_id"] = $arrData["download_id"];
        $arrData["repo_elementsperpage"] = $arrData["download_amount"];
        $arrData["repo_template"] = $arrData["download_template"];

        $objDownloadsModule = class_module_system_module::getModuleByName("mediamanager");

        if($objDownloadsModule != null) {

            /** @var $objDownloads class_module_mediamanager_portal */
            $objDownloads = $objDownloadsModule->getPortalInstanceOfConcreteModule($arrData);
            $arrReturn = $objDownloads->getNavigationNodes();

            return $arrReturn;
        }

        return false;
    }


}
