<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_postacomment.php 3679 2011-03-18 12:37:20Z sidler $						        *
********************************************************************************************************/

/**
 * Portal-part of the postacomment-element
 *
 * @package module_postacomment
 * @author sidler@mulchprod.de
 */
class class_element_postacomment_portal extends class_element_portal implements interface_portal_element {

	/**
     * Constructor
     *
     * @param $objElementData
     */
	public function __construct($objElementData) {
        parent::__construct($objElementData);
		$this->setArrModuleEntry("table", _dbprefix_."element_universal");

        //we support ratings, so add cache-busters
        $this->setStrCacheAddon(getCookie("kj_ratingHistory"));
	}


    /**
     * Loads the postacomment-class and passes control
     *
     * @return string
     */
	public function loadData() {
		$strReturn = "";
		//Load the data
		$objPostacommentModule = class_module_system_module::getModuleByName("postacomment");
		if($objPostacommentModule != null) {

            //action-filter set within the element?
            if(trim($this->arrElementData["char2"]) != "") {
                if($this->getParam("action") != $this->arrElementData["char2"])
                    return "";
            }

    		$objPostacomment = $objPostacommentModule->getPortalInstanceOfConcreteModule($this->arrElementData);
            $strReturn = $objPostacomment->action();
		}
		return $strReturn;
	}

}
