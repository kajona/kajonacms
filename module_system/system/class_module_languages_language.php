<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                           *
********************************************************************************************************/

/**
 * Model for a language
 *
 * @package module_languages
 * @author sidler@mulchprod.de
 * @targetTable languages.language_id
 *
 * @module languages
 * @moduleId _languages_modul_id_
 */
class class_module_languages_language extends class_model implements interface_model, interface_admin_listable {

    /**
     * @var string
     * @tableColumn language_name
     * @tableColumnDatatype char254
     *
     * @fieldType dropdown
     * @fieldLabel commons_title
     * @fieldMandatory
     * @fieldValidator class_twochars_validator
     *
     * @addSearchIndex
     */
    private $strName = "";

    /**
     * @var bool
     * @tableColumn language_default
     * @tableColumnDatatype int
     *
     * @fieldType yesno
     * @fieldMandatory
     */
    private $bitDefault = false;

    private $strLanguagesAvailable = "ar,bg,cs,da,de,el,en,es,fi,fr,ga,he,hr,hu,hy,id,it,ja,ko,nl,no,pl,pt,ro,ru,sk,sl,sv,th,tr,uk,zh";

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {
        return "icon_language";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        return "";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        return "";
    }


    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getLang("lang_" . $this->getStrName(), "languages") . ($this->getBitDefault() == 1 ? " (" . $this->getLang("language_isDefault", "languages") . ")" : "");
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    protected function updateStateToDb() {

        //if no other language exists, we have a new default language
        $arrObjLanguages = class_module_languages_language::getObjectList();
        if(count($arrObjLanguages) == 0) {
            $this->setBitDefault(1);
        }

        if($this->getBitDefault() == 1) {
            self::resetAllDefaultLanguages();
        }

        return parent::updateStateToDb();
    }

    /**
     * Returns an array of all languages available
     *
     * @param bool $bitJustActive
     * @param null $intStart
     * @param null $intEnd
     *
     * @return class_module_languages_language[]
     * @static
     */
    public static function getObjectList($bitJustActive = false, $intStart = null, $intEnd = null) {

        $objOrmList = new class_orm_objectlist();
        if($bitJustActive)
            $objOrmList->addWhereRestriction(new class_orm_objectlist_restriction(" AND system_status != 0 "));

        return $objOrmList->getObjectList(__CLASS__, "", $intStart, $intEnd);
    }

    /**
     * Returns the number of languages installed in the system
     *
     * @param bool $bitJustActive
     *
     * @return int
     */
    public static function getNumberOfLanguagesAvailable($bitJustActive = false) {

        $objOrmList = new class_orm_objectlist();
        if($bitJustActive)
            $objOrmList->addWhereRestriction(new class_orm_objectlist_restriction(" AND system_status != 0 "));

        return $objOrmList->getObjectCount(__CLASS__);
    }

    /**
     * Returns the language requested.
     * If the language doesn't exist, false is returned
     *
     * @param string $strName
     *
     * @static
     * @return  class_module_languages_language or false
     */
    public static function getLanguageByName($strName) {

        $objOrmList = new class_orm_objectlist();
        $objOrmList->addWhereRestriction(new class_orm_objectlist_restriction("AND language_name = ?", $strName));
        $arrReturn = $objOrmList->getObjectList(__CLASS__);
        if(count($arrReturn) > 0) {
            return new $arrReturn[0];
        }
        else {
            return false;
        }
    }


