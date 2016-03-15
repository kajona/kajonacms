<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                             *
********************************************************************************************************/

namespace Kajona\Tags\Admin;

use Kajona\System\Admin\AdminController;
use Kajona\System\Admin\XmlAdminInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\Exception;
use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\Link;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\Session;
use Kajona\System\System\SystemChangelog;
use Kajona\Tags\System\TagsFavorite;
use Kajona\Tags\System\TagsTag;

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
class TagsAdminXml extends AdminController implements XmlAdminInterface {


    /**
     * @return string
     * @throws Exception
     * @permissions right1
     */
    protected function actionAddFavorite() {

        $objTags = Objectfactory::getInstance()->getObject($this->getSystemid());

        ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_XML);
        $strError = "<message>".$this->getLang("favorite_save_error")."</message>";
        $strSuccess = "<message>".$this->getLang("favorite_save_success").": ".$objTags->getStrDisplayName()."</message>";
        $strExisting = "<message>".$this->getLang("favorite_save_remove").": ".$objTags->getStrDisplayName()."</message>";

        //already added before?
        if(count(TagsFavorite::getAllFavoritesForUserAndTag($this->objSession->getUserID(), $this->getSystemid())) > 0) {
            $arrFavorites = TagsFavorite::getAllFavoritesForUserAndTag($this->objSession->getUserID(), $this->getSystemid());
            foreach($arrFavorites as $objOneFavorite)
                $objOneFavorite->deleteObjectFromDatabase();

            return $strExisting;
        }

        $objFavorite = new TagsFavorite();
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
            $objTag = TagsTag::getTagByName($strOneTag);
            if($objTag == null) {
                $objTag = new TagsTag();
                $objTag->setStrName($strOneTag);
                $objTag->updateObjectToDb();
            }

            //add the connection itself
            if(!$objTag->assignToSystemrecord($strSystemid, $strAttribute))
                $bitError = true;

            Carrier::getInstance()->getObjDB()->flushQueryCache();
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

        $arrTags = TagsTag::getTagsForSystemid($strSystemid, $strAttribute);

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
        $objTag = new TagsTag($this->getSystemid());

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

        $arrTags = TagsTag::getTagsByFilter($strFilter);
        foreach($arrTags as $objOneTag) {
            $arrReturn[] = $objOneTag->getStrName();
        }

        ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_JSON);
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
        Session::getInstance()->sessionClose();
        Carrier::getInstance()->getObjSession()->setBitBlockDbUpdate(true);
        SystemChangelog::$bitChangelogEnabled = false;
        $arrReturn = array();

        $arrFavorites = TagsFavorite::getAllFavoritesForUser(Carrier::getInstance()->getObjSession()->getUserID(), 0, 10);

        foreach($arrFavorites as $objOneFavorite) {
            $arrReturn[] = array(
                "name" => $objOneFavorite->getStrDisplayName(),
                "onclick" => "location.href='".Link::getLinkAdminHref("tags", "showAssignedRecords", "&systemid=".$objOneFavorite->getMappedTagSystemid(), false)."'",
                "url" => Link::getLinkAdminHref("tags", "showAssignedRecords", "&systemid=".$objOneFavorite->getMappedTagSystemid(), false)
            );
        }

        ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_JSON);
        return json_encode($arrReturn);
    }

}

