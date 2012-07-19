<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_guestbook_search_portal.php 4647 2012-05-11 14:37:22Z sidler $                    *
********************************************************************************************************/

/**
 * Backend search plugin of the postacomment-module.
 *
 * @package module_postacomment
 */
class class_module_postacomment_search_admin implements interface_search_plugin  {

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
        if(class_module_system_module::getModuleByName("postacomment") != null) {
            $arrWhere = array(
                "postacomment_title LIKE ?",
                "postacomment_comment LIKE ?"
            );
            $arrParams = array(
                "%".$this->strSearchterm."%",
                "%".$this->strSearchterm."%"
            );

            $strWhere = "( ".implode(" OR ", $arrWhere). " ) ";

            //Query bauen
            $strQuery ="SELECT system_id
                      FROM "._dbprefix_."postacomment,
                           "._dbprefix_."system
                     WHERE ".$strWhere."
                       AND system_id = postacomment_id ";

            $arrPosts = $this->objDB->getPArray($strQuery, $arrParams);

            foreach($arrPosts as $arrOnePost) {
                $objPost = class_objectfactory::getInstance()->getObject($arrOnePost["system_id"]);
                $objResult = new class_search_result();
                $objResult->setObjObject($objPost);
                $this->arrHits[] = $objResult;
            }
        }

        return $this->arrHits;
    }

}

