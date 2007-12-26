<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_element_tagto.php																		        *
* 	Portal-class of the tagto element															        *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                     *
********************************************************************************************************/

//base-class
require_once(_portalpath_."/class_elemente_portal.php");
//Interface
require_once(_portalpath_."/interface_portal_element.php");

/**
 * Loads the last-modified date of the current page and prepares it for output
 *
 * @package modul_pages
 */
class class_element_tagto extends class_element_portal implements interface_portal_element {

	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct($objElementData) {
		$arrModule["name"] 			= "element_tagto";
		$arrModule["author"] 		= "sidler@mulchprod.de";
		$arrModule["moduleId"] 		= _pages_elemente_modul_id_;
		$arrModule["table"]		    = _dbprefix_."element_universal";
		$arrModule["modul"]		    = "elemente";

		parent::__construct($arrModule, $objElementData);
	}

    /**
     * Looks up the last modified-date of the current page
     *
     * @return string the prepared html-output
     */
	public function loadData() {
		$strReturn = "";

        //actions or systemids passed? pagename?
        $strSystemid = $this->getParam("systemid");
        $strActions = $this->getParam("action");
        $strPagenme = $this->getPagename();

        //load the template
        $strTemplateID = $this->objTemplate->readTemplate("/element_tagto/".$this->arrElementData["char1"], "tagtos");
        $strLink = getLinkPortalRaw($strPagenme, "", $strActions, "", $strSystemid);
        $strReturn = $this->objTemplate->fillTemplate(array("pageurl" => $strLink), $strTemplateID);

		return $strReturn;
	}

}
?>