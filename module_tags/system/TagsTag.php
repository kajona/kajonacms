<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

namespace Kajona\Tags\System;

use Kajona\System\System\AdminListableInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\Link;
use Kajona\System\System\Model;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\OrmComparatorEnum;
use Kajona\System\System\OrmObjectlist;
use Kajona\System\System\OrmObjectlistOrderby;
use Kajona\System\System\OrmPropertyCondition;
use Kajona\System\System\SearchResultobjectInterface;
use Kajona\System\System\SystemSetting;

/**
 * Model-Class for tags.
 * There are two main purposes for this class:
 * - Representing the tag itself
 * - Acting as a wrapper to all tag-handling related methods such as assigning a tag
 *
 * @package module_tags
 * @author sidler@mulchprod.de
 * @since 3.4
 *
 * @targetTable tags_tag.tags_tag_id
 * @module tags
 * @moduleId _tags_modul_id_
 */
class TagsTag extends Model implements ModelInterface, AdminListableInterface, SearchResultobjectInterface {

    /**
     * @var string
     * @tableColumn tags_tag.tags_tag_name
     * @tableColumnDatatype char254
     * @listOrder
     *
     * @addSearchIndex
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldMandatory
     */
    private $strName;

    /**
     * @var int
     * @tableColumn tags_tag.tags_tag_private
     * @tableColumnDatatype int
     */
    private $intPrivate = 0;

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {

        if(SystemSetting::getConfigValue("_tags_defaultprivate_") == "true")
            $this->intPrivate = 1;

        parent::__construct($strSystemid);

    }

    /**
     * Return an on-lick link for the passed object.
     * This link is used by the backend-search for the autocomplete-field
     *
     * @see getLinkAdminHref()
     * @return mixed
     */
    public function getSearchAdminLinkForObject() {
        return Link::getLinkAdminHref($this->getArrModule("modul"), "showAssignedRecords", "&systemid=".$this->getSystemid());
    }


    /**
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrName();
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {
        return "icon_tag";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     * @return string
     */
    public function getStrAdditionalInfo() {
        $strReturn = $this->getIntAssignments()." ".$this->getLang("tag_assignments", "tags");
        if($this->getIntPrivate() == 1)
            $strReturn .= ", ".$this->getLang("form_tags_private", "tags");

        return $strReturn;
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     * @return string
     */
    public function getStrLongDescription() {
        return "";
    }


    /**
     * Returns the list of tags related with the systemid passed.
     * If given, an attribute used to specify the relation can be passed, too.
     *
     * @param string $strSystemid
     * @param string $strAttribute
     * @return TagsTag[]
     */
    public static function getTagsForSystemid($strSystemid, $strAttribute = null) {

        $objORM = new OrmObjectlist();
        $arrParams = array($strSystemid, Carrier::getInstance()->getObjSession()->getUserID());

        $strWhere = "";
        if($strAttribute != null) {
            $strWhere = "AND tags_attribute = ?";
            $arrParams[] = $strAttribute;
        }

        $strQuery = "SELECT DISTINCT(tags_tagid), tags_tag_name
                       FROM "._dbprefix_."tags_member,
                            "._dbprefix_."tags_tag,
                            "._dbprefix_."system
                      WHERE tags_systemid = ?
                        AND tags_tag_id = tags_tagid
                        AND tags_tagid = system_id
                        AND (tags_tag_private IS NULL OR tags_tag_private != 1 OR (tags_owner IS NULL OR tags_owner = '' OR tags_owner = ?))
                          ".$strWhere."
                          ".$objORM->getDeletedWhereRestriction()."
                   ORDER BY tags_tag_name ASC";

        $arrRows = Carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);
        $arrReturn = array();
        foreach($arrRows as $arrSingleRow) {
            $arrReturn[] = new TagsTag($arrSingleRow["tags_tagid"]);
        }

        return $arrReturn;
    }

    /**
     * Returns a tag for a given tag-name - if present. Otherwise null.
     *
     * @param string $strName
     * @return TagsTag
     */
    public static function getTagByName($strName) {
        $objORM = new OrmObjectlist();
        $objORM->addWhereRestriction(new OrmPropertyCondition("strName", OrmComparatorEnum::Like(), trim($strName)));
        return $objORM->getSingleObject(get_called_class());
    }

