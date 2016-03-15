<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                              *
********************************************************************************************************/

namespace Kajona\System\Admin;

use Kajona\System\Admin\Formentries\FormentryHidden;
use Kajona\System\System\ArraySectionIterator;
use Kajona\System\System\Carrier;
use Kajona\System\System\Exception;
use Kajona\System\System\LanguagesLanguage;
use Kajona\System\System\Link;
use Kajona\System\System\Model;


/**
 * Admin-class to manage all languages
 *
 * @package module_languages
 * @author sidler@mulchprod.de
 *
 * @module languages
 * @moduleId _languages_modul_id_
 */
class LanguagesAdmin extends AdminSimple implements AdminInterface {

    private static $arrLanguageSwitchEntries = null;
    private static $strOnChangeHandler = "KAJONA.admin.switchLanguage(this.value);";
    private static $strActiveKey = "";


    public function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("view", Link::getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        return $arrReturn;
    }


    /**
     * Returns a list of the languages
     *
     * @return string
     * @autoTestable
     * @permissions view
     */
    protected function actionList() {

        $objArraySectionIterator = new ArraySectionIterator(LanguagesLanguage::getNumberOfLanguagesAvailable());
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(LanguagesLanguage::getObjectList(false, $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        return $this->renderList($objArraySectionIterator);

    }

    protected function renderCopyAction(Model $objListEntry) {
        return "";
    }


    /**
     * @return string
     * @permissions edit
     */
    protected function actionEdit() {
        return $this->actionNew("edit");
    }

    /**
     * Creates the form to edit an existing language, or to create a new language
     *
     * @param string $strMode
     *
     * @return string
     * @permissions edit
     * @autoTestable
     */
    protected function actionNew($strMode = "new") {

        $objLang = new LanguagesLanguage();
        $arrLanguages = $objLang->getAllLanguagesAvailable();
        $arrLanguagesDD = array();
        foreach($arrLanguages as $strLangShort) {
            $arrLanguagesDD[$strLangShort] = $this->getLang("lang_" . $strLangShort);
        }

        if($strMode == "new") {
            $objLanguage = new LanguagesLanguage();
        }
        else {
            $objLanguage = new LanguagesLanguage($this->getSystemid());
            if(!$objLanguage->rightEdit()) {
                return $this->getLang("commons_error_permissions");
            }
        }

        $objForm = $this->getAdminForm($objLanguage);

        $objForm->addField(new FormentryHidden("", "mode"))->setStrValue($strMode);
        return $objForm->renderForm(Link::getLinkAdminHref($this->getArrModule("modul"), "saveLanguage"));

    }

    /**
     * Creates the admin-form object
     *
     * @param LanguagesLanguage $objLanguage
     *
     * @return AdminFormgenerator
     */
    private function getAdminForm(LanguagesLanguage $objLanguage) {

        $objLang = new LanguagesLanguage();
        $arrLanguages = $objLang->getAllLanguagesAvailable();
        $arrLanguagesDD = array();
        foreach($arrLanguages as $strLangShort) {
            $arrLanguagesDD[$strLangShort] = $this->getLang("lang_" . $strLangShort);
        }

        $objForm = new AdminFormgenerator("language", $objLanguage);
        $objForm->addDynamicField("strName")->setArrKeyValues($arrLanguagesDD);
        $objForm->addDynamicField("bitDefault");

        return $objForm;
    }

    /**
     * saves the submitted form-data as a new language, oder updates the corresponding language
     *
     * @throws Exception
     * @return string, "" in case of success
     * @permissions edit
     */
    protected function actionSaveLanguage() {
        $strOldLang = "";
        if($this->getParam("mode") == "new") {
            $objLanguage = new LanguagesLanguage();
        }
        else {
            $objLanguage = new LanguagesLanguage($this->getSystemid());
            $strOldLang = $objLanguage->getStrName();
            if(!$objLanguage->rightEdit()) {
                return $this->getLang("commons_error_permissions");
            }
        }

        $objForm = $this->getAdminForm($objLanguage);

        if(!$objForm->validateForm())
            return $this->actionNew($this->getParam("mode"), $objForm);


        $objForm->updateSourceObject();


        if($this->getParam("mode") == "new") {
            //language already existing?
            if(LanguagesLanguage::getLanguageByName($objLanguage->getStrName()) !== false) {
                return $this->getLang("language_existing");
            }
        }
        elseif($this->getParam("mode") == "edit") {
            $objTestLang = LanguagesLanguage::getLanguageByName($objLanguage->getStrName());
            if($objTestLang !== false && $objTestLang->getSystemid() != $objLanguage->getSystemid()) {
                return $this->getLang("language_existing");
            }
        }

        if(!$objLanguage->updateObjectToDb()) {
            throw new Exception("Error creating new language", Exception::$level_ERROR);
        }

        if($this->getParam("mode") == "edit") {
            //move contents to a new language
            if($strOldLang != $objLanguage->getStrName()) {
                if(!$objLanguage->moveContentsToCurrentLanguage($strOldLang)) {
                    throw new Exception("Error moving contents to new language", Exception::$level_ERROR);
                }
            }
        }

        $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul")));
    }


    /**
     * Creates a language-switch as ready-to-output html-code
     * If there's just one language installed, an empty string is returned
     *
     * @return string
     */
    public function getLanguageSwitch() {
        $strReturn = "";
        $strButtons = "";
        if(self::$arrLanguageSwitchEntries != null && count(self::$arrLanguageSwitchEntries) > 1) {
            foreach(self::$arrLanguageSwitchEntries as $strKey => $strValue) {
                $strButtons .= $this->objToolkit->getLanguageButton(
                    $strKey,
                    $strValue,
                    $strKey == self::$strActiveKey
                );
            }

            $strReturn = $this->objToolkit->getLanguageSwitch($strButtons, self::$strOnChangeHandler);
        }

        return $strReturn;
    }

    /**
     * Enables the common language switch, switching the backends language to work on.
     * If you want to create a custom switch, use setArrLanguageSwitchEntries and setStrOnChangeHandler
     * to customize all switch-content.
     *
     * @static
     */
    public static function enableLanguageSwitch() {
        if(self::$arrLanguageSwitchEntries == null) {
            $arrObjLanguages = LanguagesLanguage::getObjectList(true);
            if(count($arrObjLanguages) > 1) {
                self::$arrLanguageSwitchEntries = array();
                foreach($arrObjLanguages as $objOneLang) {
                    self::$arrLanguageSwitchEntries[$objOneLang->getStrName()] = Carrier::getInstance()->getObjLang()->getLang("lang_" . $objOneLang->getStrName(), "languages");
                }
                $objLanguage = new LanguagesLanguage();
                self::$strActiveKey = $objLanguage->getAdminLanguage();
            }
        }
    }

    /**
     * Pass custom entries to the current switch, replacing the default ones.
     * Schema key => value
     *
     * @static
     *
     * @param $arrLanguageSwitchEntries
     */
    public static function setArrLanguageSwitchEntries($arrLanguageSwitchEntries) {
        self::$arrLanguageSwitchEntries = $arrLanguageSwitchEntries;
    }

    /**
     * Change the default on-change handler of the languages dropdown to a custom function.
     *
     * @static
     *
     * @param $onChangeHandler
     */
    public static function setStrOnChangeHandler($onChangeHandler) {
        self::$strOnChangeHandler = $onChangeHandler;
    }

    /**
     * Set the currently active key for the language switch
     *
     * @static
     *
     * @param $strActiveKey
     */
    public static function setStrActiveKey($strActiveKey) {
        self::$strActiveKey = $strActiveKey;
    }

}
