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
 * Model for a faqscategory
 *
 * @package modul_faqs
 */
class class_modul_faqs_category extends class_model implements interface_model  {

    private $strTitle = "";


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_faqs";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _faqs_modul_id_;
		$arrModul["table"]       		= _dbprefix_."faqs_category";
		$arrModul["table2"]       		= _dbprefix_."faqs_member";
		$arrModul["modul"]				= "faqs";

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
						WHERE system_id = faqs_cat_id
						AND system_id = '".$this->objDB->dbsafeString($this->getSystemid())."'";
        $arrRow = $this->objDB->getRow($strQuery);

        $this->setStrTitle($arrRow["faqs_cat_title"]);
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    public function updateObjectToDb() {
        class_logger::getInstance()->addLogRow("updated faqscat ".$this->getSystemid(), class_logger::$levelInfo);
        $this->setEditDate();
        $strQuery = "UPDATE ".$this->arrModule["table"]."
                    SET faqs_cat_title ='".$this->objDB->dbsafeString($this->getStrTitle())."'
					  WHERE faqs_cat_id ='".$this->objDB->dbsafeString($this->getSystemid())."'";
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
        $strCatId = $this->createSystemRecord(0, "faqs cat: ".$this->getStrTitle());
        $this->setSystemid($strCatId);
        class_logger::getInstance()->addLogRow("new faqscat ".$this->getSystemid(), class_logger::$levelInfo);
        $strQuery = "INSERT INTO ".$this->arrModule["table"]."
                        (faqs_cat_id, faqs_cat_title) VALUES
                        ('".dbsafeString($strCatId)."', '".dbsafeString($this->getStrTitle())."')";
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
	 * @param bool $bitJustActive
	 * @return mixed
	 * @static
	 */
	public static function getCategories($bitJustActive = false) {
		$strQuery = "SELECT system_id FROM "._dbprefix_."faqs_category,
						"._dbprefix_."system
						WHERE system_id = faqs_cat_id
						".($bitJustActive ? " AND system_status = 1 ": "" )."
						ORDER BY faqs_cat_title";

		$arrIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_faqs_category($arrOneId["system_id"]);

		return $arrReturn;
	}


	/**
	 * Loads all categories, the given faq is in
	 *
	 * @param string $strSystemid
	 * @return array
	 * @static
	 */
	public static function getFaqsMember($strSystemid) {
	    $strQuery = "SELECT faqsmem_category as system_id FROM "._dbprefix_."faqs_member
	                   WHERE faqsmem_faq = '".dbsafeString($strSystemid)."'";
	    $arrIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_faqs_category($arrOneId["system_id"]);

		return $arrReturn;
	}

	/**
	 * Deletes all memberships of the given FAQ
	 *
	 * @param string $strSystemid FAQ-ID
	 * @return bool
	 */
	public static function deleteFaqsMemberships($strSystemid) {
	    $strQuery = "DELETE FROM "._dbprefix_."faqs_member
	                  WHERE faqsmem_faq = '".dbsafeString($strSystemid)."'";
        return class_carrier::getInstance()->getObjDB()->_query($strQuery);
	}

	/**
	 * Deletes a category and all memberships related with the category
	 *
	 * @param string $strSystemid
	 * @return bool
	 */
	public static function deleteCategory($strSystemid) {
	    class_logger::getInstance()->addLogRow("deleted faqscat ".$strSystemid, class_logger::$levelInfo);
	    $objRoot = new class_modul_system_common();
	    //start by deleting from members an cat table
        $strQuery1 = "DELETE FROM "._dbprefix_."faqs_category WHERE faqs_cat_id = '".dbsafeString($strSystemid)."'";
        $strQuery2 = "DELETE FROM "._dbprefix_."faqs_member WHERE faqsmem_category = '".dbsafeString($strSystemid)."'";
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