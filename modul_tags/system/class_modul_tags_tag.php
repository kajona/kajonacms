<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_modul_postacomment_post.php 3290 2010-04-24 13:12:31Z sidler $                                    *
********************************************************************************************************/

/**
 * Model-Class for tags.
 * There are two main purposes for this class:
 * - Representing the tag itself
 * - Acting as a wrapper to all tag-handling related methods such as assigning a tag
 *
 *
 * @package modul_tags
 */
class class_modul_tags_tag extends class_model implements interface_model, interface_sortable_rating  {

    private $strName;  

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_tags";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _tags_modul_id_;
		$arrModul["table"]       		= _dbprefix_."tags_tag";
		$arrModul["modul"]				= "tags";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }

    /**
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array(_dbprefix_."tags_tag" => "tags_tag_id");
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "tag ".$this->getStrName();
    }

    /**
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {
        $strQuery = "SELECT * 
		   			 FROM ".$this->arrModule["table"]."
					 WHERE tags_tag_id = '".$this->getSystemid()."'";
        $arrRow = $this->objDB->getRow($strQuery);
        $this->setStrName($arrRow["tags_tag_name"]);
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    protected function updateStateToDb() {
        
        $strQuery = "UPDATE ".$this->arrModule["table"]." SET 
                    	    tags_tag_name = '".dbsafeString($this->getStrName())."'
					  WHERE tags_tag_id = '".dbsafeString($this->getSystemid())."'";
        return $this->objDB->_query($strQuery);
    }

    
    /**
     * Deletes the tag with the given systemid from the system
     *
     * @param string $strSystemid
     * @return bool
     */
    public function deleteTag() {
        class_logger::getInstance()->addLogRow("deleted ".$this->getObjectDescription(), class_logger::$levelInfo);
        $objDB = class_carrier::getInstance()->getObjDB();
        //start a tx
		$objDB->transactionBegin();
		$bitCommit = false;

        //delete memberships
        $strQuery1 = "DELETE FROM "._dbprefix_."tags_member WHERE tags_tagid='".dbsafeString($this->getSystemid())."'";

        //delete the record itself
        $strQuery2 = "DELETE FROM "._dbprefix_."tags_tag WHERE tags_tag_id='".dbsafeString($this->getSystemid())."'";
	    if($this->objDB->_query($strQuery1) && $this->objDB->_query($strQuery2))    {
	        if($this->deleteSystemRecord($this->getSystemid())) {
	            $bitCommit = true;
	        }
	    }

	    //End tx
		if($bitCommit) {
			$objDB->transactionCommit();
			return true;
		}
		else {
			$objDB->transactionRollback();
			return false;
		}
    }

    /**
     * Returns a list of tags available
     *
     * @param int $intStart
     * @param int $intEnd
     * @return class_modul_tags_tag
     */
    public static function getAllTags($intStart = null, $intEnd = null) {

        $strQuery = "SELECT tags_tag_id
                       FROM "._dbprefix_."tags_tag
                   ORDER BY tags_tag_name ASC";

        if($intStart != null && $intEnd != null)
            $arrRows = class_carrier::getInstance()->getObjDB()->getArraySection($strQuery, $intStart, $intEnd);
        else
            $arrRows = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
        $arrReturn = array();
        foreach($arrRows as $arrSingleRow) {
            $arrReturn[] = new class_modul_tags_tag($arrSingleRow["tags_tag_id"]);
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
                       FROM "._dbprefix_."tags_tag
                   ORDER BY tags_tag_name ASC";

        $arrRow = class_carrier::getInstance()->getObjDB()->getRow($strQuery);
        return $arrRow["COUNT(*)"];
    }

    /**
     * Returns the list of tags related with the systemid passed.
     * If given, an attribute used to specify the relation can be passed, too.
     *
     * @param string $strSystemid
     * @param string $strAttribute
     * @return array
     */
    public static function getTagsForSystemid($strSystemid, $strAttribute = null) {

        $strWhere = "";
        if($strAttribute != null)
            $strWhere = "AND tags_attribute = '".  dbsafeString($strAttribute)."'";
        
        $strQuery = "SELECT DISTINCT(tags_tagid)
                       FROM "._dbprefix_."tags_member,
                            "._dbprefix_."tags_tag
                      WHERE tags_systemid = '".  dbsafeString($strSystemid)."'
                        AND tags_tag_id = tags_tagid
                          ".$strWhere."
                   ORDER BY tags_tag_name ASC";

        $arrRows = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
        $arrReturn = array();
        foreach($arrRows as $arrSingleRow) {
            $arrReturn[] = new class_modul_tags_tag($arrSingleRow["tags_tagid"]);
        }

        return $arrReturn;
    }

    /**
     * Returns a tag for a given tag-name - if present. Otherwise null.
     *
     * @param string $strName
     * @return class_modul_tags_tag
     */
    public static function getTagByName($strName) {
        $strQuery = "SELECT tags_tag_id
                       FROM "._dbprefix_."tags_tag
                      WHERE tags_tag_name LIKE '".  dbsafeString(trim($strName))."'";
        $arrCols = class_carrier::getInstance()->getObjDB()->getRow($strQuery);
        if(isset($arrCols["tags_tag_id"]) && validateSystemid($arrCols["tags_tag_id"]))
            return new class_modul_tags_tag($arrCols["tags_tag_id"]);
        else
            return null;
    }

    /**
     * Creates a list of tags matching the passed filter.
     *
     * @param string $strFilter
     * @return class_modul_tags_tag
     */
    public static function getTagsByFilter($strFilter) {
        $strQuery = "SELECT tags_tag_id
                       FROM "._dbprefix_."tags_tag
                      WHERE tags_tag_name LIKE '".  dbsafeString($strFilter)."%'
                   ORDER BY tags_tag_name ASC";

        $arrRows = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
        $arrReturn = array();
        foreach($arrRows as $arrSingleRow) {
            $arrReturn[] = new class_modul_tags_tag($arrSingleRow["tags_tag_id"]);
        }

        return $arrReturn;
    }

    /**
     * Loads all tags having at least one assigned systemrecord
     * and being active
     * @return class_modul_tags_tag
     */
    public static function getTagsWithAssignments() {
        $strQuery = "SELECT DISTINCT(tags_tagid)
                       FROM "._dbprefix_."tags_member,
                            "._dbprefix_."tags_tag,
                            "._dbprefix_."system
                      WHERE tags_tag_id = tags_tagid
                        AND tags_tag_id = system_id
                        AND system_status = 1
                   ORDER BY tags_tag_name ASC";
        
        $arrRows = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
        $arrReturn = array();
        foreach($arrRows as $arrSingleRow) {
            $arrReturn[] = new class_modul_tags_tag($arrSingleRow["tags_tagid"]);
        }

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
                      WHERE tags_tagid = '".  dbsafeString($this->getSystemid())."'
                        AND system.system_id = member.tags_systemid
                        AND system.system_status = 1";

        return $this->objDB->getArray($strQuery);
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

        //check of not already set
        $strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."tags_member
                      WHERE tags_systemid= '".  dbsafeString($strTargetSystemid)."'
                        AND tags_tagid = '".  dbsafeString($this->getSystemid())."'
                        AND tags_attribute = '".  dbsafeString($strAttribute)."'";
        $arrRow = $this->objDB->getRow($strQuery);
        if($arrRow["COUNT(*)"] != 0)
            return true;

        $strQuery = "INSERT INTO "._dbprefix_."tags_member
                      (tags_systemid, tags_tagid, tags_attribute) VALUES
                      ('".dbsafeString($strTargetSystemid)."', '".dbsafeString($this->getSystemid())."', '".dbsafeString($strAttribute)."')";

        return $this->objDB->_query($strQuery);
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
                           WHERE tags_systemid = '".  dbsafeString($strTargetSystemid)."'
                             AND tags_attribute = '".  dbsafeString($strAttribute)."'
                             AND tags_tagid = '".  dbsafeString($this->getSystemid())."'";

        return $this->objDB->_query($strQuery);
    }

    /**
     * Searches for tags assigned to the systemid to be deleted.
     * Overwrites class_model::doAdditionalCleanupsOnDeletion($strSystemid) 
     *
     * @param string $strSystemid
     * @return bool
     * 
     */
    public function doAdditionalCleanupsOnDeletion($strSystemid) {
        $bitReturn = true;
        //module installed?
        if(class_modul_system_module::getModuleByName("tags") == null)
            return true;

        //check that systemid isn't the id of a tag to avoid recursions
        $arrRecordModulId = $this->getSystemRecord($strSystemid);
        if(isset($arrRecordModulId["system_modul_nr"]) && $arrRecordModulId["system_module_nr"] == _tags_modul_id_)
            return true;
            
        //delete memberships. Fire a plain query, faster then searching.
        $strQuery = "DELETE FROM "._dbprefix_."tags_member WHERE tags_systemid='".dbsafeString($strSystemid)."'";
        $bitReturn = $this->objDB->_query($strQuery);

        return $bitReturn;
    }
    
    

// --- GETTERS / SETTERS --------------------------------------------------------------------------------

    public function getStrName() {
        return $this->strName;
    }

    public function setStrName($strName) {
        $this->strName = trim($strName);
    }


}
?>