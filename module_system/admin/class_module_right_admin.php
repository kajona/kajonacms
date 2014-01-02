<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
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
class class_module_right_admin extends class_admin implements interface_admin {

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
     */
    protected function actionChange() {

        $strReturn = "";
        $strSystemID = $this->getParam("systemid");
        $objTargetRecord = null;

        //Determine the systemid
        if($this->getParam("systemid") != "") {
            $objTargetRecord = class_objectfactory::getInstance()->getObject($this->getParam("systemid"));
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
            if(_system_changehistory_enabled_ == "true")
                $arrTemplateTotal["title9"] = (isset($arrTitles[9]) ? $arrTitles[9] : $arrDefaultHeader[9]); //fallback for pre 4.3.2 systems

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
                if($objSingleGroup->getSystemid() == _admins_group_id_ && !in_array(_admins_group_id_, $this->objSession->getGroupIdsAsArray())) {
                    continue;
                }


                //Building Checkboxes
                if(in_array($arrSingleGroup["group_id"], $arrRights["view"])) {
                    $arrTemplateRow["box0"] = "<input type=\"checkbox\" name=\"1," . $arrSingleGroup["group_id"] . "\" id=\"1," . $arrSingleGroup["group_id"] . "\" value=\"1\" checked=\"checked\" />";
                }
                else {
                    $arrTemplateRow["box0"] = "<input type=\"checkbox\" name=\"1," . $arrSingleGroup["group_id"] . "\" id=\"1," . $arrSingleGroup["group_id"] . "\" value=\"1\" />";
                }

                if(in_array($arrSingleGroup["group_id"], $arrRights["edit"])) {
                    $arrTemplateRow["box1"] = "<input type=\"checkbox\" name=\"2," . $arrSingleGroup["group_id"] . "\" id=\"2," . $arrSingleGroup["group_id"] . "\" value=\"1\" checked=\"checked\" />";
                }
                else {
                    $arrTemplateRow["box1"] = "<input type=\"checkbox\" name=\"2," . $arrSingleGroup["group_id"] . "\" id=\"2," . $arrSingleGroup["group_id"] . "\" value=\"1\" />";
                }

                if(in_array($arrSingleGroup["group_id"], $arrRights["delete"])) {
                    $arrTemplateRow["box2"] = "<input type=\"checkbox\" name=\"3," . $arrSingleGroup["group_id"] . "\" id=\"3," . $arrSingleGroup["group_id"] . "\" value=\"1\" checked=\"checked\" />";
                }
                else {
                    $arrTemplateRow["box2"] = "<input type=\"checkbox\" name=\"3," . $arrSingleGroup["group_id"] . "\" id=\"3," . $arrSingleGroup["group_id"] . "\" value=\"1\" />";
                }

                if(in_array($arrSingleGroup["group_id"], $arrRights["right"])) {
                    $arrTemplateRow["box3"] = "<input type=\"checkbox\" name=\"4," . $arrSingleGroup["group_id"] . "\" id=\"4," . $arrSingleGroup["group_id"] . "\" value=\"1\" checked=\"checked\" />";
                }
                else {
                    $arrTemplateRow["box3"] = "<input type=\"checkbox\" name=\"4," . $arrSingleGroup["group_id"] . "\" id=\"4," . $arrSingleGroup["group_id"] . "\" value=\"1\" />";
                }

                if(in_array($arrSingleGroup["group_id"], $arrRights["right1"])) {
                    //field editable?
                    if($arrTemplateTotal["title4"] != "") {
                        $arrTemplateRow["box4"] = "<input type=\"checkbox\" name=\"5," . $arrSingleGroup["group_id"] . "\" id=\"5," . $arrSingleGroup["group_id"] . "\" value=\"1\" checked=\"checked\" />";
                    }
                    else {
                        $arrTemplateRow["box4"] = "<input type=\"hidden\" name=\"5," . $arrSingleGroup["group_id"] . "\" id=\"5," . $arrSingleGroup["group_id"] . "\" value=\"1\" />";
                    }
                }
                elseif($arrTemplateTotal["title4"] != "") {
                    $arrTemplateRow["box4"] = "<input type=\"checkbox\" name=\"5," . $arrSingleGroup["group_id"] . "\" id=\"5," . $arrSingleGroup["group_id"] . "\" value=\"1\" />";
                }

                if(in_array($arrSingleGroup["group_id"], $arrRights["right2"])) {
                    //field editable?
                    if($arrTemplateTotal["title5"] != "") {
                        $arrTemplateRow["box5"] = "<input type=\"checkbox\" name=\"6," . $arrSingleGroup["group_id"] . "\" id=\"6," . $arrSingleGroup["group_id"] . "\" value=\"1\" checked=\"checked\" />";
                    }
                    else {
                        $arrTemplateRow["box5"] = "<input type=\"hidden\" name=\"6," . $arrSingleGroup["group_id"] . "\" id=\"6," . $arrSingleGroup["group_id"] . "\" value=\"1\" />";
                    }

                }
                elseif($arrTemplateTotal["title5"] != "") {
                    $arrTemplateRow["box5"] = "<input type=\"checkbox\" name=\"6," . $arrSingleGroup["group_id"] . "\" id=\"6," . $arrSingleGroup["group_id"] . "\" value=\"1\" />";
                }

                if(in_array($arrSingleGroup["group_id"], $arrRights["right3"])) {
                    //field editable?
                    if($arrTemplateTotal["title6"] != "") {
                        $arrTemplateRow["box6"] = "<input type=\"checkbox\" name=\"7," . $arrSingleGroup["group_id"] . "\" id=\"7," . $arrSingleGroup["group_id"] . "\" value=\"1\" checked=\"checked\" />";
                    }
                    else {
                        $arrTemplateRow["box6"] = "<input type=\"hidden\" name=\"7," . $arrSingleGroup["group_id"] . "\" id=\"7," . $arrSingleGroup["group_id"] . "\" value=\"1\" />";
                    }
                }
                elseif($arrTemplateTotal["title6"] != "") {
                    $arrTemplateRow["box6"] = "<input type=\"checkbox\" name=\"7," . $arrSingleGroup["group_id"] . "\" id=\"7," . $arrSingleGroup["group_id"] . "\" value=\"1\" />";
                }

                if(in_array($arrSingleGroup["group_id"], $arrRights["right4"])) {
                    //field editable?
                    if($arrTemplateTotal["title7"] != "") {
                        $arrTemplateRow["box7"] = "<input type=\"checkbox\" name=\"8," . $arrSingleGroup["group_id"] . "\" id=\"8," . $arrSingleGroup["group_id"] . "\" value=\"1\" checked=\"checked\" />";
                    }
                    else {
                        $arrTemplateRow["box7"] = "<input type=\"hidden\" name=\"8," . $arrSingleGroup["group_id"] . "\" id=\"8," . $arrSingleGroup["group_id"] . "\" value=\"1\" />";
                    }
                }
                elseif($arrTemplateTotal["title7"] != "") {
                    $arrTemplateRow["box7"] = "<input type=\"checkbox\" name=\"8," . $arrSingleGroup["group_id"] . "\" id=\"8," . $arrSingleGroup["group_id"] . "\" value=\"1\" />";
                }


                if(in_array($arrSingleGroup["group_id"], $arrRights["right5"])) {
                    //field editable?
                    if($arrTemplateTotal["title8"] != "") {
                        $arrTemplateRow["box8"] = "<input type=\"checkbox\" name=\"9," . $arrSingleGroup["group_id"] . "\" id=\"9," . $arrSingleGroup["group_id"] . "\" value=\"1\" checked=\"checked\" />";
                    }
                    else {
                        $arrTemplateRow["box8"] = "<input type=\"hidden\" name=\"9," . $arrSingleGroup["group_id"] . "\" id=\"9," . $arrSingleGroup["group_id"] . "\" value=\"1\" />";
                    }
                }
                elseif($arrTemplateTotal["title8"] != "") {
                    $arrTemplateRow["box8"] = "<input type=\"checkbox\" name=\"9," . $arrSingleGroup["group_id"] . "\" id=\"9," . $arrSingleGroup["group_id"] . "\" value=\"1\" />";
                }

                if(_system_changehistory_enabled_ == "true") {
                    if(in_array($arrSingleGroup["group_id"], $arrRights["changelog"])) {
                        //field editable?
                        if($arrTemplateTotal["title9"] != "") {
                            $arrTemplateRow["box9"] = "<input type=\"checkbox\" name=\"10," . $arrSingleGroup["group_id"] . "\" id=\"10," . $arrSingleGroup["group_id"] . "\" value=\"1\" checked=\"checked\" />";
                        }
                        else {
                            $arrTemplateRow["box9"] = "<input type=\"hidden\" name=\"10," . $arrSingleGroup["group_id"] . "\" id=\"10," . $arrSingleGroup["group_id"] . "\" value=\"1\" />";
                        }
                    }
                    elseif($arrTemplateTotal["title9"] != "") {
                        $arrTemplateRow["box9"] = "<input type=\"checkbox\" name=\"10," . $arrSingleGroup["group_id"] . "\" id=\"10," . $arrSingleGroup["group_id"] . "\" value=\"1\" />";
                    }
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
            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saverights"), "rightsForm");
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
            $strReturn .= "<script type=\"text/javascript\">KAJONA.admin.checkRightMatrix();</script>";
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
     */
    protected function actionSaveRights() {
        $strReturn = "";
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
        if($objTarget->rightRight()) {
            //Inheritance?
            if($this->getParam("inherit") == 1) {
                $intInherit = 1;
            }
            else {
                $intInherit = 0;
            }

            //Modified RootRecord? Here Inheritance is NOT allowed!
            if($strSystemid == "0") {
                $intInherit = 0;
            }

            //Get Groups
            $arrGroups = class_module_user_group::getObjectList();

            $strView = _admins_group_id_;
            $strEdit = _admins_group_id_;
            $strDelete = _admins_group_id_;
            $strRight = _admins_group_id_;
            $strRight1 = _admins_group_id_;
            $strRight2 = _admins_group_id_;
            $strRight3 = _admins_group_id_;
            $strRight4 = _admins_group_id_;
            $strRight5 = _admins_group_id_;
            $strChangelog = _admins_group_id_;

            foreach($arrGroups as $objSingleGroup) {
                $strGroupId = $objSingleGroup->getSystemid();
                if($strGroupId == _admins_group_id_) {
                    continue;
                }


                if($this->getParam("1," . $strGroupId) == 1) {
                    $strView .= "," . $strGroupId;
                }
                if($this->getParam("2," . $strGroupId) == 1) {
                    $strEdit .= "," . $strGroupId;
                }
                if($this->getParam("3," . $strGroupId) == 1) {
                    $strDelete .= "," . $strGroupId;
                }
                if($this->getParam("4," . $strGroupId) == 1) {
                    $strRight .= "," . $strGroupId;
                }
                if($this->getParam("5," . $strGroupId) == 1) {
                    $strRight1 .= "," . $strGroupId;
                }
                if($this->getParam("6," . $strGroupId) == 1) {
                    $strRight2 .= "," . $strGroupId;
                }
                if($this->getParam("7," . $strGroupId) == 1) {
                    $strRight3 .= "," . $strGroupId;
                }
                if($this->getParam("8," . $strGroupId) == 1) {
                    $strRight4 .= "," . $strGroupId;
                }
                if($this->getParam("9," . $strGroupId) == 1) {
                    $strRight5 .= "," . $strGroupId;
                }
                if($this->getParam("10," . $strGroupId) == 1) {
                    $strChangelog .= "," . $strGroupId;
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

                //Redirecting
                $strUrlHistory = $this->getHistory(0);
                $arrHistory = explode("&", $strUrlHistory);
                if(isset($arrHistory[1]) && $arrHistory[1] != "module=rights") {
                    $this->adminReload(_indexpath_ . "?" . $this->getHistory(0) . ($this->getParam("pe") != "" ? "&peClose=1" : ""));
                }
                else {
                    $this->adminReload(_indexpath_ . "?" . $this->getHistory(0) . ($this->getParam("pe") != "" ? "&peClose=1" : ""));
                }

                return "";
            }
            else {
                throw new class_exception($this->getLang("fehler_setzen"), class_exception::$level_ERROR);
            }
        }
        else {
            $strReturn .= $this->getLang("commons_error_permissions");
        }
        return $strReturn;
    }
}
