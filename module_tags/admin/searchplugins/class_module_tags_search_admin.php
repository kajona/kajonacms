<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                 *
********************************************************************************************************/


/**
 * Search plugin of the tags-module.
 *
 * @package module_tags
 * @autor sidler@mulchprod.de
 */
class class_module_tags_search_admin implements interface_search_plugin  {

    private $strSearchterm = "";

    /**
     * @var class_search_result
     */
    private $arrHits = array();

    /**
     * @var class_db
     */
    private $objDB;

    public function  __construct($strSearchterm) {
        $this->strSearchterm = $strSearchterm;
        $this->objDB = class_carrier::getInstance()->getObjDB();
    }


    public function doSearch() {

        $arrHits = array();

        if(class_module_system_module::getModuleByName("tags") != null) {

            $arrTags = class_module_tags_tag::getTagsByFilter($this->strSearchterm);

            foreach($arrTags as $objOneTag) {

                //add the tag itself
                $objResult = new class_search_result();
                $objResult->setObjObject($objOneTag);
                $arrHits[] = $objResult;

                //add referenced records
                $arrAssignedObjects = $objOneTag->getArrAssignedRecords();
                foreach($arrAssignedObjects as $objOneObject) {
                    $objResult = new class_search_result();
                    $objResult->setObjObject($objOneObject);
                    $arrHits[] = $objResult;
                }

            }


       }

        return $arrHits;
    }

}
