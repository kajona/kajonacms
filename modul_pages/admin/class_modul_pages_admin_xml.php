<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                             *
********************************************************************************************************/


//Include der Mutter-Klasse
include_once(_adminpath_."/class_admin.php");
include_once(_adminpath_."/interface_xml_admin.php");
//model
include_once(_systempath_."/class_modul_system_common.php");
include_once(_systempath_."/class_modul_pages_page.php");

/**
 * admin-class of the pages-module
 * Serves xml-requests, e.g. a list of available pages
 *
 * @package modul_pages
 */
class class_modul_pages_admin_xml extends class_admin implements interface_xml_admin {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$arrModul["name"] 			= "modul_pages";
		$arrModul["author"] 		= "sidler@mulchprod.de";
		$arrModul["moduleId"] 		= _pages_modul_id_;
		$arrModul["modul"]			= "pages";

		//base class
		parent::__construct($arrModul);
	}

	/**
	 * Actionblock. Controls the further behaviour.
	 *
	 * @param string $strAction
	 * @return string
	 */
	public function action($strAction) {
        $strReturn = "";
        if($strAction == "getPagesByFilter")
            $strReturn .= $this->actionGetPagesByFilter();

        return $strReturn;
	}




	/**
	 * Creates a list of sites in the current folder
	 *
	 * @return string
	 */
	private function actionGetPagesByFilter() {
		$strReturn = "";
        $strFilter = $this->getParam("filter");
        $arrPages = class_modul_pages_page::getAllPages(0, 0, $strFilter);

        $strReturn .= "<pages>\n";
        foreach ($arrPages as $objOnePage) {
            $strReturn .= "  <page>\n";
            $strReturn .= "    <title>".xmlSafeString($objOnePage->getStrName())."</title>\n";
            $strReturn .= "  </page>\n";
        }
        $strReturn .= "</pages>\n";
		return $strReturn;
	}


} //class_modul_pages_admin_xml

?>