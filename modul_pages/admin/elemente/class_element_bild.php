<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_element_bild.php																				*
* 	Admin-class of the picture element																	*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

//base class
include_once(_adminpath_."/class_element_admin.php");
//Interface
include_once(_adminpath_."/interface_admin_element.php");

/**
 * Class to handle the admin-part of the element
 *
 * @package modul_pages
 */
class class_element_bild extends class_element_admin implements interface_admin_element {


	public function __construct() {
		$arrModul["name"] 			= "element_bild";
		$arrModul["author"] 		= "sidler@mulchprod.de";
		$arrModul["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModul["table"]	 		= _dbprefix_."element_bild";
		$arrModul["modul"]			= "elemente";

		$arrModul["tableColumns"]   = "";

		parent::__construct($arrModul);
	}


	/**
	 * Returns the element-part of the admin-form
	 *
	 * @param mixed $arrElementData
	 * @return string
	 */
	public function getEditForm($arrElementData) {
		$strReturn = "";
		$strReturn .= $this->objToolkit->formInputText("bild_titel", $this->getText("bild_titel"), (isset($arrElementData["bild_titel"]) ? $arrElementData["bild_titel"] : "" ));
		$strReturn .= $this->objToolkit->formInputPageSelector("bild_link", $this->getText("bild_link"), (isset($arrElementData["bild_link"]) ? $arrElementData["bild_link"] : "" ));
		$strReturn .= $this->objToolkit->formInputText("bild_bild", $this->getText("bild_bild"), (isset($arrElementData["bild_bild"]) ? $arrElementData["bild_bild"] : "" ), "inputText", getLinkAdminPopup("folderview", "list", "&form_element=bild_bild", $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", 500, 500, "ordneransicht"));
		$strReturn .= $this->objToolkit->formTextRow($this->getText("bild_xy_hint"));
		$strReturn .= $this->objToolkit->formInputText("bild_x", $this->getText("bild_x"), (isset($arrElementData["bild_x"]) ? $arrElementData["bild_x"] : "" ));
		$strReturn .= $this->objToolkit->formTextRow($this->getText("bild_xy_hint"));
		$strReturn .= $this->objToolkit->formInputText("bild_y", $this->getText("bild_y"), (isset($arrElementData["bild_y"]) ? $arrElementData["bild_y"] : "" ));
		
		return $strReturn;
	}

	/**
	 * Returns an abstract of the current element
	 *
	 * @return string
	 */
	public function getContentTitle() {
	    $arrData = $this->loadElementData();
        return htmlToString(uniStrTrim($arrData["bild_bild"], 25), true);
	}

	/**
	 * saves the submitted data to the database
	 * It IS wanted to not let the system save the element here!
	 *
	 * @param string $strSystemid
	 * @return bool
	 */
	public function actionSave($strSystemid) {
		$strImage = $this->getParam("bild_bild");
		//We have to replace the webpath to remain flexible
		$strImage = str_replace(_webpath_, "", $strImage);
		//Secure the text
		//And to the database
		$strQuery = "UPDATE ".$this->arrModule["table"]." SET
				bild_titel = '".dbsafeString($this->getParam("bild_titel"))."',
				bild_link = '".dbsafeString($this->getParam("bild_link"))."',
				bild_bild = '".dbsafeString($strImage)."',
				bild_x = ".(int)dbsafeString($this->getParam("bild_x")).",
				bild_y = ".(int)dbsafeString($this->getParam("bild_y"))."
				WHERE content_id='".dbsafeString($strSystemid)."'";

		if($this->objDB->_query($strQuery))
			return true;
		else
			return false;
	}


} //class_element_absatz.php
?>