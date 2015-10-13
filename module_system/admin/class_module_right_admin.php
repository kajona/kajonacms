<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                *
********************************************************************************************************/


/**
 * This class handles the backend-part of permission-management
 *
 * @package module_system
 * @author sidler@mulchprod.de
 *
 * @module right
 * @moduleId _system_modul_id_
 */
class class_module_right_admin extends class_admin_controller implements interface_admin {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->setStrLangBase("system");

        if($this->getAction() == "list") {
            $this->setAction("change");
        }
    }


    protected function getOutputModuleTitle() {
        return $this->getLang("moduleRightsTitle");
    }


    /**
     * Returns a form to modify the rights
     *
     * @return string
     * @permissions right
     */
    protected function actionChange() {

        $strReturn = "";
        $strSystemID = $this->getParam("systemid");
        $objTargetRecord = null;
        
        if($strSystemID == "")
        	$strSystemID = "0";

        //Determine the systemid
        if($strSystemID != "") {
            $objTargetRecord = class_objectfactory::getInstance()->getObject($strSystemID);
        }
        //Edit a module?
        if($this->getParam("changemodule") != "") {
            $objTargetRecord = class_module_system_module::getModuleByName($this->getParam("changemodule"));
            $strSystemID = $objTargetRecord->getSystemid();
        }

        if($objTargetRecord == null) {
            return $this->getLang("commons_error_permissions");
        }

        $objRights = class_carrier::getInstance()->getObjRights();

        if($objTargetRecord->rightRight()) {
            //Get Rights
            $arrRights = $objRights->getArrayRights($objTargetRecord->getSystemid());
            //Get groups
            $arrGroups = class_module_user_group::getObjectList();

            //Determine name of the record
            if($objTargetRecord instanceof class_module_system_module)
                $strTitle = class_carrier::getInstance()->getObjLang()->getLang("modul_titel", $objTargetRecord->getStrName()) ." (".$objTargetRecord->getStrDisplayName().")";
            else if($objTargetRecord->getStrDisplayName() == "")
                $strTitle = $this->getLang("titel_leer");
            else
                $strTitle = $objTargetRecord->getStrDisplayName() . " ";

            //Load the rights header-row
            if($objTargetRecord->getIntModuleNr() == 0)
                $strModule = "system";
            else if($objTargetRecord instanceof class_module_system_module)
                $strModule = $objTargetRecord->getStrName();
            else if(defined("_pages_folder_id_") && $objTargetRecord->getIntModuleNr() == _pages_folder_id_)
                $strModule = "pages";
            else
                $strModule = $objTargetRecord->getArrModule("modul");


            $arrHeaderRow = $this->getLang("permissions_header", $strModule);
            $arrDefaultHeader = $this->getLang("permissions_default_header", "system");


            if($arrHeaderRow == "!permissions_header!")
                $arrHeaderRow = $arrDefaultHeader;

            if($strSystemID == "0")
                $arrHeaderRow = $this->getLang("permissions_root_header", "system");

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
            if(class_module_system_setting::getConfigValue("_system_changehistory_enabled_") == "true") {
                if(!isset($arrTitles[9]))  //fallback for pre 4.3.2 systems
                    $arrTitles[9] = $arrDefaultHeader[9];

                $arrTemplateTotal["title9"] = $arrTitles[9];
            }

            //Read the template
            $strTemplateRowID = $this->objTemplate->readTemplate("/elements.tpl", "rights_form_row");
            $arrTemplateTotal["rows"] = "";
            //Inserting Rows
            foreach($arrGroups as $objSingleGroup) {
                $arrTemplateRow = array();
                $arrSingleGroup = array();
                $arrTemplateRow["group"] = $objSingleGroup->getStrName();
                $arrSingleGroup["group_id"] = $objSingleGroup->getSystemid();

                //hide the superglobal admin-row from non-members
                if($objSingleGroup->getSystemid() == class_module_system_setting::getConfigValue("_admins_group_id_") && !in_array(class_module_system_setting::getConfigValue("_admins_group_id_"), $this->objSession->getGroupIdsAsArray())) {
                    continue;
                }


                //Building Checkboxes
                $arrTemplateRow["box0"] = "<input title=\"".$arrTitles[0]."\" rel=\"tooltip\" type=\"checkbox\" name=\"1,".$arrSingleGroup["group_id"]."\" id=\"1,".$arrSingleGroup["group_id"]."\" value=\"1\" ".(in_array($arrSingleGroup["group_id"], $arrRights["view"]) ? " checked=\"checked\" " : "")." />";
                $arrTemplateRow["box1"] = "<input title=\"".$arrTitles[1]."\" rel=\"tooltip\" type=\"checkbox\" name=\"2,".$arrSingleGroup["group_id"]."\" id=\"2,".$arrSingleGroup["group_id"]."\" value=\"1\" ".(in_array($arrSingleGroup["group_id"], $arrRights["edit"]) ? " checked=\"checked\" " : "")." />";
                $arrTemplateRow["box2"] = "<input title=\"".$arrTitles[2]."\" rel=\"tooltip\" type=\"checkbox\" name=\"3,".$arrSingleGroup["group_id"]."\" id=\"3,".$arrSingleGroup["group_id"]."\" value=\"1\" ".(in_array($arrSingleGroup["group_id"], $arrRights["delete"]) ? " checked=\"checked\" " : "")." />";
                $arrTemplateRow["box3"] = "<input title=\"".$arrTitles[3]."\" rel=\"tooltip\" type=\"checkbox\" name=\"4,".$arrSingleGroup["group_id"]."\" id=\"4,".$arrSingleGroup["group_id"]."\" value=\"1\" ".(in_array($arrSingleGroup["group_id"], $arrRights["right"]) ? " checked=\"checked\" " : "")." />";

                //loop the module specific permissions
                for($intI = 1; $intI <= 5; $intI++) {
                    if($arrTemplateTotal["title".($intI+3)] != "") {
                        $arrTemplateRow["box".($intI+3)] = "<input title=\"".$arrTitles[$intI+3]."\" rel=\"tooltip\" type=\"checkbox\" name=\"".($intI+4).",".$arrSingleGroup["group_id"]."\" id=\"".($intI+4).",".$arrSingleGroup["group_id"]."\" value=\"1\" ".(in_array($arrSingleGroup["group_id"], $arrRights["right".$intI]) ? " checked=\"checked\" " : "")." />";
                    }
                    else {
                        $arrTemplateRow["box".($intI+3)] = "<input type=\"hidden\" name=\"".($intI+4).",".$arrSingleGroup["group_id"]."\" id=\"".($intI+4).",".$arrSingleGroup["group_id"]."\" value=\"1\" />";
                    }
                }



                if(class_module_system_setting::getConfigValue("_system_changehistory_enabled_") == "true") {
                    $arrTemplateRow["box9"] = "<input title=\"".$arrTitles[9]."\" rel=\"tooltip\" type=\"checkbox\" name=\"10," . $arrSingleGroup["group_id"] . "\" id=\"10," . $arrSingleGroup["group_id"] . "\" value=\"1\" ".(in_array($arrSingleGroup["group_id"], $arrRights["changelog"]) ? " checked=\"checked\" " : "")." />";
                }


                //And Print it to template
                $arrTemplateTotal["rows"] .= $this->objTemplate->fillTemplate($arrTemplateRow, $strTemplateRowID);
            }

            //Build the inherit-box
            $strTemplateInheritID = $this->objTemplate->readTemplate("/elements.tpl", "rights_form_inherit");
            $arrTemplateInherit = array();
            $arrTemplateInherit["title"] = $this->getLang("titel_erben");
            $arrTemplateInherit["name"] = "inherit";
            if(isset($arrRights["inherit"]) && $arrRights["inherit"] == 1) {
                $arrTemplateInherit["checked"] = "checked=\"checked\"";
            }
            else {
                $arrTemplateInherit["checked"] = "";
            }

            $arrTemplateTotal["inherit"] = $this->objTemplate->fillTemplate($arrTemplateInherit, $strTemplateInheritID);

            //Creating the output, starting with the header
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "rights_form_header");
            $arrTemplate = array();
            $arrTemplate["record"] = $strTitle;
            //Backlink
            $strUrlHistory = $this->getHistory(0);
            //Buliding the right-matrix
            $arrHistory = explode("&", $strUrlHistory);
            if(isset($arrHistory[0]) && isset($arrHistory[1]))
                $arrTemplate["backlink"] = class_link::getLinkAdminManual("href=\"" . $arrHistory[0] . "&" . $arrHistory[1]."\"", $this->getLang("commons_back"));

            $arrTemplate["desc"] = $this->getLang("desc");
            $strReturn .= $this->objTemplate->fillTemplate($arrTemplate, $strTemplateID);
            //Followed by the form
            $strReturn .= $this->objToolkit->formHeader(class_link::getLinkAdminHref($this->getArrModule("modul"), "saverights"), "rightsForm", "", "KAJONA.admin.permissions.submitForm(); return false;");
            $strTemplateID = $this->objTemplate->readTemplate("/elements.tpl", "rights_form_form");
            $strReturn .= $this->objTemplate->fillTemplate($arrTemplateTotal, $strTemplateID);
            $strReturn .= $this->objToolkit->formInputHidden("systemid", $strSystemID);

            //place all inheritance-rights as hidden-fields to support the change-js script
            $strPrevId = $objTargetRecord->getPrevId();
            $arrRightsInherited = $objRights->getArrayRights($strPrevId);

            foreach($arrRightsInherited as $strRightName => $arrRightsPerAction) {
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
                    if($strRightName == "changelog") $intRightCounter = 10;

                    foreach($arrRightsPerAction as $strOneGroupId) {
                        //place hidden field
                        $strReturn .= $this->objToolkit->formInputHidden("inherit," . $intRightCounter . "," . $strOneGroupId, "1");
                    }
                }
            }

            //Close the form
            $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
            $strReturn .= $this->objToolkit->formClose();
            $strReturn .= "<script type=\"text/javascript\">
                KAJONA.admin.permissions.checkRightMatrix();
                KAJONA.admin.permissions.toggleEmtpyRows('".$this->getLang("permissions_toggle_visible")."', '".$this->getLang("permissions_toggle_hidden")."', '#rightsForm tr');
                </script>";
        }
        else {
            $strReturn .= $this->getLang("commons_error_permissions");
        }
        return $strReturn;
    }

    /**
     * Saves the rights passed by form
     *
     * @throws class_exception
     * @return string "" in case of success
     * @permissions right
     * @xml
     */
    protected function actionSaveRights() {

        class_response_object::getInstance()->setStrResponseType(class_http_responsetypes::STR_TYPE_JSON);

        $arrRequest = json_decode($this->getParam("json"));

        //Collecting & sorting the passed values
        $strSystemid = $this->getSystemid();

        $objRights = class_carrier::getInstance()->getObjRights();

        if($this->getParam("systemid") == "0") {
            $objTarget = new class_module_system_common("0");
            $objTarget->setStrSystemid("0");
            $strSystemid = "0";
        }
        else {
            $objTarget = class_objectfactory::getInstance()->getObject($this->getSystemid());
        }


        //Special case: The root-record.
        if(!$objTarget->rightRight()) {
            return $this->objToolkit->warningBox($this->getLang("commons_error_permissions"), "alert-danger");
        }

        //Inheritance?
        if($arrRequest->bitInherited) {
            $intInherit = 1;
        }
        else {
            $intInherit = 0;
        }

        //Modified RootRecord? Here Inheritance is NOT allowed!
        if($strSystemid == "0") {
            $intInherit = 0;
        }

        $strAdminsGroupId = class_module_system_setting::getConfigValue("_admins_group_id_");
        $strView = $strAdminsGroupId;
        $strEdit = $strAdminsGroupId;
        $strDelete = $strAdminsGroupId;
        $strRight = $strAdminsGroupId;
        $strRight1 = $strAdminsGroupId;
        $strRight2 = $strAdminsGroupId;
        $strRight3 = $strAdminsGroupId;
        $strRight4 = $strAdminsGroupId;
        $strRight5 = $strAdminsGroupId;
        $strChangelog = $strAdminsGroupId;


        foreach($arrRequest->arrConfigs as $strOneCfg) {
            $arrRow = explode(",", $strOneCfg);

            if($arrRow[1] == $strAdminsGroupId) {
                continue;
            }

            switch($arrRow[0]) {
                case "1":
                    $strView .= "," . $arrRow[1];
                    break;
                case "2":
                    $strEdit .= "," . $arrRow[1];
                    break;
                case "3":
                    $strDelete .= "," . $arrRow[1];
                    break;
                case "4":
                    $strRight .= "," . $arrRow[1];
                    break;
                case "5":
                    $strRight1 .= "," . $arrRow[1];
                    break;
                case "6":
                    $strRight2 .= "," . $arrRow[1];
                    break;
                case "7":
                    $strRight3 .= "," . $arrRow[1];
                    break;
                case "8":
                    $strRight4 .= "," . $arrRow[1];
                    break;
                case "9":
                    $strRight5 .= "," . $arrRow[1];
                    break;
                case "10":
                    $strChangelog .= "," . $arrRow[1];
                    break;
            }

        }

        $arrReturn = array(
            "inherit"          => $intInherit,
            "view"             => $strView,
            "edit"             => $strEdit,
            "delete"           => $strDelete,
            "right"            => $strRight,
            "right1"           => $strRight1,
            "right2"           => $strRight2,
            "right3"           => $strRight3,
            "right4"           => $strRight4,
            "right5"           => $strRight5,
            "changelog"        => $strChangelog
        );

        //Pass to right-class
        if($objRights->setRights($arrReturn, $strSystemid)) {
            $strReturn = $this->objToolkit->warningBox($this->getLang("permissions_success"), "alert-success");
        }
        else {
            $strReturn = $this->objToolkit->warningBox($this->getLang("fehler_setzen"), "alert-danger");
        }


        return json_encode(array("message" => $strReturn));
    }
}
