<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * A languageset connects a set of systemrecords and assigns every single one to defined set.
 * This allows to couple records to a languageset.
 * The languageswitch is capable to interact with a languageswitch and creates the switch-links
 * with the matching systemid.
 * Please note: Since a languageset only tights existing records together, it isn't in the regular
 * \Kajona\System\System\Model hierarchy. This also means, that a languageset is not included within the regular
 * object lifecycle and has no representation in the system-table!
 * In most cases creating a new instance via the constructor is useless. Instead use one of the
 * factory methods.
 *
 * @package module_languages
 * @author sidler@mulchprod.de
 *
 * @module languages
 * @moduleId _languages_modul_id_
 *
 * @blockFromAutosave
 */
class LanguagesLanguageset extends Model implements ModelInterface {

    private $arrLanguageSet = array();

    /**
     * Inits the current object and loads the language-mappings
     * @return void
     */
    protected function initObjectInternal() {
        $strQuery = "SELECT * FROM " . _dbprefix_ . "languages_languageset WHERE languageset_id = ?";
        $arrRow = $this->objDB->getPArray($strQuery, array($this->getSystemid()));

        if(count($arrRow) > 0) {
            $this->arrLanguageSet = array();
            foreach($arrRow as $arrSingleRow) {
                $this->arrLanguageSet[$arrSingleRow["languageset_language"]] = $arrSingleRow["languageset_systemid"];
            }
        }
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return "";
    }


    /**
     * Updates the current state to the database
     *
     * @param bool $strPrevId
     *
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
            $strQuery = "DELETE FROM " . _dbprefix_ . "languages_languageset WHERE languageset_id = ?";
            $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
        }

        Logger::getInstance()->info("updating languageset " . $this->getSystemid());

        $arrValues = array();
        foreach($this->arrLanguageSet as $strLanguage => $strSystemid) {
            $arrValues[] = array($this->getSystemid(), $strLanguage, $strSystemid);
        }

        return $this->objDB->multiInsert("languages_languageset", array("languageset_id", "languageset_language", "languageset_systemid"), $arrValues);
    }

    /**
     * Called whenever a update-request was fired.
     * Use this method to synchronize yourselves with the database.
     * Use only updates, inserts are not required to be implemented.
     *
     * @return bool
     */
    protected function updateStateToDb() {
        return true;
    }

    /**
     * Returns the id of the mapped systemrecord for the given language.
     * If no record exists, NULL is returned instead.
     *
     * @param string $strLanguageid
     *
     * @return string or null
     */
    public function getSystemidForLanguageid($strLanguageid) {
        if(isset($this->arrLanguageSet[$strLanguageid])) {
            return $this->arrLanguageSet[$strLanguageid];
        }

        return null;
    }

    /**
     * Returns the id of the language the passed record is assigned to, null otherwise.
     *
     * @param string $strSystemid
     *
     * @return string or null
     */
    public function getLanguageidForSystemid($strSystemid) {
        foreach($this->arrLanguageSet as $strLanguage => $strRecord) {
            if($strSystemid == $strRecord) {
                return $strLanguage;
            }
        }

        return null;
    }

    /**
     * Sets the systemid for a language.
     *
     * @param string $strSystemid
     * @param string $strLanguageid
     *
     * @return bool
     */
    public function setSystemidForLanguageid($strSystemid, $strLanguageid) {

        if(!validateSystemid($strSystemid) || !validateSystemid($strLanguageid)) {
            return false;
        }

        $this->arrLanguageSet[$strLanguageid] = $strSystemid;

        return $this->updateObjectToDb();
    }

    /**
     * Removes a single systemid from a languageset
     *
     * @param string $strSystemid
     * @return string
     */
    public function removeSystemidFromLanguageeset($strSystemid) {
        foreach($this->arrLanguageSet as $strId => $strSetSystemid) {
            if($strSetSystemid == $strSystemid) {
                unset($this->arrLanguageSet[$strId]);
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
     *
     * @return LanguagesLanguageset
     */
    public static function getLanguagesetForSystemid($strSystemid) {
        $strQuery = "SELECT languageset_id
                       FROM " . _dbprefix_ . "languages_languageset
                      WHERE languageset_systemid = ?";

        $arrRow = Carrier::getInstance()->getObjDB()->getPRow($strQuery, array($strSystemid));

        if(isset($arrRow["languageset_id"])) {
            $objReturn = new LanguagesLanguageset($arrRow["languageset_id"]);
            return $objReturn;
        }

        return null;
    }

    /**
     * Creates a new languageset for the passed systemid and returns the new
     * instance.
     * If there's a languageset already existing, the languageset is loaded
     * instead of creating a new one.
     *
     * @param string $strSystemid
     * @param LanguagesLanguage $objTargetLanguage
     *
     * @return LanguagesLanguageset
     */
    public static function createLanguagesetForSystemid($strSystemid, $objTargetLanguage) {

        //already existing?
        $objLanguageset = LanguagesLanguageset::getLanguagesetForSystemid($strSystemid);

        if($objLanguageset == null) {
            //create a new one
            $objLanguageset = new LanguagesLanguageset("");
            $objLanguageset->setSystemidForLanguageid($strSystemid, $objTargetLanguage->getSystemid());
        }
        elseif($objLanguageset->getSystemidForLanguageid($objTargetLanguage->getSystemid()) == null) {
            //update the languageset
            $objLanguageset->setSystemidForLanguageid($strSystemid, $objTargetLanguage->getSystemid());
        }


        return new $objLanguageset;
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
