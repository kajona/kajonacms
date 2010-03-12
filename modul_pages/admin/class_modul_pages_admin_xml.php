<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                             *
********************************************************************************************************/


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

        if($strAction == "getChildnodes")
            $strReturn .= $this->getChildNodes();


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
            if($objOnePage->rightView()) {
                $strReturn .= "  <page>\n";
                $strReturn .= "    <title>".xmlSafeString($objOnePage->getStrName())."</title>\n";
                $strReturn .= "  </page>\n";
            }
        }
        $strReturn .= "</pages>\n";
		return $strReturn;
	}

    /**
     * Fetches all child-nodes (so folders an pages) of the passed node.
     * Used by the tree-view in module-pages.
     *
     * @return string
     * @since 3.3.0
     */
    private function getChildNodes() {
        $strReturn = "";

        $arrFolder = class_modul_pages_folder::getFolderList($this->getSystemid());

        $strReturn .= "<folders>";
        if(count($arrFolder) > 0) {
            foreach ($arrFolder as $objSingleFolder) {
                if($objSingleFolder->rightView()) {
                    $strReturn .= "<folder><name>".xmlSafeString($objSingleFolder->getStrName())."</name><systemid>".$objSingleFolder->getSystemid()."</systemid></folder>";
                }
            }
        }
        $strReturn .= "</folders>";

        $strReturn .= "<pages>";
        $arrPages = class_modul_pages_folder::getPagesInFolder($this->getSystemid());
        if(count($arrPages) > 0) {
            foreach ($arrPages as $objSinglePage) {
                if($objSinglePage->rightView()) {
                    $strReturn .= "<page><name>".xmlSafeString($objSinglePage->getStrName())."</name><systemid>".$objSinglePage->getSystemid()."</systemid></page>";
                }
            }
        }
        $strReturn .= "</pages>";

        return $strReturn;
    }


}

?>