<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_modul_faqs_admin.php																			*
* 	Admin-class of the faqs          																	*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/

//Base class
include_once(_adminpath_."/class_admin.php");
include_once(_adminpath_."/interface_admin.php");
//model
include_once(_systempath_."/class_modul_faqs_category.php");
include_once(_systempath_."/class_modul_faqs_faq.php");

/**
 * Admin-Class of the faqs-module. Responsible for editing faqs and organizing them in categories
 *
 * @package modul_faqs
 */
class class_modul_faqs_admin extends class_admin implements interface_admin {

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$arrModul["name"] 				= "modul_faqs";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _faqs_modul_id_;
		$arrModul["table"] 			    = _dbprefix_."faqs";
		$arrModul["table2"]			    = _dbprefix_."faqs_category";
		$arrModul["table3"]			    = _dbprefix_."faqs_member";
		$arrModul["modul"]				= "faqs";

		//Base class
		parent::__construct($arrModul);

	}

	/**
	 * Action block to control the class
	 *
	 * @param string $strAction
	 */
	public function action($strAction = "") {
	    $strReturn = "";
	    if($strAction == "")
	       $strAction = "list";

	    try {

    		if($strAction == "list")
    			$strReturn = $this->actionList();
    		if($strAction == "newCat")
    			$strReturn = $this->actionNewCat("new");
    		if($strAction == "editCat")
    			$strReturn = $this->actionNewCat("edit");
    		if($strAction == "saveCat") {
    		    if($this->validateForm()) {
    			    $strReturn = $this->actionSaveCat();
    			    if($strReturn == "")
                        $this->adminReload(_indexpath_."?admin=1&module=".$this->arrModule["modul"]);
    		    }
    		    else {
    		        if($this->getParam("mode") == "new")
    		            $strReturn = $this->actionNewCat("new");
    		        else
    		            $strReturn = $this->actionNewCat("edit");
    		    }
    		}
    		if($strAction == "deleteCat") {
    			$strReturn = $this->actionDeleteCategory();
    			if($strReturn == "")
                    $this->adminReload(_indexpath_."?admin=1&module=".$this->arrModule["modul"]);
    		}

    		if($strAction == "newFaq")
    			$strReturn = $this->actionNewFaq("new");
    		if($strAction == "editFaq")
    			$strReturn = $this->actionNewFaq("edit");
    		if($strAction == "saveFaq") {
    		    if($this->validateForm()) {
    			    $strReturn = $this->actionSaveFaq();
    			    if($strReturn == "")
                       $this->adminReload(_indexpath_."?admin=1&module=".$this->arrModule["modul"]);
    		    }
    		    else  {
    		        if($this->getParam("mode") == "new")
    		            $strReturn = $this->actionNewFaq("new");
    		        else
    		            $strReturn = $this->actionNewFaq("edit");
    		    }
    		}
    		if($strAction == "deleteFaq") {
    			$strReturn = $this->actionDeleteFaq();
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
    	$arrReturn[] = array("", "");
		$arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "newFaq", "", $this->getText("modul_anlegen"), "", "", true, "adminnavi"));
		$arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "newCat", "", $this->getText("modul_kat_anlegen"), "", "", true, "adminnavi"));
		return $arrReturn;
	}


	protected function getRequiredFields() {
        $strAction = $this->getAction();
        $arrReturn = array();
        if($strAction == "saveCat") {
            $arrReturn["faqs_cat_title"] = "string";
        }
        if($strAction == "saveFaq") {
            $arrReturn["faqs_answer"] = "string";
            $arrReturn["faqs_question"] = "string";
        }

        return $arrReturn;
    }

