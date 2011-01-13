<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: class_modul_downloads_admin_xml.php 3405 2010-09-05 20:31:50Z sidler $                             *
********************************************************************************************************/


/**
 * The admin-xml-class of the module tags.
 * Handles all the main requests such as creating (and assigning) a tag, deleting a tag (aka the assignment)
 * and loading the list of tags.
 * Provides capabilities to search tags, too.
 *
 * @package modul_tags
 * @since 3.3.1.1
 */
class class_modul_tags_admin_xml extends class_admin implements interface_xml_admin {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $arrModul = array();
		$arrModul["name"] 			= "modul_tags";
		$arrModul["author"] 		= "sidler@mulchprod.de";
		$arrModul["moduleId"] 		= _tags_modul_id_;
		$arrModul["modul"]			= "tags";

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
        if($strAction == "saveTag")
            $strReturn = $this->actionSaveTag();
        else if($strAction == "tagList")
            $strReturn = $this->actionTagList();
        else if($strAction == "removeTag")
            $strReturn = $this->actionRemoveTag();
        else if($strAction == "getTagsByFilter")
            $strReturn = $this->actionGetTagsByFilter();


        return $strReturn;
	}

    /**
     * Creates a new tag (if not already existing) and assigns the tag to the passed systemrecord
     *
     * @return string
     */
    private function actionSaveTag() {
        $strReturn = "";
        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
            $strTagname = $this->getParam("tagname");
            $strSystemid = $this->getParam("systemid");
            $strAttribute = $this->getParam("attribute");


            $arrTags = explode(",", $strTagname);

            $bitError = false;
            foreach($arrTags as $strOneTag) {

                if(trim($strOneTag) == "")
                    continue;

                //load the tag itself
                $objTag = class_modul_tags_tag::getTagByName($strOneTag);
                if($objTag == null) {
                    $objTag = new class_modul_tags_tag();
                    $objTag->setStrName($strOneTag);
                    $objTag->updateObjectToDb();
                }

                //add the connection itself
                if(!$objTag->assignToSystemrecord($strSystemid, $strAttribute))
                    $bitError = true;

            }
            
            if(!$bitError)
                $strReturn .= "<success>assignment succeeded</success>";
            else
                $strReturn .= "<error>assignment failed</error>";

        }
	    else
	        $strReturn .= "<error>".xmlSafeString($this->getText("error_permissions"))."</error>";

        return $strReturn;
    }

    /**
     * Loads the lost of tags assigned to the passed systemrecord and renders them using the toolkit.
     *
     * @return string
     */
    private function actionTagList() {
        $strReturn = "";
        if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {

            $strSystemid = $this->getSystemid();
            $strAttribute = $this->getParam("attribute");

            $arrTags = class_modul_tags_tag::getTagsForSystemid($strSystemid, $strAttribute);

            $strReturn .=" <tags>";
            foreach($arrTags as $objOneTag) {

                $strReturn .= $this->objToolkit->getTagEntry($objOneTag->getStrName(), $objOneTag->getSystemid(), $strSystemid, $strAttribute);
            }

            $strReturn .= "</tags>";
        }
	    else
	        $strReturn .= "<error>".xmlSafeString($this->getText("error_permissions"))."</error>";

        return $strReturn;
    }

    /**
     * Removes a tag from the the systemrecord passed.
     * Please be aware of the fact, that this only deletes the assignment, not the tag itself.
     *
     * @return string
     */
    private function actionRemoveTag() {
        $strReturn = "";
        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
            $strTargetSystemid = $this->getParam("targetid");
            $strAttribute = $this->getParam("attribute");

            //load the tag itself
            $objTag = new class_modul_tags_tag($this->getSystemid());

            //add the connection itself
            if($objTag->removeFromSystemrecord($strTargetSystemid, $strAttribute))
                $strReturn .= "<success>assignment removed</success>";
            else
                $strReturn .= "<error>assignment removal failed</error>";
        }
	    else
	        $strReturn .= "<error>".xmlSafeString($this->getText("error_permissions"))."</error>";

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
    private function actionGetTagsByFilter() {
        $strReturn = "<tags>";
        if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {
             $strFilter = $this->getParam("filter");

             $arrTags = class_modul_tags_tag::getTagsByFilter($strFilter);
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

?>
