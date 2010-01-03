<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$						        *
********************************************************************************************************/

/**
 * Portal-part of the postacomment-element
 *
 * @package modul_postacomment
 */
class class_element_postacomment extends class_element_portal implements interface_portal_element {


	 /**
     * Contructor
     *
     * @param mixed $arrElementData
     */
	public function __construct($objElementData) {
        $arrModule = array();
		$arrModule["name"] 			= "element_postacomment";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]	    	= _dbprefix_."element_universal";

        parent::__construct($arrModule, $objElementData);
	}


    /**
     * Loads the postacomment-class and passes control
     *
     * @return string
     */
	public function loadData() {
		$strReturn = "";
		//Load the data
		$objpostacommentModule = class_modul_system_module::getModuleByName("postacomment");
		if($objpostacommentModule != null) {

            //action-filter set within the element?
            if(trim($this->arrElementData["char2"]) != "") {
                if($this->getParam("action") != $this->arrElementData["char2"])
                    return;
            }

    		$strClassName = uniStrReplace(".php", "", $objpostacommentModule->getStrNamePortal());
    		$objpostacomment = new $strClassName($this->arrElementData);
            $strReturn = $objpostacomment->action();
		}
		return $strReturn;
	}

}
?>