<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_element_postacomment.php																		*
* 	Portal-class of the postacomment element     														*
*-------------------------------------------------------------------------------------------------------*
*	$Id$						        *
********************************************************************************************************/


//base-class
require_once(_portalpath_."/class_elemente_portal.php");
//Interface
require_once(_portalpath_."/interface_portal_element.php");

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
    		require_once(_portalpath_."/".$objpostacommentModule->getStrNamePortal());
    		$strClassName = $objpostacommentModule->getStrClassPortal();
    		$objpostacomment = new $strClassName($this->arrElementData);
            $strReturn = $objpostacomment->action();
		}
		return $strReturn;
	}

}
?>