// --- ListenFunktionen ---------------------------------------------------------------------------------


	/**
	 * Returns a list of all categories and all faqs
	 * The list can be filtered by categories
	 *
	 * @return string
	 */
	private function actionList() {
		$strReturn = "";
        if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {

    		//Load Categories
    		$arrCategories = class_modul_faqs_category::getCategories();
    		$intI = 0;
            //Print all Categories, encapsulated by the known layoutFolder
            $strCat = "";

    		if(count($arrCategories) > 0) {
    			foreach($arrCategories as $objOneCategory) {
    			    $strAction = "";
    			    if($this->objRights->rightView($objOneCategory->getSystemid()))
    		   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "list", "&filterId=".$objOneCategory->getSystemid(), "", $this->getText("kat_anzeigen"), "icon_lens.gif"));
    		   		if($this->objRights->rightEdit($objOneCategory->getSystemid()))
    		   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "editCat", "&systemid=".$objOneCategory->getSystemid(), "", $this->getText("kat_bearbeiten"), "icon_pencil.gif"));
    		   		if($this->objRights->rightDelete($objOneCategory->getSystemid()))
    		   		    $strAction .= $this->objToolkit->listDeleteButton(
    		   		           $objOneCategory->getStrTitle().$this->getText("kat_loeschen_frage")
				               ."<br />".getLinkAdmin($this->arrModule["modul"], "deleteCat", "&systemid=".$objOneCategory->getSystemid()."&&peClose=".$this->getParam("pe"), $this->getText("kat_loeschen_link"))
    		   		    ); 
    		   		if($this->objRights->rightEdit($objOneCategory->getSystemid()))
    		   		    $strAction .= $this->objToolkit->listStatusButton($objOneCategory->getSystemid());
    		   		if($this->objRights->rightRight($objOneCategory->getSystemid()))
    				    $strAction .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$objOneCategory->getSystemid(), "", $this->getText("kat_rechte"), getRightsImageAdminName($objOneCategory->getSystemid())));
    		   		$strCat .= $this->objToolkit->listRow2Image(getImageAdmin("icon_folderOpen.gif"), $objOneCategory->getStrTitle(), $strAction, $intI++);

    			}
    		}
    		if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
    		    $strCat .= $this->objToolkit->listRow2Image("", "", getLinkAdmin($this->arrModule["modul"], "newCat", "", $this->getText("modul_kat_anlegen"), $this->getText("modul_kat_anlegen"), "icon_blank.gif"), $intI++);

    		if(uniStrlen($strCat) != 0)
    		     $strCat = $this->objToolkit->listHeader().$strCat.$this->objToolkit->listFooter();

    		$strReturn .= $this->objToolkit->getLayoutFolderPic($strCat, $this->getText("kat_ausblenden"));
    		$strReturn .= $this->objToolkit->divider();


    		//Load all faqs, maybe using a filterid
    		if($this->getParam("filterId") != "" && $this->validateSystemid($this->getParam("filterId")))
    			$arrFaqs = class_modul_faqs_faq::getFaqsList($this->getParam("filterId"));
    		else
    			$arrFaqs = class_modul_faqs_faq::getFaqsList();

			$strFaqs = "";
			foreach($arrFaqs as $objOneFaq) {
			    if($this->objRights->rightView($objOneFaq->getSystemid())) {
    				
                    $strAction = "";
                    if($this->objRights->rightEdit($objOneFaq->getSystemid()))
    		   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "editFaq", "&systemid=".$objOneFaq->getSystemid(), "", $this->getText("faq_edit"), "icon_pencil.gif"));
    		   		if($this->objRights->rightDelete($objOneFaq->getSystemid()))
    		   		    $strAction .= $this->objToolkit->listDeleteButton(
    		   		           $objOneFaq->getStrQuestion().$this->getText("faqs_loeschen_frage")
				               ."<br />".getLinkAdmin($this->arrModule["modul"], "deleteFaq", "&systemid=".$objOneFaq->getSystemid()."&faqs_loeschen_final=1&peClose=".$this->getParam("pe"), $this->getText("faqs_loeschen_link"))
    		   		    );    
    		   		if($this->objRights->rightEdit($objOneFaq->getSystemid()))
    				    $strAction .= $this->objToolkit->listStatusButton($objOneFaq->getSystemid());
    				if($this->objRights->rightRight($objOneFaq->getSystemid()))
    		   		    $strAction .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$objOneFaq->getSystemid(), "", $this->getText("faq_rechte"), getRightsImageAdminName($objOneFaq->getSystemid())));

    		   		$strFaqs .= $this->objToolkit->listRow2Image(getImageAdmin("icon_question.gif"), uniStrTrim($objOneFaq->getStrQuestion(), 80), $strAction, $intI++);
			    }

			}
			if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"])))
			    $strFaqs .= $this->objToolkit->listRow2Image("", "", getLinkAdmin($this->arrModule["modul"], "newFaq", "", $this->getText("modul_anlegen"), $this->getText("modul_anlegen"), "icon_blank.gif"), $intI++);

			if(uniStrlen($strFaqs) != 0)
			    $strFaqs = $this->objToolkit->listHeader().$strFaqs.$this->objToolkit->listFooter();

            if(count($arrFaqs) == 0)
    			$strFaqs.= $this->getText("liste_leer");

    		$strReturn .= $strFaqs;
        }
        else
            $strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}

