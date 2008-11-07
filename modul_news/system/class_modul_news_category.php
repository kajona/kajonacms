<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

include_once(_systempath_."/class_model.php");
include_once(_systempath_."/interface_model.php");
include_once(_systempath_."/class_modul_system_common.php");

/**
 * Model for a newscategory
 *
 * @package modul_news
 */
class class_modul_news_category extends class_model implements interface_model  {

    private $strTitle = "";


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModul["name"] 				= "modul_news";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _news_modul_id_;
		$arrModul["table"]       		= _dbprefix_."news_category";
		$arrModul["table2"]       		= _dbprefix_."news_member";
		$arrModul["modul"]				= "news";

		//base class
		parent::__construct($arrModul, $strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();
    }

    /**
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {
        $strQuery = "SELECT * FROM ".$this->arrModule["table"].",
						"._dbprefix_."system
						WHERE system_id = news_cat_id
						AND system_id = '".$this->objDB->dbsafeString($this->getSystemid())."'";
        $arrRow = $this->objDB->getRow($strQuery);

        $this->setStrTitle($arrRow["news_cat_title"]);
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    public function updateObjectToDb() {
        class_logger::getInstance()->addLogRow("updated newscar ".$this->getSystemid(), class_logger::$levelInfo);
        $this->setEditDate();
        $strQuery = "UPDATE ".$this->arrModule["table"]."
                    SET news_cat_title ='".$this->objDB->dbsafeString($this->getStrTitle())."'
					  WHERE news_cat_id ='".$this->objDB->dbsafeString($this->getSystemid())."'";
		return $this->objDB->_query($strQuery);
    }

    /**
     * saves the current object as a new object to the database
     *
     * @return bool
     */
    public function saveObjectToDb() {
        //Create a new record --> start tx
		$this->objDB->transactionBegin();
		$bitCommit = true;
        //Create the system-record
        $strCatId = $this->createSystemRecord(0, "news cat: ".$this->getStrTitle());
        $this->setSystemid($strCatId);
        class_logger::getInstance()->addLogRow("new newscat ".$this->getSystemid(), class_logger::$levelInfo);
        $strQuery = "INSERT INTO ".$this->arrModule["table"]."
                        (news_cat_id, news_cat_title) VALUES
                        ('".$this->objDB->dbsafeString($strCatId)."', '".$this->objDB->dbsafeString($this->getStrTitle())."')";
		if(!$this->objDB->_query($strQuery))
		    $bitCommit = false;
		//End tx
		if($bitCommit) {
			$this->objDB->transactionCommit();
			return true;
		}
		else {
			$this->objDB->transactionRollback();
			return false;
		}
    }

    /**
	 * Loads all available categories from the db
	 *
	 * @return mixed
	 * @static
	 */
	public static function getCategories() {
		$strQuery = "SELECT system_id FROM "._dbprefix_."news_category,
						"._dbprefix_."system
						WHERE system_id = news_cat_id
						ORDER BY news_cat_title";

		$arrIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_news_category($arrOneId["system_id"]);

		return $arrReturn;
	}


	/**
	 * Loads all categories, the given news is in
	 *
	 * @param string $strSystemid
	 * @return mixed
	 * @static
	 */
	public static function getNewsMember($strSystemid) {
	    $strQuery = "SELECT newsmem_category as system_id FROM "._dbprefix_."news_member
	                   WHERE newsmem_news = '".dbsafeString($strSystemid)."'";
	    $arrIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_news_category($arrOneId["system_id"]);

		return $arrReturn;
	}

	/**
	 * Deletes all memberships of the given NEWS
	 *
	 * @param string $strSystemid NEWS-ID
	 * @return bool
	 */
	public static function deleteNewsMemberships($strSystemid) {
	    $strQuery = "DELETE FROM "._dbprefix_."news_member
	                  WHERE newsmem_news = '".dbsafeString($strSystemid)."'";
        return class_carrier::getInstance()->getObjDB()->_query($strQuery);
	}

	/**
	 * Deletes a category and all memberships related with the category
	 *
	 * @param string $strSystemid
	 * @return bool
	 */
	public static function deleteCategory($strSystemid) {
	    class_logger::getInstance()->addLogRow("deleted newscat ".$strSystemid, class_logger::$levelInfo);
	    $objRoot = new class_modul_system_common();
	    //start by deleting from members an cat table
        $strQuery1 = "DELETE FROM "._dbprefix_."news_category WHERE news_cat_id = '".dbsafeString($strSystemid)."'";
        $strQuery2 = "DELETE FROM "._dbprefix_."news_member WHERE newsmem_category = '".dbsafeString($strSystemid)."'";
        if(class_carrier::getInstance()->getObjDB()->_query($strQuery1) && class_carrier::getInstance()->getObjDB()->_query($strQuery2)) {
            if($objRoot->deleteSystemRecord($strSystemid))
                return true;
        }
        return false;
	}

// --- GETTERS / SETTERS --------------------------------------------------------------------------------

    public function getStrTitle() {
        return $this->strTitle;
    }

    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
    }

}
?>