<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                    *
********************************************************************************************************/

/**
 * Search plugin of the guestbook-module.
 *
 * @package module_guestbook
 * @author sidler@mulchprod.de
 */
class class_module_guestbook_search_admin implements interface_search_plugin  {

    private $strSearchterm;

    private $objDB;

    public function  __construct($strSearchterm) {

        $this->strSearchterm = $strSearchterm;
        $this->objDB = class_carrier::getInstance()->getObjDB();
    }


    public function doSearch() {
        if(class_module_system_module::getModuleByName("guestbook") != null)
            return $this->searchGuestbook();

        return array();
    }


    /**
     * Searches the guestbook-posts
     *
     */
	private function searchGuestbook() {

        $arrHits = array();

        $arrWhere = array(
            "guestbook_post_name LIKE ?",
            "guestbook_post_text LIKE ?"
        );
        $arrParams = array(
            "%".$this->strSearchterm."%",
            "%".$this->strSearchterm."%"
        );

        $strWhere = "( ".implode(" OR ", $arrWhere). " ) ";

        //Query bauen
        $strQuery ="SELECT system_id
                      FROM "._dbprefix_."guestbook_post,
                           "._dbprefix_."system
                     WHERE ".$strWhere."
                       AND system_id  = guestbook_post_id";

        $arrPosts = $this->objDB->getPArray($strQuery, $arrParams);

        //Register found posts
        if(count($arrPosts) > 0) {

            foreach($arrPosts as $arrOnePost) {

                $objPost = new class_module_guestbook_post($arrOnePost["system_id"]);

                $objResult = new class_search_result();
                $objResult->setObjObject($objPost);
                $arrHits[] = $objResult;
            }
        }

        return $arrHits;
	}



}

