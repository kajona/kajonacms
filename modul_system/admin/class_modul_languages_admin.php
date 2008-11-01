<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*                                                                                                       *
*   class_modul_languages_admin.php                                                                     *
*   Admin-Parts to manage the languages                                                                 *
*                                                                                                       *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                              *
********************************************************************************************************/

//Base class & interface
include_once(_adminpath_."/class_admin.php");
include_once(_adminpath_."/interface_admin.php");
//Model
include_once(_systempath_."/class_modul_languages_language.php");
/**
 * Admin-class to manage all languages
 *
 * @package modul_languages
 */
class class_modul_languages_admin extends class_admin implements interface_admin {
    private $strAction;

    /**
     * Constructor
     *
     */
	public function __construct() {
		$arrModul["name"] 				= "modul_languages";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _languages_modul_id_;
		$arrModul["table"]     			= _dbprefix_."languages";
		$arrModul["modul"]				= "languages";
		parent::__construct($arrModul);

	}

	/**
	 * Action block to decide which action to perform
	 *
	 * @param string $strAction
	 */
	public function action($strAction = "") {
	    $strReturn = "";
		if($strAction == "")
			$strAction ="list";

		$this->strAction = $strAction;

		try {

    		if($strAction == "list")
    		    $strReturn .= $this->actionList();
    		if($strAction == "newLanguage")
    		    $strReturn .= $this->actionNewLanguage("new");
    		if($strAction == "editLanguage")
    		    $strReturn .= $this->actionNewLanguage("edit");
    		if($strAction == "saveLanguage") {
    		    $strReturn = $this->actionSaveLanguage();
    		    if($strReturn == "")
    		        $this->adminReload(_indexpath_."?admin=1&module=".$this->arrModule["modul"]);
    		}
    		if($strAction == "deleteLanguageFinal") {
    		    $strReturn = $this->actionDeleteLanguageFinal();
    		    if($strReturn == "")
    		        $this->adminReload(_indexpath_."?admin=1&module=".$this->arrModule["modul"]);
    		}

		}
		catch (class_exception $objException) {
		    $objException->processException();
		    $strReturn = "An internal error occured: ".$objException->getMessage();
		}

		$this->strOutput = $strReturn;
	}


	public function getOutputContent() {
		return $this->strOutput;
	}

	public function getOutputModuleNavi() {
	    $arrReturn = array();
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getText("modul_rechte"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
		$arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getText("modul_liste"), "", "", true, "adminnavi"));
	    $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "newLanguage", "", $this->getText("modul_anlegen"), "", "", true, "adminnavi"));
		$arrReturn[] = array("", "");
		return $arrReturn;
	}



