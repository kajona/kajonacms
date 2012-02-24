<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
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
 */
class class_module_tags_favorite extends class_model implements interface_model, interface_admin_listable  {

    private $strTagId;
    private $strUserId;

    private $objTag = null;

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {

        $this->setArrModuleEntry("modul", "tags");
        $this->setArrModuleEntry("moduleId", _tags_modul_id_);

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
        return "icon_favorite.gif";
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
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array(_dbprefix_."tags_favorite" => "tags_fav_id");
    }

    /**
     * Initialises the current object, if a systemid was given
     *
     */
    protected function initObjectInternal() {
        $strQuery = "SELECT *
		   			 FROM "._dbprefix_."tags_favorite,
		   			      "._dbprefix_."system,
		   			      "._dbprefix_."system_right
					 WHERE tags_fav_id = ?
					  AND tags_fav_id = system_id
					  AND system_id = right_id ";
        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));
        $this->setArrInitRow($arrRow);
        $this->setStrTagId($arrRow["tags_fav_tagid"]);
        $this->setStrUserId($arrRow["tags_fav_userid"]);
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    protected function updateStateToDb() {

        $strQuery = "UPDATE "._dbprefix_."tags_favorite SET
                    	    tags_fav_tagid = ?,
                    	    tags_fav_userid = ?
					  WHERE tags_fav_id = ?";
        return $this->objDB->_pQuery($strQuery, array($this->getStrTagId(), $this->getStrUserId(), $this->getSystemid()));
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

        $strQuery = "SELECT tags_fav_id
                       FROM "._dbprefix_."tags_favorite,
                            "._dbprefix_."tags_tag,
                            "._dbprefix_."system
                     WHERE tags_fav_id = system_id
                       AND tags_fav_userid = ?
                       AND tags_fav_tagid = tags_tag_id
                       AND tags_fav_tagid = ?
                  ORDER BY tags_tag_name ASC";

        if($intStart !== null && $intEnd !== null)
            $arrRows = class_carrier::getInstance()->getObjDB()->getPArraySection($strQuery, array($strUserid, $strTagId), $intStart, $intEnd);
        else
            $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strUserid, $strTagId));
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

        $strQuery = "SELECT tags_fav_id
                       FROM "._dbprefix_."tags_favorite,
                            "._dbprefix_."tags_tag,
                            "._dbprefix_."system
                     WHERE tags_fav_id = system_id
                       AND tags_fav_tagid = ?
                       AND tags_fav_tagid = tags_tag_id
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

        $strQuery = "SELECT tags_fav_id
                       FROM "._dbprefix_."tags_favorite,
                            "._dbprefix_."tags_tag,
                            "._dbprefix_."system
                     WHERE tags_fav_id = system_id
                       AND tags_fav_userid = ?
                       AND tags_fav_tagid = tags_tag_id
                  ORDER BY tags_tag_name ASC";

        if($intStart !== null && $intEnd !== null)
            $arrRows = class_carrier::getInstance()->getObjDB()->getPArraySection($strQuery, array($strUserid), $intStart, $intEnd);
        else
            $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strUserid));
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

        $strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."tags_favorite
                      WHERE tags_fav_userid = ?";

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strUserid));
        return $arrRow["COUNT(*)"];
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
