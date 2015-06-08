<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
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
 *
 * @module tags
 * @moduleId _tags_modul_id_
 */
class class_module_tags_admin_xml extends class_admin_controller implements interface_xml_admin {


    /**
     * @return string
     * @throws class_exception
     * @permissions right1
     */
    protected function actionAddFavorite() {

        $objTags = class_objectfactory::getInstance()->getObject($this->getSystemid());

        class_response_object::getInstance()->setStrResponseType(class_http_responsetypes::STR_TYPE_XML);
        $strError = "<message>".$this->getLang("favorite_save_error")."</message>";
        $strSuccess = "<message>".$this->getLang("favorite_save_success").": ".$objTags->getStrDisplayName()."</message>";
        $strExisting = "<message>".$this->getLang("favorite_save_remove").": ".$objTags->getStrDisplayName()."</message>";

        //already added before?
        if(count(class_module_tags_favorite::getAllFavoritesForUserAndTag($this->objSession->getUserID(), $this->getSystemid())) > 0) {
            $arrFavorites = class_module_tags_favorite::getAllFavoritesForUserAndTag($this->objSession->getUserID(), $this->getSystemid());
            foreach($arrFavorites as $objOneFavorite)
                $objOneFavorite->deleteObjectFromDatabase();

            return $strExisting;
        }

        $objFavorite = new class_module_tags_favorite();
        $objFavorite->setStrUserId($this->objSession->getUserID());
        $objFavorite->setStrTagId($objTags->getSystemid());

        if(!$objFavorite->updateObjectToDb())
            return $strError;
        else
            return $strSuccess;
    }


    /**
     * Creates a new tag (if not already existing) and assigns the tag to the passed system-record
     *
     * @return string
     * @permissions view
     */
    protected function actionSaveTag() {
        $strReturn = "";
        $strSystemid = $this->getParam("systemid");
        $strAttribute = $this->getParam("attribute");
        $arrTags = explode(",", $this->getParam("tagname"));

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

            class_carrier::getInstance()->getObjDB()->flushQueryCache();
        }

        if(!$bitError)
            $strReturn .= "<success>assignment succeeded</success>";
        else
            $strReturn .= "<error>assignment failed</error>";

        return $strReturn;
    }

    /**
     * Loads the list of tags assigned to the passed system-record and renders them using the toolkit.
     *
     * @return string
     * @permissions view
     */
    protected function actionTagList() {
        $strReturn = "";
        $strSystemid = $this->getSystemid();
        $strAttribute = $this->getParam("attribute");
        $bitDelete = $this->getParam("delete") != "false";

        $arrTags = class_module_tags_tag::getTagsForSystemid($strSystemid, $strAttribute);

        $strReturn .=" <tags>";
        foreach($arrTags as $objOneTag) {

            $strReturn .= $this->objToolkit->getTagEntry($objOneTag, $strSystemid, $strAttribute);
        }

        $strReturn .= "</tags>";

        return $strReturn;
    }

    /**
     * Removes a tag from the the system-record passed.
     * Please be aware of the fact, that this only deletes the assignment, not the tag itself.
     *
     * @return string
     * @permissions view
     */
    protected function actionRemoveTag() {
        $strReturn = "";
        $strTargetSystemid = $this->getParam("targetid");
        $strAttribute = $this->getParam("attribute");

        //load the tag itself
        $objTag = new class_module_tags_tag($this->getSystemid());

        //add the connection itself
        if($objTag->removeFromSystemrecord($strTargetSystemid, $strAttribute != '' ? $strAttribute : null))
            $strReturn .= "<success>assignment removed</success>";
        else
            $strReturn .= "<error>assignment removal failed</error>";

        return $strReturn;
    }

    /**
     * Generates the list of tags matching the passed filter-criteria.
     * Returned structure is json based.
     *
     * @return string
     * @permissions view
     */
    protected function actionGetTagsByFilter() {
        $arrReturn = array();
        $strFilter = $this->getParam("filter");

        $arrTags = class_module_tags_tag::getTagsByFilter($strFilter);
        foreach($arrTags as $objOneTag) {
            $arrReturn[] = $objOneTag->getStrName();
        }

        class_response_object::getInstance()->setStrResponseType(class_http_responsetypes::STR_TYPE_JSON);
        return json_encode($arrReturn);
    }

    /**
     * Generates the list of favorite tags for the current user.
     * Returned structure is json based.
     *
     * @return string
     * @permissions view
     */
    protected function actionGetFavoriteTags() {
        class_carrier::getInstance()->getObjSession()->setBitBlockDbUpdate(true);
        $arrReturn = array();

        $arrFavorites = class_module_tags_favorite::getAllFavoritesForUser(class_carrier::getInstance()->getObjSession()->getUserID(), 0, 10);

        foreach($arrFavorites as $objOneFavorite) {
            $arrReturn[] = array(
                "name" => $objOneFavorite->getStrDisplayName(),
                "onclick" => "location.href='".getLinkAdminHref("tags", "showAssignedRecords", "&systemid=".$objOneFavorite->getMappedTagSystemid(), false)."'",
                "url" => getLinkAdminHref("tags", "showAssignedRecords", "&systemid=".$objOneFavorite->getMappedTagSystemid(), false)
            );
        }

        class_response_object::getInstance()->setStrResponseType(class_http_responsetypes::STR_TYPE_JSON);
        return json_encode($arrReturn);
    }

}

