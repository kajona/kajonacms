<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

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
     * @param string $strSystemid (use "" on new objects)
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
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array(_dbprefix_."faqs_category" => "faqs_cat_id");
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "faq category ".$this->getStrTitle();
    }

    /**
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {
        
        $strQuery = "SELECT * 
                       FROM ".$this->arrModule["table"]."
				      WHERE faqs_cat_id = '".dbsafeString($this->getSystemid())."'";

        $arrRow = $this->objDB->getRow($strQuery);

        $this->setStrTitle($arrRow["faqs_cat_title"]);
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    protected function updateStateToDb() {
        
        $strQuery = "UPDATE ".$this->arrModule["table"]."
                        SET faqs_cat_title ='".dbsafeString($this->getStrTitle())."'
					  WHERE faqs_cat_id ='".dbsafeString($this->getSystemid())."'";

		return $this->objDB->_query($strQuery);
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
	public function deleteCategory() {

	    class_logger::getInstance()->addLogRow("deleted ".$this->getObjectDescription(), class_logger::$levelInfo);
	    //start by deleting from members an cat table
        $strQuery1 = "DELETE FROM "._dbprefix_."faqs_category WHERE faqs_cat_id = '".dbsafeString($this->getSystemid())."'";
        $strQuery2 = "DELETE FROM "._dbprefix_."faqs_member WHERE faqsmem_category = '".dbsafeString($this->getSystemid())."'";

        if($this->objDB->_query($strQuery1) && $this->objDB->_query($strQuery2)) {
            if($this->deleteSystemRecord($this->getSystemid())) {
                $this->unsetSystemid();
                return true;
            }
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