<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

namespace Kajona\Tags\Admin;

use Kajona\System\Admin\AdminEvensimpler;
use Kajona\System\Admin\AdminInterface;
use Kajona\System\Admin\AdminSimple;
use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\ArraySectionIterator;
use Kajona\System\System\Carrier;
use Kajona\System\System\Exception;
use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\Link;
use Kajona\System\System\Model;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\Session;
use Kajona\System\System\SystemChangelog;
use Kajona\System\System\SystemModule;
use Kajona\Tags\System\TagsFavorite;
use Kajona\Tags\System\TagsTag;

/**
 * Admin-Part of the tags.
 * No classical functionality, rather a list of helper-methods, e.g. in order to
 * create the form to tag content.
 *
 * @package module_tags
 * @author sidler@mulchprod.de
 *
 * @objectList Kajona\Tags\System\TagsTag
 * @objectEdit Kajona\Tags\System\TagsTag
 *
 * @autoTestable list
 *
 * @module tags
 * @moduleId _tags_modul_id_
 */
class TagsAdmin extends AdminEvensimpler implements AdminInterface
{

    public function getOutputModuleNavi()
    {
        $arrReturn = array();
        $arrReturn[] = array("view", Link::getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        $arrReturn[] = array("right1", Link::getLinkAdmin($this->getArrModule("modul"), "listFavorites", "", $this->getLang("action_list_favorites"), "", "", true, "adminnavi"));

        return $arrReturn;
    }


    protected function getNewEntryAction($strListIdentifier, $bitDialog = false)
    {
        return "";
    }

    protected function renderAdditionalActions(Model $objListEntry)
    {
        if ($objListEntry instanceof TagsTag) {
            $arrButtons = array();
            $arrButtons[] = $this->objToolkit->listButton(
                Link::getLinkAdmin(
                    $this->getArrModule("modul"),
                    "showAssignedRecords",
                    "&systemid=".$objListEntry->getSystemid(),
                    $this->getLang("action_show_assigned_records"),
                    $this->getLang("action_show_assigned_records"),
                    "icon_folderActionOpen"
                )
            );

            if ($objListEntry->rightRight1()) {
                $strJs = "<script type='text/javascript'>
                require(['tags'], function(tags) {
                    tags.createFavoriteEnabledIcon = '".addslashes(AdminskinHelper::getAdminImage("icon_favorite", $this->getLang("tag_favorite_remove")))."';
                    tags.createFavoriteDisabledIcon = '".addslashes(AdminskinHelper::getAdminImage("icon_favoriteDisabled", $this->getLang("tag_favorite_add")))."';
                });</script>";

                $strImage = TagsFavorite::getAllFavoritesForUserAndTag($this->objSession->getUserID(), $objListEntry->getSystemid()) != null ?
                    AdminskinHelper::getAdminImage("icon_favorite", $this->getLang("tag_favorite_remove")) :
                    AdminskinHelper::getAdminImage("icon_favoriteDisabled", $this->getLang("tag_favorite_add"));

                $arrButtons[] = $strJs.$this->objToolkit->listButton("<a href=\"#\" onclick=\"require('tags').createFavorite('".$objListEntry->getSystemid()."', this); return false;\">".$strImage."</a>");
            }

            return $arrButtons;

        } else {
            return array();
        }
    }


    /**
     * @param ModelInterface|Model $objListEntry
     *
     * @return string
     */
    protected function renderDeleteAction(ModelInterface $objListEntry)
    {
        if ($objListEntry instanceof TagsFavorite) {
            if ($objListEntry->rightDelete()) {
                return $this->objToolkit->listDeleteButton(
                    $objListEntry->getStrDisplayName(),
                    $this->getLang("delete_question_fav", $objListEntry->getArrModule("modul")),
                    Link::getLinkAdminHref($objListEntry->getArrModule("modul"), "delete", "&systemid=".$objListEntry->getSystemid())
                );
            }
        } else {
            return parent::renderDeleteAction($objListEntry);
        }


        return "";
    }


    /**
     * @permissions view
     * @return string
     */
    protected function actionShowAssignedRecords()
    {
        //load tag
        $objTag = new TagsTag($this->getSystemid());
        //get assigned record-ids

        $objArraySectionIterator = new ArraySectionIterator($objTag->getIntAssignments());
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection($objTag->getArrAssignedRecords($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        return $this->renderList($objArraySectionIterator, false, "assignedTagList");
    }

    public function getActionIcons($objOneIterable, $strListIdentifier = "")
    {
        if ($strListIdentifier == "assignedTagList") {
            //call the original module to render the action-icons
            $objAdminInstance = SystemModule::getModuleByName($objOneIterable->getArrModule("modul"))->getAdminInstanceOfConcreteModule();
            if ($objAdminInstance != null && $objAdminInstance instanceof AdminSimple) {
                return $objAdminInstance->getActionIcons($objOneIterable);
            }
        }

        return parent::getActionIcons($objOneIterable, $strListIdentifier);
    }

    /**
     * Renders the generic tag-form, in case to be embedded from external.
     * Therefore, two params are evaluated:
     *  - the param "systemid"
     *  - the param "attribute"
     *
     * @return string
     * @permissions view
     */
    protected function actionGenericTagForm()
    {
        $this->setArrModuleEntry("template", "/folderview.tpl");
        return $this->getTagForm($this->getSystemid(), $this->getParam("attribute"));
    }

    /**
     * Generates a form to add tags to the passed systemid.
     * Since all functionality is performed using ajax, there's no page-reload when adding or removing tags.
     * Therefore the form-handling of existing forms can remain as is
     *
     * @param string $strTargetSystemid the systemid to tag
     * @param string $strAttribute additional info used to differ between tag-sets for a single systemid
     *
     * @return string
     * @permissions view
     */
    public function getTagForm($strTargetSystemid, $strAttribute = null)
    {
        $strTagContent = "";

        $strTagsWrapperId = generateSystemid();

        $strTagContent .= $this->objToolkit->formHeader(
            Link::getLinkAdminHref($this->getArrModule("modul"), "saveTags"), "", "", "require('tags').saveTag(document.getElementById('tagname').value+'', '".$strTargetSystemid."', '".$strAttribute."');return false;"
        );
        $strTagContent .= $this->objToolkit->formTextRow($this->getLang("tag_name_hint"));
        $strTagContent .= $this->objToolkit->formInputTagSelector("tagname", $this->getLang("form_tags_name"));
        $strTagContent .= $this->objToolkit->formInputSubmit($this->getLang("button_add"), $this->getLang("button_add"), "");
        $strTagContent .= $this->objToolkit->formClose();
        $strTagContent .= $this->objToolkit->setBrowserFocus("tagname");

        $strTagContent .= $this->objToolkit->getTaglistWrapper($strTagsWrapperId, $strTargetSystemid, $strAttribute);

        return $strTagContent;
    }

    protected function getOutputNaviEntry(ModelInterface $objInstance)
    {
        if ($objInstance instanceof TagsTag) {
            return Link::getLinkAdmin($this->getArrModule("modul"), "showAssignedRecords", "&systemid=".$objInstance->getSystemid(), $objInstance->getStrName());
        }

        return null;
    }


    /**
     * Renders the list of favorites created by the current user
     *
     * @return string
     * @autoTestable
     * @permissions right1
     */
    protected function actionListFavorites()
    {
        $objArraySectionIterator = new ArraySectionIterator(TagsFavorite::getNumberOfFavoritesForUser($this->objSession->getUserID()));
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(TagsFavorite::getAllFavoritesForUser($this->objSession->getUserID(), $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        return $this->renderList($objArraySectionIterator);
    }



    /**
     * @return string
     * @throws Exception
     * @permissions right1
     */
    protected function actionAddFavorite()
    {
        $objTags = Objectfactory::getInstance()->getObject($this->getSystemid());

        ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_XML);
        $strError = "<message>".$this->getLang("favorite_save_error")."</message>";
        $strSuccess = "<message>".$this->getLang("favorite_save_success").": ".$objTags->getStrDisplayName()."</message>";
        $strExisting = "<message>".$this->getLang("favorite_save_remove").": ".$objTags->getStrDisplayName()."</message>";

        //already added before?
        if (count(TagsFavorite::getAllFavoritesForUserAndTag($this->objSession->getUserID(), $this->getSystemid())) > 0) {
            $arrFavorites = TagsFavorite::getAllFavoritesForUserAndTag($this->objSession->getUserID(), $this->getSystemid());
            foreach ($arrFavorites as $objOneFavorite) {
                $objOneFavorite->deleteObjectFromDatabase();
            }

            return $strExisting;
        }

        $objFavorite = new TagsFavorite();
        $objFavorite->setStrUserId($this->objSession->getUserID());
        $objFavorite->setStrTagId($objTags->getSystemid());

        if (!$objFavorite->updateObjectToDb()) {
            return $strError;
        } else {
            return $strSuccess;
        }
    }


    /**
     * Creates a new tag (if not already existing) and assigns the tag to the passed system-record
     *
     * @return string
     * @permissions view
     */
    protected function actionSaveTag()
    {
        $strReturn = "";
        $strSystemid = $this->getParam("systemid");
        $strAttribute = $this->getParam("attribute");
        $arrTags = explode(",", $this->getParam("tagname"));

        $bitError = false;
        foreach ($arrTags as $strOneTag) {
            if (trim($strOneTag) == "") {
                continue;
            }

            //load the tag itself
            $objTag = TagsTag::getTagByName($strOneTag);
            if ($objTag == null) {
                $objTag = new TagsTag();
                $objTag->setStrName($strOneTag);
                $objTag->updateObjectToDb();
            }

            //add the connection itself
            if (!$objTag->assignToSystemrecord($strSystemid, $strAttribute)) {
                $bitError = true;
            }

            Carrier::getInstance()->getObjDB()->flushQueryCache();
        }

        if (!$bitError) {
            $strReturn .= "<success>assignment succeeded</success>";
        } else {
            $strReturn .= "<error>assignment failed</error>";
        }

        return $strReturn;
    }

    /**
     * Loads the list of tags assigned to the passed system-record and renders them using the toolkit.
     *
     * @return string
     * @permissions view
     */
    protected function actionTagList()
    {
        $strReturn = "";
        $strSystemid = $this->getSystemid();
        $strAttribute = $this->getParam("attribute");
        $bitDelete = $this->getParam("delete") != "false";

        $arrTags = TagsTag::getTagsForSystemid($strSystemid, $strAttribute);

        $strReturn .= " <tags>";
        foreach ($arrTags as $objOneTag) {
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
    protected function actionRemoveTag()
    {
        $strReturn = "";
        $strTargetSystemid = $this->getParam("targetid");
        $strAttribute = $this->getParam("attribute");

        //load the tag itself
        $objTag = new TagsTag($this->getSystemid());

        //add the connection itself
        if ($objTag->removeFromSystemrecord($strTargetSystemid, $strAttribute != '' ? $strAttribute : null)) {
            $strReturn .= "<success>assignment removed</success>";
        } else {
            $strReturn .= "<error>assignment removal failed</error>";
        }

        return $strReturn;
    }

    /**
     * Generates the list of tags matching the passed filter-criteria.
     * Returned structure is json based.
     *
     * @return string
     * @permissions view
     */
    protected function actionGetTagsByFilter()
    {
        $arrReturn = array();
        $strFilter = $this->getParam("filter");

        $arrTags = TagsTag::getTagsByFilter($strFilter);
        foreach ($arrTags as $objOneTag) {
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
    protected function actionGetFavoriteTags()
    {
        Session::getInstance()->sessionClose();
        Carrier::getInstance()->getObjSession()->setBitBlockDbUpdate(true);
        SystemChangelog::$bitChangelogEnabled = false;
        $arrReturn = array();

        $arrFavorites = TagsFavorite::getAllFavoritesForUser(Carrier::getInstance()->getObjSession()->getUserID(), 0, 10);

        foreach ($arrFavorites as $objOneFavorite) {
            $arrReturn[] = array(
                "name"    => $objOneFavorite->getStrDisplayName(),
                "onclick" => "location.href='".Link::getLinkAdminHref("tags", "showAssignedRecords", "&systemid=".$objOneFavorite->getMappedTagSystemid(), false)."'",
                "url"     => Link::getLinkAdminHref("tags", "showAssignedRecords", "&systemid=".$objOneFavorite->getMappedTagSystemid(), false)
            );
        }

        ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_JSON);
        return json_encode($arrReturn);
    }

}