    /**
     * Creates a list of tags matching the passed filter.
     *
     * @param string $strFilter
     * @return TagsTag[]
     */
    public static function getTagsByFilter($strFilter) {
        $objORM = new OrmObjectlist();
        $objORM->addWhereRestriction(new OrmPropertyCondition("strName", OrmComparatorEnum::Like(), trim($strFilter."%")));
        $objORM->addOrderBy(new OrmObjectlistOrderby("tags_tag_name ASC"));
        return $objORM->getObjectList(get_called_class());
    }

    /**
     * Loads all tags having at least one assigned systemrecord
     * and being active
     * @return TagsTag[]
     */
    public static function getTagsWithAssignments() {
        $objORM = new OrmObjectlist();
        $strQuery = "SELECT DISTINCT(tags_tagid)
                       FROM "._dbprefix_."tags_member,
                            "._dbprefix_."tags_tag,
                            "._dbprefix_."system
                      WHERE tags_tag_id = tags_tagid
                        AND tags_tag_id = system_id
                        AND (tags_tag_private IS NULL OR tags_tag_private != 1 OR (tags_owner IS NULL OR tags_owner = '' OR tags_owner = ?))
                        ".$objORM->getDeletedWhereRestriction()."
                        AND system_status = 1";

        $arrRows = Carrier::getInstance()->getObjDB()->getPArray($strQuery, array(Carrier::getInstance()->getObjSession()->getUserID()));
        $arrReturn = array();
        foreach($arrRows as $arrSingleRow) {
            $arrReturn[] = new TagsTag($arrSingleRow["tags_tagid"]);
        }

        //search them by name
        usort(
            $arrReturn,
            function (TagsTag $objA, TagsTag $objB) {
                return strcmp($objA->getStrName(), $objB->getStrName());
            }
        );

        return $arrReturn;
    }

    /**
     * Loads the list of assignments.
     * Please note that this is only the raw array, not yet the object-structure.
     * By default, only active records are returned.
     * @return array
     */
    public function getListOfAssignments() {
        $objORM = new OrmObjectlist();
        $strQuery = "SELECT member.*
                       FROM "._dbprefix_."tags_member as member,
                            "._dbprefix_."system as system,
                            "._dbprefix_."tags_tag as tag
                      WHERE tags_tagid = ?
                        AND system.system_id = member.tags_systemid
                        AND member.tags_tagid = tag.tags_tag_id
                        ".$objORM->getDeletedWhereRestriction()."
                        AND (tags_tag_private IS NULL OR tags_tag_private != 1 OR (tags_owner IS NULL OR tags_owner = '' OR tags_owner = ?))
                        ";

        return $this->objDB->getPArray($strQuery, array($this->getSystemid(), $this->objSession->getUserID()));
    }

