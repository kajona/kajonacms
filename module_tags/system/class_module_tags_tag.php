<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

/**
 * Model-Class for tags.
 * There are two main purposes for this class:
 * - Representing the tag itself
 * - Acting as a wrapper to all tag-handling related methods such as assigning a tag
 *
 *
 * @package module_tags
 * @author sidler@mulchprod.de
 * @since 3.4
 *
 * @targetTable tags_tag.tags_tag_id
 */
class class_module_tags_tag extends class_model implements interface_model, interface_sortable_rating, interface_recorddeleted_listener, interface_admin_listable, interface_recordcopied_listener {

    /**
     * @var string
     * @tableColumn tags_tag_name
     */
    private $strName;

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {

        $this->setArrModuleEntry("modul", "tags");
        $this->setArrModuleEntry("moduleId", _tags_modul_id_);

        parent::__construct($strSystemid);

    }

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
        return "icon_tag.gif";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     * @return string
     */
    public function getStrAdditionalInfo() {
        return $this->getIntAssignments()." ".$this->getLang("tag_assignments", "tags");
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     * @return string
     */
    public function getStrLongDescription() {
        return "";
    }

    /**
     * Deletes the tag with the given systemid from the system
     *
     * @return bool
     */
    protected function deleteObjectInternal() {

        //delete matching favorites
        $arrFavorites = class_module_tags_favorite::getAllFavoritesForTag($this->getSystemid());
        foreach($arrFavorites as $objOneFavorite) {
            $objOneFavorite->deleteObject();
        }

        //delete memberships
        $strQuery1 = "DELETE FROM "._dbprefix_."tags_member WHERE tags_tagid=?";
        //delete the record itself
        if($this->objDB->_pQuery($strQuery1, array($this->getSystemid())))
            return parent::deleteObjectInternal();

        return false;
    }

    /**
     * Returns a list of tags available
     *
     * @param int|null $intStart
     * @param int|null $intEnd
     * @return class_module_tags_tag[]
     */
    public static function getAllTags($intStart = null, $intEnd = null) {

        $strQuery = "SELECT tags_tag_id
                       FROM "._dbprefix_."tags_tag,
                            "._dbprefix_."system,
                            "._dbprefix_."system_right
                     WHERE tags_tag_id = system_id
                       AND system_id = right_id
                   ORDER BY tags_tag_name ASC";

        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array(), $intStart, $intEnd);
        $arrReturn = array();
        foreach($arrRows as $arrSingleRow) {
            $arrReturn[] = new class_module_tags_tag($arrSingleRow["tags_tag_id"]);
        }

        return $arrReturn;
    }

