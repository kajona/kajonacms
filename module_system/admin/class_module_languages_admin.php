<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                              *
********************************************************************************************************/

/**
 * Admin-class to manage all languages
 *
 * @package module_languages
 * @author sidler@mulchprod.de
 */
class class_module_languages_admin extends class_admin_simple implements interface_admin {

    /**
     * Constructor
     *
     */
	public function __construct() {
        $this->setArrModuleEntry("modul", "languages");
        $this->setArrModuleEntry("moduleId", _languages_modul_id_);
		parent::__construct();

	}


    public function getOutputModuleNavi() {
	    $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
		$arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
	    $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "new", "", $this->getLang("module_action_new"), "", "", true, "adminnavi"));
		return $arrReturn;
	}


    /**
	 * Returns a list of the languages
	 *
	 * @return string
     * @permissions view
	 */
	protected function actionList() {

        $objArraySectionIterator = new class_array_section_iterator(class_module_languages_language::getNumberOfLanguagesAvailable());
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(class_module_languages_language::getAllLanguages(false, $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        return $this->renderList($objArraySectionIterator);

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
     * @return string
     * @permissions edit
     */
	protected function actionNew($strMode = "new") {

	    $objLang = new class_module_languages_language();
	    $arrLanguages = $objLang->getAllLanguagesAvailable();
	    $arrLanguagesDD = array();
	    foreach ($arrLanguages as $strLangShort)
	       $arrLanguagesDD[$strLangShort] = $this->getLang("lang_".$strLangShort);

        if($strMode == "new")
            $objLanguage = new class_module_languages_language();
        else {
            $objLanguage = new class_module_languages_language($this->getSystemid());
            if(!$objLanguage->rightEdit())
                return $this->getLang("commons_error_permissions");
        }

        $objForm = $this->getAdminForm($objLanguage);

        $objForm->addField(new class_formentry_hidden("", "mode"))->setStrValue($strMode);
        return $objForm->renderForm(getLinkAdminHref($this->arrModule["modul"], "saveLanguage"));

	}

    /**
     * Creates the admin-form object
     * @param class_module_languages_language $objLanguage
     * @return class_admin_formgenerator
     */
    private function getAdminForm(class_module_languages_language $objLanguage) {

        $objLang = new class_module_languages_language();
        $arrLanguages = $objLang->getAllLanguagesAvailable();
        $arrLanguagesDD = array();
        foreach ($arrLanguages as $strLangShort)
           $arrLanguagesDD[$strLangShort] = $this->getLang("lang_".$strLangShort);

        $objForm = new class_admin_formgenerator("language", $objLanguage);
        $objForm->addDynamicField("name")->setArrKeyValues($arrLanguagesDD);
        $objForm->addDynamicField("default");

        return $objForm;
    }

	/**
	 * saves the submitted form-data as a new language, oder updates the corresponding language
	 *
	 * @return string, "" in case of success
     * @permissions edit
	 */
	protected function actionSaveLanguage() {
        $strOldLang = "";
	    if($this->getParam("mode") == "new") {
            $objLanguage = new class_module_languages_language();
        }
        else {
            $objLanguage = new class_module_languages_language($this->getSystemid());
            $strOldLang = $objLanguage->getStrName();
            if(!$objLanguage->rightEdit())
                return $this->getLang("commons_error_permissions");
        }

        $objForm = $this->getAdminForm($objLanguage);
        $objForm->updateSourceObject();


	    if($this->getParam("mode") == "new") {
            //language already existing?
            if(class_module_languages_language::getLanguageByName($objLanguage->getStrName()) !== false)
               return $this->getLang("language_existing");
        }
        elseif ($this->getParam("mode") == "edit") {
            $objTestLang = class_module_languages_language::getLanguageByName($objLanguage->getStrName());
            if($objTestLang !== false && $objTestLang->getSystemid() != $objLanguage->getSystemid())
               return $this->getLang("language_existing");
        }

        if(!$objLanguage->updateObjectToDb() )
            throw new class_exception("Error creating new language", class_exception::$level_ERROR);

        if ($this->getParam("mode") == "edit") {
            //move contents to a new language
            if($strOldLang != $objLanguage->getStrName()) {
                if(!$objLanguage->moveContentsToCurrentLanguage($strOldLang))
                    throw new class_exception("Error moving contents to new language", class_exception::$level_ERROR);
            }
        }

        $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
	}

	/**
	 * Deletes the language
	 *
	 * @return string
     * @permissions delete
	 */
	protected function actionDelete() {
	    $strReturn = "";
        $objLang = new class_module_languages_language($this->getSystemid());
        if($objLang->rightDelete()) {
            if(!$objLang->deleteObject())
                throw new class_exception("Error deleting language", class_exception::$level_ERROR);

            //check if the current active one was deleted. if, then reset. #kajona trace id 613
            if($this->getLanguageToWorkOn() == $objLang->getStrName()) {
                $this->objDB->flushQueryCache();
                $arrLangs = class_module_languages_language::getAllLanguages();
                if(count($arrLangs) > 0 ) {
                    $objLang->setStrAdminLanguageToWorkOn($arrLangs[0]->getStrName());
                }
            }

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
        }
        else
		    $strReturn = $this->getLang("commons_error_permissions");
		return $strReturn;
	}

	/**
	 * Creates a language-switch as ready-to-output html-code
	 * If there's just one language installed, an empty string is returned
	 *
	 * @return string
	 */
	public function getLanguageSwitch() {
	    $strReturn = "";
        //Load all languages available
        $arrObjLanguages = class_module_languages_language::getAllLanguages(true);
        //create a button for each of them
        $strButtons = "";
        if(count($arrObjLanguages) > 1) {
            foreach ($arrObjLanguages as $objOneLanguage) {
            	$strButtons .= $this->objToolkit->getLanguageButton(
                    $objOneLanguage->getStrName(),
                    $this->getLang("lang_".$objOneLanguage->getStrName()),
            	    ($objOneLanguage->getStrName() == $this->getLanguageToWorkOn() ? true : false)
                );
            }

            $strReturn = $this->objToolkit->getLanguageSwitch($strButtons);
        }

        return $strReturn;
	}

}