    /**
     * Resets all default languages.
     * Afterwards, no default language is available!
     *
     * @return bool
     */
    public static function resetAllDefaultLanguages() {
        $strQuery = "UPDATE " . _dbprefix_ . "languages
                     SET language_default = 0";
        return class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array());
    }


    /**
     * Deletes the current object from the database
     *
     * @return bool
     */
    protected function deleteObjectInternal() {
        parent::deleteObjectInternal();

        //if we have just one language remaining, set this one as default
        $arrObjLanguages = class_module_languages_language::getObjectList();
        if(count($arrObjLanguages) == 1) {
            $objOneLanguage = $arrObjLanguages[0];
            $objOneLanguage->setBitDefault(1);
            $objOneLanguage->updateObjectToDb();
        }

        return true;
    }


    /**
     * Moves all contents created in a given language to the current langugage
     *
     * @param string $strSourceLanguage
     *
     * @return bool
     */
    public function moveContentsToCurrentLanguage($strSourceLanguage) {
        $this->objDB->transactionBegin();

        $strQuery1 = "UPDATE " . _dbprefix_ . "page_properties
                        SET pageproperties_language = ?
                        WHERE pageproperties_language = ?";

        $strQuery2 = "UPDATE " . _dbprefix_ . "page_element
                        SET page_element_ph_language = ?
                        WHERE page_element_ph_language = ?";


        $bitCommit = (
            $this->objDB->_pQuery($strQuery1, array($this->getStrName(), $strSourceLanguage))
                && $this->objDB->_pQuery($strQuery2, array($this->getStrName(), $strSourceLanguage))
        );

        if($bitCommit) {
            $this->objDB->transactionCommit();
            class_logger::getInstance()->addLogRow("moved contents from " . $strSourceLanguage . " to " . $this->getStrName() . " successfully", class_logger::$levelInfo);
        }
        else {
            $this->objDB->transactionRollback();
            class_logger::getInstance()->addLogRow("moved contents from " . $strSourceLanguage . " to " . $this->getStrName() . " failed", class_logger::$levelError);
        }

        return $bitCommit;
    }

    /**
     * Tries to determine the language currently active
     * Looks up the session for previous languages,
     * if no entry was found, the default language is being returned
     * part for the portal
     * tries to load the language, the browser sends as accept-language
     *
     * @return string
     */
    public function getPortalLanguage() {
        if($this->objSession->getSession("portalLanguage") !== false && $this->objSession->getSession("portalLanguage") != "") {
            //Return language saved before in the session
            return $this->objSession->getSession("portalLanguage");
        }
        else {
            //try to load the default language
            //maybe the user sent a wanted language
            $strUserLanguages = str_replace(";", ",", getServer("HTTP_ACCEPT_LANGUAGE"));
            if(uniStrlen($strUserLanguages) > 0) {
                $arrLanguages = explode(",", $strUserLanguages);
                //check, if one of the requested languages is available on our system
                foreach($arrLanguages as $strOneLanguage) {
                    if(!preg_match("#q\=[0-9]\.[0-9]#i", $strOneLanguage)) {
                        //search language
                        $strQuery = "SELECT language_name
                                 FROM " . _dbprefix_ . "languages, " . _dbprefix_ . "system
            		             WHERE system_id = language_id
            		             AND system_status = 1
            		             AND language_name= ?
            		             ORDER BY system_sort ASC, system_comment ASC";
                        $arrRow = $this->objDB->getPRow($strQuery, array($strOneLanguage));
                        if(count($arrRow) > 0) {
                            //save to session
                            if(!$this->objSession->getBitClosed()) {
                                $this->objSession->setSession("portalLanguage", $arrRow["language_name"]);
                            }
                            return $arrRow["language_name"];
                        }
                    }
                }
            }

            $strQuery = "SELECT language_name
                     FROM " . _dbprefix_ . "languages, " . _dbprefix_ . "system
		             WHERE system_id = language_id
		             AND language_default = 1
		             AND system_status = 1
		             ORDER BY system_sort ASC, system_comment ASC";
            $arrRow = $this->objDB->getPRow($strQuery, array());
            if(count($arrRow) > 0) {
                //save to session
                if(!$this->objSession->getBitClosed()) {
                    $this->objSession->setSession("portalLanguage", $arrRow["language_name"]);
                }
                return $arrRow["language_name"];
            }
            else {
                //No default language set? Uh oh...
                $strQuery = "SELECT language_name
                     FROM " . _dbprefix_ . "languages, " . _dbprefix_ . "system
		             WHERE system_id = language_id
		             AND system_status = 1
		             ORDER BY system_sort ASC, system_comment ASC";
                $arrRow = $this->objDB->getPRow($strQuery, array());
                if(count($arrRow) > 0) {
                    //save to session
                    if(!$this->objSession->getBitClosed()) {
                        $this->objSession->setSession("portalLanguage", $arrRow["language_name"]);
                    }
                    return $arrRow["language_name"];
                }
                else {
                    return "";
                }
            }
        }
    }

    /**
     * Tries to determin the language currently active
     * Looks up the session for previous languages,
     * if no entry was found, the default language is being returned
     * part for the admin
     *
     * @return string
     */
    public function getAdminLanguage() {
        if($this->objSession->getSession("adminLanguage") !== false && $this->objSession->getSession("adminLanguage") != "") {
            //Return language saved before in the session
            return $this->objSession->getSession("adminLanguage");
        }
        else {

            $strQuery = "SELECT language_name
                     FROM " . _dbprefix_ . "languages, " . _dbprefix_ . "system
		             WHERE system_id = language_id
		             AND language_default = 1
		             ORDER BY system_sort ASC, system_comment ASC";
            $arrRow = $this->objDB->getPRow($strQuery, array());
            if(count($arrRow) > 0) {
                //save to session
                if(!$this->objSession->getBitClosed()) {
                    $this->objSession->setSession("adminLanguage", $arrRow["language_name"]);
                }
                return $arrRow["language_name"];
            }
            else {
                //No default language set? Uh oh...
                $strQuery = "SELECT language_name
                     FROM " . _dbprefix_ . "languages, " . _dbprefix_ . "system
		             WHERE system_id = language_id
		             ORDER BY system_sort ASC, system_comment ASC";
                $arrRow = $this->objDB->getPRow($strQuery, array());
                if(count($arrRow) > 0) {
                    //save to session
                    if(!$this->objSession->getBitClosed()) {
                        $this->objSession->setSession("adminLanguage", $arrRow["language_name"]);
                    }
                    return $arrRow["language_name"];
                }
                else {
                    return "";
                }
            }
        }
    }

    /**
     * Returns the default language, defined in the admin.
     *
     * @return class_module_languages_language
     */
    public static function getDefaultLanguage() {
        //try to load the default language
        $strQuery = "SELECT *
                 FROM " . _dbprefix_ . "languages,
                      " . _dbprefix_ . "system_right,
                      " . _dbprefix_ . "system
                LEFT JOIN "._dbprefix_."system_date
                    ON system_id = system_date_id
	             WHERE system_id = language_id
	             AND system_id = right_id
	             AND language_default = 1
	             AND system_status = 1
	             ORDER BY system_sort ASC, system_comment ASC";
        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array());
        if(count($arrRow) > 0) {
            class_orm_rowcache::addSingleInitRow($arrRow);
            return class_objectfactory::getInstance()->getObject($arrRow["system_id"]);
        }
        else {
            if(count(class_module_languages_language::getObjectList(true)) > 0) {
                $arrLangs = class_module_languages_language::getObjectList(true);
                return $arrLangs[0];
            }

            return null;
        }
    }

    /**
     * Writes the passed language to the session, if the language exists
     *
     * @param string $strLanguage
     */
    public function setStrPortalLanguage($strLanguage) {
        $objLanguage = class_module_languages_language::getLanguageByName($strLanguage);
        if($objLanguage !== false) {
            if($objLanguage->getIntRecordStatus() != 0) {
                if(!$this->objSession->getBitClosed()) {
                    $this->objSession->setSession("portalLanguage", $objLanguage->getStrName());
                }
            }
        }
    }

    /**
     * Writes the passed language to the session, if the language exists
     *
     * @param string $strLanguage
     */
    public function setStrAdminLanguageToWorkOn($strLanguage) {
        $objLanguage = class_module_languages_language::getLanguageByName($strLanguage);
        if($objLanguage !== false) {
            if($objLanguage->getIntRecordStatus() != 0) {
                if(!$this->objSession->getBitClosed()) {
                    $this->objSession->setSession("adminLanguage", $objLanguage->getStrName());
                }
            }
        }
    }


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

    /**
     * Returns a list of all languages available
     *
     * @return array
     */
    public function getAllLanguagesAvailable() {
        return explode(",", $this->strLanguagesAvailable);
    }
}
