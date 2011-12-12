<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
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
class class_module_languages_admin extends class_admin implements interface_admin {

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
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getText("commons_module_permissions"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
		$arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getText("commons_list"), "", "", true, "adminnavi"));
	    $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "newLanguage", "", $this->getText("modul_anlegen"), "", "", true, "adminnavi"));
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

		$strReturn = "";
		$intI = 0;
        $arrObjLanguages = class_module_languages_language::getAllLanguages();

        foreach ($arrObjLanguages as $objOneLanguage) {
            //Correct Rights?
            if($objOneLanguage->rightView()) {
                $strAction = "";
                if($objOneLanguage->rightEdit())
                    $strAction .= $this->objToolkit->listButton(getLinkAdmin("languages", "editLanguage", "&systemid=".$objOneLanguage->getSystemid(), "", $this->getText("language_bearbeiten"), "icon_pencil.gif"));
                if($objOneLanguage->rightDelete())
                    $strAction .= $this->objToolkit->listDeleteButton($this->getText("lang_".$objOneLanguage->getStrName()), $this->getText("delete_question"), getLinkAdminHref($this->arrModule["modul"], "deleteLanguageFinal", "&systemid=".$objOneLanguage->getSystemid()));
                if($objOneLanguage->rightEdit())
                    $strAction .= $this->objToolkit->listStatusButton($objOneLanguage->getSystemid());
                if($objOneLanguage->rightRight())
                    $strAction .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$objOneLanguage->getSystemid(), "", $this->getText("commons_edit_permissions"), getRightsImageAdminName($objOneLanguage->getSystemid())));

                $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_language.gif"), $this->getText("lang_".$objOneLanguage->getStrName()).($objOneLanguage->getBitDefault() == 1 ? " (".$this->getText("language_isDefault").")" : ""), $strAction, $intI++);
            }
        }
        if($this->getObjModule()->rightEdit())
            $strReturn .= $this->objToolkit->listRow2Image("", "", getLinkAdmin($this->arrModule["modul"], "newLanguage", "", $this->getText("modul_anlegen"), $this->getText("modul_anlegen"), "icon_new.gif"), $intI++);

        if(uniStrlen($strReturn) != 0)
            $strReturn = $this->objToolkit->listHeader().$strReturn.$this->objToolkit->listFooter();

        if(count($arrObjLanguages) == 0)
            $strReturn .= $this->getText("liste_leer");

		return $strReturn;
	}


    protected function actionEditLanguage() {
        return $this->actionNewLanguage("edit");
    }

	/**
	 * Creates the form to edit an existing language, or to create a new language
	 *
	 * @param string $strMode
	 * @return string
     * @permissions edit
	 */
	protected function actionNewLanguage($strMode = "new") {
	    $strReturn = "";
	    $arrDefault = array(0 => $this->getText("commons_no"), 1 => $this->getText("commons_yes"));
	    $objLang = new class_module_languages_language();
	    $arrLanguages = $objLang->getAllLanguagesAvailable();
	    $arrLanguagesDD = array();
	    foreach ($arrLanguages as $strLangShort)
	       $arrLanguagesDD[$strLangShort] = $this->getText("lang_".$strLangShort);

        if($strMode == "new") {
            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveLanguage"));
            $strReturn .= $this->objToolkit->formInputDropdown("language_name", $arrLanguagesDD, $this->getText("commons_language_field"));
            $strReturn .= $this->objToolkit->formInputDropdown("language_default", $arrDefault, $this->getText("language_default"));
            $strReturn .= $this->objToolkit->formInputHidden("mode", "new");
            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("commons_save"));
            $strReturn .= $this->objToolkit->formClose();

            $strReturn .= $this->objToolkit->setBrowserFocus("language_name");
        }
        elseif ($strMode == "edit") {
            $objLanguage = new class_module_languages_language($this->getSystemid());
            if($objLanguage->rightEdit()) {
                $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveLanguage"));
                $strReturn .= $this->objToolkit->formInputDropdown("language_name", $arrLanguagesDD, $this->getText("commons_language_field"), $objLanguage->getStrName());
                $strReturn .= $this->objToolkit->formInputDropdown("language_default", $arrDefault, $this->getText("language_default"), $objLanguage->getBitDefault());
                $strReturn .= $this->objToolkit->formInputHidden("mode", "edit");
                $strReturn .= $this->objToolkit->formInputHidden("systemid", $objLanguage->getSystemid());
                $strReturn .= $this->objToolkit->formInputSubmit($this->getText("commons_save"));
                $strReturn .= $this->objToolkit->formClose();

                $strReturn .= $this->objToolkit->setBrowserFocus("language_name");
            }
            else
			    $strReturn = $this->getText("commons_error_permissions");

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
               return $this->getText("language_existing");

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
	               return $this->getText("language_existing");

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
			    $strReturn = $this->getText("commons_error_permissions");
	    }

        return $strReturn;
	}

	/**
	 * Deletes the language
	 *
	 * @return string
	 */
	protected function actionDeleteLanguageFinal() {
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
		    $strReturn = $this->getText("commons_error_permissions");
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
                    $this->getText("lang_".$objOneLanguage->getStrName()),
            	    ($objOneLanguage->getStrName() == $this->getLanguageToWorkOn() ? true : false)
                );
            }

            $strReturn = $this->objToolkit->getLanguageSwitch($strButtons);
        }

        return $strReturn;
	}

}
