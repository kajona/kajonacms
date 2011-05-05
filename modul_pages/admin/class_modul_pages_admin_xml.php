<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                             *
********************************************************************************************************/


/**
 * admin-class of the pages-module
 * Serves xml-requests, e.g. a list of available pages
 *
 * @package modul_pages
 * @author sidler@mulchprod.de
 */
class class_modul_pages_admin_xml extends class_admin implements interface_xml_admin {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $arrModul = array();
		$arrModul["name"] 			= "modul_pages";
		$arrModul["moduleId"] 		= _pages_modul_id_;
		$arrModul["modul"]			= "pages";

		//base class
		parent::__construct($arrModul);
	}


	/**
	 * Creates a list of sites reduced to match the filter passed.
     * Used e.g. by the page-selector.
	 *
	 * @return string
	 */
	protected function actionGetPagesByFilter() {
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
     * Fetches all child-nodes (folders and pages) of the passed node.
     * Used by the tree-view in module-pages.
     *
     * @return string
     * @since 3.3.0
     */
    protected function actionGetchildNodes() {
        $strReturn = "";

        $strReturn .= "<entries>";

        $arrFolder = class_modul_pages_folder::getFolderList($this->getSystemid());
        foreach ($arrFolder as $objSingleEntry) {
                if($objSingleEntry->rightView()) {
                    if($objSingleEntry instanceof class_modul_pages_folder) {
                        $strReturn .= "<folder>";
                        $strReturn .= "<name>".xmlSafeString($objSingleEntry->getStrName())."</name>";
                        $strReturn .= "<systemid>".$objSingleEntry->getSystemid()."</systemid>";
                        $strReturn .= "<link>".getLinkAdminHref("pages", "list", "systemid=".$objSingleEntry->getSystemid())."</link>";
                        $strReturn .= "<isleaf>".(count(class_modul_pages_folder::getPagesAndFolderList($objSingleEntry->getSystemid())) == 0 ? "true" : "false")."</isleaf>";
                        $strReturn .= "</folder>";
                    }
                }
            }


        $arrPages = class_modul_pages_folder::getPagesInFolder($this->getSystemid());
        if(count($arrPages) > 0) {
            foreach ($arrPages as $objSingleEntry) {
                if($objSingleEntry->rightView()) {
                    if($objSingleEntry instanceof class_modul_pages_page) {
                        $strReturn .= "<page>";
                        $strReturn .= "<name>".xmlSafeString($objSingleEntry->getStrName())."</name>";
                        $strReturn .= "<systemid>".$objSingleEntry->getSystemid()."</systemid>";
                        $strReturn .= "<link>".getLinkAdminHref("pages_content", "list", "&systemid=".$objSingleEntry->getSystemid())."</link>";
                        $strReturn .= "<isleaf>".(count(class_modul_pages_folder::getPagesAndFolderList($objSingleEntry->getSystemid())) == 0 ? "true" : "false")."</isleaf>";
                        $strReturn .= "</page>";
                    }
                    
                }
            }
        }
        $strReturn .= "</entries>";

        return $strReturn;
    }


}

?>