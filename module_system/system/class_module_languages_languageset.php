<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/

/**
 * A languageset connects a set of systemrecords and assigns every single one to defined set.
 * This allows to couple records to a languageset.
 * The languageswitch is capable to interact with a languageswitch and creates the switch-links
 * with the matching systemid.
 *
 * Please note: Since a languageset only tighs existing records together, it isn't in the regular
 * class_model hierarchy. This also means, that a languageset is not included within the regular
 * object lifecycle and has no representation in the system-table!
 *
 * In most cases creating a new instance via the constructor is useless. Instead use one of the
 * factory methods.
 *
 * @package module_languages
 * @author sidler@mulchprod.de
 */
class class_module_languages_languageset extends class_model implements interface_model, interface_recorddeleted_listener {

    private $arrLanguageSet = array();


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {

        $this->setArrModuleEntry("modul", "languages");
        $this->setArrModuleEntry("moduleId", _languages_modul_id_);

        //base class
		parent::__construct($strSystemid);

		//init current object
		if($strSystemid != "")
		    $this->initObject();

    }

    /**
     * Inits the current object and loads the language-mappings
     */
    public function initObject() {
        $strQuery = "SELECT * FROM "._dbprefix_."languages_languageset WHERE languageset_id = ?";
        $arrRow = $this->objDB->getPArray($strQuery, array($this->getSystemid()));

        if(count($arrRow) > 0) {
            $this->arrLanguageSet = array();
            foreach($arrRow as $arrSingleRow) {
                $this->arrLanguageSet[$arrSingleRow["languageset_language"]] = $arrSingleRow["languageset_systemid"];
            }
        }
    }

    /**
     * Returns a human readable description of the current object. Used mainly for internal reasons, e.g. in database-descriptions
     * @return string
     */
    public function getObjectDescription() {
        return "";
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @return string
     */
    public function getStrDisplayName() {
        return "";
    }


    /**
     * Updates the current state to the database
     *
     * @param bool $strPrevId
     * @return bool
     */
    public function updateObjectToDb($strPrevId = false) {
        //new one or existing one?
        if($this->getSystemid() == "") {
            $strSystemid = generateSystemid();
            $this->setSystemid($strSystemid);
        }
        else {
            //remove old records
            $strQuery = "DELETE FROM "._dbprefix_."languages_languageset WHERE languageset_id = ?";
            $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
        }


        class_logger::getInstance()->addLogRow("updating languageset ".$this->getSystemid(), class_logger::$levelInfo);

        $bitReturn = true;
        foreach($this->arrLanguageSet as $strLanguage => $strSystemid) {
            $strQuery = "INSERT INTO "._dbprefix_."languages_languageset
                           (languageset_id, languageset_language, languageset_systemid) VALUES
                           (?, ?, ?)";

            $bitReturn &= $this->objDB->_pQuery($strQuery, array($this->getSystemid(), $strLanguage, $strSystemid));
        }

        return $bitReturn;
    }

    /**
     * Called whenever a update-request was fired.
     * Use this method to synchronize yourselves with the database.
     * Use only updates, inserts are not required to be implemented.
     *
     * @return bool
     */
    public function updateStateToDb() {
        return true;
    }

    /**
     * Deletes the current object from the system
     * @return bool
     */
    public function deleteObject() {
        return true;
    }

    /**
     * Returns a list of tables the current object is persisted to.
     * A new record is created in each table, as soon as a save-/update-request was triggered by the framework.
     * The array should contain the name of the table as the key and the name
     * of the primary-key (so the column name) as the matching value.
     * E.g.: array(_dbprefix_."pages" => "page_id)
     *
     * @return array [table => primary row name]
     */
    public function getObjectTables() {
        return array();
    }


    /**
     * Returns the id of the mapped systemrecord for the given language.
     * If no record exists, NULL is returned instead.
     *
     * @param string $strLanguageid
     * @return string or null
     */
    public function getSystemidForLanguageid($strLanguageid) {
        if(isset($this->arrLanguageSet[$strLanguageid]))
            return $this->arrLanguageSet[$strLanguageid];

        return null;
    }

    /**
     * Returns the id of the language the passed record is assigned to, null otherwise.
     *
     * @param string $strSystemid
     * @return string or null
     */
    public function getLanguageidForSystemid($strSystemid) {
        foreach($this->arrLanguageSet as $strLanguage => $strRecord)
            if($strSystemid == $strRecord)
                return $strLanguage;

        return null;
    }

    /**
     * Sets the systemid for a language.
     *
     * @param string $strSystemid
     * @param string $strLanguageid
     * @return bool
     */
    public function setSystemidForLanguageid($strSystemid, $strLanguageid) {

        if(!validateSystemid($strSystemid) || !validateSystemid($strLanguageid))
            return false;

        $this->arrLanguageSet[$strLanguageid] = $strSystemid;

        return $this->updateObjectToDb();
    }

    /**
     * Removes a single systemid from a languageset
     *
     * @param string $strSystemid
     */
    public function removeSystemidFromLanguageeset($strSystemid) {
        foreach($this->arrLanguageSet as $strId => $strSetSystemid) {
            if($strSetSystemid == $strSystemid) {
                unset($this->arrLanguageSet[$strId]) ;
                $this->updateObjectToDb();
                break;
            }
        }
    }

    /**
     * Tries to load the languageset for the passed systemid.
     * If no record is found, null is returned instead.
     *
     * @param string $strSystemid
     * @return class_module_languages_languageset
     */
    public static function getLanguagesetForSystemid($strSystemid) {
        $strQuery = "SELECT languageset_id
                       FROM "._dbprefix_."languages_languageset
                      WHERE languageset_systemid = ?";

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strSystemid) );

