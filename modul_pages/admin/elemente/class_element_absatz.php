<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*                                                                                                       *
*   class_element_absatz.php                                                                            *
*   Admin-Class of the paragraph                                                                        *
*                                                                                                       *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                     *
********************************************************************************************************/

//Base-Class
include_once(_adminpath_."/class_element_admin.php");
//Interface
include_once(_adminpath_."/interface_admin_element.php");

/**
 * Admin-Class to handle the paragraphs
 *
 * @package modul_pages
 */
class class_element_absatz extends class_element_admin implements interface_admin_element {

	/**
	 * Contructor
	 *
	 */
	public function __construct() {
		$arrModule["name"] 			= "element_absatz";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]			= _dbprefix_."element_absatz";
		$arrModule["modul"]			= "elemente";

		$arrModul["tableColumns"]   = "";

		parent::__construct($arrModule);
	}


	/**
	 * Returns a form to edit the element-data
	 *
	 * @param mixed $arrElementData
	 * @return string
	 */
	public function getEditForm($arrElementData)	{
		$strReturn = "";
		$strReturn .= $this->objToolkit->formInputText("absatz_titel", $this->getText("absatz_titel"), (isset($arrElementData["absatz_titel"]) ? $arrElementData["absatz_titel"] : ""));
		$strReturn .= $this->objToolkit->formWysiwygEditor("absatz_inhalt", $this->getText("absatz_inhalt"), (isset($arrElementData["absatz_inhalt"]) ? $arrElementData["absatz_inhalt"] : ""));
		$strReturn .= $this->objToolkit->formInputText("absatz_bild", $this->getText("absatz_bild"), (isset($arrElementData["absatz_bild"]) ? $arrElementData["absatz_bild"] : ""), "inputText", getLinkAdminPopup("folderview", "list", "&form_element=absatz_bild", $this->getText("browser"), $this->getText("browser"), "icon_externalBrowser.gif", 500, 500, "ordneransicht"));
		$strReturn .= $this->objToolkit->formInputPageSelector("absatz_link", $this->getText("absatz_link"), (isset($arrElementData["absatz_link"]) ? $arrElementData["absatz_link"] : ""));
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
		$strContent = $this->getParam("absatz_inhalt");
		$strImage = $this->getParam("absatz_bild");
		//We have to replace the webpath to remain flexible
		$strContent = str_replace(_webpath_, "_webpath_", $strContent);
		$strImage = str_replace(_webpath_, "_webpath_", $strImage);
		//Secure the text
		//$strContent = htmlToString($strContent, false, false);
		//And to the database
		$strQuery = "UPDATE ".$this->arrModule["table"]." SET
				absatz_titel = '".dbsafeString($this->getParam("absatz_titel"))."',
				absatz_inhalt = '".dbsafeString($strContent, false)."',
				absatz_link = '".dbsafeString($this->getParam("absatz_link"))."',
				absatz_bild = '".dbsafeString($strImage)."'
				WHERE content_id='".dbsafeString($strSystemid)."'";

		if($this->objDB->_query($strQuery))
			return true;
		else
			return false;
	}

	/**
	 * Returns an abstract of the current element
	 *
	 * @return string
	 */
	public function getContentTitle() {

	    $arrData = $this->loadElementData();
	    if($arrData["absatz_titel"] != "")
	       return htmlToString(uniStrTrim($arrData["absatz_titel"], 25), true);
	    else
	       return htmlToString(uniStrTrim($arrData["absatz_inhalt"], 25), true);
	}


} //class_element_absatz.php
?>