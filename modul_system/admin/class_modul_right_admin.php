<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/


/**
 * This class handles the adminside of right-management
 *
 * @package modul_system
 */
class class_modul_right_admin extends class_admin implements interface_admin {

	private $strTemp = "";
	/**
	 * Contructor
	 *
	 */
	public function __construct() {
		$arrModul["name"] 			= "class_modul_right_admin";
		$arrModul["author"] 		= "sidler@mulchprod.de";
		$arrModul["moduleId"] 		= _system_modul_id_;
		$arrModul["modul"]			= "right";

		parent::__construct($arrModul);
		$this->setStrTextBase("system");

	}

	/**
	 * Action-block. Decides, what to do
	 *
	 * @param string $strAction
	 */
	public function action($strAction = "") {
		if($strAction == "")
			$strAction = "change";

		try {
    		if($strAction == "change")
    			$strReturn = $this->actionChange();

    		if($strAction == "saverights") {
    			$strReturn = $this->actionSaveRights();
    			if($strReturn == "") {
    				//Redirecting
    				$strUrlHistory = $this->getHistory(0);
    				$arrHistory = explode("&", $strUrlHistory);
    				if($arrHistory[1] != "module=rights") {
    					$this->adminReload(_indexpath_."?".$this->getHistory(0));
    				}
    			}
    		}
		}
		catch (class_exception $objException) {
		    $objException->processException();
		    $strReturn = "An internal error occured: ".$objException->getMessage();
		}
		$this->strTemp = $strReturn;
	}


	public function getOutputContent() {
		return $this->strTemp;
	}

    protected function getOutputModuleTitle() {
        return $this->getText("moduleRightsTitle");
    }



//*"*******************************************************************************************************
//--Rechte-Verwaltung--------------------------------------------------------------------------------------

