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
 * @package module_pages
 * @author sidler@mulchprod.de
 */
class class_module_pages_content_admin extends class_admin implements interface_admin {
	/**
	 * Constructor
	 *
	 */
	public function __construct() {

        $this->setArrModuleEntry("modul", "pages");
        $this->setArrModuleEntry("moduleId", _pages_content_modul_id_);

        if(_xmlLoader_)
            $this->setArrModuleEntry("modul", "pages_content");

		parent::__construct();
        //If there's anything to unlock, do it now
		if($this->getParam("unlockid") != "") {
            $objLockmanager = new class_lockmanager($this->getParam("unlockid"));
            $objLockmanager->unlockRecord();
		}
		if($this->getParam("adminunlockid") != "") {
            $objLockmanager = new class_lockmanager($this->getParam("adminunlockid"));
            $objLockmanager->unlockRecord(true);
		}
	}

    /**
     * Adds the global path-navigation to the output created by the module
     *
     * @return string
     * @overwrites
     */
	public function getOutputContent() {
        if($this->getParam("pe") != 1) {
            $this->strOutput = $this->getPathNavigation().$this->strOutput;
        }
		return $this->strOutput;
	}

    /**
     * Adds the current page-name to the module-title
     * @return string
     */
	public function getOutputModuleTitle() {
		$objPage = new class_module_pages_page($this->getSystemid());
		if($objPage->getStrName() == "")
			$objPage = new class_module_pages_page($objPage->getPrevId());
		return $this->getLang("modul_titel") . " (".$objPage->getStrName().")";
	}

	protected function getOutputModuleNavi() {
	    return array();
	}


