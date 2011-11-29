<?php
/*"******************************************************************************************************
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                          *
********************************************************************************************************/

/**
 * Model for a single aspect. An aspect is a filter-type that can be applied to the backend.
 * E.g. there could be different dashboard for each aspect or a module may be visible only for given
 * aspects.
 * Aspects should and will not replace the permissions! If a module was removed from an aspect, it may
 * still be accessible directly due to sufficient permissions.
 * This means aspects are rather some kind of view-filter then business-logic filters.
 *
 * @package module_system
 * @since 3.4
 * @author sidler@mulchprod.de
 */
class class_module_system_aspect extends class_model implements interface_model  {

    private $strName = "";
    private $bitDefault = false;

    private static $STR_SESSION_ASPECT_KEY = "STR_SESSION_ASPECT_KEY";
    private static $STR_SESSION_ASPECT_OBJECT = "STR_SESSION_ASPECT_OBJECT";

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $arrModul = array();
        $arrModul["name"] 				= "module_system";
		$arrModul["moduleId"] 			= _system_modul_id_;
		$arrModul["table"]       		= _dbprefix_."aspects";
		$arrModul["modul"]				= "system";

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
        return array(_dbprefix_."aspects" => "aspect_id");
    }


    /**
     * @see class_model::getObjectDescription();
     * @return string
     */
    protected function getObjectDescription() {
        return "aspect ".$this->getStrName();
    }


    /**
     * Initalises the current object, if a systemid was given
     *
     */
    public function initObject() {
        $strQuery = "SELECT * FROM "._dbprefix_."system, ".$this->arrModule["table"]."
                     WHERE system_id = aspect_id
                     AND system_id = ?";
        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));

        if(count($arrRow) > 1) {
            $this->setBitDefault($arrRow["aspect_default"]);
            $this->setStrName($arrRow["aspect_name"]);
        }
    }


    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    protected function updateStateToDb() {

        //if no other aspect exists, we have a new default aspect
        $arrObjAspects = class_module_system_aspect::getAllAspects();
        if(count($arrObjAspects) == 0 ) {
        	$this->setBitDefault(1);
        }

        $strQuery = "UPDATE ".$this->arrModule["table"]."
                        SET aspect_name = ?,
                            aspect_default = ?
                      WHERE aspect_id = ?";
        return $this->objDB->_pQuery($strQuery, array($this->getStrName(), $this->getBitDefault(), $this->getSystemid()));
    }


    /**
     * Returns an array of all aspects available
     *
     * @param bool $bitJustActive
     * @return class_module_system_aspect
     * @static
     */
    public static function getAllAspects($bitJustActive = false) {
        $strQuery = "SELECT system_id
                     FROM "._dbprefix_."aspects, "._dbprefix_."system
		             WHERE system_id = aspect_id
		             ".($bitJustActive ? "AND system_status != 0 ": "")."
		             ORDER BY system_sort ASC, aspect_name ASC";
        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array());
        $arrReturn = array();
        foreach($arrIds as $arrOneId)
            $arrReturn[] = new class_module_system_aspect($arrOneId["system_id"]);

        return $arrReturn;
    }


    /**
     * Returns the number of aspectss installed in the system
     *
     * @param bool $bitJustActive
     * @return int
     */
    public static function getNumberOfAspectsAvailable($bitJustActive = false) {
    	$strQuery = "SELECT COUNT(*)
                     FROM "._dbprefix_."aspects, "._dbprefix_."system
                     WHERE system_id = aspect_id
                     ".($bitJustActive ? "AND system_status != 0 ": "")."";
        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array());

        return (int)$arrRow["COUNT(*)"];

    }


    /**
     * Resets all default aspects.
     * Afterwards, no default aspect is available!
     *
     * @return bool
     */
    public static function resetDefaultAspect() {
        $strQuery = "UPDATE "._dbprefix_."aspects
                     SET aspect_default = 0";
        return class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array());
    }


    /**
     * Deletes the current object from the database
     *
     * @return bool
     */
    public function deleteObject() {
        //Start tx
		$this->objDB->transactionBegin();
		$bitCommit = true;
        class_logger::getInstance()->addLogRow("deleted ".$this->getObjectDescription(), class_logger::$levelInfo);
        //start with the modul-table
        $strQuery = "DELETE FROM ".$this->arrModule["table"]." WHERE aspect_id = ?";

		//rights an systemrecords
		if(!$this->objDB->_pQuery($strQuery, array($this->getSystemid())) || !$this->deleteSystemRecord($this->getSystemid()))
		    $bitCommit = false;

		//if we have just one aspect remaining, set this one as default
        $arrObjAspects = class_module_system_aspect::getAllAspects();
        if(count($arrObjAspects) == 1) {
        	$objOneLanguage = $arrObjAspects[0];
        	$objOneLanguage->setBitDefault(1);
        	$objOneLanguage->updateObjectToDb();
        }

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
     * Returns the default aspect, defined in the admin.
     * This takes permissions into account!
     *
     * @return class_module_system_aspect null if no aspect is set up
     */
    public static function getDefaultAspect() {
        //try to load the default language
        $strQuery = "SELECT system_id
                 FROM "._dbprefix_."aspects, "._dbprefix_."system
	             WHERE system_id = aspect_id
	             AND aspect_default = 1
	             AND system_status = 1
	             ORDER BY system_sort ASC, system_comment ASC";
        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array());
        if(count($arrRow) > 0 && class_carrier::getInstance()->getObjRights()->rightView($arrRow["system_id"])) {
            return new class_module_system_aspect($arrRow["system_id"]);
        }
        else {
            if(count(class_module_system_aspect::getAllAspects(true)) > 0) {
                $arrAspects = class_module_system_aspect::getAllAspects(true);
                foreach($arrAspects as $objOneAspect)
                    if($objOneAspect->rightView())
                        return $objOneAspect;
            }

            return null;
        }
    }

    /**
     * Returns an aspect by name, ignoring the status
     *
     * @param string $strName
     * @return class_module_system_aspect or null if not found
     */
    public static function getAspectByName($strName) {
        $strQuery = "SELECT system_id
                 FROM "._dbprefix_."aspects, "._dbprefix_."system
	             WHERE system_id = aspect_id
	             AND aspect_name = ?
	             ORDER BY system_sort ASC, system_comment ASC";
        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strName));
        if(count($arrRow) > 0) {
            return new class_module_system_aspect($arrRow["system_id"]);
        }
        else {
            return null;
        }
    }


    /**
     * Returns the aspect currently selected by the user.
     * If no aspect was selected before, the default aspect is returned instead.
     * In addition, the current params are processed in order to react on changes made
     * by the user / external sources.
     *
     * @return class_module_system_aspect null if no aspect is set up
     */
    public static function getCurrentAspect() {

        //process params maybe existing
        if(defined("_admin_") && _admin_ && getGet("aspect") != "" && validateSystemid(getGet("aspect"))) {
            self::setCurrentAspectId(getGet("aspect"));
        }

        //aspect registered in session?
        if(validateSystemid(class_carrier::getInstance()->getObjSession()->getSession(class_module_system_aspect::$STR_SESSION_ASPECT_KEY))) {
            return class_carrier::getInstance()->getObjSession()->getSession(class_module_system_aspect::$STR_SESSION_ASPECT_OBJECT);
        }
        else {
            return class_module_system_aspect::getDefaultAspect();
        }
    }

    /**
     * Wrapper to getCurrentAspect(), returning the ID of the aspect currently selected.
     * If no aspect is selected, an empty string is returned.
     *
     * @return string
     */
    public static function getCurrentAspectId() {
        $objAspect = class_module_system_aspect::getCurrentAspect();
        if($objAspect != null)
            return $objAspect->getSystemid();
        else
            return "";
    }

    /**
     * Saves an aspect id as the current active one - but only if the previous one was changed
     *
     * @param string $strAspectId
     */
    public static function setCurrentAspectId($strAspectId) {
        if(validateSystemid($strAspectId) && $strAspectId != class_carrier::getInstance()->getObjSession()->getSession(class_module_system_aspect::$STR_SESSION_ASPECT_KEY) ) {
            class_carrier::getInstance()->getObjSession()->setSession(class_module_system_aspect::$STR_SESSION_ASPECT_KEY, $strAspectId);
            class_carrier::getInstance()->getObjSession()->setSession(class_module_system_aspect::$STR_SESSION_ASPECT_OBJECT, new class_module_system_aspect($strAspectId));
        }
    }



// --- GETTERS / SETTERS --------------------------------------------------------------------------------

    public function setStrName($strName) {
        $this->strName = $strName;
    }

    public function setBitDefault($bitDefault) {
        $this->bitDefault = $bitDefault;
    }

    public function getStrName() {
        return $this->strName;
    }

    public function getBitDefault() {
        return $this->bitDefault;
    }

}
?>