// --- List-Functions -----------------------------------------------------------------------------------

	/**
	 * Returns a list of the languages
	 *
	 * @return string
	 */
	private function actionList() {

		$strReturn = "";
		$intI = 0;
		//rights
		if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {
		   $arrObjLanguages = class_modul_languages_language::getAllLanguages();

            foreach ($arrObjLanguages as $objOneLanguage) {
                //Correct Rights?
				if($this->objRights->rightView($objOneLanguage->getSystemid())) {
					$strAction = "";
					if($this->objRights->rightEdit($objOneLanguage->getSystemid()))
		    		    $strAction .= $this->objToolkit->listButton(getLinkAdmin("languages", "editLanguage", "&systemid=".$objOneLanguage->getSystemid(), "", $this->getText("language_bearbeiten"), "icon_pencil.gif"));
		    		if($this->objRights->rightDelete($objOneLanguage->getSystemid()))
		    		    $strAction .= $this->objToolkit->listDeleteButton($this->getText("lang_".$objOneLanguage->getStrName()).$this->getText("delete_question").getLinkAdmin($this->arrModule["modul"], "deleteLanguageFinal", "&systemid=".$objOneLanguage->getSystemid(), $this->getText("delete_link")));
		    		if($this->objRights->rightEdit($objOneLanguage->getSystemid()))
		    		    $strAction .= $this->objToolkit->listStatusButton($objOneLanguage->getSystemid());
		    		if($this->objRights->rightRight($objOneLanguage->getSystemid()))
		    		    $strAction .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$objOneLanguage->getSystemid(), "", $this->getText("language_rechte"), getRightsImageAdminName($objOneLanguage->getSystemid())));

		  			$strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_language.gif"), $this->getText("lang_".$objOneLanguage->getStrName()).($objOneLanguage->getBitDefault() == 1 ? " (".$this->getText("language_isDefault").")" : ""), $strAction, $intI++);
				}
            }
            if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
                $strReturn .= $this->objToolkit->listRow2Image("", "", getLinkAdmin($this->arrModule["modul"], "newLanguage", "", $this->getText("modul_anlegen"), $this->getText("modul_anlegen"), "icon_blank.gif"), $intI++);

            if(uniStrlen($strReturn) != 0)
                $strReturn = $this->objToolkit->listHeader().$strReturn.$this->objToolkit->listFooter();

		   if(count($arrObjLanguages) == 0)
		       $strReturn .= $this->getText("liste_leer");

		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}


	/**
	 * Creates the form to edit an existing language, or to create a new language
	 *
	 * @param string $strMode
	 * @return string
	 */
	private function actionNewLanguage($strMode = "new") {
	    $strReturn = "";
	    $arrLanguages = array();
	    $arrDefault = array(0 => $this->getText("nondefault"), 1 => $this->getText("default"));
	    $objLang = new class_modul_languages_language();
	    $arrLanguages = $objLang->getAllLanguagesAvailable();
	    $arrLanguagesDD = array();
	    foreach ($arrLanguages as $strLangShort)
	       $arrLanguagesDD[$strLangShort] = $this->getText("lang_".$strLangShort);

        if($strMode == "new") {
            if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
                $strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&amp;module=languages&amp;action=saveLanguage");
                $strReturn .= $this->objToolkit->formInputDropdown("language_name", $arrLanguagesDD, $this->getText("language_name"));
                $strReturn .= $this->objToolkit->formInputDropdown("language_default", $arrDefault, $this->getText("language_default"));
                $strReturn .= $this->objToolkit->formInputHidden("mode", "new");
                $strReturn .= $this->objToolkit->formInputSubmit($this->getText("lang_save"));
                $strReturn .= $this->objToolkit->formClose();
            }
            else
			    $strReturn = $this->getText("fehler_recht");
        }
        elseif ($strMode == "edit") {
            $objLanguage = new class_modul_languages_language($this->getSystemid());
            if($objLanguage->rightEdit()) {
                $strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&amp;module=languages&amp;action=saveLanguage");
                $strReturn .= $this->objToolkit->formInputDropdown("language_name", $arrLanguagesDD, $this->getText("language_name"), $objLanguage->getStrName());
                $strReturn .= $this->objToolkit->formInputDropdown("language_default", $arrDefault, $this->getText("language_default"), $objLanguage->getBitDefault());
                $strReturn .= $this->objToolkit->formInputHidden("mode", "edit");
                $strReturn .= $this->objToolkit->formInputHidden("systemid", $objLanguage->getSystemid());
                $strReturn .= $this->objToolkit->formInputSubmit($this->getText("lang_save"));
                $strReturn .= $this->objToolkit->formClose();
            }
            else
			    $strReturn = $this->getText("fehler_recht");

        }
        return $strReturn;
	}


	/**
	 * saves the submitted form-data as a new language, oder updates the corresponding language
	 *
	 * @return string, "" in case of success
	 */
	private function actionSaveLanguage() {
	    if($this->getParam("mode") == "new") {
	        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {

	            //language already existing?
	            if(class_modul_languages_language::getLanguageByName($this->getParam("language_name")) !== false)
	               return $this->getText("language_existing");

	            //reset the default languages?
	            if($this->getParam("language_default") == "1")
	                class_modul_languages_language::resetAllDefaultLanguages();

                $objLanguage = new class_modul_languages_language();
                $objLanguage->setStrName($this->getParam("language_name"));
               	$objLanguage->setBitDefault($this->getParam("language_default"));

                if(!$objLanguage->saveObjectToDb())
                    throw new class_exception("Error creating new language", class_exception::$level_ERROR);
	        }
	        else
			    $strReturn = $this->getText("fehler_recht");
	    }
	    elseif ($this->getParam("mode") == "edit") {
	        $objLanguage = new class_modul_languages_language($this->getSystemid());
	        $strOldLanguage = $objLanguage->getStrName();
	        if($objLanguage->rightEdit()) {
	            //language already existing?
	            $objTestLang = class_modul_languages_language::getLanguageByName($this->getParam("language_name"));
	            if($objTestLang !== false && $objTestLang->getSystemid() != $this->getSystemid())
	               return $this->getText("language_existing");

	            //reset the default languages?
	            if($this->getParam("language_default") == "1")
	                class_modul_languages_language::resetAllDefaultLanguages();


                $objLanguage->setStrName($this->getParam("language_name"));
                $objLanguage->setBitDefault($this->getParam("language_default"));
                if(!$objLanguage->updateObjectToDb())
                    throw new class_exception("Error updating language", class_exception::$level_ERROR);

                //move contents to a new language
                if($strOldLanguage != $objLanguage->getStrName()) {
                    if(!$objLanguage->moveContentsToCurrentLanguage($strOldLanguage))
                        throw new class_exception("Error moving contents to new language", class_exception::$level_ERROR);
                }
            }
            else
			    $strReturn = $this->getText("fehler_recht");
	    }
	}

	/**
	 * Deletes the language
	 *
	 * @return string
	 */
	private function actionDeleteLanguageFinal() {
	    $strReturn = "";
        if($this->objRights->rightDelete($this->getSystemid())) {
            $objLang = new class_modul_languages_language($this->getSystemid());
            if(!$objLang->deleteObject())
                throw new class_exception("Error deleting language", class_exception::$level_ERROR);
        }
        else
		    $strReturn = $this->getText("fehler_recht");
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
        $arrObjLanguages = class_modul_languages_language::getAllLanguages(true);
        //create a button for each of them
        $strButtons = "";
        if(count($arrObjLanguages) > 1) {
            foreach ($arrObjLanguages as $objOneLanguage) {
            	$strButtons .= $this->objToolkit->getLanguageButton($this->getText("lang_".$objOneLanguage->getStrName()),
            	                                                     "switchLanguage('".$objOneLanguage->getStrName()."')",
            	                                                     ($objOneLanguage->getStrName() == $this->getLanguageToWorkOn() ? true : false));
            }

            $strReturn = $this->objToolkit->getLanguageSwitch($strButtons);
        }

        return $strReturn;
	}

}
?>