// -- faqs-Kategorien -----------------------------------------------------------------------------------

	/**
	 * Show the form to create or edit a faqs cat
	 *
	 * @param string $strMode
	 * @return string
	 */
	private function actionNewCat($strMode = "new") {
		$strReturn = "";
		//Mode?
		if($strMode == "new") {
			//New Category
			if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
			    $strReturn .= $this->objToolkit->getValidationErrors($this);
				$strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&amp;module=faqs&amp;action=saveCat");
			    $strReturn .= $this->objToolkit->formInputHidden("mode", "new");
				$strReturn .= $this->objToolkit->formInputText("faqs_cat_title", $this->getText("faqs_cat_title"), $this->getParam("faqs_cat_title"));
				$strReturn .= $this->objToolkit->formInputSubmit($this->getText("speichern"));
				$strReturn .= $this->objToolkit->formClose();
			}
			else
				$strReturn.= $this->getText("fehler_recht");
		}
		elseif ($strMode == "edit") {
			//Edit
			if($this->objRights->rightEdit($this->getSystemid())) {
				//Load cat data
				$objCat = new class_modul_faqs_category($this->getSystemid());
				$strReturn .= $this->objToolkit->getValidationErrors($this);
				$strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&amp;module=faqs&amp;action=saveCat");
			    $strReturn .= $this->objToolkit->formInputHidden("mode", "edit");
			    $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
				$strReturn .= $this->objToolkit->formInputText("faqs_cat_title", $this->getText("faqs_cat_title"), $objCat->getStrTitle());
				$strReturn .= $this->objToolkit->formInputSubmit($this->getText("speichern"));
				$strReturn .= $this->objToolkit->formClose();
			}
			else
				$strReturn .= $this->getText("fehler_recht");
		}
		return $strReturn;
	}

	/**
	 * Saves the passed values as a new category to the db
	 *
	 * @return string "" in case of success
	 */
	private function actionSaveCat() {
		$strReturn = "";
		if($this->getParam("mode") == "new") {
			//Check rights
			if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
			    $objCat = new class_modul_faqs_category();
			    $objCat->setStrTitle($this->getParam("faqs_cat_title"));
			    if(!$objCat->saveObjectToDb())
			        throw new class_exception("Error saving object to db", class_exception::$level_ERROR);
			}
		}
		elseif($this->getParam("mode") == "edit") {
		    //"just" update
			if($this->objRights->rightEdit($this->getSystemid())) {
				$objCat = new class_modul_faqs_category($this->getSystemid());
				$objCat->setStrTitle($this->getParam("faqs_cat_title"));
				if(!$objCat->updateObjectToDb())
				    throw new class_exception("Error updating object to db", class_exception::$level_ERROR);
			}
			else
				$strReturn .= $this->getText("fehler_recht");
		}
		return $strReturn;
	}

	/**
	 * Shows the warning or deletes a cat from the system
	 *
	 * @return string "" in case of success
	 */
	private function actionDeleteCategory() {
		$strReturn = "";
		//Check rights
		if($this->objRights->rightDelete($this->getSystemid())) {
           if(!class_modul_faqs_category::deleteCategory($this->getSystemid()))
               throw new class_exception("Error deleting object from db", class_exception::$level_ERROR);
		}
		else
			$strReturn .= $this->getText("fehler_recht");


		return $strReturn;
	}