	/**
	 * Returns a form to modify the rights
	 *
	 * @return string
	 */
	private function actionChange() {

		$strReturn = "";
		$strSystemID = "";
		//Determing the systemid
		if($this->getParam("systemid") != "")
			$strSystemID = $this->getParam("systemid");
		//Edit a module?
		if($this->getParam("changemodule") != "")
			$strSystemID = $this->getModuleSystemid($this->getParam("changemodule"));
		if($strSystemID == "")
			return $this->getText("fehler_recht");

		if($this->objRights->rightRight($strSystemID)) {
			//Get Rights
			$arrRights = $this->objRights->getArrayRights($strSystemID);
			//Get groups
			$arrGroups = class_modul_user_group::getAllGroups();

			//Determin name of the record
			$arrSystemRecord = $this->getSystemRecord($strSystemID);
			if($arrSystemRecord["system_comment"] == "")
				$strTitle = $this->getText("titel_leer");
			else
				$strTitle = $arrSystemRecord["system_comment"] . " ";
			$strUrlHistory = $this->getHistory(0);
			$strTemp = "";

			//Load the rights header-row
			if($arrSystemRecord["system_module_nr"] == 0)
			    $strModule = "system";
            else if($arrSystemRecord["system_module_nr"] == _pages_folder_id_)
                $strModule = "pages";
			else {
			    $strTempId = class_modul_system_module::getModuleIdByNr($arrSystemRecord["system_module_nr"]);
			    $objModule = new class_modul_system_module($strTempId);
			    $strModule = $objModule->getStrName();
			}

			$arrHeaderRow = $this->getText("permissions_header", $strModule, "admin");

			if($arrHeaderRow == "!permissions_header!")
			    $arrHeaderRow = $this->getText("permissions_default_header", "system", "admin");

			if($strSystemID == "0")
			    $arrHeaderRow = $this->getText("permissions_root_header", "system", "admin");


			$arrTitles = $arrHeaderRow;
			$arrTemplateTotal = array();
			$arrTemplateTotal["title0"] = $arrTitles[0];
			$arrTemplateTotal["title1"] = $arrTitles[1];
			$arrTemplateTotal["title2"] = $arrTitles[2];
			$arrTemplateTotal["title3"] = $arrTitles[3];
			$arrTemplateTotal["title4"] = $arrTitles[4];
			$arrTemplateTotal["title5"] = $arrTitles[5];
			$arrTemplateTotal["title6"] = $arrTitles[6];
			$arrTemplateTotal["title7"] = $arrTitles[7];
			$arrTemplateTotal["title8"] = $arrTitles[8];

			//Read the template
			$strTemplateRow1ID = $this->objTemplate->readTemplate("/elements.tpl", "rights_form_row_1");
			$strTemplateRow2ID = $this->objTemplate->readTemplate("/elements.tpl", "rights_form_row_2");
			$arrTemplateTotal["rows"] = "";
			//Inserting Rows
			$intCounter = 1;
			foreach($arrGroups as $objSingleGroup) {
			  	$arrTemplateRow = array();
			  	$arrSingleGroup = array();
			  	$arrTemplateRow["group"] = $objSingleGroup->getStrName();
			  	$arrSingleGroup["group_id"] = $objSingleGroup->getSystemid();

			  	//Building Checkboxes
			  	if(in_array($arrSingleGroup["group_id"], $arrRights["view"]))
			  		$arrTemplateRow["box0"] = "<input type=\"checkbox\" name=\"1,".$arrSingleGroup["group_id"]."\" id=\"1,".$arrSingleGroup["group_id"]."\" value=\"1\" checked=\"checked\" />";
			  	else
			  		$arrTemplateRow["box0"] = "<input type=\"checkbox\" name=\"1,".$arrSingleGroup["group_id"]."\" id=\"1,".$arrSingleGroup["group_id"]."\" value=\"1\" />";

			  	if(in_array($arrSingleGroup["group_id"], $arrRights["edit"]))
			  		$arrTemplateRow["box1"] = "<input type=\"checkbox\" name=\"2,".$arrSingleGroup["group_id"]."\" id=\"2,".$arrSingleGroup["group_id"]."\" value=\"1\" checked=\"checked\" />";
			  	else
			  		$arrTemplateRow["box1"] = "<input type=\"checkbox\" name=\"2,".$arrSingleGroup["group_id"]."\" id=\"2,".$arrSingleGroup["group_id"]."\" value=\"1\" />";

			  	if(in_array($arrSingleGroup["group_id"], $arrRights["delete"]))
			  		$arrTemplateRow["box2"] = "<input type=\"checkbox\" name=\"3,".$arrSingleGroup["group_id"]."\" id=\"3,".$arrSingleGroup["group_id"]."\" value=\"1\" checked=\"checked\" />";
			  	else
			  		$arrTemplateRow["box2"] = "<input type=\"checkbox\" name=\"3,".$arrSingleGroup["group_id"]."\" id=\"3,".$arrSingleGroup["group_id"]."\" value=\"1\" />";

			  	if(in_array($arrSingleGroup["group_id"], $arrRights["right"]))
		  		    $arrTemplateRow["box3"] = "<input type=\"checkbox\" name=\"4,".$arrSingleGroup["group_id"]."\" id=\"4,".$arrSingleGroup["group_id"]."\" value=\"1\" checked=\"checked\" />";
			  	else
			  		$arrTemplateRow["box3"] = "<input type=\"checkbox\" name=\"4,".$arrSingleGroup["group_id"]."\" id=\"4,".$arrSingleGroup["group_id"]."\" value=\"1\" />";

			  	if(in_array($arrSingleGroup["group_id"], $arrRights["right1"])) {
			  	    //field editable?
			  	    if($arrTemplateTotal["title4"] != "")
			  		    $arrTemplateRow["box4"] = "<input type=\"checkbox\" name=\"5,".$arrSingleGroup["group_id"]."\" id=\"5,".$arrSingleGroup["group_id"]."\" value=\"1\" checked=\"checked\" />";
			  		else
			  		    $arrTemplateRow["box4"] = "<input type=\"hidden\" name=\"5,".$arrSingleGroup["group_id"]."\" id=\"5,".$arrSingleGroup["group_id"]."\" value=\"1\" />";
			  	}
			  	elseif($arrTemplateTotal["title4"] != "")
			  		$arrTemplateRow["box4"] = "<input type=\"checkbox\" name=\"5,".$arrSingleGroup["group_id"]."\" id=\"5,".$arrSingleGroup["group_id"]."\" value=\"1\" />";

			  	if(in_array($arrSingleGroup["group_id"], $arrRights["right2"])) {
			  	    //field editable?
			  	    if($arrTemplateTotal["title5"] != "")
			  		    $arrTemplateRow["box5"] = "<input type=\"checkbox\" name=\"6,".$arrSingleGroup["group_id"]."\" id=\"6,".$arrSingleGroup["group_id"]."\" value=\"1\" checked=\"checked\" />";
			  		else
			  		    $arrTemplateRow["box5"] = "<input type=\"hidden\" name=\"6,".$arrSingleGroup["group_id"]."\" id=\"6,".$arrSingleGroup["group_id"]."\" value=\"1\" />";

			  	}
			  	elseif($arrTemplateTotal["title5"] != "")
			  		$arrTemplateRow["box5"] = "<input type=\"checkbox\" name=\"6,".$arrSingleGroup["group_id"]."\" id=\"6,".$arrSingleGroup["group_id"]."\" value=\"1\" />";

			  	if(in_array($arrSingleGroup["group_id"], $arrRights["right3"])) {
			  	    //field editable?
			  	    if($arrTemplateTotal["title6"] != "")
			  		    $arrTemplateRow["box6"] = "<input type=\"checkbox\" name=\"7,".$arrSingleGroup["group_id"]."\" id=\"7,".$arrSingleGroup["group_id"]."\" value=\"1\" checked=\"checked\" />";
			  		else
			  		    $arrTemplateRow["box6"] = "<input type=\"hidden\" name=\"7,".$arrSingleGroup["group_id"]."\" id=\"7,".$arrSingleGroup["group_id"]."\" value=\"1\" />";
			  	}
			  	elseif($arrTemplateTotal["title6"] != "")
			  		$arrTemplateRow["box6"] = "<input type=\"checkbox\" name=\"7,".$arrSingleGroup["group_id"]."\" id=\"7,".$arrSingleGroup["group_id"]."\" value=\"1\" />";

			  	if(in_array($arrSingleGroup["group_id"], $arrRights["right4"])) {
			  	    //field editable?
			  	    if($arrTemplateTotal["title7"] != "")
			  		    $arrTemplateRow["box7"] = "<input type=\"checkbox\" name=\"8,".$arrSingleGroup["group_id"]."\" id=\"8,".$arrSingleGroup["group_id"]."\" value=\"1\" checked=\"checked\" />";
			  		else
			  		    $arrTemplateRow["box7"] = "<input type=\"hidden\" name=\"8,".$arrSingleGroup["group_id"]."\" id=\"8,".$arrSingleGroup["group_id"]."\" value=\"1\" />";
			  	}
			  	elseif($arrTemplateTotal["title7"] != "")
			  		$arrTemplateRow["box7"] = "<input type=\"checkbox\" name=\"8,".$arrSingleGroup["group_id"]."\" id=\"8,".$arrSingleGroup["group_id"]."\" value=\"1\" />";


			  	if(in_array($arrSingleGroup["group_id"], $arrRights["right5"])) {
			  	    //field editable?
			  	    if($arrTemplateTotal["title8"] != "")
			  		    $arrTemplateRow["box8"] = "<input type=\"checkbox\" name=\"9,".$arrSingleGroup["group_id"]."\" id=\"9,".$arrSingleGroup["group_id"]."\" value=\"1\" checked=\"checked\" />";
			  		else
			  		    $arrTemplateRow["box8"] = "<input type=\"hidden\" name=\"9,".$arrSingleGroup["group_id"]."\" id=\"9,".$arrSingleGroup["group_id"]."\" value=\"1\" />";
			  	}
			  	elseif($arrTemplateTotal["title8"] != "")
			  		$arrTemplateRow["box8"] = "<input type=\"checkbox\" name=\"9,".$arrSingleGroup["group_id"]."\" id=\"9,".$arrSingleGroup["group_id"]."\" value=\"1\" />";


			  	//And Print it to template
			  	if($intCounter++ % 2 == 0)
			  		$arrTemplateTotal["rows"] .= $this->objTemplate->fillTemplate($arrTemplateRow, $strTemplateRow1ID);
			  	else
			  		$arrTemplateTotal["rows"] .= $this->objTemplate->fillTemplate($arrTemplateRow, $strTemplateRow2ID);
			}

			//Build the inherit-box
			$strTemplateInheritID = $this->objTemplate->readTemplate("/elements.tpl", "rights_form_inherit");
			$arrTemplateInherit["title"] = $this->getText("titel_erben");
			$arrTemplateInherit["name"] = "inherit";
			if(isset($arrRights["inherit"]) && $arrRights["inherit"] == 1)
				$arrTemplateInherit["checked"] = "checked=\"checked\"";
			else
				$arrTemplateInherit["checked"] = "";

			$arrTemplateTotal["inherit"] = $this->objTemplate->fillTemplate($arrTemplateInherit, $strTemplateInheritID);

			//Creating the output, starting with the header
			$strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "rights_form_header");
			$arrTemplate["record"] = $strTitle;
			//Backlink
			$strUrlHistory = $this->getHistory(0);
			//Buliding the right-matrix
			$arrHistory = explode("&", $strUrlHistory);
			$arrTemplate["backlink"] = getLinkAdminRaw("".$arrHistory[0]."&".$arrHistory[1], $this->getText("backlink"));
			$arrTemplate["desc"] = $this->getText("desc");
			$strReturn .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
			//Followed by the form
			$strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saverights"), "rightsForm");
			$strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "rights_form_form");
			$strReturn .= $this->objTemplate->fillTemplate($arrTemplateTotal, $strTemplateID);
			$strReturn .= $this->objToolkit->formInputHidden("systemid", $strSystemID);

