<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                             *
********************************************************************************************************/


/**
 * The admin-xml-class of the module tags.
 * Handles all the main requests such as creating (and assigning) a tag, deleting a tag (aka the assignment)
 * and loading the list of tags.
 * Provides capabilities to search tags, too.
 *
 * @package module_tags
 * @since 3.3.1.1
 * @author sidler@mulchprod.de
 */
class class_module_tags_admin_xml extends class_admin implements interface_xml_admin {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $arrModul = array();
		$arrModul["name"] 			= "module_tags";
		$arrModul["moduleId"] 		= _tags_modul_id_;
		$arrModul["modul"]			= "tags";

		//base class
		parent::__construct($arrModul);
	}


    /**
     * Creates a new tag (if not already existing) and assigns the tag to the passed system-record
     *
     * @return string
     */
    protected function actionSaveTag() {
        $strReturn = "";
        if($this->getObjModule()->rightEdit()) {
            $strTagname = $this->getParam("tagname");
            $strSystemid = $this->getParam("systemid");
            $strAttribute = $this->getParam("attribute");


            $arrTags = explode(",", $strTagname);

            $bitError = false;
            foreach($arrTags as $strOneTag) {

                if(trim($strOneTag) == "")
                    continue;

                //load the tag itself
                $objTag = class_module_tags_tag::getTagByName($strOneTag);
                if($objTag == null) {
                    $objTag = new class_module_tags_tag();
                    $objTag->setStrName($strOneTag);
                    $objTag->updateObjectToDb();
                }

                //add the connection itself
                if(!$objTag->assignToSystemrecord($strSystemid, $strAttribute))
                    $bitError = true;

                $this->objDB->flushQueryCache();
            }

            if(!$bitError)
                $strReturn .= "<success>assignment succeeded</success>";
            else
                $strReturn .= "<error>assignment failed</error>";

        }
	    else {
            header(class_http_statuscodes::$strSC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getText("commons_error_permissions"))."</error></message>";
        }

        return $strReturn;
    }

    /**
     * Loads the lost of tags assigned to the passed system-record and renders them using the toolkit.
     *
     * @return string
     */
    protected function actionTagList() {
        $strReturn = "";
        if($this->getObjModule()->rightView()) {

            $strSystemid = $this->getSystemid();
            $strAttribute = $this->getParam("attribute");

            $arrTags = class_module_tags_tag::getTagsForSystemid($strSystemid, $strAttribute);

            $strReturn .=" <tags>";
            foreach($arrTags as $objOneTag) {

                $strReturn .= $this->objToolkit->getTagEntry($objOneTag->getStrName(), $objOneTag->getSystemid(), $strSystemid, $strAttribute);
            }

            $strReturn .= "</tags>";
        }
	    else {
            header(class_http_statuscodes::$strSC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getText("commons_error_permissions"))."</error></message>";
        }

        return $strReturn;
    }

    /**
     * Removes a tag from the the system-record passed.
     * Please be aware of the fact, that this only deletes the assignment, not the tag itself.
     *
     * @return string
     */
    protected function actionRemoveTag() {
        $strReturn = "";
        if($this->getObjModule()->rightEdit()) {
            $strTargetSystemid = $this->getParam("targetid");
            $strAttribute = $this->getParam("attribute");

            //load the tag itself
            $objTag = new class_module_tags_tag($this->getSystemid());

            //add the connection itself
            if($objTag->removeFromSystemrecord($strTargetSystemid, $strAttribute))
                $strReturn .= "<success>assignment removed</success>";
            else
                $strReturn .= "<error>assignment removal failed</error>";
        }
	    else {
            header(class_http_statuscodes::$strSC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getText("commons_error_permissions"))."</error></message>";
        }

        return $strReturn;
    }

    /**
     * Generates the list of tags matching the passed filter-criteria.
     * Returned structure:
     * <tags>
     *   <tag>
     *      <name></name>
     *   </tag>
     * </tags>
     *
     * @return string
     */
    protected function actionGetTagsByFilter() {
        $strReturn = "<tags>";
        if($this->getObjModule()->rightView()) {
             $strFilter = $this->getParam("filter");

             $arrTags = class_module_tags_tag::getTagsByFilter($strFilter);
             foreach($arrTags as $objOneTag) {
                 $strReturn .="<tag>";
                 $strReturn .= "<name>".  xmlSafeString($objOneTag->getStrName())."</name>";
                 $strReturn .="</tag>";
             }

        }

        $strReturn .= "</tags>";

        return $strReturn;
    }

}