// --- Faqs-Funktionen ----------------------------------------------------------------------------------

	/**
	 * Shows the form to edit oder create a faq
	 *
	 * @param string $strMode new || edit
	 * @return string
	 */
	private function actionNewFaq($strMode = "new") {
		$strReturn = "";
		if($strMode == "new") {
			//Form to create new faq
			if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
			    $strReturn .= $this->objToolkit->getValidationErrors($this);
				$strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&amp;module=faqs&amp;action=saveFaq");
                $strReturn .= $this->objToolkit->formInputTextArea("faqs_question", $this->getText("faqs_question"), $this->getParam("faqs_question"));
                $strReturn .= $this->objToolkit->formWysiwygEditor("faqs_answer", $this->getText("faqs_answer"), $this->getParam("faqs_answer"), "minimal");
                $strReturn .= $this->objToolkit->divider();
                //and the cats
                $arrCats = class_modul_faqs_category::getCategories();
                foreach ($arrCats as $objOneCat) {
            	   $strReturn .= $this->objToolkit->formInputCheckbox("cat[".$objOneCat->getSystemid()."]", $objOneCat->getStrTitle());
                }

                $strReturn .= $this->objToolkit->formInputHidden("systemid", "");
                $strReturn .= $this->objToolkit->formInputHidden("mode", "new");
                $strReturn .= $this->objToolkit->formInputHidden("peClose", $this->getParam("pe"));
				$strReturn .= $this->objToolkit->formInputSubmit($this->getText("speichern"));
				$strReturn .= $this->objToolkit->formClose();
			}
			else
				$strReturn .= $this->getText("fehler_recht");
		}
		elseif ($strMode == "edit") {
			//Rights
			if($this->objRights->rightEdit($this->getSystemid())) {
			    $objFaq = new class_modul_faqs_faq($this->getSystemid());
			    $strReturn .= $this->objToolkit->getValidationErrors($this);
			    $strReturn .= $this->objToolkit->formHeader(_indexpath_."?admin=1&amp;module=faqs&amp;action=saveFaq");
                $strReturn .= $this->objToolkit->formInputTextArea("faqs_question", $this->getText("faqs_question"), $objFaq->getStrQuestion());
                $strReturn .= $this->objToolkit->formWysiwygEditor("faqs_answer", $this->getText("faqs_answer"), $objFaq->getStrAnswer(), "minimal");
                $strReturn .= $this->objToolkit->divider();
                //and the cats
                $arrCats = class_modul_faqs_category::getCategories();
                $arrFaqsMember = class_modul_faqs_category::getFaqsMember($this->getSystemid());

                foreach ($arrCats as $objOneCat) {
                    $bitChecked = false;
                    foreach ($arrFaqsMember as $objOneMember)
                        if($objOneMember->getSystemid() == $objOneCat->getSystemid())
                            $bitChecked = true;

            	   $strReturn .= $this->objToolkit->formInputCheckbox("cat[".$objOneCat->getSystemid()."]", $objOneCat->getStrTitle(), $bitChecked);
                }

                $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
                $strReturn .= $this->objToolkit->formInputHidden("mode", "edit");
                $strReturn .= $this->objToolkit->formInputHidden("peClose", $this->getParam("pe"));
				$strReturn .= $this->objToolkit->formInputSubmit($this->getText("speichern"));
				$strReturn .= $this->objToolkit->formClose();
			}
			else
				$strReturn .= $this->getText("fehler_recht");
		}
		return $strReturn;
	}


	/**
	 * Saves or updates faqs
	 *
	 * @return string "" in case of success
	 */
	private function actionSaveFaq() {
		$strReturn = "";
		if($this->getParam("mode") == "new") {
			//Check rights
			if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {

				$objFaq = new class_modul_faqs_faq();
				$objFaq->setStrQuestion($this->getParam("faqs_question"));
				$objFaq->setStrAnswer($this->getParam("faqs_answer"));
                $arrParams = $this->getAllParams();
                $arrCats = array();
                if(isset($arrParams["cat"])) {
                    foreach($arrParams["cat"] as $strCatID => $strValue) {
                        $arrCats[$strCatID] = $strValue;
                    }
                }
                $objFaq->setArrCats($arrCats);

                if(!$objFaq->saveObjectToDb())
                    throw new class_exception("Error saving object to db", class_exception::$level_ERROR);

			}
			else
				$strReturn .= $this->getText("fehler_recht");
		}
		elseif($this->getParam("mode") == "edit") {
			if($this->objRights->rightEdit($this->getSystemid())) {

				$objFaq = new class_modul_faqs_faq($this->getSystemid());
				$objFaq->setStrQuestion($this->getParam("faqs_question"));
				$objFaq->setStrAnswer($this->getParam("faqs_answer"));

                $arrParams = $this->getAllParams();
                $arrCats = array();
                if(isset($arrParams["cat"])) {
                    foreach($arrParams["cat"] as $strCatID => $strValue) {
                        $arrCats[$strCatID] = $strValue;
                    }
                }
                $objFaq->setArrCats($arrCats);
                if(!$objFaq->updateObjectToDb())
                    throw new class_exception("Error updating object to db", class_exception::$level_ERROR);

			}
			else
				$strReturn .= $this->getText("fehler_recht");
		}
		return $strReturn;
	}

	/**
	 * Deletes faqs or shows the form to warn
	 *
	 * @return string "" in case of success
	 */
	private function actionDeleteFaq() {
		$strReturn = "";
		//Rights
		if($this->objRights->rightDelete($this->getSystemid())) {
		    if($this->getParam("faqs_loeschen_final") == "") {
			    $objFaq = new class_modul_faqs_faq($this->getSystemid());
				$strName = $objFaq->getStrQuestion();
				$strReturn .= $this->objToolkit->warningBox($strName.$this->getText("faqs_loeschen_frage")
				               ."<br /><a href=\""._indexpath_."?admin=1&amp;module=".$this->arrModule["modul"]."&amp;action=deleteFaq&amp;systemid="
				               .$this->getSystemid().($this->getParam("pe") == "" ? "" : "&amp;peClose=".$this->getParam("pe"))."&amp;faqs_loeschen_final=1\">"
				               .$this->getText("faqs_loeschen_link"));
			}
			elseif($this->getParam("faqs_loeschen_final") == "1") {
			    if(!class_modul_faqs_faq::deleteFaqs($this->getSystemid()))
			        throw new class_exception("Error deleting object from db", class_exception::$level_ERROR);
			}
		}
		else
			$strReturn .= $this->getText("fehler_recht");


		return $strReturn;
	}


}

?>