    /**
     * Returns the number of tags available
     *
     * @return int
     */
    public static function getNumberOfTags() {

        $strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."tags_tag";

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array());
        return $arrRow["COUNT(*)"];
    }

    /**
     * Returns the list of tags related with the systemid passed.
     * If given, an attribute used to specify the relation can be passed, too.
     *
     * @param string $strSystemid
     * @param string $strAttribute
     * @return class_module_tags_tag[]
     */
    public static function getTagsForSystemid($strSystemid, $strAttribute = null) {

        $arrParams = array($strSystemid);

        $strWhere = "";
        if($strAttribute != null) {
            $strWhere = "AND tags_attribute = ?";
            $arrParams[] = $strAttribute;
        }

        $strQuery = "SELECT DISTINCT(tags_tagid)
                       FROM "._dbprefix_."tags_member,
                            "._dbprefix_."tags_tag
                      WHERE tags_systemid = ?
                        AND tags_tag_id = tags_tagid
                          ".$strWhere."
                   ORDER BY tags_tag_name ASC";

        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);
        $arrReturn = array();
        foreach($arrRows as $arrSingleRow) {
            $arrReturn[] = new class_module_tags_tag($arrSingleRow["tags_tagid"]);
        }

        return $arrReturn;
    }

    /**
     * Returns a tag for a given tag-name - if present. Otherwise null.
     *
     * @param string $strName
     * @return class_module_tags_tag
     */
    public static function getTagByName($strName) {
        $strQuery = "SELECT tags_tag_id
                       FROM "._dbprefix_."tags_tag
                      WHERE tags_tag_name LIKE ?";
        $arrCols = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array(trim($strName)));
        if(isset($arrCols["tags_tag_id"]) && validateSystemid($arrCols["tags_tag_id"]))
            return new class_module_tags_tag($arrCols["tags_tag_id"]);
        else
            return null;
    }

    /**
     * Creates a list of tags matching the passed filter.
     *
     * @param string $strFilter
     * @return class_module_tags_tag[]
     */
    public static function getTagsByFilter($strFilter) {
        $strQuery = "SELECT tags_tag_id
                       FROM "._dbprefix_."tags_tag
                      WHERE tags_tag_name LIKE ?
                   ORDER BY tags_tag_name ASC";

        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strFilter."%"));
        $arrReturn = array();
        foreach($arrRows as $arrSingleRow) {
            $arrReturn[] = new class_module_tags_tag($arrSingleRow["tags_tag_id"]);
        }

        return $arrReturn;
    }

    /**
     * Loads all tags having at least one assigned systemrecord
     * and being active
     * @return class_module_tags_tag[]
     */
    public static function getTagsWithAssignments() {
        $strQuery = "SELECT DISTINCT(tags_tagid)
                       FROM "._dbprefix_."tags_member,
                            "._dbprefix_."tags_tag,
                            "._dbprefix_."system
                      WHERE tags_tag_id = tags_tagid
                        AND tags_tag_id = system_id
                        AND system_status = 1";

        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array());
        $arrReturn = array();
        foreach($arrRows as $arrSingleRow) {
            $arrReturn[] = new class_module_tags_tag($arrSingleRow["tags_tagid"]);
        }

        //search them by name
        usort($arrReturn, function(class_module_tags_tag $objA, class_module_tags_tag $objB) {
            return strcmp($objA->getStrName(), $objB->getStrName());
        });

        return $arrReturn;
    }

    /**
     * Loads the list of assignments.
     * Please note that this is only the raw array, not yet the object-structure.
     * By default, only active records are returned.
     * @return array
     */
    public function getListOfAssignments() {
        $strQuery = "SELECT member.*
                       FROM "._dbprefix_."tags_member as member,
                            "._dbprefix_."system as system
                      WHERE tags_tagid = ?
                        AND system.system_id = member.tags_systemid
                        AND system.system_status = 1";

        return $this->objDB->getPArray($strQuery, array($this->getSystemid()));
    }

    /**
     * Counts the number of assignments
     *
     * @return int
     */
    public function getIntAssignments() {
        $strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."tags_member as member,
                            "._dbprefix_."system as system
                      WHERE tags_tagid = ?
                        AND system.system_id = member.tags_systemid";

        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));
        return $arrRow["COUNT(*)"];
    }

    /**
     * Loads a list of assigned records and creates the concrete instances.
     *
     * @param $intStart
     * @param $intEnd
     * @return class_model[]
     */
    public function getArrAssignedRecords($intStart = null, $intEnd = null) {
        $strQuery = "SELECT system.system_id
                       FROM "._dbprefix_."tags_member as member,
                            "._dbprefix_."system as system
                      WHERE tags_tagid = ?
                        AND system.system_id = member.tags_systemid
                   ORDER BY system_comment ASC";

        $arrRecords = $this->objDB->getPArray($strQuery, array($this->getSystemid()), $intStart, $intEnd);

        $arrReturn = array();
        foreach($arrRecords as $arrOneRecord)
            $arrReturn[] = class_objectfactory::getInstance()->getObject($arrOneRecord["system_id"]);

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

        $this->objDB->flushQueryCache();

        //check of not already set
        $strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."tags_member
                      WHERE tags_systemid= ?
                        AND tags_tagid = ?
                        AND tags_attribute = ?";
        $arrRow = $this->objDB->getPRow($strQuery, array($strTargetSystemid, $this->getSystemid(), $strAttribute), 0, false);
        if($arrRow["COUNT(*)"] != 0)
            return true;

        $strQuery = "INSERT INTO "._dbprefix_."tags_member
                      (tags_systemid, tags_tagid, tags_attribute) VALUES
                      (?, ?, ?)";

        return $this->objDB->_pQuery($strQuery, array($strTargetSystemid, $this->getSystemid(), $strAttribute));
    }

    /**
     * Deletes an assignment of the current tag from the database.
     *
     * @param string $strTargetSystemid
     * @param string $strAttribute
     * @return bool
     */
    public function removeFromSystemrecord($strTargetSystemid, $strAttribute = null) {

        $strQuery = "DELETE FROM "._dbprefix_."tags_member
                           WHERE tags_systemid = ?
                             AND tags_attribute = ?
                             AND tags_tagid = ?";

        return $this->objDB->_pQuery($strQuery, array($strTargetSystemid, $strAttribute, $this->getSystemid()));
    }

    /**
     * Searches for tags assigned to the systemid to be deleted.
     *
     * Called whenever a records was deleted using the common methods.
     * Implement this method to be notified when a record is deleted, e.g. to to additional cleanups afterwards.
     * There's no need to register the listener, this is done automatically.
     *
     * Make sure to return a matching boolean-value, otherwise the transaction may be rolled back.
     *
     * @param $strSystemid
     * @param string $strSourceClass
     *
     * @return bool
     */
    public function handleRecordDeletedEvent($strSystemid, $strSourceClass) {
        if($strSourceClass == "class_module_tags_tag" || class_module_system_module::getModuleByName("tags") == null)
            return true;

        //delete memberships. Fire a plain query, faster then searching.
        $strQuery = "DELETE FROM "._dbprefix_."tags_member WHERE tags_systemid=?";
        $bitReturn = $this->objDB->_pQuery($strQuery, array($strSystemid));

        return $bitReturn;
    }

    /**
     * Called whenever a record was copied.
     * copies the tag-assignments from the source object to the target object
     *
     * @param $strOldSystemid
     * @param $strNewSystemid
     *
     * @internal param $strSystemid
     * @internal param $intNewStatus
     * @return bool
     */
    public function handleRecordCopiedEvent($strOldSystemid, $strNewSystemid) {
        $strQuery = "SELECT tags_tagid, tags_attribute
                       FROM "._dbprefix_."tags_member
                      WHERE tags_systemid = ?";
        $arrRows = $this->objDB->getPArray($strQuery, array($strOldSystemid));
        foreach($arrRows as $arrSingleRow) {
            $strQuery = "INSERT INTO "._dbprefix_."tags_member (tags_tagid, tags_systemid, tags_attribute) VALUES (?, ?, ?)";
            $this->objDB->_pQuery($strQuery, array($arrSingleRow["tags_tagid"], $strNewSystemid, $arrSingleRow["tags_attribute"]));
        }

        return true;
    }


    /**
     * @return mixed
     * @fieldType text
     * @fieldMandatory
     */
    public function getStrName() {
        return $this->strName;
    }

    public function setStrName($strName) {
        $this->strName = trim($strName);
    }


}