			//place all inheritance-rights as hidden-fields to support the change-js script
            $strPrevId = $this->getPrevId($strSystemID);
            $arrRightsInherited = $this->objRights->getArrayRights($strPrevId);

            foreach ($arrRightsInherited as $strRightName => $arrRightsPerAction) {
                if($strRightName != "inherit") {
                    $intRightCounter = 0;
                    if($strRightName == "view") $intRightCounter = 1;
                    if($strRightName == "edit") $intRightCounter = 2;
                    if($strRightName == "delete") $intRightCounter = 3;
                    if($strRightName == "right") $intRightCounter = 4;
                    if($strRightName == "right1") $intRightCounter = 5;
                    if($strRightName == "right2") $intRightCounter = 6;
                    if($strRightName == "right3") $intRightCounter = 7;
                    if($strRightName == "right4") $intRightCounter = 8;
                    if($strRightName == "right5") $intRightCounter = 9;

                    foreach($arrRightsPerAction as $strOneGroupId) {
                        //place hidden field
                        $strReturn .= $this->objToolkit->formInputHidden("inherit,".$intRightCounter.",".$strOneGroupId, "1");
                    }
                }
            }

			//Close the form
			$strReturn .= $this->objToolkit->formInputSubmit($this->getText("submit"));
			$strReturn .= $this->objToolkit->formClose();
			$strReturn .= "<script type=\"text/javascript\">KAJONA.admin.checkRightMatrix();</script>";
		}
		else
			$strReturn .= $this->getText("fehler_recht");
		return $strReturn;
	}

	/**
	 * Saves the rights passed by form
	 *
	 * @return string "" in case of success
	 */
	private function actionSaveRights() {
		$strReturn = "";
		//Collecting & sorting the passed values
		$strSystemid = $this->getSystemid();

		//Special case: The root-record.
        if($strSystemid == null) {
            if($this->getParam("systemid") == "0") {
                $strSystemid = "0";
            }
        }

		if($this->objRights->rightRight($strSystemid)) {
			//Inheritance?
			if($this->getParam("inherit") == 1)
			 	$intInherit = 1;
			 else
			 	$intInherit = 0;

			//Modified RootRecord? Here Inheritance is NOT allowed!
			if($strSystemid == "0")
				$intInherit = 0;

			//Get AdminID
			$strAdminId = _admins_group_id_;

			//Get Groups
			$arrGroups = class_modul_user_group::getAllGroups();

			$strView = $strAdminId;
			$strEdit = $strAdminId;
			$strDelete = $strAdminId;
			$strRight = $strAdminId;
			$strRight1 = $strAdminId;
			$strRight2 = $strAdminId;
			$strRight3 = $strAdminId;
			$strRight4 = $strAdminId;
			$strRight5 = $strAdminId;

			foreach($arrGroups as $objSingleGroup) {
				$strGroupId = $objSingleGroup->getSystemid();
				if($strGroupId == $strAdminId)
					continue;

				if($this->getParam("1,".$strGroupId) == 1)
					$strView .= ",".$strGroupId;
				if($this->getParam("2,".$strGroupId) == 1)
					$strEdit .= ",".$strGroupId;
				if($this->getParam("3,".$strGroupId) == 1)
					$strDelete .= ",".$strGroupId;
				if($this->getParam("4,".$strGroupId) == 1)
					$strRight .= ",".$strGroupId;
				if($this->getParam("5,".$strGroupId) == 1)
					$strRight1 .= ",".$strGroupId;
				if($this->getParam("6,".$strGroupId) == 1)
					$strRight2 .= ",".$strGroupId;
				if($this->getParam("7,".$strGroupId) == 1)
					$strRight3 .= ",".$strGroupId;
				if($this->getParam("8,".$strGroupId) == 1)
					$strRight4 .= ",".$strGroupId;
				if($this->getParam("9,".$strGroupId) == 1)
					$strRight5 .= ",".$strGroupId;
			}
			$arrReturn = array(
							"inherit"		=> $intInherit,
							"view"			=> $strView,
							"edit" 			=> $strEdit,
							"delete" 		=> $strDelete,
							"right"			=> $strRight,
							"right1"		=> $strRight1,
							"right2"		=> $strRight2,
							"right3"		=> $strRight3,
							"right4"		=> $strRight4,
							"right5"		=> $strRight5);

			//Pass to right-class
			if($this->objRights->setRights($arrReturn, $strSystemid ))	{
				return "";
			}
			else
			    throw new class_exception($this->getText("fehler_setzen"), class_exception::$level_ERROR);
		}
		else
			$strReturn .= $this->getText("fehler_recht");
		return $strReturn;
	}
}
?>