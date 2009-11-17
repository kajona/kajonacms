<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

/**
 * Model for a faq itself
 *
 * @package modul_faqs
 */
class class_modul_faqs_faq extends class_model implements interface_model, interface_sortable_rating  {

    private $strQuestion = "";
    private $strAnswer = "";

    private $arrCats = array();

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
		$arrModul["table"]       		= _dbprefix_."faqs";
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
        return array(_dbprefix_."faqs" => "faqs_id");
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "faq ".$this->getStrQuestion();
    }

    /**
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {
         $strQuery = "SELECT * FROM ".$this->arrModule["table"]."
	                   WHERE faqs_id = '".dbsafeString($this->getSystemid())."'";

         $arrRow = $this->objDB->getRow($strQuery);
         $this->setStrAnswer($arrRow["faqs_answer"]);
         $this->setStrQuestion($arrRow["faqs_question"]);
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    protected function updateStateToDb($bitMemberships = true) {
        class_logger::getInstance()->addLogRow("updated faq ".$this->getSystemid(), class_logger::$levelInfo);
        $this->setEditDate();
        //Update all needed tables
        //faqs
        $strQuery = "UPDATE ".$this->arrModule["table"]."
                        SET faqs_answer = '".dbsafeString($this->getStrAnswer(), false)."',
                            faqs_question = '".dbsafeString($this->getStrQuestion(), true)."'
                       WHERE faqs_id = '".dbsafeString($this->getSystemid())."'";
        $this->objDB->_query($strQuery);

        //delete all relations
        if($bitMemberships) {
            class_modul_faqs_category::deleteFaqsMemberships($this->getSystemid());
            $arrParams = $this->getAllParams();
            //insert all memberships
            foreach($this->arrCats as $strCatID => $strValue) {
                $strQuery = "INSERT INTO ".$this->arrModule["table2"]."
                            (faqsmem_id, faqsmem_faq, faqsmem_category) VALUES
                            ('".dbsafeString($this->generateSystemid())."', '".dbsafeString($this->getSystemid())."',
                             '".dbsafeString($strCatID)."')";

                if(!$this->objDB->_query($strQuery))
                    return false;
            }
        }

        return true;
    }

    /**
	 * Loads all faqs from the database
	 * if passed, the filter is used to load the faqs of the given category
	 *
	 * @param string $strFilter
	 * @return mixed
	 * @static
	 */
	public static function getFaqsList($strFilter = "") {
        $strQuery = "";
		if($strFilter != "") {
			$strQuery = "SELECT system_id
							FROM "._dbprefix_."faqs,
							      "._dbprefix_."system,
							      "._dbprefix_."faqs_member
							WHERE system_id = faqs_id
							  AND faqs_id = faqsmem_faq
							  AND faqsmem_category = '".dbsafeString($strFilter)."'
							ORDER BY faqs_question ASC";
		}
		else {
			$strQuery = "SELECT system_id
							FROM "._dbprefix_."faqs,
							      "._dbprefix_."system
							WHERE system_id = faqs_id
							ORDER BY faqs_question ASC";
		}

		$arrIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_faqs_faq($arrOneId["system_id"]);

		return $arrReturn;
	}

	/**
	 * Deletes the given faq and all relating memberships
	 *
	 * @param string $strSystemid
	 * @return bool
	 */
	public function deleteFaq() {
	    class_logger::getInstance()->addLogRow("deleted faq ".$this->getObjectDescription(), class_logger::$levelInfo);
	    //Delete memberships
	    if(class_modul_faqs_category::deleteFaqsMemberships($strSystemid)) {
			$strQuery = "DELETE FROM "._dbprefix_."faqs WHERE faqs_id = '".dbsafeString($this->getSystemid())."'";
            
			if($this->objDB->_query($strQuery)) {
			    if($this->deleteSystemRecord($this->getSystemid()))
			        return true;
			}
	    }
	    return false;
	}


	/**
	 * Loads all faqs from the db assigned to the passed cat
	 *
	 * @param string $strCat
	 * @return mixed
	 * @static
	 */
	public static function loadListFaqsPortal($strCat) {
		$arrReturn = array();
        $strQuery = "";
		if($strCat == 1) {
		    $strQuery = "SELECT system_id
    						FROM "._dbprefix_."faqs,
    		                     "._dbprefix_."system
    		                WHERE system_id = faqs_id
    		                  AND system_status = 1
    						ORDER BY faqs_question ASC";
		}
		else {
    		$strQuery = "SELECT system_id
    						FROM "._dbprefix_."faqs,
    						     "._dbprefix_."faqs_member,
    		                     "._dbprefix_."system
    		                WHERE system_id = faqs_id
    		                  AND faqs_id = faqsmem_faq
    		                  AND faqsmem_category = '".dbsafeString($strCat)."'
    		                  AND system_status = 1
    						ORDER BY faqs_question ASC";
		}
		$arrIds = class_carrier::getInstance()->getObjDB()->getArray($strQuery);
		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_faqs_faq($arrOneId["system_id"]);

		return $arrReturn;
	}



// --- GETTERS / SETTERS --------------------------------------------------------------------------------

    public function getStrAnswer() {
        return $this->strAnswer;
    }
    public function getStrQuestion() {
        return $this->strQuestion;
    }
    public function getArrCats() {
        return $this->arrCats;
    }

    public function setStrAnswer($strAnswer) {
        $this->strAnswer = $strAnswer;
    }
    public function setStrQuestion($strQuestion) {
        $this->strQuestion = $strQuestion;
    }

    public function setArrCats($arrCats) {
        $this->arrCats = $arrCats;
    }
}
?>