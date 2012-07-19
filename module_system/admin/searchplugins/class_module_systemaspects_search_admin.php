<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_guestbook_search_portal.php 4647 2012-05-11 14:37:22Z sidler $                    *
********************************************************************************************************/

/**
 * Backend search plugin of the system-module.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_module_systemaspects_search_admin implements interface_search_plugin  {

    private $strSearchterm;

    /**
     * @var class_search_result
     */
    private $arrHits = array();
    private $objDB;

    public function  __construct($strSearchterm) {
        $this->strSearchterm = $strSearchterm;
        $this->objDB = class_carrier::getInstance()->getObjDB();
    }


    public function doSearch() {
        if(class_module_system_module::getModuleByName("system") != null) {
            $arrWhere = array(
                "aspect_name LIKE ?"
            );
            $arrParams = array(
                "%".$this->strSearchterm."%"
            );

            $strWhere = "( ".implode(" OR ", $arrWhere). " ) ";

            //Query bauen
            $strQuery ="SELECT system_id
                      FROM "._dbprefix_."aspects,
                           "._dbprefix_."system
                     WHERE ".$strWhere."
                       AND system_id = aspect_id ";

            $arrEntries = $this->objDB->getPArray($strQuery, $arrParams);

            foreach($arrEntries as $arrOnePost) {
                $objPost = class_objectfactory::getInstance()->getObject($arrOnePost["system_id"]);
                $objResult = new class_search_result();
                $objResult->setObjObject($objPost);
                $this->arrHits[] = $objResult;
            }
        }

        return $this->arrHits;
    }

}

