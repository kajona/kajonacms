<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_guestbook_search_portal.php 4779 2012-07-18 07:19:11Z sidler $                    *
********************************************************************************************************/

/**
 * Search plugin of the navigation-module.
 *
 * @package module_navigation
 * @author sidler@mulchprod.de
 */
class class_module_navigation_search_admin implements interface_search_plugin  {
    private $strSearchterm;

    private $objDB;

    public function  __construct($strSearchterm) {

        $this->strSearchterm = $strSearchterm;
        $this->objDB = class_carrier::getInstance()->getObjDB();
    }


    public function doSearch() {
        if(class_module_system_module::getModuleByName("guestbook") != null)
            return $this->searchNavigation();

        return array();
    }


    /**
     * Searches the guestbook-posts
     *
     */
	private function searchNavigation() {

        $arrHits = array();

        $arrWhere = array(
            "navigation_name LIKE ? ",
            "navigation_page_e LIKE ? ",
            "navigation_page_i LIKE ? ",
            "navigation_image LIKE ? ",
        );
        $arrParams = array(
            "%".$this->strSearchterm."%",
            "%".$this->strSearchterm."%",
            "%".$this->strSearchterm."%",
            "%".$this->strSearchterm."%"
        );

        $strWhere = "( ".implode(" OR ", $arrWhere). " ) ";
        $strQuery ="SELECT system_id
                      FROM "._dbprefix_."navigation,
                           "._dbprefix_."system
                     WHERE ".$strWhere."
                       AND system_id  = navigation_id";

        $arrPoints = $this->objDB->getPArray($strQuery, $arrParams);

        foreach($arrPoints as $arrOnePoint) {
            $objPoint = class_objectfactory::getInstance()->getObject($arrOnePoint["system_id"]);
            $objResult = new class_search_result();
            $objResult->setObjObject($objPoint);
            $arrHits[] = $objResult;
        }

        return $arrHits;
	}

}