	/**
	 * Returns a list of available placeholders & elements on this page
	 *
	 * @return string
     * @permissions edit
	 */
	protected function actionList() {
		$strReturn = "";
        $objPage = new class_module_pages_page($this->getSystemid());
        //get infos about the page

        $arrToolbarEntries = array();
        $arrToolbarEntries[0] = "<a href=\"".getLinkAdminHref("pages", "editPage", "&systemid=".$this->getSystemid())."\" style=\"background-image:url("._skinwebpath_."/pics/icon_page.gif);\">".$this->getLang("contentToolbar_pageproperties")."</a>";
        $arrToolbarEntries[1] = "<a href=\"".getLinkAdminHref("pages_content", "list", "&systemid=".$this->getSystemid())."\" style=\"background-image:url("._skinwebpath_."/pics/icon_pencil.gif);\">".$this->getLang("contentToolbar_content")."</a>";
        $arrToolbarEntries[2] = "<a href=\"".getLinkPortalHref($objPage->getStrName(), "", "", "&preview=1", "", $this->getLanguageToWorkOn())."\" target=\"_blank\" style=\"background-image:url("._skinwebpath_."/pics/icon_lens.gif);\">".$this->getLang("contentToolbar_preview")."</a>";

        //if languages are installed, present a language switch right here
        $objLanguages = new class_module_languages_admin();
        $arrToolbarEntries[3] = $objLanguages->getLanguageSwitch();

        if($objPage->getIntType() != class_module_pages_page::$INT_TYPE_ALIAS)
            $strReturn .= $this->objToolkit->getContentToolbar($arrToolbarEntries, 1);

        $arrTemplate = array();
        $arrTemplate["pagetemplate"] = $objPage->getStrTemplate();
        $arrTemplate["pagetemplateTitle"] = $this->getLang("template");

        $arrTemplate["lastuserTitle"] = $this->getLang("lastuserTitle");
        $arrTemplate["lasteditTitle"] = $this->getLang("lasteditTitle");
        $arrTemplate["lastuser"] = $objPage->getLastEditUser();

        if(_system_changehistory_enabled_ != "false")
            $arrTemplate["lastuser"] .= " (".getLinkAdmin("pages", "showHistory", "&systemid=".$this->getSystemid(), $this->getLang("show_history")).")";

        $arrTemplate["lastedit"] = timeToString($objPage->getIntLmTime());
        $strReturn .= $this->objToolkit->getPageInfobox($arrTemplate);

        //try to load template, otherwise abort
        $strTemplateID = null;
        try {
            $strTemplateID = $this->objTemplate->readTemplate("/module_pages/".$objPage->getStrTemplate(), "", false, true);
        } catch (class_exception $objException) {
            $strReturn .= $this->getLang("templateNotLoaded")."<br />";
        }

        //Load elements on template, master-page special case!
        if($objPage->getStrName() == "master")
            $arrElementsOnTemplate = $this->objTemplate->getElements($strTemplateID, 1);
        else
            $arrElementsOnTemplate = $this->objTemplate->getElements($strTemplateID, 0);

        $arrElementsOnPage = array();
        //Language-dependant loading of elements, if installed
        $arrElementsOnPage = class_module_pages_pageelement::getElementsOnPage($this->getSystemid(), false, $this->getLanguageToWorkOn());
        //save a copy of the array to be able to check against all values later on
        $arrElementsOnPageCopy = $arrElementsOnPage;

        //Loading all Elements installed on the system ("RAW"-Elements)
        $arrElementsInSystem = class_module_pages_element::getAllElements();


        //So, loop through the placeholders and check, if there's any element already belonging to this one
        $intI = 0;
        if(is_array($arrElementsOnTemplate) && count($arrElementsOnTemplate) > 0) {
            //Iterate over every single placeholder provided by the template
            foreach($arrElementsOnTemplate as $intKeyElementOnTemplate => $arrOneElementOnTemplate) {

                $strOutputAtPlaceholder = "";
                //Do we have one or more elements already in db at this placeholder?
                $bitHit = false;

                //Iterate over every single element-type provided by the placeholder
                foreach ($arrElementsOnPage as $intArrElementsOnPageKey => $objOneElementOnPage) {
                    //Check, if its the same placeholder
                    $bitSamePlaceholder = false;
                    if($arrOneElementOnTemplate["placeholder"] == $objOneElementOnPage->getStrPlaceholder()) {
                        $bitSamePlaceholder = true;
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
                                $strActions .= $this->objToolkit->listButton(getLinkAdmin("pages_content", "list", "&systemid=".$this->getSystemid()."&adminunlockid=".$objOneElementOnPage->getSystemid(), "", $this->getLang("ds_entsperren"), "icon_lockerOpen.gif"));
                            }
                            //If the Element is locked, then its not allowed to edit or delete the record, so disable the icons
                            $strActions .= $this->objToolkit->listButton(getImageAdmin("icon_pencilLocked.gif", $this->getLang("ds_gesperrt")));
                            $strActions .= $this->objToolkit->listButton(getImageAdmin("icon_tonLocked.gif", $this->getLang("ds_gesperrt")));
                        }
                        else {
                            //if it's the user who locked the record, unlock it now
                            if($objLockmanager->isLockedByCurrentUser())
                                $objLockmanager->unlockRecord();

                            $strActions .= $this->objToolkit->listButton(getLinkAdmin("pages_content", "editElement", "&systemid=".$objOneElementOnPage->getSystemid()."&placeholder=".$arrOneElementOnTemplate["placeholder"], "", $this->getLang("element_bearbeiten"), "icon_pencil.gif"));
                            $strActions .= $this->objToolkit->listDeleteButton($objOneElementOnPage->getStrName(). ($objOneElementOnPage->getStrTitle() != "" ? " - ".$objOneElementOnPage->getStrTitle() : "" ), $this->getLang("element_loeschen_frage"), getLinkAdminHref("pages_content", "deleteElementFinal", "&systemid=".$objOneElementOnPage->getSystemid().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
                        }

                        //The Icons to sort the list and to copy the element
                        $strActions .= $this->objToolkit->listButton(getLinkAdmin("pages_content", "copyElement", "&systemid=".$objOneElementOnPage->getSystemid(), "", $this->getLang("element_copy"), "icon_copy.gif"));

                        //The status-icons
                        $strActions .= $this->objToolkit->listStatusButton($objOneElementOnPage->getSystemid());

                        //Put all Output together
                        $strOutputAtPlaceholder .= $this->objToolkit->simpleAdminList($objOneElementOnPage, $strActions, $intI++);

                        //remove the element from the array
                        unset($arrElementsOnPage[$intArrElementsOnPageKey]);
                    }

                }

                //Check, if one of the elements in the placeholder is allowed to be used multiple times
                foreach ($arrOneElementOnTemplate["elementlist"] as $arrSingleElementOnTemplateplaceholder) {
                    foreach($arrElementsInSystem as $objOneElementInSystem) {
                        if($objOneElementInSystem->getStrName() == $arrSingleElementOnTemplateplaceholder["element"]) {
                            $objElement = $objOneElementInSystem;
                            if($objElement->getIntRepeat() == 1 || $bitHit === false)	{
                                //So, the Row for a new element: element is repeatable or not yet created
                                $strActions = $this->objToolkit->listButton(getLinkAdmin("pages_content", "newElement", "&placeholder=".$arrOneElementOnTemplate["placeholder"]."&element=".$arrSingleElementOnTemplateplaceholder["element"]."&systemid=".$this->getSystemid(), "", $this->getLang("element_anlegen"), "icon_new.gif"));
                                $strOutputAtPlaceholder .= $this->objToolkit->genericAdminList("", $arrSingleElementOnTemplateplaceholder["name"] . " (".$objOneElementInSystem->getStrDisplayName() . ")", "", $strActions, $intI++);
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
                                    $strActions = $this->objToolkit->listButton(getLinkAdmin("pages_content", "newElement", "&placeholder=".$arrOneElementOnTemplate["placeholder"]."&element=".$arrSingleElementOnTemplateplaceholder["element"]."&systemid=".$this->getSystemid(), "", $this->getLang("element_anlegen"), "icon_new.gif"));
                                    $strOutputAtPlaceholder .= $this->objToolkit->genericAdminList("", $arrSingleElementOnTemplateplaceholder["name"] . " (".$arrSingleElementOnTemplateplaceholder["element"] . ")", "", $strActions, $intI++);
                                }
                            }
                        }
                    }
                }

                if((int)uniStrlen($strOutputAtPlaceholder) > 0) {
                    $strListId = generateSystemid();
                    $strReturn .= $this->objToolkit->dragableListHeader($strListId, true);
                    $strReturn .= $strOutputAtPlaceholder;
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
            $strReturn .= $this->getLang("element_liste_leer");
        }

        //if there are any page-elements remaining, print a warning and print the elements row
        if(count($arrElementsOnPage) > 0) {
            $strReturn .= $this->objToolkit->divider();
            $strReturn .= $this->objToolkit->warningBox($this->getLang("warning_elementsremaining"));
            $strReturn .= $this->objToolkit->listHeader();

            //minimized actions now, plz. this ain't being a real element anymore!
            foreach($arrElementsOnPage as $objOneElement) {
                $strActions = "";
                $strActions .= $this->objToolkit->listDeleteButton($objOneElement->getStrName(). ($objOneElement->getStrTitle() != "" ? " - ".$objOneElement->getStrTitle() : "" ), $this->getLang("element_loeschen_frage"), getLinkAdminHref("pages_content", "deleteElementFinal", "&systemid=".$objOneElement->getSystemid().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));

                //Put all Output together
                $strReturn .= $this->objToolkit->genericAdminList("", $objOneElement->getStrName() . " (".$objOneElement->getStrElement() . ") - ".$this->getLang("placeholder").$objOneElement->getStrPlaceholder(), "", $strActions, $intI++);
            }
            $strReturn .= $this->objToolkit->listFooter();
        }



		return $strReturn;
	}

    /**
     * Loads the form to create a new element
     *
     * @param bool $bitShowErrors
     * @return string
     */
	protected function actionNewElement($bitShowErrors = false) {
		$strReturn = "";
        //check rights
        $objCommon = new class_module_system_common($this->getSystemid());
		if($objCommon->rightEdit()) {
    		//OK, here we go. So, what information do we have?
    		$strPlaceholderElement = $this->getParam("element");
    		//Now, load all infos about the requested element
    		$objElement = class_module_pages_element::getElement($strPlaceholderElement);
    		//Build the class-name
    		$strElementClass = str_replace(".php", "", $objElement->getStrClassAdmin());
    		//and finally create the object
    		$objElement = new $strElementClass();
    		if($bitShowErrors)
    		  $objElement->setDoValidation(true);

    		$strReturn = $objElement->actionEdit("new");
		}
		else
		    $strReturn .= $this->getLang("commons_error_permissions");

		return $strReturn;
	}


    /**
     * Loads the form to edit the element
     *
     * @param bool $bitShowErrors
     * @return string
     */
	protected function actionEditElement($bitShowErrors = false) {
		$strReturn = "";
		//check rights
        $objElement = new class_module_pages_pageelement($this->getSystemid());
		if($objElement->rightEdit()) {
    		//Load the element data
    		//check, if the element isn't locked
    		if($objElement->getLockManager()->isAccessibleForCurrentUser()) {
                $objElement->getLockManager()->lockRecord();

    			//Load the class to create an object
    			$strElementClass = str_replace(".php", "", $objElement->getStrClassAdmin());
    			//and finally create the object
    			$objPageElement = new $strElementClass();
    			if($bitShowErrors)
    		        $objPageElement->setDoValidation(true);
    			$strReturn .= $objPageElement->actionEdit("edit");


    		}
    		else {
    			$strReturn .= $this->objToolkit->warningBox($this->getLang("ds_gesperrt"));
    		}
		}
		else
		    $strReturn .= $this->getLang("commons_error_permissions");

		return $strReturn;
	}

	/**
	 * Saves the passed Element to the database (edit or new modes)
	 *
	 * @return string "" in case of success
	 */
	protected function actionSaveElement() {
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
			$objElement = class_module_pages_element::getElement($strPlaceholderElement);
			//Load the class to create an object
			$strElementClass = str_replace(".php", "", $objElement->getStrClassAdmin());
			//and finally create the object
			$objElement = new $strElementClass();

			//really continue? try to validate the passed data.
			if(!$objElement->validateForm()) {
			    $strReturn .= $this->actionNewElement(true);
			    return $strReturn;
			}

			//So, lets do the magic - create the records
			$objPageElement = new class_module_pages_pageelement("");
			$objPageElement->setStrName($strPlaceholderName);
			$objPageElement->setStrPlaceholder($strPlaceholder);
			$objPageElement->setStrElement($strPlaceholderElement);
            $objPageElement->setStrLanguage($this->getParam("page_element_ph_language"));
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
        $objCommons = new class_module_system_common($this->getSystemid());
		$strPageSystemid = $objCommons->getPrevId();

        $objLockmanager = new class_lockmanager($this->getSystemid());

		if($objLockmanager->isLockedByCurrentUser()) {
			//Load the data of the current element
			$objElementData = new class_module_pages_pageelement($this->getSystemid());
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
            //FIXME in eigene methode auslagern
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
                    throw new class_exception("Element has invalid tableRows value!!!", class_exception::$level_ERROR);
			}
			else {
			    //To remain backwards-compatible:
			    //Call the save-method of the element instead or if the element wants to update its data specially
			    if(method_exists($objElement, "actionSave") && !$objElement->actionSave($this->getSystemid()))
                    throw new class_exception("Element returned error saving to database!!!", class_exception::$level_ERROR);
			}
			//Edit Date of page & unlock
            $objCommons = new class_module_system_common($strPageSystemid);
            $objCommons->updateObjectToDb();
			$objLockmanager->unlockRecord();
			//And update the internal comment and language
			$objElementData->setStrTitle($this->getParam("page_element_ph_title"));
			$objElementData->setStrLanguage($this->getParam("page_element_ph_language"));
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

			$objSystemCommon = new class_module_system_common($this->getSystemid());
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


			//Loading the data of the corresponding site
			$objPage = new class_module_pages_page($this->getPrevId());
			$this->flushPageFromPagesCache($objPage->getStrName());

            $this->adminReload(getLinkAdminHref("pages_content", "list", "systemid=".$this->getPrevId()));

		}
		else  {
			$strReturn = $this->objToolkit->warningBox($this->getLang("ds_gesperrt"));
		}
		return $strReturn;
	}

