<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                      *
********************************************************************************************************/



/**
 * Admin-Class to handle the paragraphs
 * @package modul_pages
 *
 */
class class_element_zeile extends class_element_admin implements interface_admin_element {

	/**
	 * Contructor
	 *
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 			= "element_zeile";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]			= _dbprefix_."element_absatz";
		$arrModule["modul"]			= "elemente";

		$arrModule["tableColumns"]  = "absatz_titel|char";

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

		$strReturn .= $this->objToolkit->setBrowserFocus("absatz_titel");

		return $strReturn;
	}

	/**
	 * Returns an abstract of the current element
	 *
	 * @return string
	 */
	public function getContentTitle() {
	    $arrData = $this->loadElementData();
        return uniStrTrim(htmlStripTags($arrData["absatz_titel"]), 60);
	}

	/**
     * Overwrite this function, if you want to validate passed form-input
     *
     * @return mixed
     */
    protected function getRequiredFields() {
        return array("absatz_titel" => "string");
    }


} //class_element_absatz.php
?>