<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                         *
********************************************************************************************************/

/**
 * Business Object for a single participant, participating at an event.
 *
 * @package modul_eventmanager
 * @author sidler@mulchprod.de
 * @since 3.4
 */
class class_modul_eventmanager_participant extends class_model implements interface_model, interface_versionable  {

    private $strActionEdit = "edit";


    private $strForename = "";
    private $strLastname = "";
    private $strEmail = "";
    private $strPhone = "";
    private $strComment = "";


    private $strOldForename = "";
    private $strOldLastname = "";
    private $strOldEmail = "";
    private $strOldPhone = "";
    private $strOldComment = "";

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "modul_eventmanager";
		$arrModul["moduleId"] 			= _eventmanager_modul_id_;
		$arrModul["modul"]				= "eventmanager";
		$arrModul["table"]				= _dbprefix_."em_participant";

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
        return array(_dbprefix_."em_participant" => "em_pt_id");
    }

    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "eventmanager participant ".$this->getStrEmail();
    }

    /**
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {
        
        $strQuery = "SELECT * 
                       FROM ".$this->arrModule["table"].",
                            "._dbprefix_."system
				      WHERE em_pt_id = ?
                        AND system_id=em_pt_id";

        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));

        if(count($arrRow) > 0) {
            $this->setStrForename($arrRow["em_pt_forename"]);
            $this->setStrLastname($arrRow["em_pt_lastname"]);
            $this->setStrEmail($arrRow["em_pt_email"]);
            $this->setStrPhone($arrRow["em_pt_phone"]);
            $this->setStrComment($arrRow["em_pt_comment"]);

            $this->strOldForename = $this->strForename;
            $this->strOldLastname = $this->strLastname;
            $this->strOldEmail = $this->strEmail;
            $this->strOldPhone = $this->strPhone;
            $this->strOldComment = $this->strComment;
        }
    }

    /**
     * saves the current object with all its params back to the database.
     * This method is called from the framework automatically.
     *
     * @return bool
     */
    protected function updateStateToDb() {

        //create change-logs
        $objChanges = new class_modul_system_changelog();
        $objChanges->createLogEntry($this, $this->strActionEdit);

        $strQuery = "UPDATE ".$this->arrModule["table"]."
                        SET em_pt_forename =?,
                            em_pt_lastname =?,
                            em_pt_email =?,
                            em_pt_phone =?,
                            em_pt_comment =?
					  WHERE em_pt_id =?";

		return $this->objDB->_pQuery($strQuery, array(
            $this->getStrForename(),
            $this->getStrLastname(),
            $this->getStrEmail(),
            $this->getStrPhone(),
            $this->getStrComment(),
            $this->getSystemid()
        ));
    }


    /**
     * Loads all participants for a single event
     * @param <type> $strEventId
     * @param int $intStart
     * @param int $intEnd
     * @return class_modul_eventmanager_participant
     */
	public static function getAllParticipants($strEventId, $intStart = null, $intEnd = null) {
		$strQuery = "SELECT system_id 
                       FROM "._dbprefix_."eventmanager_participant,
						     "._dbprefix_."system
				      WHERE system_id = em_pt_id
                        AND system_prev_id = ?
                   ORDER BY em_pt_email ASC, em_pt_lastname ASC";

        if($intStart != null && $intEnd != null)
            $arrIds = class_carrier::getInstance()->getObjDB()->getPArraySection($strQuery, array($strEventId), $intStart, $intEnd);
        else
            $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strEventId));
		$arrReturn = array();
		foreach($arrIds as $arrOneId)
		    $arrReturn[] = new class_modul_eventmanager_participant($arrOneId["system_id"]);

		return $arrReturn;
	}

    /**
     * Counts the number of participants for a single systemid
     * @param string $strEventId
     * @return int
     */
    public static function getAllParticipantsCount($strEventId) {
		$strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."eventmanager_participant,
						     "._dbprefix_."system
				      WHERE system_id = em_pt_id
                        AND system_prev_id = ?
                   ORDER BY em_pt_email ASC, em_pt_lastname ASC";

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strEventId));
        return $arrRow["COUNT(*)"];
	}
	

	/**
	 * Deletes a category and all memberships related with the category
	 *
	 * @param string $strSystemid
	 * @return bool
	 */
	public function deleteParticipant() {
	    class_logger::getInstance()->addLogRow("deleted ".$this->getObjectDescription(), class_logger::$levelInfo);
        $strQuery = "DELETE FROM "._dbprefix_."eventmanager_participant WHERE em_pt_id = ?";
        if($this->objDB->_pQuery($strQuery, array($this->getSystemid()))) {
            if($this->deleteSystemRecord($this->getSystemid())) {
                $this->unsetSystemid();
                return true;
            }
        }
        
        return false;
	}




    public function getActionName($strAction) {
        if($strAction == $this->strActionEdit)
            return $this->getText("participant_edit", "eventmanager", "admin");

        return $strAction;
    }

    public function getChangedFields($strAction) {
        if($strAction == $this->strActionEdit) {
            return array(
                array("property" => "email",  "oldvalue" => $this->strOldEmail, "newvalue" => $this->getStrEmail()),
                array("property" => "forename",  "oldvalue" => $this->strOldForename, "newvalue" => $this->getStrForename()),
                array("property" => "lastname",  "oldvalue" => $this->strOldLastname, "newvalue" => $this->getStrLastname()),
                array("property" => "phone",  "oldvalue" => $this->strOldPhone, "newvalue" => $this->getStrPhone()),
                array("property" => "comment",  "oldvalue" => $this->strOldComment, "newvalue" => $this->getStrComment())
            );
        }
    }

    public function renderValue($strProperty, $strValue) {
        return $strValue;
    }

    public function getClassname() {
        return __CLASS__;
    }

    public function getModuleName() {
        return $this->arrModule["modul"];
    }

    public function getPropertyName($strProperty) {
        return $strProperty;
    }

    public function getRecordName() {
        return class_carrier::getInstance()->getObjText()->getText("change_object_participant", "eventmanager", "admin");
    }

// --- GETTERS / SETTERS --------------------------------------------------------------------------------

    public function getStrForename() {
        return $this->strForename;
    }

    public function setStrForename($strForename) {
        $this->strForename = $strForename;
    }

    public function getStrLastname() {
        return $this->strLastname;
    }

    public function setStrLastname($strLastname) {
        $this->strLastname = $strLastname;
    }

    public function getStrEmail() {
        return $this->strEmail;
    }

    public function setStrEmail($strEmail) {
        $this->strEmail = $strEmail;
    }

    public function getStrPhone() {
        return $this->strPhone;
    }

    public function setStrPhone($strPhone) {
        $this->strPhone = $strPhone;
    }

    public function getStrComment() {
        return $this->strComment;
    }

    public function setStrComment($strComment) {
        $this->strComment = $strComment;
    }



}
?>