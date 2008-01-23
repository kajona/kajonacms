<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_element_imagelightbox.php																		*
* 	Admin-class of the imagelightbox-element															*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_element_imagelightbox.php 1884 2007-12-26 15:04:48Z sidler $                             *
********************************************************************************************************/

//Base-Class
include_once(_adminpath_."/class_element_admin.php");
//Interface
include_once(_adminpath_."/interface_admin_element.php");

/**
 * Class to handle the admin-stuff of the imagelightbox-element
 *
 * @package modul_pages
 *
 */
class class_element_imagelightbox extends class_element_admin implements interface_admin_element {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$arrModul["name"] 			= "element_imagelightbox";
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
    		
		//Build the form
		$strReturn .= $this->objToolkit->formInputText("char2", $this->getText("imagelightbox_title"), (isset($arrElementData["char2"]) ? $arrElementData["char2"] : ""));
		$strReturn .= $this->objToolkit->formInputText("char1", $this->getText("imagelightbox_image"), (isset($arrElementData["char1"]) ? $arrElementData["char1"] : ""),
		                                               "inputText", getLinkAdminPopup("folderview", "list", "&form_element=char1", $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", 500, 500, "ordneransicht"));
		
		
		

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
        $strImage = $this->getParam("char1");
        //We have to replace the webpath to remain flexible
        $strImage = str_replace(_webpath_, "", $strImage);
        //And to the database
        $strQuery = "UPDATE ".$this->arrModule["table"]." SET
                char1 = '".dbsafeString($strImage)."',
                char2 = '".dbsafeString($this->getParam("char2"))."'
                WHERE content_id='".dbsafeString($strSystemid)."'";
    
        if($this->objDB->_query($strQuery))
            return true;
        else
            return false;
    }


} 
?>