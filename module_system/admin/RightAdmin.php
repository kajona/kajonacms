<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                *
********************************************************************************************************/

namespace Kajona\System\Admin;

use Kajona\System\System\Carrier;
use Kajona\System\System\Exception;
use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\Link;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\SystemCommon;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;
use Kajona\System\System\UserGroup;


/**
 * This class handles the backend-part of permission-management
 *
 * @package module_system
 * @author sidler@mulchprod.de
 *
 * @module right
 * @moduleId _system_modul_id_
 */
class RightAdmin extends AdminController implements AdminInterface {

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
            $objTargetRecord = Objectfactory::getInstance()->getObject($strSystemID);
        }
        //Edit a module?
        if($this->getParam("changemodule") != "") {
            $objTargetRecord = SystemModule::getModuleByName($this->getParam("changemodule"));
            $strSystemID = $objTargetRecord->getSystemid();
        }

        if($objTargetRecord == null) {
            return $this->getLang("commons_error_permissions");
        }

        $objRights = Carrier::getInstance()->getObjRights();

        if($objTargetRecord->rightRight()) {
            //Get Rights
            $arrRights = $objRights->getArrayRights($objTargetRecord->getSystemid());
            //Get groups
            $arrGroups = UserGroup::getObjectListFiltered();

            //Determine name of the record
            if($objTargetRecord instanceof SystemModule)
                $strTitle = Carrier::getInstance()->getObjLang()->getLang("modul_titel", $objTargetRecord->getStrName()) ." (".$objTargetRecord->getStrDisplayName().")";
            elseif($objTargetRecord->getStrDisplayName() == "")
                $strTitle = $this->getLang("titel_leer");
            else
                $strTitle = $objTargetRecord->getStrDisplayName() . " ";

            //Load the rights header-row
            if($objTargetRecord->getIntModuleNr() == 0)
                $strModule = "system";
            elseif($objTargetRecord instanceof SystemModule)
                $strModule = $objTargetRecord->getStrName();
            elseif(defined("_pages_folder_id_") && $objTargetRecord->getIntModuleNr() == _pages_folder_id_)
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
            if(SystemSetting::getConfigValue("_system_changehistory_enabled_") == "true") {
                if(!isset($arrTitles[9]))  //fallback for pre 4.3.2 systems
                    $arrTitles[9] = $arrDefaultHeader[9];

                $arrTemplateTotal["title9"] = $arrTitles[9];
            }

            //Read the template
            $arrTemplateTotal["rows"] = "";
            //Inserting Rows
            foreach($arrGroups as $objSingleGroup) {
                $arrTemplateRow = array();
                $arrSingleGroup = array();
                $arrTemplateRow["group"] = $objSingleGroup->getStrName();
                $arrSingleGroup["group_id"] = $objSingleGroup->getSystemid();

                //hide the superglobal admin-row from non-members
                if($objSingleGroup->getSystemid() == SystemSetting::getConfigValue("_admins_group_id_") && !in_array(SystemSetting::getConfigValue("_admins_group_id_"), $this->objSession->getGroupIdsAsArray())) {
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



                if(SystemSetting::getConfigValue("_system_changehistory_enabled_") == "true") {
                    $arrTemplateRow["box9"] = "<input title=\"".$arrTitles[9]."\" rel=\"tooltip\" type=\"checkbox\" name=\"10," . $arrSingleGroup["group_id"] . "\" id=\"10," . $arrSingleGroup["group_id"] . "\" value=\"1\" ".(in_array($arrSingleGroup["group_id"], $arrRights["changelog"]) ? " checked=\"checked\" " : "")." />";
                }


                //And Print it to template
                $arrTemplateTotal["rows"] .= $this->objTemplate->fillTemplateFile($arrTemplateRow, "/elements.tpl", "rights_form_row");
            }

            //Build the inherit-box
            $arrTemplateInherit = array();
            $arrTemplateInherit["title"] = $this->getLang("titel_erben");
            $arrTemplateInherit["name"] = "inherit";
            if(isset($arrRights["inherit"]) && $arrRights["inherit"] == 1) {
                $arrTemplateInherit["checked"] = "checked=\"checked\"";
            }
            else {
                $arrTemplateInherit["checked"] = "";
            }

            $arrTemplateTotal["inherit"] = $this->objTemplate->fillTemplateFile($arrTemplateInherit, "/elements.tpl", "rights_form_inherit");

            //Creating the output, starting with the header
            $arrTemplate = array();
            $arrTemplate["record"] = $strTitle;
            //Backlink
            $strUrlHistory = $this->getHistory(0);
            //Buliding the right-matrix
            $arrHistory = explode("&", $strUrlHistory);
            if(isset($arrHistory[0]) && isset($arrHistory[1]))
                $arrTemplate["backlink"] = Link::getLinkAdminManual("href=\"" . $arrHistory[0] . "&" . $arrHistory[1]."\"", $this->getLang("commons_back"));

            $arrTemplate["desc"] = $this->getLang("desc");
            $strReturn .= $this->objTemplate->fillTemplateFile($arrTemplate, "/elements.tpl", "rights_form_header");
            //Followed by the form
            $strReturn .= $this->objToolkit->formHeader(Link::getLinkAdminHref($this->getArrModule("modul"), "saverights"), "rightsForm", "", "require('permissions').submitForm(); return false;");
            $strReturn .= $this->objToolkit->formInputText("filter", $this->getLang("permissons_filter"));
            $strReturn .= $this->objTemplate->fillTemplateFile($arrTemplateTotal, "/elements.tpl", "rights_form_form");
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
                require(['jquery', 'permissions'], function($, permissions){
                    permissions.checkRightMatrix();
                    permissions.toggleEmtpyRows('".$this->getLang("permissions_toggle_visible")."', '".$this->getLang("permissions_toggle_hidden")."', '#rightsForm tr');
                    $('#filter').bind('input propertychange', permissions.filterMatrix);

                    $(document).ready(function() {
                        $(window).keydown(function(event){
                            if (event.keyCode == 13) {
                                event.preventDefault();
                                return false;
                            }
                        });
                    });
                });
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
     * @throws Exception
     * @return string "" in case of success
     * @permissions right
     */
    protected function actionSaveRights() {

        ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_JSON);

        $arrRequest = json_decode($this->getParam("json"));

        //Collecting & sorting the passed values
        $strSystemid = $this->getSystemid();

        $objRights = Carrier::getInstance()->getObjRights();

        if($this->getParam("systemid") == "0") {
            $objTarget = new SystemCommon("0");
            $objTarget->setStrSystemid("0");
            $strSystemid = "0";
        }
        else {
            $objTarget = Objectfactory::getInstance()->getObject($this->getSystemid());
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

        $strAdminsGroupId = SystemSetting::getConfigValue("_admins_group_id_");
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