    /**
     * Counts the number of assignments
     *
     * @return int
     */
    public function getIntAssignments() {
        $objORM = new OrmObjectlist();
        $strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."tags_member as member,
                            "._dbprefix_."tags_tag as tag,
                            "._dbprefix_."system as system
                      WHERE member.tags_tagid = ?
                        AND member.tags_tagid = tag.tags_tag_id
                        ".$objORM->getDeletedWhereRestriction()."
                        AND system.system_id = member.tags_systemid
                        AND (tags_tag_private IS NULL OR tags_tag_private != 1 OR (tags_owner IS NULL OR tags_owner = '' OR tags_owner = ?)) ";

        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid(), $this->objSession->getUserID()));
        return $arrRow["COUNT(*)"];
    }

    /**
     * Loads a list of assigned records and creates the concrete instances.
     *
     * @param int $intStart
     * @param int $intEnd
     * @return \Kajona\System\System\Model[]
     */
    public function getArrAssignedRecords($intStart = null, $intEnd = null) {
        $objORM = new OrmObjectlist();
        $strQuery = "SELECT system.system_id
                       FROM "._dbprefix_."tags_member as member,
                            "._dbprefix_."tags_tag,
                            "._dbprefix_."system as system
                      WHERE tags_tagid = ?
                        AND tags_tagid = tags_tag_id
                        AND system.system_id = member.tags_systemid
                        ".$objORM->getDeletedWhereRestriction()."
                        AND (tags_tag_private IS NULL OR tags_tag_private != 1 OR (tags_owner IS NULL OR tags_owner = '' OR tags_owner = ?))
                   ORDER BY system.system_id ASC";

        $arrRecords = $this->objDB->getPArray($strQuery, array($this->getSystemid(), $this->objSession->getUserID()), $intStart, $intEnd);

        $arrReturn = array();
        foreach($arrRecords as $arrOneRecord)
            $arrReturn[] = Objectfactory::getInstance()->getObject($arrOneRecord["system_id"]);

        return $arrReturn;
    }

    /**
     * Connects the current tag with a systemid (and attribute, if given).
     * If the assignment already exists, nothing is done.
     *
     * @param string $strTargetSystemid
     * @param string $strAttribute
     * @return bool
     */
    public function assignToSystemrecord($strTargetSystemid, $strAttribute = null) {
        if($strAttribute == null)
            $strAttribute = "";

        $arrParams = array($strTargetSystemid, $this->getSystemid(), $strAttribute);

        $this->objDB->flushQueryCache();

        $strPrivate = "";
        if($this->getIntPrivate() == 1) {
            $strPrivate = "AND tags_owner = ?";
            $arrParams[] = $this->objSession->getUserID();
        }

        //check if not already set
        $strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."tags_member
                      WHERE tags_systemid= ?
                        AND tags_tagid = ?
                        AND tags_attribute = ?
                        ".$strPrivate;
        $arrRow = $this->objDB->getPRow($strQuery, $arrParams, 0, false);
        if($arrRow["COUNT(*)"] != 0)
            return true;

        $strQuery = "INSERT INTO "._dbprefix_."tags_member
                      (tags_memberid, tags_systemid, tags_tagid, tags_attribute, tags_owner) VALUES
                      (?, ?, ?, ?, ?)";

        $bitReturn = $this->objDB->_pQuery($strQuery, array(generateSystemid(), $strTargetSystemid, $this->getSystemid(), $strAttribute, $this->objSession->getUserID()));

        //trigger an object update
        Objectfactory::getInstance()->getObject($strTargetSystemid)->updateObjectToDb();

        return $bitReturn;
    }

    /**
     * Deletes an assignment of the current tag from the database.
     *
     * @param string $strTargetSystemid
     * @param string $strAttribute
     * @return bool
     */
    public function removeFromSystemrecord($strTargetSystemid, $strAttribute = null) {

        $arrParams = array();
        $arrParams[] = $strTargetSystemid;

        if($strAttribute != null)
            $arrParams[] = $strAttribute;

        $arrParams[] = $this->getSystemid();

        $strPrivate = "";
        if($this->getIntPrivate() == 1) {
            $strPrivate = "AND (tags_owner IS NULL OR tags_owner = '' OR tags_owner = ?)";
            $arrParams[] = $this->objSession->getUserID();
        }

        $strQuery = "DELETE FROM "._dbprefix_."tags_member
                           WHERE tags_systemid = ?
                             ".($strAttribute != null ? "AND tags_attribute = ?" : "")."
                             AND tags_tagid = ?
                             ".$strPrivate;

        $bitReturn = $this->objDB->_pQuery($strQuery, $arrParams);

        //trigger an object update
        Objectfactory::getInstance()->getObject($strTargetSystemid)->updateObjectToDb();

        return $bitReturn;
    }


    /**
     * @param string $strNewPrevid
     * @param bool $bitChangeTitle
     *
     * @return bool
     */
    public function copyObject($strNewPrevid = "", $bitChangeTitle = true, $bitCopyChilds = true) {

        $strPrefix = $this->getStrName()."_";
        $intCount = 1;

        $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."tags_tag WHERE tags_tag_name = ?";
        $arrRow = $this->objDB->getPRow($strQuery, array($strPrefix.$intCount));

        while($arrRow["COUNT(*)"] > 0) {
            $arrRow = $this->objDB->getPRow($strQuery, array($strPrefix.++$intCount));
        }

        $this->setStrName($strPrefix.$intCount);

        //save assigned records
        $arrRecords = $this->getListOfAssignments();

        parent::copyObject($strNewPrevid, $bitChangeTitle, $bitCopyChilds);

        //copy the tag assignments
        foreach($arrRecords as $arrOneRecord) {
            $this->assignToSystemrecord($arrOneRecord["tags_systemid"]);
        }

        return true;
    }


    /**
     * @return string
     */
    public function getStrName() {
        return $this->strName;
    }

    /**
     * @param string $strName
     * @return void
     */
    public function setStrName($strName) {
        $this->strName = trim($strName);
    }

    /**
     * @param int $intPrivate
     * @return void
     */
    public function setIntPrivate($intPrivate) {
        $this->intPrivate = $intPrivate;
    }

    /**
     * @return int
     */
    public function getIntPrivate() {
        return $this->intPrivate;
    }


}
