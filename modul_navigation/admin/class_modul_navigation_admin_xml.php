<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: class_modul_navigation_admin_xml.php 3081 2010-01-03 10:14:41Z sidler $                        *
********************************************************************************************************/


/**
 * Admin-class to manage all navigations
 *
 * @package modul_navigation
 */
class class_modul_navigation_admin_xml extends class_admin implements interface_xml_admin {

    /**
     * Constructor
     *
     */
	public function __construct() {
        $arrModul = array();
		$arrModul["name"] 				= "modul_navigation";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _navigation_modul_id_;
		$arrModul["modul"]				= "navigation";
		parent::__construct($arrModul);
	}

	/**
	 * Action block to decide which action to perform
	 *
	 * @param string $strAction
	 */
	public function action($strAction) {
	    $strReturn = "";
        if($strAction == "getChildnodes")
            $strReturn = $this->getChildNodes();

        return $strReturn;
	}

    /**
     * Fetches all child-nodes of the passed node.
     * Used by the tree-view in module-navigation admin view.
     *
     * @return string
     * @since 3.3.0
     */
    private function getChildNodes() {
        $strReturn = " ";

        $arrNavigations = class_modul_navigation_point::getNaviLayer($this->getSystemid());

        if(count($arrNavigations) > 0) {
            foreach ($arrNavigations as $objSinglePoint) {
                if($objSinglePoint->rightView()) {
                    $strReturn .= "<point>";
                    $strReturn .= "<name>".xmlSafeString($objSinglePoint->getStrName())."</name>";
                    $strReturn .= "<systemid>".$objSinglePoint->getSystemid()."</systemid>";
                    $strReturn .= "<link>".getLinkAdminHref("navigation", "editNaviPoint", "&systemid=".$objSinglePoint->getSystemid())."</link>";
                    $strReturn .= "</point>";
                }
            }
        }

        return $strReturn;
    }

	

	

}
?>