        if(isset($arrRow["languageset_id"])) {
            $objReturn = new class_module_languages_languageset($arrRow["languageset_id"]);
            return $objReturn;
        }

        return null;
    }

    /**
     * Creates a new languageset for the passed systemid and returns the new
     * instance.
     * If theres a languageset already existing, the languageset is loaded
     * instead of creating a new one.
     *
     * @param string $strSystemid
     * @param class_module_languages_language $objTargetLanguage
     * @return class_module_languages_languageset
     */
    public static function createLanguagesetForSystemid($strSystemid, $objTargetLanguage) {

        //already existing?
        $objLanguageset = class_module_languages_languageset::getLanguagesetForSystemid($strSystemid);

        if($objLanguageset == null) {
            //create a new one
            $objLanguageset = new class_module_languages_languageset("");
            $objLanguageset->setSystemidForLanguageid($strSystemid, $objTargetLanguage->getSystemid());
        }
        else if($objLanguageset->getSystemidForLanguageid($objTargetLanguage->getSystemid()) == null) {
            //update the languageset
            $objLanguageset->setSystemidForLanguageid($strSystemid, $objTargetLanguage->getSystemid());
        }


        return new $objLanguageset;
    }

    /**
     * Searches for languagesets containing the current systemid. either as a language or a referenced record.
     *
     * Called whenever a records was deleted using the common methods.
     * Implement this method to be notified when a record is deleted, e.g. to to additional cleanups afterwards.
     * There's no need to register the listener, this is done automatically.
     *
     * Make sure to return a matching boolean-value, otherwise the transaction may be rolled back.
     *
     * @param $strSystemid
     *
     * @return bool
     */
    public function handleRecordDeletedEvent($strSystemid) {
        //fire a plain query on the database, much faster then searching for matching records
        $strQuery = "DELETE FROM "._dbprefix_."languages_languageset
                      WHERE languageset_language = ?
                         OR languageset_systemid = ?";

        return class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array($strSystemid, $strSystemid));
    }

    /**
     * Returns the list of current associations
     *
     * @return array
     */
    public function getArrLanguageSet() {
        return $this->arrLanguageSet;
    }
}
