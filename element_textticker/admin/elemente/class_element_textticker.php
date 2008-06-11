<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_element_textticker.php																		*
* 	Admin-class of the textticker-element															    *
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_textticker.php 1884 2007-12-26 15:04:48Z sidler $                                *
********************************************************************************************************/

//Base-Class
include_once(_adminpath_."/class_element_admin.php");
//Interface
include_once(_adminpath_."/interface_admin_element.php");

/**
 * Class to handle the admin-stuff of the textticker-element
 *
 * @package modul_pages
 *
 */
class class_element_textticker extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$arrModul["name"] 			= "element_textticker";
		$arrModul["author"] 		= "sidler@mulchprod.de";
		$arrModul["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModul["table"] 		    = _dbprefix_."element_universal";
		$arrModul["modul"]			= "elemente";
		$arrModul["tableColumns"]   = "";

		parent::__construct($arrModul);
	}

   /**
	 * Returns a form to edit the element-data
	 *
	 * @param mixed $arrElementData
	 * @return string
	 */
	public function getEditForm($arrElementData) {
		$strReturn = "";

		$strReturn .= $this->objToolkit->formWysiwygEditor("char1", $this->getText("textticker_text"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : "" ), "minimal");

		return $strReturn;
	}
	
    /**
     * saves the submitted data to the database
     * It IS wanted to not let the system save the element here!
     *
     * @param string $strSystemid
     * @return bool
     */
    public function actionSave($strSystemid) {
        $strContent = $this->getParam("char1");
        $strContent = str_replace(_webpath_, "_webpath_", $strContent);
        //And to the database
        $strQuery = "UPDATE ".$this->arrModule["table"]." SET
                char1= '".dbsafeString($strContent, false)."'
                WHERE content_id='".dbsafeString($strSystemid)."'";

        if($this->objDB->_query($strQuery))
            return true;
        else
            return false;
    }


} //class_element_textticker.php
?>