    /**
	 * Updates a single field of an element already existing.
	 *
	 * @return string "" in case of success
     * @xml
     */
	protected function actionUpdateElementField() {
		$strReturn = "";
		//check, if the element isn't locked
        $objCommons = new class_module_system_common($this->getSystemid());
		$strPageSystemid = $objCommons->getPrevId();

        $objLockmanager = new class_lockmanager($this->getSystemid());

		if($objLockmanager->isLockedByCurrentUser() && $objCommons->rightEdit()) {
			//Load the data of the current element
			$objElementData = new class_module_pages_pageelement($this->getSystemid());
			//Build the class-name
			$strElementClass = str_replace(".php", "", $objElementData->getStrClassAdmin());
			//and finally create the object
            /** @var class_element_admin $objElement  */
			$objElement = new $strElementClass();
            $arrElementData = $objElement->loadElementData();

            //see if we could set the param to the element
            if($this->getParam("field") != "") {
                $arrElementData[$this->getParam("field")] = $this->getParam("value");
            }

            //pass the data to the element, maybe the element wants to update some data
            $objElement->setArrParamData($arrElementData);
            $objElement->doBeforeSaveToDb();

			//check, if we could save the data, so the element needn't to
			//woah, we are soooo great
            //FIXME: in eigene methode aulagern
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
                    throw new class_exception("Element has invalid tableRows value!!!", class_exception::$level_ERROR);
			}
			else {
			    //To remain backwards-compatible:
			    //Call the save-method of the element instead or if the element wants to update its data specially
			    if(method_exists($objElement, "actionSave") && !$objElement->actionSave($this->getSystemid()))
				    throw new class_exception("Element returned error saving to database!!!", class_exception::$level_ERROR);
			}
			//Edit Date of page & unlock
            $objCommons = new class_module_system_common($strPageSystemid);
            $objCommons->updateObjectToDb();
			$objLockmanager->unlockRecord();

            //allow the element to run actions after saving
            $objElement->doAfterSaveToDb();

			//Loading the data of the corresp site
			$objPage = new class_module_pages_page($this->getPrevId());
			$this->flushPageFromPagesCache($objPage->getStrName());

            $strReturn = "<message><success>update succeeded</success></message>";
		}
		else  {
            header(class_http_statuscodes::$strSC_UNAUTHORIZED);
			$strReturn = "<message><error>".$this->getLang("ds_gesperrt").".".$this->getLang("commons_error_permissions")."</error></message>";
		}
		return $strReturn;
	}


	/**
	 * Shows the warning box before deleteing a element
	 *
	 * @return string
	 */
	protected function actionDeleteElement() {
		$strReturn = "";
        $objElement = new class_module_pages_pageelement($this->getSystemid());
		if($objElement->rightDelete()) {
            $strQuestion = uniStrReplace("%%element_name%%", htmlToString($objElement->getStrName(). ($objElement->getStrTitle() != "" ? " - ".$objElement->getStrTitle() : "" ), true), $this->getLang("element_loeschen_frage"));

			$strReturn .= $this->objToolkit->warningBox($strQuestion
			             ." <br /><a href=\"".getLinkAdminHref("pages_content", "deleteElementFinal", "systemid=".$this->getSystemid().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe")))."\">"
			             .$this->getLang("commons_delete"));
		}
		else
			$strReturn .= $this->getLang("commons_error_permissions");

		return $strReturn;
	}

	/**
	 * Deletes an Element
	 *
	 * @param string $strSystemid
	 * @return string, "" in case of success
	 */
	protected function actionDeleteElementFinal($strSystemid) {
		$strReturn = "";

        $objPageElement = new class_module_pages_pageelement($this->getSystemid());
		if($objPageElement->rightDelete($strSystemid)) {
			//Locked?
			$objLockmanager = new class_lockmanager($this->getSystemid());
			$strPrevId = $this->getPrevId();

			if($objLockmanager->isAccessibleForCurrentUser()) {
			    //delete object
			    if(!$objPageElement->deleteObject())
			        throw new class_exception("Error deleting element from db", class_exception::$level_ERROR);

                $this->adminReload(getLinkAdminHref("pages_content", "list", "systemid=".$strPrevId.($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
			}
			else  {
				$strReturn .= $this->objToolkit->warningBox($this->getLang("ds_gesperrt"));
			}
		}
		else
			$strReturn = $this->getLang("commons_error_permissions");

		return $strReturn;
	}


    /**
     * Provides a form to set up the params needed to copy a single element from one placeholder to another.
     * Collects the target language, the target page and the target placeholder, invokes the copy-procedure.
     *
     * @return string, "" in case of success
     */
    protected function actionCopyElement() {
        $strReturn = "";

        $objSourceElement = new class_module_pages_pageelement($this->getSystemid());
        if($objSourceElement->rightEdit($this->getSystemid())) {

            $objLang = null;
            if($this->getParam("copyElement_language") != "") {
                $objLang = new class_module_languages_language($this->getParam("copyElement_language"));
            } else {
                $objLang = class_module_languages_language::getLanguageByName($this->getLanguageToWorkOn());
            }

            $objPage = null;
            if($this->getParam("copyElement_page") != "") {
                $objPage = class_module_pages_page::getPageByName($this->getParam("copyElement_page"));
                $objPage->setStrLanguage($objLang->getStrName());
                $objPage->initObject();
            } else {
                $objPage = new class_module_pages_page($this->getPrevId());
            }

            //form header
            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref("pages_content", "copyElement"), "formCopyElement");
            $strReturn .= $this->objToolkit->formInputHidden("copyElement_doCopy", 1);
            $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());

            $strReturn .= $this->objToolkit->formHeadline($this->getLang("copyElement_element")." ".$objSourceElement->getStrName()."_".$objSourceElement->getStrElement()." (".$objSourceElement->getStrTitle().")");


            //step one: language selection
            $arrLanguages = class_module_languages_language::getAllLanguages(true);
            $arrLanguageDD = array();
            foreach($arrLanguages as $objSingleLanguage)
                $arrLanguageDD[$objSingleLanguage->getSystemid()] = $this->getLang("lang_".$objSingleLanguage->getStrName(), "languages");

            $strReturn .= $this->objToolkit->formInputDropdown("copyElement_language", $arrLanguageDD, $this->getLang("copyElement_language"), $objLang->getSystemid());


            //step two: page selection
            $strReturn .= $this->objToolkit->formInputPageSelector("copyElement_page", $this->getLang("copyElement_page"), $objPage->getStrName(), "inputText", false);


            //step three: placeholder-selection
            //here comes the tricky part. load the template, analyze the placeholders and validate all those against things like repeatable and more...
            $strTemplate = $objPage->getStrTemplate();

            //load the placeholders
            $strTemplateId = $this->objTemplate->readTemplate("/templates/module_pages/".$strTemplate, "", true);
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
                            $arrElementsOnPage = class_module_pages_pageelement::getElementsOnPage($objPage->getSystemid(), false, $objLang->getStrName());
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
                $strReturn .= $this->objToolkit->formTextRow($this->getLang("copyElement_err_placeholder"));
                $bitCopyingAllowed = false;
            }
            else {
                $strReturn .= $this->objToolkit->formInputDropdown("copyElement_placeholder", $arrPlaceholdersDD, $this->getLang("copyElement_placeholder"));
            }
            $strReturn .= $this->objToolkit->formTextRow($this->getLang("copyElement_template")." ".$strTemplate);

            $strReturn .= $this->objToolkit->divider();

            $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("copyElement_submit"), "Submit", "", "inputSubmit", $bitCopyingAllowed);
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

                    $this->adminReload(getLinkAdminHref("pages_content", "list", "systemid=".$this->getPrevId().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
                }
                else
                    throw new class_exception("Error copying the pageelement ".$objSourceElement->getSystemid(), class_exception::$level_ERROR);

            }


        }
        else
			$strReturn = $this->getLang("commons_error_permissions");
        return $strReturn;
    }


	/**
	 * Helper to generate a small path-navigation
	 *
	 * @return string
     * @permissions view
	 */
	private function getPathNavigation() {
		$arrPath = $this->getPathArray();

		$arrPathLinks = array();
		$arrPathLinks[] = getLinkAdmin("pages", "list", "&unlockid=".$this->getSystemid(), "&nbsp;/&nbsp;", " / ");

		foreach($arrPath as $strOneSystemid) {
            $objObject = class_objectfactory::getInstance()->getObject($strOneSystemid);
			//Skip Elements: No sense to show in path-navigation
			if($objObject->getIntModuleNr() == _pages_content_modul_id_)
				continue;

            if($objObject instanceof class_module_pages_folder) {
                $arrPathLinks[] = getLinkAdmin("pages", "list", "&systemid=".$strOneSystemid."&unlockid=".$this->getSystemid(), $objObject->getStrName());
            }
            if($objObject instanceof class_module_pages_page) {
                $arrPathLinks[] = getLinkAdmin("pages", "list", "&systemid=".$strOneSystemid."&unlockid=".$this->getSystemid(), $objObject->getStrBrowsername());
            }

		}
		return $this->objToolkit->getPathNavigation($arrPathLinks);
	}


    /**
     * Sorts the current element upwards
     */
    protected function actionElementSortUp() {
        //Create the object
		$objElement = new class_module_pages_pageelement($this->getSystemid());
		$objElement->setPosition($this->getSystemid(), "up");
        $this->adminReload(getLinkAdminHref("pages_content", "list", "systemid=".$this->getPrevId().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));

    }

    /**
     * Sorts the current element downwards
     */
    protected function actionElementSortDown() {
        //Create the object
		$objElement = new class_module_pages_pageelement($this->getSystemid());
		$objElement->setPosition($this->getSystemid(), "down");
        $this->adminReload(getLinkAdminHref("pages_content", "list", "systemid=".$this->getPrevId().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
    }

    /**
     * Sorts the current element upwards
     */
    protected function actionElementStatus() {
        //Create the object
		$objElement = new class_module_pages_pageelement($this->getSystemid());
		$objElement->setStatus();
        $this->adminReload(getLinkAdminHref("pages_content", "list", "systemid=".$this->getPrevId().($this->getParam("pe") == "" ? "" : "&peClose=".$this->getParam("pe"))));
    }

}
