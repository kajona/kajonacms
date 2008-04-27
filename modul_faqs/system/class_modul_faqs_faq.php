<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_modul_faqs_faq.php                                                                            *
* 	Model for a single faq                                                                             *
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

include_once(_systempath_."/class_model.php");
include_once(_systempath_."/interface_model.php");
include_once(_systempath_."/class_modul_system_common.php");

/**
 * Model for a faq itself
 *
 * @package modul_faqs
 */
class class_modul_faqs_faq extends class_model implements interface_model  {

    private $strQuestion = "";
    private $strAnswer = "";

    private $arrCats = array();

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objets)
     */
    public function __construct($strSystemid = "") {
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
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {
         $strQuery = "SELECT * FROM ".$this->arrModule["table"].",
	                "._dbprefix_."system
	                WHERE system_id = faqs_id
	                  AND system_id = '".$this->objDB->dbsafeString($this->getSystemid())."'";

         $arrRow = $this->objDB->getRow($strQuery);
         $this->setStrAnswer($arrRow["faqs_answer"]);
         $this->setStrQuestion($arrRow["faqs_question"]);
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    public function updateObjectToDb($bitMemberships = true) {
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
     * saves the current object as a new object to the database
     *
     * @return bool
     */
    public function saveObjectToDb() {
        //Start wit the system-recods and a tx
		$this->objDB->transactionBegin();
		$bitCommit = true;
        $strFaqId = $this->createSystemRecord(0, "faq:".uniSubstr($this->getStrAnswer(), 0, 200));
        $this->setSystemid($strFaqId);
        class_logger::getInstance()->addLogRow("new faq ".$this->getSystemid(), class_logger::$levelInfo);
        //The faqs-Table
        $strQuery = "INSERT INTO ".$this->arrModule["table"]."
                    (faqs_id, faqs_answer, faqs_question) VALUES
                    ('".dbsafeString($strFaqId)."', '".dbsafeString($this->getStrAnswer(), false)."',
                     '".dbsafeString($this->getStrQuestion())."')";

        if($this->objDB->_query($strQuery)) {
            //and all memberships
            foreach($this->arrCats as $strCatID => $strValue) {
                $strQuery = "INSERT INTO ".$this->arrModule["table2"]."
                            (faqsmem_id, faqsmem_faq, faqsmem_category) VALUES
                            ('".dbsafeString($this->generateSystemid())."', '".dbsafeString($strFaqId)."', '".dbsafeString($strCatID)."')";
                if(!$this->objDB->_query($strQuery))
                    $bitCommit = false;
            }
        }
        else
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
	 * Loads all faqs from the database
	 * if passed, the filter is used to load the faqs of the given category
	 *
	 * @param string $strFilter
	 * @return mixed
	 * @static
	 */
	public static function getFaqsList($strFilter = "") {
		if($strFilter != "") {
			$strQuery = "SELECT system_id
							FROM "._dbprefix_."faqs,
							      "._dbprefix_."system,
							      "._dbprefix_."faqs_member
							WHERE system_id = faqs_id
							  AND faqs_id = faqsmem_faq
							  AND faqsmem_category = '".dbsafeString($strFilter)."'
							ORDER BY faqs_question DESC";
		}
		else {
			$strQuery = "SELECT system_id
							FROM "._dbprefix_."faqs,
							      "._dbprefix_."system
							WHERE system_id = faqs_id
							ORDER BY faqs_question DESC";
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
	public static function deleteFaqs($strSystemid) {
	    class_logger::getInstance()->addLogRow("deleted faqs ".$strSystemid, class_logger::$levelInfo);
	    $objRoot = new class_modul_system_common();
	    //Delete memberships
	    if(class_modul_faqs_category::deleteFaqsMemberships($strSystemid)) {
			$strQuery = "DELETE FROM "._dbprefix_."faqs WHERE faqs_id = '".dbsafeString($strSystemid)."'";
			if(class_carrier::getInstance()->getObjDB()->_query($strQuery)) {
			    if($objRoot->deleteSystemRecord($strSystemid))
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

		if($strCat == 1) {
		    $strQuery = "SELECT system_id
    						FROM "._dbprefix_."faqs,
    		                     "._dbprefix_."system
    		                WHERE system_id = faqs_id
    		                  AND system_status = 1
    						ORDER BY faqs_question DESC";
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
    						ORDER BY faqs_question DESC";
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