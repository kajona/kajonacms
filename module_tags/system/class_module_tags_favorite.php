<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

/**
 * Model-Class for tags-favorites
 *
 * @package module_tags
 * @author sidler@mulchprod.de
 * @since 4.0
 *
 * @targetTable tags_favorite.tags_fav_id
 *
 * @module tags
 * @moduleId _tags_modul_id_
 */
class class_module_tags_favorite extends \Kajona\System\System\Model implements \Kajona\System\System\ModelInterface, interface_admin_listable  {

    /**
     * @var string
     * @tableColumn tags_favorite.tags_fav_tagid
     * @tableColumnDatatype char20
     */
    private $strTagId;

    /**
     * @var string
     * @tableColumn tags_favorite.tags_fav_userid
     * @tableColumnDatatype char20
     */
    private $strUserId;

    private $objTag = null;

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        parent::__construct($strSystemid);
        $this->objTag = new class_module_tags_tag($this->getStrTagId());
    }

    public function getStrDisplayName() {
        return $this->objTag->getStrDisplayName();
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {
        return "icon_favorite";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     * @return string
     */
    public function getStrAdditionalInfo() {
        return "";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     * @return string
     */
    public function getStrLongDescription() {
        return "";
    }

    /**
     * Returns a list of tags available
     *
     * @param string $strUserid
     * @param $strTagId
     * @param int|null $intStart
     * @param int|null $intEnd
     *
     * @return class_module_tags_favorite[]
     */
    public static function getAllFavoritesForUserAndTag($strUserid, $strTagId, $intStart = null, $intEnd = null) {

        $objORM = new class_orm_objectlist();
        $strQuery = "SELECT tags_fav_id
                       FROM "._dbprefix_."tags_favorite,
                            "._dbprefix_."tags_tag,
                            "._dbprefix_."system
                     WHERE tags_fav_id = system_id
                       AND tags_fav_userid = ?
                       AND tags_fav_tagid = tags_tag_id
                       AND tags_fav_tagid = ?
                       ".$objORM->getDeletedWhereRestriction()."
                  ORDER BY tags_tag_name ASC";

        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strUserid, $strTagId), $intStart, $intEnd);
        $arrReturn = array();
        foreach($arrRows as $arrSingleRow) {
            $arrReturn[] = new class_module_tags_favorite($arrSingleRow["tags_fav_id"]);
        }

        return $arrReturn;
    }

    /**
     * Returns a list of tags available
     *
     * @param $strTagid
     *
     * @return class_module_tags_favorite[]
     */
    public static function getAllFavoritesForTag($strTagid) {
        $objORM = new class_orm_objectlist();
        $strQuery = "SELECT tags_fav_id
                       FROM "._dbprefix_."tags_favorite,
                            "._dbprefix_."tags_tag,
                            "._dbprefix_."system
                     WHERE tags_fav_id = system_id
                       AND tags_fav_tagid = ?
                       AND tags_fav_tagid = tags_tag_id
                       ".$objORM->getDeletedWhereRestriction()."
                  ORDER BY tags_tag_name ASC";

        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strTagid));
        $arrReturn = array();
        foreach($arrRows as $arrSingleRow) {
            $arrReturn[] = new class_module_tags_favorite($arrSingleRow["tags_fav_id"]);
        }

        return $arrReturn;
    }

    /**
     * Returns a list of tags available
     *
     * @param string $strUserid
     * @param int|null $intStart
     * @param int|null $intEnd
     *
     * @return class_module_tags_favorite[]
     */
    public static function getAllFavoritesForUser($strUserid, $intStart = null, $intEnd = null) {
        $objORM = new class_orm_objectlist();
        $strQuery = "SELECT tags_fav_id
                       FROM "._dbprefix_."tags_favorite,
                            "._dbprefix_."tags_tag,
                            "._dbprefix_."system
                     WHERE tags_fav_id = system_id
                       AND tags_fav_userid = ?
                       AND tags_fav_tagid = tags_tag_id
                       ".$objORM->getDeletedWhereRestriction()."
                  ORDER BY tags_tag_name ASC";

        $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strUserid), $intStart, $intEnd);
        $arrReturn = array();
        foreach($arrRows as $arrSingleRow) {
            $arrReturn[] = new class_module_tags_favorite($arrSingleRow["tags_fav_id"]);
        }

        return $arrReturn;
    }

    /**
     * Returns the number of tags available
     *
     * @param string $strUserid
     *
     * @return int
     */
    public static function getNumberOfFavoritesForUser($strUserid) {

        $objORM = new class_orm_objectlist();
        $strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."tags_favorite,
                            "._dbprefix_."tags_tag,
                            "._dbprefix_."system
                     WHERE tags_fav_id = system_id
                       AND tags_fav_userid = ?
                       AND tags_fav_tagid = tags_tag_id
                       ".$objORM->getDeletedWhereRestriction()."
                  ";

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strUserid));
        return $arrRow["COUNT(*)"];
    }

    /**
     * Returns the systemid of the tag mapped by the current favorite
     * @return string
     */
    public function getMappedTagSystemid() {
        return $this->objTag->getSystemid();
    }

    public function rightEdit() {
        return false;
    }


    public function setStrTagId($strTagId) {
        $this->strTagId = $strTagId;
    }

    public function getStrTagId() {
        return $this->strTagId;
    }

    public function setStrUserId($strUserId) {
        $this->strUserId = $strUserId;
    }

    public function getStrUserId() {
        return $this->strUserId;
    }


}
