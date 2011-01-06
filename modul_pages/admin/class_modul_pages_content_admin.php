<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                          *
********************************************************************************************************/


/**
 * This class is used to edit the content of a page. So, to create / delete / modify elements on a
 * given page.
 *
 * @package modul_pages
 */
class class_modul_pages_content_admin extends class_admin implements interface_admin {
	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 				= "modul_pages_elemente";
		$arrModule["author"] 			= "sidler@mulchprod.de";
		$arrModule["moduleId"] 			= _pages_content_modul_id_;
		$arrModule["modul"]				= "pages";

		//Calling the base class
		parent::__construct($arrModule);
	}

	/**
	 * Action-Block
	 *
	 * @param string $strAction
	 */
	public function action($strAction = "") {
		$strReturn = "";
		//If theres anything to unlock, do it now
		if($this->getParam("unlockid") != "") {
            $objLockmanager = new class_lockmanager($this->getParam("unlockid"));
            $objLockmanager->unlockRecord();
		}
		if($this->getParam("adminunlockid") != "") {
            $objLockmanager = new class_lockmanager($this->getParam("adminunlockid"));
            $objLockmanager->unlockRecord(true);
		}

		if($this->getSystemid() == "")
		    return $this->getText("fehler_recht");

		if($strAction == "")
			$strAction = "list";

		try {

    		if($strAction == "list")
    			$strReturn = $this->actionList();
    		if($strAction == "newElement")
    			$strReturn = $this->actionNewElement();
    		if($strAction == "editElement")
    			$strReturn = $this->actionEditElement();
    		if($strAction == "saveElement") {
    			$strReturn = $this->actionSaveElement();
    			if($strReturn == "")
    				$this->adminReload(getLinkAdminHref("pages_content", "list", "systemid=".$this->getPrevId()));
    		}
    		if($strAction == "deleteElement")
    			$strReturn = $this->actionDeleteElement();
    		if($strAction == "deleteElementFinal") {
    			$strReturn = $this->actionDeleteElementFinal();
    			if($strReturn == "")
    				$this->adminReload(getLinkAdminHref("pages_content", "list", "systemid=".$this->getParam("deleteid").($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
    		}
    		if($strAction == "elementSortUp") {
    			$strReturn = $this->actionShiftElement("up");
    			if($strReturn == "")
    				$this->adminReload(getLinkAdminHref("pages_content", "list", "systemid=".$this->getPrevId().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
    		}
    		if($strAction == "elementSortDown") {
    			$strReturn = $this->actionShiftElement("down");
    			if($strReturn == "")
    				$this->adminReload(getLinkAdminHref("pages_content", "list", "systemid=".$this->getPrevId().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
    		}
            if($strAction == "copyElement") {
                $strReturn = $this->actionCopyElement();
                if($strReturn == "")
                    $this->adminReload(getLinkAdminHref("pages_content", "list", "systemid=".$this->getPrevId().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
            }

    		//add a pathnavigation when not in pe mode
    		if($this->getParam("pe") != 1) {
    		    $strReturn = $this->getPathNavigation().$strReturn;
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

	public function getOutputModuleTitle() {
		$objPage = new class_modul_pages_page($this->getSystemid());
		if($objPage->getStrName() == "")
			$objPage = new class_modul_pages_page($objPage->getPrevId());
		return $this->getText("modul_titel") . " (".$objPage->getStrName().")";
	}

	public function getOutputModuleNavi() {
	    return array();
	}


	/**
	 * Returns a list of available placeholders & elements on this page
	 *
	 * @return string
	 */
	private function actionList() {
		$strReturn = "";
		if($this->objRights->rightEdit($this->getSystemid())) {
            //get infos about the page
            $objPage = new class_modul_pages_page($this->getSystemid());

            $arrToolbarEntries = array();
            $arrToolbarEntries[0] = "<a href=\"".getLinkAdminHref("pages", "newPage", "&systemid=".$this->getSystemid())."\" style=\"background-image:url("._skinwebpath_."/pics/icon_page.gif);\">".$this->getText("contentToolbar_pageproperties")."</a>";
            $arrToolbarEntries[1] = "<a href=\"".getLinkAdminHref("pages_content", "list", "&systemid=".$this->getSystemid())."\" style=\"background-image:url("._skinwebpath_."/pics/icon_pencil.gif);\">".$this->getText("contentToolbar_content")."</a>";
            $arrToolbarEntries[2] = "<a href=\"".getLinkPortalHref($objPage->getStrName(), "", "", "&preview=1", "", $this->getLanguageToWorkOn())."\" target=\"_blank\" style=\"background-image:url("._skinwebpath_."/pics/icon_lens.gif);\">".$this->getText("contentToolbar_preview")."</a>";

            //if languages are installed, present a language switch right here
            $objLanguages = new class_modul_languages_admin();
            $arrToolbarEntries[3] = $objLanguages->getLanguageSwitch();

		    $strReturn .= $this->objToolkit->getContentToolbar($arrToolbarEntries, 1);

			$arrTemplate = array();
			$arrTemplate["pagetemplate"] = $objPage->getStrTemplate();
			$arrTemplate["pagetemplateTitle"] = $this->getText("template");

			$arrTemplate["lastuserTitle"] = $this->getText("lastuserTitle");
			$arrTemplate["lasteditTitle"] = $this->getText("lasteditTitle");
			$arrTemplate["lastuser"] = $objPage->getLastEditUser();
			$arrTemplate["lastedit"] = timeToString($objPage->getEditDate());
			$strReturn .= $this->objToolkit->getPageInfobox($arrTemplate);

            //try to load template, otherwise abort
            $strTemplateID = null;
			try {
                $strTemplateID = $this->objTemplate->readTemplate("templates/modul_pages/".$objPage->getStrTemplate(), "", true, true);
			} catch (class_exception $objException) {
                $strReturn .= $this->getText("templateNotLoaded")."<br />";
            }

			//Load elements on template, master-page special case!
			if($objPage->getStrName() == "master")
				$arrElementsOnTemplate = $this->objTemplate->getElements($strTemplateID, 1);
			else
				$arrElementsOnTemplate = $this->objTemplate->getElements($strTemplateID, 0);

			$arrElementsOnPage = array();
			//Language-dependant loading of elements, if installed
		    $arrElementsOnPage = class_modul_pages_pageelement::getElementsOnPage($this->getSystemid(), false, $this->getLanguageToWorkOn());
            //save a copy of the array to be able to check against all values later on
            $arrElementsOnPageCopy = $arrElementsOnPage;

			//Loading all Elements installed on the system ("RAW"-Elements)
			$arrElementsInSystem = class_modul_pages_element::getAllElements();


			//So, loop through the placeholders and check, if theres any element already belonging to this one
            $intI = 0;
			if(is_array($arrElementsOnTemplate) && count($arrElementsOnTemplate) > 0) {
			    //Iterate over every single placeholder provided by the template
				foreach($arrElementsOnTemplate as $intKeyElementOnTemplate => $arrOneElementOnTemplate) {
				    //Iterate over every single element-type provided by the placeholder
					$bitHit = false;
					$bitOutputAtPlaceholder = false;

					$strOutputAtPlaceholder = "";
					//Do we have one or more elements already in db at this placeholder?
					$bitHit = false;

					foreach ($arrElementsOnPage as $intArrElementsOnPageKey => $objOneElementOnPage) {
					    //Check, if its the same placeholder
					    $bitSamePlaceholder = false;
					    if($arrOneElementOnTemplate["placeholder"] == $objOneElementOnPage->getStrPlaceholder()) {
					        $bitSamePlaceholder = true;
					    }
					    else {
					        //Check, if the current placeholder was modified?
					        //corresponding to ticket 0000159, this functioniality is removed
					        /*
					        $arrPlaceholderDescriber = explode("_", $arrOneElementOnTemplate["placeholder"]);
					        if($arrPlaceholderDescriber[0] == $objOneElementOnPage->getStrName())
					            $bitSamePlaceholder = true;
					        */
					    }

						if($bitSamePlaceholder) {
							$bitHit = true;

                            $objLockmanager = $objOneElementOnPage->getLockManager();

							//Create a row to handle the element, check all necessary stuff such as locking etc
							$strActions = "";
							//First step - Record locked? Offer button to unlock? But just as admin! For the user, who locked the record, the unlock-button
							//won't be visible
							if(!$objLockmanager->isAccessibleForCurrentUser()) {
								//So, return a button, if we have an admin in front of us
								if($objLockmanager->isUnlockableForCurrentUser() ) {
									$strActions .= $this->objToolkit->listButton(getLinkAdmin("pages_content", "list", "&systemid=".$this->getSystemid()."&adminunlockid=".$objOneElementOnPage->getSystemid(), "", $this->getText("ds_entsperren"), "icon_lockerOpen.gif"));
								}
								//If the Element is locked, then its not allowed to edit or delete the record, so disable the icons
								$strActions .= $this->objToolkit->listButton(getNoticeAdminWithoutAhref($this->getText("ds_gesperrt"), "icon_pencilLocked.gif"));
								$strActions .= $this->objToolkit->listButton(getNoticeAdminWithoutAhref($this->getText("ds_gesperrt"), "icon_tonLocked.gif"));
							}
							else {
								//if its the user who locked the record, unlock it now
								if($objLockmanager->isLockedByCurrentUser())
								    $objLockmanager->unlockRecord();

								$strActions .= $this->objToolkit->listButton(getLinkAdmin("pages_content", "editElement", "&systemid=".$objOneElementOnPage->getSystemid()."&placeholder=".$arrOneElementOnTemplate["placeholder"], "", $this->getText("element_bearbeiten"), "icon_pencil.gif"));
								$strActions .= $this->objToolkit->listDeleteButton($objOneElementOnPage->getStrName(). ($objOneElementOnPage->getStrTitle() != "" ? " - ".$objOneElementOnPage->getStrTitle() : "" ), $this->getText("element_loeschen_frage"), getLinkAdminHref("pages_content", "deleteElementFinal", "&systemid=".$objOneElementOnPage->getSystemid().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
							}

							//The Icons to sort the list and to copy the element
    		    			$strActions .= $this->objToolkit->listButton(getLinkAdmin("pages_content", "copyElement", "&systemid=".$objOneElementOnPage->getSystemid(), "", $this->getText("element_copy"), "icon_copy.gif"));
							$strActions .= $this->objToolkit->listButton(getLinkAdmin("pages_content", "elementSortUp", "&systemid=".$objOneElementOnPage->getSystemid(), "", $this->getText("element_hoch"), "icon_arrowUp.gif"));
							$strActions .= $this->objToolkit->listButton(getLinkAdmin("pages_content", "elementSortDown", "&systemid=".$objOneElementOnPage->getSystemid(), "", $this->getText("element_runter"), "icon_arrowDown.gif"));


							//The status-icons
    						$strActions .= $this->objToolkit->listStatusButton($objOneElementOnPage->getSystemid());

							//Rights - could be used, but not up to now not needed, so not yet implemented completely
							//$strActions .= $this->objToolkit->listButton(get_link_admin("rechte", "aendern", "&systemid=".$element_hier["systemid"], "", $this->obj_texte->get_text($this->modul["modul"], "element_rechte"), getRightsImageAdminName($objOneElementOnPage->getSystemid())));

							//Put all Output together
							$strOutputAtPlaceholder .= $this->objToolkit->listRow2($objOneElementOnPage->getStrName() . " (".$objOneElementOnPage->getStrReadableName() . ") - ".$objOneElementOnPage->getStrTitle(), $strActions, $intI++, "", $objOneElementOnPage->getSystemid());
							$bitOutputAtPlaceholder = true;

							//remove the element from the array
							unset($arrElementsOnPage[$intArrElementsOnPageKey]);
						}

					}

					//Check, if one of the elements in the placeholder is allowed to be used multiple times
					//$bitOneInstalled = false;
					foreach ($arrOneElementOnTemplate["elementlist"] as $arrSingleElementOnTemplateplaceholder) {
        				foreach($arrElementsInSystem as $objOneElementInSystem) {
        					if($objOneElementInSystem->getStrName() == $arrSingleElementOnTemplateplaceholder["element"]) {
        						$objElement = $objOneElementInSystem;
        						if($objElement->getIntRepeat() == 1 || $bitHit === false)	{
            						//So, the Row for a new element: element is repeatable or not yet created
            						$strActions = $this->objToolkit->listButton(getLinkAdmin("pages_content", "newElement", "&placeholder=".$arrOneElementOnTemplate["placeholder"]."&element=".$arrSingleElementOnTemplateplaceholder["element"]."&systemid=".$this->getSystemid(), "", $this->getText("element_anlegen"), "icon_blank.gif"));
            						$strOutputAtPlaceholder .= $this->objToolkit->listRow2($arrSingleElementOnTemplateplaceholder["name"] . " (".$objOneElementInSystem->getStrReadableName() . ")", $strActions, $intI++);
            						$bitOutputAtPlaceholder = true;
            					}
            					else {
            						//element not repeatable.
            					    //Is there already one element installed? if not, then it IS allowed to create a new one
            					    $bitOneInstalled = false;
            					    foreach($arrElementsOnPageCopy as $objOneElementToCheck) {
            					        if($arrOneElementOnTemplate["placeholder"] == $objOneElementToCheck->getStrPlaceholder() && $arrSingleElementOnTemplateplaceholder["element"] == $objOneElementToCheck->getStrElement())
            					           $bitOneInstalled = true;
            					    }
            					    if(!$bitOneInstalled) {
            					        //So, the Row for a new element
                						$strActions = $this->objToolkit->listButton(getLinkAdmin("pages_content", "newElement", "&placeholder=".$arrOneElementOnTemplate["placeholder"]."&element=".$arrSingleElementOnTemplateplaceholder["element"]."&systemid=".$this->getSystemid(), "", $this->getText("element_anlegen"), "icon_blank.gif"));
                						$strOutputAtPlaceholder .= $this->objToolkit->listRow2($arrSingleElementOnTemplateplaceholder["name"] . " (".$arrSingleElementOnTemplateplaceholder["element"] . ")", $strActions, $intI++);
                						$bitOutputAtPlaceholder = true;
            					    }
            					}
        					}
        				}
					}

					if((int)uniStrlen($strOutputAtPlaceholder) > 0) {
                        $strListId = generateSystemid();
                        //$strReturn .= $this->objToolkit->listHeader();
                        $strReturn .= $this->objToolkit->dragableListHeader($strListId, true);
                        $strReturn .= $strOutputAtPlaceholder;
                        //$strReturn .= $this->objToolkit->listFooter();
                        $strReturn .= $this->objToolkit->dragableListFooter($strListId);
					}

					//Done with this placeholder, so its time to draw a divider or offer the possibility to add a new element
					//but just, if the next placeholder isn't the same as the current, but a different element
					if(isset($arrElementsOnTemplate[$intKeyElementOnTemplate+1])) {
					   if($arrElementsOnTemplate[$intKeyElementOnTemplate]["placeholder"] != $arrElementsOnTemplate[$intKeyElementOnTemplate+1]["placeholder"]) {
					       if((int)uniStrlen($strOutputAtPlaceholder) > 0)
				              $strReturn .= $this->objToolkit->divider();
					   }
					}
				}


            } else {
                $strReturn .= $this->getText("element_liste_leer");
			}

            //if there are any pagelements remaining, print a warning and print the elements row
            if(count($arrElementsOnPage) > 0) {
                $strReturn .= $this->objToolkit->divider();
                $strReturn .= $this->objToolkit->warningBox($this->getText("warning_elementsremaining"));
                $strReturn .= $this->objToolkit->listHeader();

                //minimized actions now, plz. this ain't being a real element anymore!
                foreach($arrElementsOnPage as $objOneElement) {
                    $strActions = "";
                    $strActions .= $this->objToolkit->listDeleteButton($objOneElement->getStrName(). ($objOneElement->getStrTitle() != "" ? " - ".$objOneElement->getStrTitle() : "" ), $this->getText("element_loeschen_frage"), getLinkAdminHref("pages_content", "deleteElementFinal", "&systemid=".$objOneElement->getSystemid().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));

                    //Put all Output together
                    $strReturn .= $this->objToolkit->listRow2($objOneElement->getStrName() . " (".$objOneElement->getStrElement() . ") - ".$this->getText("placeholder").$objOneElement->getStrPlaceholder(), $strActions, $intI++);
                }
                $strReturn .= $this->objToolkit->listFooter();
            }



		} else {
			$strReturn = $this->getText("fehler_recht");
		}

		return $strReturn;
	}

	/**
	 * Loads the form to create a new element
	 *
	 * @return string
	 */
	private function actionNewElement($bitShowErrors = false) {
		$strReturn = "";
        //check rights
		if($this->objRights->rightEdit($this->getSystemid())) {
    		//OK, here we go. So, what information do we have?
    		$strParentPageSystemID = $this->getSystemid();
    		$strPlaceholderElement = $this->getParam("element");
    		//Now, load all infos about the requested element
    		$objElement = class_modul_pages_element::getElement($strPlaceholderElement);
    		//Load the class to create an object
    		include_once(_adminpath_."/elemente/".$objElement->getStrClassAdmin());
    		//Build the class-name
    		$strElementClass = str_replace(".php", "", $objElement->getStrClassAdmin());
    		//and finally create the object
    		$objElement = new $strElementClass();
    		if($bitShowErrors)
    		  $objElement->setDoValidation(true);

    		$strReturn = $objElement->actionEdit("new");
		}
		else
		    $strReturn .= $this->getText("fehler_recht");

		return $strReturn;
	}


	/**
	 * Loads the form to edit the element
	 *
	 * @return string
	 */
	private function actionEditElement($bitShowErrors = false) {
		$strReturn = "";
		//check rights
		if($this->objRights->rightEdit($this->getSystemid())) {
    		//Load the element data
    		$objElement = new class_modul_pages_pageelement($this->getSystemid());
    		//check, if the element isn't locked
    		if($objElement->getLockManager()->isAccessibleForCurrentUser()) {

                $objElement->getLockManager()->lockRecord();

    			//Load the class to create an object
    			include_once(_adminpath_."/elemente/".$objElement->getStrClassAdmin());
    			//Build the class-name
    			$strElementClass = str_replace(".php", "", $objElement->getStrClassAdmin());
    			//and finally create the object
    			$objPageElement = new $strElementClass();
    			if($bitShowErrors)
    		        $objPageElement->setDoValidation(true);
    			$strReturn .= $objPageElement->actionEdit("edit");


    		}
    		else {
    			$strReturn .= $this->objToolkit->warningBox($this->getText("ds_gesperrt"));
    		}
		}
		else
		    $strReturn .= $this->getText("fehler_recht");

		return $strReturn;
	}

	/**
	 * Saves the passed Element to the databse (edit or new modes)
	 *
	 * @return string "" in case of success
	 */
	private function actionSaveElement() {
		$strReturn = "";
		//There are two modes - edit an new
		//The element itself just knows the edit mode, so in case of new, we have to create a dummy element - before
		//passing control to the element
		if($this->getParam("mode") == "new") {
			//Using the passed placeholder-param to load the element and get the table
			$strPlaceholder = $this->getParam("placeholder");
			//Split up the placeholder
			$arrPlaceholder = explode("_", $strPlaceholder);
			$strPlaceholderName = $arrPlaceholder[0];
			$strPlaceholderElement = $this->getParam("element");
			//Now, load all infos about the requested element
			$objElement = class_modul_pages_element::getElement($strPlaceholderElement);
			//Load the class to create an object
			include_once(_adminpath_."/elemente/".$objElement->getStrClassAdmin());
			//Build the class-name
			$strElementClass = str_replace(".php", "", $objElement->getStrClassAdmin());
			//and finally create the object
			$objElement = new $strElementClass();

			//really continue? try to validate the passed data.
			if(!$objElement->validateForm()) {
			    $strReturn .= $this->actionNewElement(true);
			    return $strReturn;
			}

			//So, lets do the magic - create the records
			$objPageElement = new class_modul_pages_pageelement("");
			$objPageElement->setStrName($strPlaceholderName);
			$objPageElement->setStrPlaceholder($strPlaceholder);
			$objPageElement->setStrElement($strPlaceholderElement);
            $objPageElement->setStrLanguage($this->getParam("page_element_placeholder_language"));
			if(!$objPageElement->updateObjectToDb($this->getSystemid()))
			    throw new class_exception("Error saving new element-object to db", class_exception::$level_ERROR);
			$strElementSystemId = $objPageElement->getSystemid();

            //shift to last position? first is default.
            if($this->getParam("element_pos") == "first")
                $objPageElement->setAbsolutePosition($objPageElement->getSystemid(), 1);

            $objLockmanager = new class_lockmanager($strElementSystemId);
            $objLockmanager->lockRecord();

			//To have the element working as expected, set the systemid
			//Note: in the param-Array still remains the "wrong" systemid!!
			$this->setSystemid($strElementSystemId);
		}


		// ************************************* Edit the current Element *******************************

		//check, if the element isn't locked
		$strPageSystemid = $this->getPrevId($this->getSystemid());

        $objLockmanager = new class_lockmanager($this->getSystemid());

		if($objLockmanager->isLockedByCurrentUser()) {
			//Load the data of the current element
			$objElementData = new class_modul_pages_pageelement($this->getSystemid());
			//Load the class to create an object
			include_once(_adminpath_."/elemente/".$objElementData->getStrClassAdmin());
			//Build the class-name
			$strElementClass = str_replace(".php", "", $objElementData->getStrClassAdmin());
			//and finally create the object
			$objElement = new $strElementClass();

			//really continue? try to validate the passed data.
			if(!$objElement->validateForm()) {
			    $strReturn .= $this->actionEditElement(true);
			    return $strReturn;
			}

            //pass the data to the element, maybe the element wants to update some data
            $objElement->setArrParamData($this->getAllParams());
            $objElement->doBeforeSaveToDb();

			//check, if we could save the data, so the element needn't to
			//woah, we are soooo great
			$strElementTableColumns = $objElement->getArrModule("tableColumns");
			if($strElementTableColumns != "") {

			    //open new tx
			    $this->objDB->transactionBegin();

                $arrElementParams = $objElement->getArrParamData();

                $arrTableRows = explode(",", $strElementTableColumns);
                if(count($arrTableRows) > 0) {
                    $arrInserts = array();
                    foreach($arrTableRows as $strOneTableColumnConf) {


                        //explode to get tableColumnName and tableColumnDatatype
                        //currently, datatypes are 'number' and 'char' -> casts!
                        $arrTemp = explode("|", $strOneTableColumnConf);
                        $strTableColumnName = $arrTemp[0];
                        $strTableColumnDatatype = $arrTemp[1];

                        $strColumnValue = "";
                        if(isset($arrElementParams[$strTableColumnName]))
                            $strColumnValue = $arrElementParams[$strTableColumnName];


                        if ($strTableColumnDatatype == "number")
                            $arrInserts[] = " ".$this->objDB->encloseColumnName($strTableColumnName)." = ".(int)$this->objDB->dbsafeString($strColumnValue)." ";
                        elseif ($strTableColumnDatatype == "char")
                            $arrInserts[] = " ".$this->objDB->encloseColumnName($strTableColumnName)." = '".$this->objDB->dbsafeString($strColumnValue)."' ";
                    }

                    $strRowUpdates = implode(", ", $arrInserts);
                    $strUpdateQuery =
                    " UPDATE ".$objElement->getTable()." SET "
                      .$strRowUpdates.
                    " WHERE content_id='".$this->getSystemid()."'";

                    if(!$this->objDB->_query($strUpdateQuery)) {
                        $strReturn .= "Error updating element data.";
                        $this->objDB->transactionRollback();
                    }
                    else
                        $this->objDB->transactionCommit();
                }
                else
                    return "Element has invalid tableRows value!!!";
			}
			else {
			    //To remain backwards-compatible:
			    //Call the save-method of the element instead or if the element wants to update its data specially
			    if(method_exists($objElement, "actionSave") && !$objElement->actionSave($this->getSystemid()))
				    return "Element returned error saving to database!!!";
			}
			//Edit Date & unlock
			$this->setEditDate($strPageSystemid);
			$objLockmanager->unlockRecord();
			//And update the internal comment and language
			$objElementData->setStrTitle($this->getParam("page_element_placeholder_title"));
			$objElementData->setStrLanguage($this->getParam("page_element_placeholder_language"));
			//placeholder to update?
			if($this->getParam("placeholder") != "")
			    $objElementData->setStrPlaceholder($this->getParam("placeholder"));

			if(!$objElementData->updateObjectToDb())
				throw new class_exception("Error updating object to db", class_exception::$level_ERROR);


			//check, if we have to update the date-records
            $objStartDate = new class_date("0");
            $objEndDate = new class_date("0");
            $objStartDate->generateDateFromParams("start", $this->getAllParams());
            $objEndDate->generateDateFromParams("end", $this->getAllParams());

			$objSystemCommon = new class_modul_system_common($this->getSystemid());
			if($objStartDate->getIntYear() == "0000" && $objEndDate->getIntYear() == "0000") {
			    //Delete the record (maybe) existing in the dates-table
			    if(!$objSystemCommon->deleteDateRecord())
			        throw new class_exception("Error deleting dates from db", class_exception::$level_ERROR);
			}
			else {
			    //inserts needed
			    $objSystemCommon->setStartDate($objStartDate);
			    $objSystemCommon->setEndDate($objEndDate);
			}

            //allow the element to run actions after saving
            $objElement->doAfterSaveToDb();


			//Loading the data of the corresp site
			$objPage = new class_modul_pages_page($this->getPrevId());
			$this->flushPageFromPagesCache($objPage->getStrName());

		}
		else  {
			$strReturn = $this->objToolkit->warningBox($this->getText("ds_gesperrt"));
		}
		return $strReturn;
	}


	/**
	 * Shows the warning box before deleteing a element
	 *
	 * @return string
	 */
	private function actionDeleteElement() {
		$strReturn = "";
		//Rights?
		if($this->objRights->rightDelete($this->getSystemid())) {
			$objElement = new class_modul_pages_pageelement($this->getSystemid());

            $strQuestion = uniStrReplace("%%element_name%%", htmlToString($objElement->getStrName(). ($objElement->getStrTitle() != "" ? " - ".$objElement->getStrTitle() : "" ), true), $this->getText("element_loeschen_frage"));

			$strReturn .= $this->objToolkit->warningBox($strQuestion
			             ." <br /><a href=\"".getLinkAdminHref("pages_content", "deleteElementFinal", "systemid=".$this->getSystemid().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe")))."\">"
			             .$this->getText("element_loeschen_link"));
		}
		else
			$strReturn .= $this->getText("fehler_recht");

		return $strReturn;
	}

	/**
	 * Deletes an Element
	 *
	 * @param string $strSystemid
	 * @return string, "" in case of success
	 */
	public function actionDeleteElementFinal($strSystemid = "") {
		$strReturn = "";

		if($strSystemid == "")
			$strSystemid = $this->getSystemid();
		//Check the rights
		if($this->objRights->rightDelete($strSystemid)) {
			//Locked?
			$objLockmanager = new class_lockmanager($this->getSystemid());
			$strPrevId = $this->getPrevId();

			if($objLockmanager->isAccessibleForCurrentUser()) {
			    //delete object
			    if(!class_modul_pages_pageelement::deletePageElement($strSystemid))
			        throw new class_exception("Error deleting element from db", class_exception::$level_ERROR);

				//save the prev_id
				$this->setParam("deleteid", $strPrevId);
			}
			else  {
				$strReturn .= $this->objToolkit->warningBox($this->getText("ds_gesperrt"));
			}
		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}


	/**
	 * Shifts an element up or down
	 * This is a special implementation, because we don't have the usual system_prev_id relations
	 * Note: Could be optimized!
	 *
	 * @param string $strMode up || down
	 * @param string $strSystemid
	 * @return string "" in case of success
	 */
	private function actionShiftElement($strMode = "up", $strSystemid = "") {
		$strReturn = "";
		//Load the current Element
		if($strSystemid == "")
		    $strSystemid = $this->getSystemid();
		//Create the objecet
		$objElement = new class_modul_pages_pageelement($strSystemid);
		return $objElement->setPosition($strSystemid, $strMode);
	}

    /**
     * Provides a form to set up the params needed to copy a single element from one placeholder to another.
     * Collects the target language, the target page and the target placeholder, invokes the copy-procedure.
     *
     * @return string, "" in case of success
     */
    private function actionCopyElement() {
        $strReturn = "";

        if($this->objRights->rightEdit($this->getSystemid())) {

            $objLang = null;
            if($this->getParam("copyElement_language") != "") {
                $objLang = new class_modul_languages_language($this->getParam("copyElement_language"));
            } else {
                $objLang = class_modul_languages_language::getLanguageByName($this->getLanguageToWorkOn());
            }

            $objPage = null;
            if($this->getParam("copyElement_page") != "") {
                $objPage = class_modul_pages_page::getPageByName($this->getParam("copyElement_page"));
                $objPage->setStrLanguage($objLang->getStrName());
                $objPage->initObject();
            } else {
                $objPage = new class_modul_pages_page($this->getPrevId());
            }

            $objSourceElement = new class_modul_pages_pageelement($this->getSystemid());


            //form header
            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref("pages_content", "copyElement"), "formCopyElement");
            $strReturn .= $this->objToolkit->formInputHidden("copyElement_doCopy", 1);
            $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());

            $strReturn .= $this->objToolkit->formHeadline($this->getText("copyElement_element")." ".$objSourceElement->getStrName()."_".$objSourceElement->getStrElement()." (".$objSourceElement->getStrTitle().")");


            //step one: language selection
            $arrLanguages = class_modul_languages_language::getAllLanguages(true);
            $arrLanguageDD = array();
            foreach($arrLanguages as $objSingleLanguage)
                $arrLanguageDD[$objSingleLanguage->getSystemid()] = $this->getText("lang_".$objSingleLanguage->getStrName(), "languages", "admin");

            $strReturn .= $this->objToolkit->formInputDropdown("copyElement_language", $arrLanguageDD, $this->getText("copyElement_language"), $objLang->getSystemid());


            //step two: page selection
            $strReturn .= $this->objToolkit->formInputPageSelector("copyElement_page", $this->getText("copyElement_page"), $objPage->getStrName());


            //step three: placeholder-selection
            //here comes the tricky part. load the template, analyze the placeholders and validate all those against things like repeatable and more...
            $strTemplate = $objPage->getStrTemplate();

            //load the placeholders
            $strTemplateId = $this->objTemplate->readTemplate("/templates/modul_pages/".$strTemplate, "", true);
            $arrPlaceholders = $this->objTemplate->getElements($strTemplateId);
            $arrPlaceholdersDD = array();

            foreach($arrPlaceholders as $arrSinglePlaceholder) {

                foreach($arrSinglePlaceholder["elementlist"] as $arrSinglePlaceholderlist) {
                    if($objSourceElement->getStrElement() == $arrSinglePlaceholderlist["element"]) {
                        if($objSourceElement->getIntRepeat() == 1) {
                            //repeatable, ok in every case
                            $arrPlaceholdersDD[$arrSinglePlaceholder["placeholder"]] = $arrSinglePlaceholder["placeholder"];
                        }
                        else {
                            //not repeatable - element already existing at placeholder?
                            $arrElementsOnPage = class_modul_pages_pageelement::getElementsOnPage($objPage->getSystemid(), false, $objLang->getStrName());
                            //loop in order to find same element-types - other elements may be possible due to piped placeholders, too
                            $bitAdd = true;
                            //var_dump($arrElementsOnPage);
                            foreach($arrElementsOnPage as $objSingleElementOnPage) {
                                if($objSingleElementOnPage->getStrElement() == $objSourceElement->getStrElement())
                                    $bitAdd = false;
                            }

                            if($bitAdd)
                                $arrPlaceholdersDD[$arrSinglePlaceholder["placeholder"]] = $arrSinglePlaceholder["placeholder"];
                        }
                    }
                }
            }


            $bitCopyingAllowed = true;
            if(count($arrPlaceholdersDD) == 0) {
                $strReturn .= $this->objToolkit->formTextRow($this->getText("copyElement_err_placeholder"));
                $bitCopyingAllowed = false;
            }
            else {
                $strReturn .= $this->objToolkit->formInputDropdown("copyElement_placeholder", $arrPlaceholdersDD, $this->getText("copyElement_placeholder"));
            }
            $strReturn .= $this->objToolkit->formTextRow($this->getText("copyElement_template")." ".$strTemplate);

            $strReturn .= $this->objToolkit->divider();

            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("copyElement_submit"), "Submit", "", "inputSubmit", $bitCopyingAllowed);
            $strReturn .= $this->objToolkit->formClose();


            $strReturn .= "
                <script type=\"text/javascript\">
                    KAJONA.admin.loader.loadAutocompleteBase(function () {
	                    function reloadForm() {
	                        //ugly part: add a delay to be sure the field is filled by the autocomplete
							YAHOO.lang.later(100, this, function() {
								document.getElementById('copyElement_doCopy').value = 0;
								var formElement = document.getElementById('formCopyElement');
								formElement.submit();
                            });
	                    }

	                    KAJONA.admin.copyElement_page.itemSelectEvent.subscribe(reloadForm);

	                    var languageField = document.getElementById('copyElement_language');
	                    languageField.onchange = reloadForm;

                        var pageField = document.getElementById('copyElement_page');
	                    pageField.onchange = reloadForm;
                    });
                </script>";

            //any actions to take?
            if($this->getParam("copyElement_doCopy") == 1) {
                $objNewElement = $objSourceElement->copyElementToPage($objPage->getSystemid());
                $objNewElement->setStrLanguage( $objLang->getStrName() );
                $objNewElement->setStrPlaceholder($this->getParam("copyElement_placeholder"));
                if($objNewElement->updateObjectToDb()) {
                    $this->setSystemid($objNewElement->getSystemid());
                    $strReturn = "";
                }
                else
                    throw new class_exception("Error copying the pageelement ".$objSourceElement->getSystemid(), class_exception::$level_ERROR);

            }


        }
        else
			$strReturn = $this->getText("fehler_recht");
        return $strReturn;
    }


	/**
	 * Helper to generate a small path-navigation
	 *
	 * @return string
	 */
	private function getPathNavigation() {
		$arrPath = $this->getPathArray();

		$arrPathLinks = array();
		$arrPathLinks[] = getLinkAdmin("pages", "list", "&unlockid=".$this->getSystemid()."&folderid=0", "&nbsp;/&nbsp;", " / ");

		foreach($arrPath as $strOneSystemid) {
			$arrFolder = $this->getSystemRecord($strOneSystemid);
			//Skip Elements: No sense to show in path-navigations
			if($arrFolder["system_module_nr"] == _pages_content_modul_id_)
				continue;

			if($arrFolder["system_module_nr"] == _pages_modul_id_)
			    $arrPathLinks[] = getLinkAdmin("pages_content", "list", "&unlockid=".$this->getSystemid()."&systemid=".$strOneSystemid, $arrFolder["system_comment"], $arrFolder["system_comment"]);
			else
			    $arrPathLinks[] = getLinkAdmin("pages", "list", "&unlockid=".$this->getSystemid()."&folderid=".$strOneSystemid, $arrFolder["system_comment"], $arrFolder["system_comment"]);
		}
		return $this->objToolkit->getPathNavigation($arrPathLinks);
	}

}
?>