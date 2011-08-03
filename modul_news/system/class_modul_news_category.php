<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

/**
 * Model for a newscategory
 *
 * @package modul_news
 * @author sidler@mulchprod.de
 */
class class_modul_news_category extends class_model implements interface_model  {

    private $strTitle = "";


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_news";
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
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array(_dbprefix_."news_category" => "news_cat_id");
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "news category ".$this->getStrTitle();
    }

    /**
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {
        $strQuery = "SELECT * FROM ".$this->arrModule["table"].",
						"._dbprefix_."system
						WHERE system_id = news_cat_id
						AND system_id = ? ";
        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));

        $this->setStrTitle($arrRow["news_cat_title"]);
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    protected function updateStateToDb() {
        $strQuery = "UPDATE ".$this->arrModule["table"]."
                    SET news_cat_title = ?
					  WHERE news_cat_id = ?";
		return $this->objDB->_pQuery($strQuery, array($this->getStrTitle(), $this->getSystemid()));
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

		$arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array());
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
	                   WHERE newsmem_news = ? ";
	    $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strSystemid));
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
	                  WHERE newsmem_news = ?";
        return class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array($strSystemid));
	}

	/**
	 * Deletes a category and all memberships related with the category
	 *
	 * @param string $strSystemid
	 * @return bool
	 */
	public function deleteCategory() {
	    class_logger::getInstance()->addLogRow("deleted newscat ".$this->getSystemid(), class_logger::$levelInfo);
	    //start by deleting from members an cat table
        $strQuery1 = "DELETE FROM "._dbprefix_."news_category WHERE news_cat_id = ?";
        $strQuery2 = "DELETE FROM "._dbprefix_."news_member WHERE newsmem_category = ?";
        if($this->objDB->_pQuery($strQuery1, array($this->getSystemid())) && $this->objDB->_pQuery($strQuery2, array($this->getSystemid()))) {
            if($this->deleteSystemRecord($this->getSystemid()))
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