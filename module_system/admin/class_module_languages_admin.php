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


	protected function getOutputModuleNavi() {
	    $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
		$arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
	    $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "new", "", $this->getLang("module_action_new"), "", "", true, "adminnavi"));
		$arrReturn[] = array("", "");
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
	    $strReturn = "";
	    $arrDefault = array(0 => $this->getLang("commons_no"), 1 => $this->getLang("commons_yes"));
	    $objLang = new class_module_languages_language();
	    $arrLanguages = $objLang->getAllLanguagesAvailable();
	    $arrLanguagesDD = array();
	    foreach ($arrLanguages as $strLangShort)
	       $arrLanguagesDD[$strLangShort] = $this->getLang("lang_".$strLangShort);

        if($strMode == "new") {
            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveLanguage"));
            $strReturn .= $this->objToolkit->formInputDropdown("language_name", $arrLanguagesDD, $this->getLang("commons_language_field"));
            $strReturn .= $this->objToolkit->formInputDropdown("language_default", $arrDefault, $this->getLang("language_default"));
            $strReturn .= $this->objToolkit->formInputHidden("mode", "new");
            $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
            $strReturn .= $this->objToolkit->formClose();

            $strReturn .= $this->objToolkit->setBrowserFocus("language_name");
        }
        elseif ($strMode == "edit") {
            $objLanguage = new class_module_languages_language($this->getSystemid());
            if($objLanguage->rightEdit()) {
                $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveLanguage"));
                $strReturn .= $this->objToolkit->formInputDropdown("language_name", $arrLanguagesDD, $this->getLang("commons_language_field"), $objLanguage->getStrName());
                $strReturn .= $this->objToolkit->formInputDropdown("language_default", $arrDefault, $this->getLang("language_default"), $objLanguage->getBitDefault());
                $strReturn .= $this->objToolkit->formInputHidden("mode", "edit");
                $strReturn .= $this->objToolkit->formInputHidden("systemid", $objLanguage->getSystemid());
                $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
                $strReturn .= $this->objToolkit->formClose();

                $strReturn .= $this->objToolkit->setBrowserFocus("language_name");
            }
            else
			    $strReturn = $this->getLang("commons_error_permissions");

        }
        return $strReturn;
	}


	/**
	 * saves the submitted form-data as a new language, oder updates the corresponding language
	 *
	 * @return string, "" in case of success
     * @permissions edit
	 */
	protected function actionSaveLanguage() {
        $strReturn = "";
	    if($this->getParam("mode") == "new") {
            //language already existing?
            if(class_module_languages_language::getLanguageByName($this->getParam("language_name")) !== false)
               return $this->getLang("language_existing");

            //reset the default languages?
            if($this->getParam("language_default") == "1")
                class_module_languages_language::resetAllDefaultLanguages();

            $objLanguage = new class_module_languages_language();
            $objLanguage->setStrName($this->getParam("language_name"));
            $objLanguage->setBitDefault($this->getParam("language_default"));

            if(!$objLanguage->updateObjectToDb() )
                throw new class_exception("Error creating new language", class_exception::$level_ERROR);

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
	    }
	    elseif ($this->getParam("mode") == "edit") {
	        $objLanguage = new class_module_languages_language($this->getSystemid());
	        $strOldLanguage = $objLanguage->getStrName();
	        if($objLanguage->rightEdit()) {
	            //language already existing?
	            $objTestLang = class_module_languages_language::getLanguageByName($this->getParam("language_name"));
	            if($objTestLang !== false && $objTestLang->getSystemid() != $this->getSystemid())
	               return $this->getLang("language_existing");

	            //reset the default languages?
	            if($this->getParam("language_default") == "1")
	                class_module_languages_language::resetAllDefaultLanguages();


                $objLanguage->setStrName($this->getParam("language_name"));
                $objLanguage->setBitDefault($this->getParam("language_default"));
                if(!$objLanguage->updateObjectToDb())
                    throw new class_exception("Error updating language", class_exception::$level_ERROR);

                //move contents to a new language
                if($strOldLanguage != $objLanguage->getStrName()) {
                    if(!$objLanguage->moveContentsToCurrentLanguage($strOldLanguage))
                        throw new class_exception("Error moving contents to new language", class_exception::$level_ERROR);
                }

                $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
            }
            else
			    $strReturn = $this->getLang("commons_error_permissions");
	    }

        return $strReturn;
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
