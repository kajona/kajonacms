<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/


/**
 * Class to handle infos about the system and to set systemwide properties
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_module_system_admin extends class_admin implements interface_admin {

    private $strUpdateServer = "updatecheck.kajona.de";
    private $strUpdateUrl = "/updates.php";

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $this->setArrModuleEntry("modul", "system");
        $this->setArrModuleEntry("moduleId", _system_modul_id_);
		parent::__construct();


        if($this->getAction() == "list")
            $this->setAction("moduleList");
	}


	protected function getOutputModuleNavi() {
	    $arrReturn = array();
	    $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getText("commons_module_permissions"), "", "", true, "adminnavi"));
	    $arrReturn[] = array("right", getLinkAdmin("right", "change", "&systemid=0",  $this->getText("modul_rechte_root"), "", "", true, "adminnavi"));
		$arrReturn[] = array("", "");
  	    $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "moduleList", "", $this->getText("module_liste"), "", "", true, "adminnavi"));
		$arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "systemInfo", "", $this->getText("system_info"), "", "", true, "adminnavi"));
	    $arrReturn[] = array("right1", getLinkAdmin($this->arrModule["modul"], "systemSettings", "", $this->getText("system_settings"), "", "", true, "adminnavi"));
		$arrReturn[] = array("right2", getLinkAdmin($this->arrModule["modul"], "systemTasks", "", $this->getText("systemTasks"), "", "", true, "adminnavi"));
	    $arrReturn[] = array("right3", getLinkAdmin($this->arrModule["modul"], "systemlog", "", $this->getText("systemlog"), "", "", true, "adminnavi"));
        if(_system_changehistory_enabled_ != "false")
            $arrReturn[] = array("right3", getLinkAdmin($this->arrModule["modul"], "genericChangelog", "", $this->getText("changelog"), "", "", true, "adminnavi"));
		$arrReturn[] = array("right5", getLinkAdmin($this->arrModule["modul"], "aspects", "", $this->getText("aspects"), "", "", true, "adminnavi"));
	    $arrReturn[] = array("right1", getLinkAdmin($this->arrModule["modul"], "systemSessions", "", $this->getText("system_sessions"), "", "", true, "adminnavi"));
		$arrReturn[] = array("right4", getLinkAdmin($this->arrModule["modul"], "updateCheck", "", $this->getText("updatecheck"), "", "", true, "adminnavi"));
		$arrReturn[] = array("", "");
		$arrReturn[] = array("", getLinkAdmin($this->arrModule["modul"], "about", "", $this->getText("about"), "", "", true, "adminnavi"));
		return $arrReturn;
	}


    public function getRequiredFields() {
        $strAction = $this->getAction();
        if($strAction == "sendMail") {
            return array(
              "mail_recipient" => "string",
              "mail_subject" => "string",
              "mail_body" => "string"
            );
        }
    }

    // -- Module --------------------------------------------------------------------------------------------

    /**
     * Sorts a module upwards.
     */
    protected function actionModuleSortUp() {
        $this->setPositionAndReload($this->getSystemid(), "upwards");
    }

    /**
     * Sorts a module downwards.
     */
    protected function actionModuleSortDown() {
        $this->setPositionAndReload($this->getSystemid(), "downwards");
    }

    /**
     * Sets the status of a module.
     * Therefore you have to be member of the admin-group.
     */
    protected function actionModuleStatus() {
        //status: for setting the status of modules, you have to be member of the admin-group
        $objUser = new class_module_user_user($this->objSession->getUserID());
        $arrGroups = $objUser->getObjSourceUser()->getGroupIdsForUser();
        $objCommon = new class_module_system_common($this->getSystemid());
        if($objCommon->rightEdit() &&  in_array(_admins_group_id_, $arrGroups)) {
            $this->setStatus();
            $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
        }
    }

	/**
	 * Creates a list of all installed modules
	 *
	 * @return string
     * @permissions view
     * @autoTestable
	 */
	protected function actionModuleList() {
		$strReturn = "";
		$strListId = generateSystemid();

        //Loading the modules
        $arrModules = class_module_system_module::getAllModules();
        $intI = 0;
        $strReturn .= $this->objToolkit->dragableListHeader($strListId);
        foreach($arrModules as $objSingleModule) {
            $strActions = "";
            $strCenter = "V ".$objSingleModule->getStrVersion()." &nbsp;(".timeToString($objSingleModule->getIntDate(), true).")";

            $objAdminInstance = $objSingleModule->getAdminInstanceOfConcreteModule();
            if($objAdminInstance != null)
                $strDescription = $objAdminInstance->getModuleDescription();
            else
                $strDescription = "";
            $strDescription .= ($strDescription != "" ? "<br />" : "");
            $strDescription .= $objSingleModule->getStrName()." <br /> ".$objSingleModule->getStrVersion();

            if($objSingleModule->getSystemid() != "") {
                if($objSingleModule->rightRight5())
                    $strActions .= $this->objToolkit->listButton(getLinkAdmin("system", "moduleAspect", "&systemid=".$objSingleModule->getSystemid(), "", $this->getText("modul_aspectedit"), "icon_aspect.gif"));

                //status: for setting the status of modules, you have to be member of the admin-group
                $objUser = new class_module_user_user($this->objSession->getUserID());
                $arrGroups = array();
                if($objUser->getObjSourceUser() != null )
                    $arrGroups = $objUser->getObjSourceUser()->getGroupIdsForUser();

                if($objSingleModule->rightEdit() && in_array(_admins_group_id_, $arrGroups)) {
                    if($objSingleModule->getStrName() == "system")
                        $strActions .= $this->objToolkit->listButton(getLinkAdmin("system", "moduleList", "", "", $this->getText("modul_status_system"), "icon_enabled.gif"));
                    else if($objSingleModule->getStatus() == 0)
                        $strActions .= $this->objToolkit->listButton(getLinkAdmin("system", "moduleStatus", "&systemid=".$objSingleModule->getSystemid(), "", $this->getText("modul_status_disabled"), "icon_disabled.gif"));
                    else
                        $strActions .= $this->objToolkit->listButton(getLinkAdmin("system", "moduleStatus", "&systemid=".$objSingleModule->getSystemid(), "", $this->getText("modul_status_enabled"), "icon_enabled.gif"));
                }
                //rights
                if($objSingleModule->rightRight())
                    $strActions .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&changemodule=".$objSingleModule->getStrName(), "", $this->getText("commons_module_permissions"), getRightsImageAdminName($objSingleModule->getSystemid())));
            }
            $strReturn .= $this->objToolkit->listRow3($objSingleModule->getStrName(), $strCenter, $strActions, getImageAdmin("icon_module.gif", $strDescription), $intI++, $objSingleModule->getSystemid());
        }
        $strReturn .= $this->objToolkit->dragableListFooter($strListId);

		return $strReturn;
	}

    /**
     * Creates the form to manipulate the aspects of a single module
     * @return string
     * @permissions right5
     */
    protected function actionModuleAspect() {
        $strReturn = "";
        $objModule = new class_module_system_module($this->getSystemid());
        $strReturn .= $this->objToolkit->formHeadline($objModule->getStrName());
        $arrAspectsSet = explode(",", $objModule->getStrAspect());
        $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveModuleAspect"));
        $arrAspects = class_module_system_aspect::getAllAspects();
        foreach($arrAspects as $objOneAspect)
            $strReturn .= $this->objToolkit->formInputCheckbox("aspect_".$objOneAspect->getSystemid(), $objOneAspect->getStrName(), in_array($objOneAspect->getSystemid(), $arrAspectsSet));

        $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
        $strReturn .= $this->objToolkit->formInputSubmit($this->getText("commons_save"));
        $strReturn .= $this->objToolkit->formClose();

        return $strReturn;
    }

    /**
     * @return string
     * @permissions right5
     */
    protected function actionSaveModuleAspect() {
        $arrParams = array();
        foreach($this->getAllParams() as $strName => $intValue)
            if(uniStrpos($strName, "aspect_") !== false)
                $arrParams[] = uniSubstr($strName, 7);

        $objModule = new class_module_system_module($this->getSystemid());
        $objModule->setStrAspect(implode(",", $arrParams));

        $objModule->updateObjectToDb();
        $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "moduleList"));
    }



	/**
	 * Shows information about the current system
	 *
	 * @return string
     * @permissions edit
	 */
	protected function actionSystemInfo() {
		$strReturn = "";
        $objCommon = new class_module_system_common();

        //Phpinfo
        $arrPHP = $objCommon->getPHPInfo();
        $intI = 0;
        $strPHP = $this->objToolkit->listHeader();
        foreach($arrPHP as $strKey => $strValue) {
            $strPHP .= $this->objToolkit->listRow2($this->getText($strKey), $strValue, $intI++, "_b");
        }
        $strPHP .= $this->objToolkit->listFooter();
        //And put it into a fieldset
        $strPHP = $this->objToolkit->getFieldset($this->getText("php"), $strPHP);

        //Webserver
        $arrWebserver = $objCommon->getWebserverInfos();
        $intI = 0;
        $strServer = $this->objToolkit->listHeader();
        foreach($arrWebserver as $strKey => $strValue) {
            $strServer .= $this->objToolkit->listRow2($this->getText($strKey), $strValue, $intI++, "_b");
        }
        $strServer .= $this->objToolkit->listFooter();
        //And put it into a fieldset
        $strServer = $this->objToolkit->getFieldset($this->getText("server"), $strServer);

        //database
        $arrDatabase = $objCommon->getDatabaseInfos();
        $intI = 0;
        $strDB = $this->objToolkit->listHeader();
        foreach($arrDatabase as $strKey => $strValue) {
            $strDB .= $this->objToolkit->listRow2($this->getText($strKey), $strValue, $intI++, "_b");
        }
        $strDB .= $this->objToolkit->listFooter();
        //And put it into a fieldset
        $strDB = $this->objToolkit->getFieldset($this->getText("db"), $strDB);

        //GD-Lib info
        $arrGd = $objCommon->getGDInfos();
        $intI = 0;
        $strGD = $this->objToolkit->listHeader();
        foreach($arrGd as $strKey => $strValue) {
            $strGD .= $this->objToolkit->listRow2($this->getText($strKey), $strValue, $intI++, "_b");
        }
        $strGD .= $this->objToolkit->listFooter();
        //And put it into a fieldset
        $strGD = $this->objToolkit->getFieldset($this->getText("gd"), $strGD);

        $strReturn .= $strPHP.$strServer.$strDB.$strGD;
		return $strReturn;
	}

    // -- SystemSettings ------------------------------------------------------------------------------------

    /**
     * Creates a form to edit systemsettings or updates them
     *
     * @return string "" in case of success
     * @autoTestable
     * @permissions right1
     */
    protected function actionSystemSettings() {
        $strReturn = "";
        //Check for needed rights
        if($this->getParam("save") != "true") {
            //Create a warning before doing s.th.
            $strReturn .= $this->objToolkit->warningBox($this->getText("warnung_settings"));

            $arrSettings = class_module_system_setting::getAllConfigValues();
            $objCurrentModule = null;
            $strRows = "";
            foreach ($arrSettings as $objOneSetting) {
                if($objCurrentModule ===  null || $objCurrentModule->getIntNr() != $objOneSetting->getIntModule()) {
                    $objTemp = $this->getModuleDataID($objOneSetting->getIntModule(), true);
                    if($objTemp !== null) {
                        //In the first loop, ignore the output
                        if($objCurrentModule !== null) {
                            //Build a form to return
                            $strFieldset = $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "systemSettings"));
                            $strFieldset .= $strRows;
                            $strFieldset .= $this->objToolkit->formInputHidden("save", "true");
                            $strFieldset .= $this->objToolkit->formInputSubmit($this->getText("commons_save"));
                            $strFieldset .= $this->objToolkit->formClose();
                            $strReturn .= $this->objToolkit->getFieldset($this->getText("modul_titel", $objCurrentModule->getStrName()), $strFieldset);
                        }
                        $strRows = "";
                        $objCurrentModule = $objTemp;
                    }
                }
                //Build the rows
                //Print a help-text?
                $strHelper = $this->getText($objOneSetting->getStrName()."hint", $objCurrentModule->getStrName());
                if($strHelper != "!".$objOneSetting->getStrName()."hint!")
                    $strRows .= $this->objToolkit->formTextRow($strHelper);

                //The input element itself
                if($objOneSetting->getIntType() ==  0) {
                    $arrDD = array();
                    $arrDD["true"] = $this->getText("commons_yes");
                    $arrDD["false"] = $this->getText("commons_no");
                    $strRows .= $this->objToolkit->formInputDropdown("set[".$objOneSetting->getSystemid()."]", $arrDD, $this->getText($objOneSetting->getStrName(), $objCurrentModule->getStrName()), $objOneSetting->getStrValue());
                }
                elseif ($objOneSetting->getIntType() == 3) {
                    $strRows .= $this->objToolkit->formInputPageSelector("set[".$objOneSetting->getSystemid()."]", $this->getText($objOneSetting->getStrName(), $objCurrentModule->getStrName()), $objOneSetting->getStrValue());
                }
                else {
                    $strRows .= $this->objToolkit->formInputText("set[".$objOneSetting->getSystemid()."]", $this->getText($objOneSetting->getStrName(), $objCurrentModule->getStrName()), $objOneSetting->getStrValue());
                }
            }
            //Build a form to return -> include the last module
            $strFieldset = $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "systemSettings"));
            $strFieldset .= $strRows;
            $strFieldset .= $this->objToolkit->formInputHidden("save", "true");
            $strFieldset .= $this->objToolkit->formInputSubmit($this->getText("commons_save"));
            $strFieldset .= $this->objToolkit->formClose();

            $strReturn .= $this->objToolkit->getFieldset($this->getText("modul_titel", $objCurrentModule->getStrName()), $strFieldset);
            $strRows = "";
        }
        else {
            //Seems we have to update a few records
            $arrSettings = $this->getAllParams();
            foreach($arrSettings["set"] as $strKey => $strValue) {
                $objSetting = new class_module_system_setting($strKey);
                $objSetting->setStrValue($strValue);
                $objSetting->updateObjectToDb();
            }
            $strReturn .= $this->objToolkit->warningBox($this->getText("settings_updated"));
        }

		return $strReturn;
    }



    /**
     * Loads the list of all systemtasks available and creates the form required to trigger a task
     * @return string
     * @autoTestable
     * @permissions right2
     */
    protected function actionSystemTasks() {
        $strReturn = "";
        $strTaskOutput = "";

        //include the list of possible tasks
        $arrFiles = class_resourceloader::getInstance()->getFolderContent(_adminpath_."/systemtasks/", array(".php"));
        asort($arrFiles);

        //react on special task-commands?
        if($this->getParam("task") != "") {
            //search for the matching task
            foreach ($arrFiles as $strPath => $strOneFile) {
                if($strOneFile != "class_systemtask_base.php" && $strOneFile != "interface_admin_systemtask.php" ) {

                    //instantiate the current task
                    $strClassname = uniStrReplace(".php", "", $strOneFile);
                    $objTask = new $strClassname();
                    if($objTask instanceof interface_admin_systemtask && $objTask->getStrInternalTaskname() == $this->getParam("task")) {

                        //execute the task or show the form?
                        if($this->getParam("execute") == "true") {
                            $strTaskOutput = "
                                <script type=\"text/javascript\">
                                   KAJONA.admin.loader.loadDialogBase( function() {
                                   KAJONA.admin.loader.loadAjaxBase( function() {
                                       KAJONA.admin.systemtask.executeTask('".$objTask->getStrInternalTaskname()."', '".$objTask->getSubmitParams()."');
                                       KAJONA.admin.systemtask.setName('".$this->getText("systemtask_runningtask")." ".$objTask->getStrTaskName()."');
                                    })
                                 })   ;
                                </script>";
                        }
                        else {
                            $strForm = $objTask->generateAdminForm();
                            if($strForm != "") {
                               $strTaskOutput .= $strForm;
                            }
                        }

                        break;
                    }
                }
            }
        }

        $intI = 0;
        //loop over the found files and group them
        $arrTaskGroups = array();
        foreach ($arrFiles as $strOneFile) {
            if($strOneFile != "class_systemtask_base.php" && $strOneFile != "interface_admin_systemtask.php" ) {

                //instantiate the current task
                $strClassname = uniStrReplace(".php", "", $strOneFile);
                $objTask = new $strClassname();
                if(!isset($arrTaskGroups[$objTask->getGroupIdentifier()]))
                    $arrTaskGroups[$objTask->getGroupIdentifier()] = array();

                $arrTaskGroups[$objTask->getGroupIdentifier()][] = $objTask;
            }
        }

        foreach($arrTaskGroups as $strGroupName => $arrTasks) {
            if($strGroupName == "")
                $strGroupName = "default";


            $strReturn .= $this->objToolkit->formHeadline($this->getText("systemtask_group_".$strGroupName));
            $strReturn .= $this->objToolkit->listHeader();
            foreach($arrTasks as $objOneTask) {

                //generate the link to execute the task
                $strLink = "";
                if($objOneTask->generateAdminForm() != "") {
                    $strLink = getLinkAdmin("system", "systemTasks", "&task=".$objOneTask->getStrInternalTaskName(),
                                                                                      $objOneTask->getStrTaskname(),
                                                                                      $this->getText("systemtask_run"),
                                                                                      "icon_accept.gif");
                }
                else {
                    $strLink = getLinkAdminManual("href=\"#\" onclick=\"KAJONA.admin.systemtask.executeTask('".$objOneTask->getStrInternalTaskName()."', ''); KAJONA.admin.systemtask.setName('".$this->getText("systemtask_runningtask")." ".$objOneTask->getStrTaskName()."');return false;\"",
                                                                                      "",
                                                                                      $this->getText("systemtask_run"),
                                                                                      "icon_accept.gif");
                }

                $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_dot.gif"),
                                                                   $objOneTask->getStrTaskname(),
                                                                   $this->objToolkit->listButton($strLink),
                                                                   $intI++);
            }
            $strReturn .= $this->objToolkit->listFooter();
        }

        $strReturn .= $this->objToolkit->jsDialog(0);

        //include js-code & stuff to handle executions
        $strDialogContent = "<div id=\"systemtaskLoadingDiv\" class=\"loadingContainer\"></div><br /><b id=\"systemtaskNameDiv\"></b><br /><br /><div id=\"systemtaskStatusDiv\"></div><br /><input id=\"systemtaskCancelButton\" type=\"submit\" value=\"".$this->getText("systemtask_cancel_execution")."\" class=\"inputSubmit\" /><br />";
        $strReturn .= "<script type=\"text/javascript\">
            var KAJONA_SYSTEMTASK_TITLE = '".$this->getText("systemtask_dialog_title")."';
            var KAJONA_SYSTEMTASK_TITLE_DONE = '".$this->getText("systemtask_dialog_title_done")."';
            var KAJONA_SYSTEMTASK_CLOSE = '".$this->getText("systemtask_close_dialog")."';
            var kajonaSystemtaskDialogContent = '".$strDialogContent."';
            </script>";
        $strReturn = $strTaskOutput.$this->objToolkit->divider().$strReturn;

        return $strReturn;
    }



    /**
     * Creates a table filled with the sessions currently registered
     *
     * @autoTestable
     * @return string
     * @permissions right1
     */
    protected function actionSystemSessions() {
        $strReturn = "";
        //react on commands?
        if($this->getParam("logout") == "true") {
            $objSession = new class_module_system_session($this->getSystemid());
            $objSession->setStrLoginstatus(class_module_system_session::$LOGINSTATUS_LOGGEDOUT);
            $objSession->updateObjectToDb();
            $this->objDB->flushQueryCache();
        }

        //showing a list using the pageview
        $objArraySectionIterator = new class_array_section_iterator(class_module_system_session::getNumberOfActiveSessions());
        $objArraySectionIterator->setIntElementsPerPage(_admin_nr_of_rows_);
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(class_module_system_session::getAllActiveSessions($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        $arrPageViews = $this->objToolkit->getSimplePageview($objArraySectionIterator, "system", "systemSessions");
        $arrSessions = $arrPageViews["elements"];


        //$arrSessions = class_module_system_session::getAllActiveSessions();
        $arrData = array();
        $arrHeader = array();
        $arrHeader[0] = "";
        $arrHeader[1] = $this->getText("session_username");
        $arrHeader[2] = $this->getText("session_valid");
        $arrHeader[3] = $this->getText("session_status");
        $arrHeader[4] = $this->getText("session_activity");
        $arrHeader[5] = "";
        foreach ($arrSessions as $objOneSession) {
            $arrRowData = array();
            $strUsername = "";
            if($objOneSession->getStrUserid() != "") {
                $objUser = new class_module_user_user($objOneSession->getStrUserid());
                $strUsername = $objUser->getStrUsername();
            }
            $arrRowData[0] = getImageAdmin("icon_user.gif");
            $arrRowData[1] = $strUsername;
            $arrRowData[2] = timeToString($objOneSession->getIntReleasetime());
            if($objOneSession->getStrLoginstatus() == class_module_system_session::$LOGINSTATUS_LOGGEDIN)
                $arrRowData[3] = $this->getText("session_loggedin");
            else
                $arrRowData[3] = $this->getText("session_loggedout");

            //find out what the user is doing...
            $strLastUrl = $objOneSession->getStrLasturl();
            if(uniStrpos($strLastUrl, "?") !== false)
                $strLastUrl = uniSubstr($strLastUrl, uniStrpos($strLastUrl, "?"));
            $strActivity = "";

            if(uniStrpos($strLastUrl, "admin=1") !== false) {
                $strActivity .= $this->getText("session_admin");
                foreach (explode("&amp;", $strLastUrl) as $strOneParam) {
                    $arrUrlParam = explode("=", $strOneParam);
                    if($arrUrlParam[0] == "module")
                        $strActivity .= $arrUrlParam[1];
                }
            }
            else {
                $strActivity .= $this->getText("session_portal");
                if($strLastUrl == "")
                    $strActivity .= _pages_indexpage_;
                else {
                    foreach (explode("&amp;", $strLastUrl) as $strOneParam) {
                        $arrUrlParam = explode("=", $strOneParam);
                        if($arrUrlParam[0] == "page")
                            $strActivity .= $arrUrlParam[1];
                    }

                    if($strActivity == $this->getText("session_portal") && uniSubstr($strLastUrl, 0, 5) == "image") {
                        $strActivity .= $this->getText("session_portal_imagegeneration");
                    }
                }
            }

            $arrRowData[4] = $strActivity;
            if($objOneSession->getStrLoginstatus() == class_module_system_session::$LOGINSTATUS_LOGGEDIN)
                $arrRowData[5] = getLinkAdmin("system", "systemSessions", "&logout=true&systemid=".$objOneSession->getSystemid(), "", $this->getText("session_logout"), "icon_ton.gif");
            else
                $arrRowData[5] = getImageAdmin("icon_tonDisabled.gif");
            $arrData[] = $arrRowData;
        }
        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrData);

        if(count($arrSessions) > 0)
            $strReturn .= $arrPageViews["pageview"];

        return $strReturn;
    }




    /**
     * Fetches the entries from the system-log an prints them as preformatted text
     *
     * @return string
     * @autoTestable
     * @permissions right3
     */
    protected function actionSystemlog() {
        $strReturn = "";
        //check needed rights
        $strLogContent = class_logger::getInstance()->getLogFileContent();
        $strPhpLogContent = class_logger::getInstance()->getPhpLogFileContent();

        if($strLogContent != "") {
            //create columns with same width
            $strLogContent = str_replace(array("INFO", "ERROR"), array("INFO   ", "ERROR  "), $strLogContent);
            $arrLogEntries = explode("\n", $strLogContent);
            //Reverse array
            $arrLogEntries = array_reverse($arrLogEntries);
            //and print the log to buffer
            $strReturn .= $this->objToolkit->getPreformatted($arrLogEntries, 25);
        }
        else
            $strReturn .= $this->getText("log_empty");

        if($strPhpLogContent != "") {
            $arrLogEntries = explode("\r", $strPhpLogContent);
            $arrLogEntries = array_reverse($arrLogEntries);
            $strReturn .= $this->objToolkit->getPreformatted($arrLogEntries, 25);
        }

    return $strReturn;
    }

    /**
     * Renders the list of changes for the passed systemrecord.
     * May be called from other modules in order to get the rendered list for a single record.
     *
     * @param string $strSystemid sytemid to filter
     * @param string $strSourceModule source-module, required for a working pageview
     * @param string $strSourceAction source-action, required for a working pageview
     * @return string
     *
     * @since 3.4.0
     * @autoTestable
     * @permissions right3
     */
    public function actionGenericChangelog($strSystemid = "", $strSourceModule = "system", $strSourceAction = "genericChangelog") {
        $strReturn = "";
        //check needed rights - done twice since public
        if(!class_carrier::getInstance()->getObjRights()->validatePermissionString("right3", $this->getObjModule()))
            return $this->getText("commons_error_permissions");

        //showing a list using the pageview
        $objArraySectionIterator = new class_array_section_iterator(class_module_system_changelog::getLogEntriesCount($strSystemid));
        $objArraySectionIterator->setIntElementsPerPage(_admin_nr_of_rows_);
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(class_module_system_changelog::getLogEntries($strSystemid, $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        $arrPageViews = $this->objToolkit->getSimplePageview($objArraySectionIterator, $strSourceModule, $strSourceAction, "&systemid=".$strSystemid);
        $arrLogs = $arrPageViews["elements"];

        $arrData = array();
        $arrHeader = array();
        $arrHeader[] = $this->getText("commons_date");
        $arrHeader[] = $this->getText("change_user");
        if($strSystemid == "")
            $arrHeader[] = $this->getText("change_module");
        if($strSystemid == "")
            $arrHeader[] = $this->getText("change_record");
        $arrHeader[] = $this->getText("change_action");
        $arrHeader[] = $this->getText("change_property");
        $arrHeader[] = $this->getText("change_oldvalue");
        $arrHeader[] = $this->getText("change_newvalue");

        foreach ($arrLogs as /** @var $objOneEntry class_changelog_container */ $objOneEntry) {
            $arrRowData = array();

            /** @var interface_versionable $objTarget */$objTarget = $objOneEntry->getObjTarget();

            $strOldValue = $objOneEntry->getStrOldValue();
            $strNewValue = $objOneEntry->getStrNewValue();

            if($objTarget != null) {
                $strOldValue = $objTarget->renderValue($objOneEntry->getStrProperty(), $strOldValue);
                $strNewValue = $objTarget->renderValue($objOneEntry->getStrProperty(), $strNewValue);
            }

            $arrRowData[] = dateToString($objOneEntry->getObjDate());
            $arrRowData[] = $this->objToolkit->getTooltipText(uniStrTrim($objOneEntry->getStrUsername(), 15), $objOneEntry->getStrUsername());
            if($strSystemid == "")
                $arrRowData[] = $objTarget != null ? $objTarget->getModuleName() : "";
            if($strSystemid == "")
                $arrRowData[] = $objTarget != null ? $this->objToolkit->getTooltipText(uniStrTrim($objTarget->getRecordName(), 20), $objTarget->getRecordName()." ".$objOneEntry->getStrSystemid()) : "";
            $arrRowData[] = $objTarget != null ? $this->objToolkit->getTooltipText(uniStrTrim($objTarget->getActionName($objOneEntry->getStrAction()), 15), $objTarget->getActionName($objOneEntry->getStrAction())) : "";
            $arrRowData[] = $objTarget != null ? $this->objToolkit->getTooltipText(uniStrTrim($objTarget->getPropertyName($objOneEntry->getStrProperty()), 20), $objTarget->getPropertyName($objOneEntry->getStrProperty()) ) : "";
            $arrRowData[] = $this->objToolkit->getTooltipText(uniStrTrim($strOldValue, 20), $strOldValue);
            $arrRowData[] = $this->objToolkit->getTooltipText(uniStrTrim($strNewValue, 20), $strNewValue);

            $arrData[] = $arrRowData;
        }
        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrData);

        if(count($arrLogs) > 0)
            $strReturn .= $arrPageViews["pageview"];

        return $strReturn;
    }


    /**
     * Looks for possible updates of the installed modules
     *
     * @return string
     * @autoTestable
     * @permissions right4
     */
    protected function actionUpdateCheck() {
        $strReturn = "";
        $strChecksum = md5(urldecode(_webpath_)."getVersions");
        $strQueryString = $this->strUpdateUrl."?action=getVersions&domain=".urlencode(_webpath_)."&checksum=".urlencode($strChecksum);
        $strXmlVersionList = false;

        //try to load the xml-file with a list of available updates
        try {
            $objRemoteloader = new class_remoteloader();
            $objRemoteloader->setStrHost($this->strUpdateServer);
            $objRemoteloader->setStrQueryParams($strQueryString);
            $strXmlVersionList = $objRemoteloader->getRemoteContent();
        }
        catch (class_exception $objExeption) {
            $strXmlVersionList = false;
        }

        if($strXmlVersionList === false) {
            return $this->objToolkit->warningBox($this->getText("update_nofilefound"));
        }

        try {
            $objXmlParser = new class_xml_parser();
            if($objXmlParser->loadString($strXmlVersionList)) {
                $arrRemoteModules = $objXmlParser->getNodesAttributesAsArray("module");
                //Do a little clean up
                $arrCleanModules = array();
                foreach ($arrRemoteModules as $arrOneRemoteModule) {
                    $arrCleanModules[$arrOneRemoteModule[0]["value"]] = $arrOneRemoteModule[1]["value"];
                }
                //Get all installed modules
                $arrModules = class_module_system_module::getAllModules();
                $arrHeader = array();
                $arrHeader[] = $this->getText("update_module_name");
                $arrHeader[] = $this->getText("update_module_localversion");
                $arrHeader[] = $this->getText("update_module_remoteversion");
                $arrHeader[] = "";

                $arrRows = array();
                $intRowCounter = 0;
                foreach ($arrModules as $objOneModule) {
                    $arrRows[$intRowCounter] = array();
                    $arrRows[$intRowCounter][] = $objOneModule->getStrName();
                    $arrRows[$intRowCounter][] = $objOneModule->getStrVersion();
                    $arrRows[$intRowCounter][] = (key_exists($objOneModule->getStrName(), $arrCleanModules) ? $arrCleanModules[$objOneModule->getStrName()] : "n.a." );
                    if(key_exists($objOneModule->getStrName(), $arrCleanModules) && version_compare($objOneModule->getStrVersion(), $arrCleanModules[$objOneModule->getStrName()]) < 0)
                        $arrRows[$intRowCounter][] = $this->getText("update_available");
                    else
                        $arrRows[$intRowCounter][] = "";
                    $intRowCounter++;
                }

                $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrRows);
            }
            else
                $strReturn .= $this->objToolkit->warningBox($this->getText("update_invalidXML"));

        }
        catch (class_exception $objException) {
            $strReturn .= $this->objToolkit->warningBox($this->getText("update_nodom"));
        }

        return $strReturn;
    }



    /**
     * Renders the list of aspects available
     * @return string
     * @autoTestable
     * @permissions right5
     */
    protected function actionAspects() {

	    $strReturn = "";
		$intI = 0;
        $arrObjAspects = class_module_system_aspect::getAllAspects();

        foreach ($arrObjAspects as $objOneAspect) {
            //Correct Rights?
            if($objOneAspect->rightView()) {
                $strAction = "";
                if($objOneAspect->rightEdit())
                    $strAction .= $this->objToolkit->listButton(getLinkAdmin($this->arrModule["modul"], "editAspect", "&systemid=".$objOneAspect->getSystemid(), "", $this->getText("aspect_edit"), "icon_pencil.gif"));
                if($objOneAspect->rightDelete())
                    $strAction .= $this->objToolkit->listDeleteButton($objOneAspect->getStrName(), $this->getText("aspect_delete_question"), getLinkAdminHref($this->arrModule["modul"], "deleteAspect", "&systemid=".$objOneAspect->getSystemid()));
                if($objOneAspect->rightEdit())
                    $strAction .= $this->objToolkit->listStatusButton($objOneAspect->getSystemid());
                if($objOneAspect->rightRight())
                    $strAction .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&systemid=".$objOneAspect->getSystemid(), "", $this->getText("commons_edit_permissions"), getRightsImageAdminName($objOneAspect->getSystemid())));

                $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_aspect.gif"), $objOneAspect->getStrName().($objOneAspect->getBitDefault() == 1 ? " (".$this->getText("aspect_isDefault").")" : ""), $strAction, $intI++);
            }
        }
        if($this->getObjModule()->rightEdit())
            $strReturn .= $this->objToolkit->listRow2Image("", "", getLinkAdmin($this->arrModule["modul"], "newAspect", "", $this->getText("aspect_create"), $this->getText("aspect_create"), "icon_new.gif"), $intI++);

        if(uniStrlen($strReturn) != 0)
            $strReturn = $this->objToolkit->listHeader().$strReturn.$this->objToolkit->listFooter();

        if(count($arrObjAspects) == 0)
            $strReturn .= $this->getText("aspect_list_empty");

		return $strReturn;
	}

    /**
     * Delegate to actionNewAspect
     * @return string
     * @see actionNewAspect
     */
    protected function actionEditAspect() {
        return $this->actionNewAspect("edit");
    }

    /**
	 * Creates the form to edit an existing aspect or to create a new one
	 *
	 * @param string $strMode
	 * @return string
     * @permissions right5
	 */
	protected function actionNewAspect($strMode = "new") {
	    $strReturn = "";
	    $arrDefault = array(0 => $this->getText("commons_no"), 1 => $this->getText("commons_yes"));

        if($strMode == "new") {
            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveAspect"));
            $strReturn .= $this->objToolkit->formInputText("aspect_name", $this->getText("commons_name"), $this->getParam("aspect_name"));
            $strReturn .= $this->objToolkit->formInputDropdown("aspect_default", $arrDefault, $this->getText("aspect_default"), $this->getParam("aspect_default"));
            $strReturn .= $this->objToolkit->formInputHidden("mode", "new");
            $strReturn .= $this->objToolkit->formInputSubmit($this->getText("commons_save"));
            $strReturn .= $this->objToolkit->formClose();

            $strReturn .= $this->objToolkit->setBrowserFocus("aspect_name");
        }
        elseif ($strMode == "edit") {
            $objAspect = new class_module_system_aspect($this->getSystemid());
            if($objAspect->rightEdit()) {
                $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveAspect"));
                $strReturn .= $this->objToolkit->formInputText("aspect_name", $this->getText("commons_name"), $objAspect->getStrName());
                $strReturn .= $this->objToolkit->formInputDropdown("aspect_default", $arrDefault, $this->getText("aspect_default"), $objAspect->getBitDefault());
                $strReturn .= $this->objToolkit->formInputHidden("mode", "edit");
                $strReturn .= $this->objToolkit->formInputHidden("systemid", $objAspect->getSystemid());
                $strReturn .= $this->objToolkit->formInputSubmit($this->getText("commons_save"));
                $strReturn .= $this->objToolkit->formClose();

                $strReturn .= $this->objToolkit->setBrowserFocus("aspect_name");
            }
            else
			    $strReturn = $this->getText("commons_error_permissions");

        }
        return $strReturn;
	}

    /**
	 * saves the submitted form-data as a new aspect or updates an existing one
	 *
	 * @return string, "" in case of success
     * @permissions right5
	 */
	protected function actionSaveAspect() {
        $objAspect = null;

        if($this->getParam("mode") == "new")
            $objAspect = new class_module_system_aspect();
        else if($this->getParam("mode") == "edit")
            $objAspect = new class_module_system_aspect($this->getSystemid());

        if($objAspect != null) {
            //reset the default aspect?
            if($this->getParam("aspect_default") == "1")
                class_module_system_aspect::resetDefaultAspect();

            $objAspect->setStrName($this->getParam("aspect_name"));
            $objAspect->setBitDefault($this->getParam("aspect_default"));
            if(!$objAspect->updateObjectToDb() )
                throw new class_exception("Error creating new aspect", class_exception::$level_ERROR);
        }
        $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "aspects"));
	}

	/**
	 * Deletes an aspect
	 *
	 * @return string
	 */
	protected function actionDeleteAspect() {
        $objAspect = new class_module_system_aspect($this->getSystemid());
        if($objAspect->rightDelete() && $objAspect->rightRight5()) {
            if(!$objAspect->deleteObject())
                throw new class_exception("Error deleting aspect", class_exception::$level_ERROR);

            $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "aspects"));
        }
        else
		    return $this->getText("commons_error_permissions");
	}


    /**
     * About Kajona, credits and co
     *
     * @return string
     * @autoTestable
     * @permissions view
     */
    protected function actionAbout() {
        $strReturn = "";
        $strReturn .= $this->objToolkit->getTextRow($this->getText("about"));
        $strReturn .= $this->objToolkit->getTextRow($this->getText("about_part1"));
        $strReturn .= $this->objToolkit->getTextRow($this->getText("about_part2"));
        $strReturn .= $this->objToolkit->getTextRow($this->getText("about_part3"));
        $strReturn .= $this->objToolkit->getTextRow($this->getText("about_part4"));
        return $strReturn;
    }


    /**
     * Generates a form in order to send an email.
     * This form is generic, so it may be called from several places.
     *
     * @return string
     * @since 3.4
     * @autoTestable
     * @permissions view
     */
    protected function actionMailForm() {
        $strReturn = "";
        $this->setArrModuleEntry("template", "/folderview.tpl");

        $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "sendMail"));
        $strReturn .= $this->objToolkit->getValidationErrors($this, "sendMail");
        $strReturn .= $this->objToolkit->formInputText("mail_recipient", $this->getText("mail_recipient"), $this->getParam("mail_recipient"));
        $strReturn .= $this->objToolkit->formInputText("mail_cc", $this->getText("mail_cc"), $this->getParam("mail_cc"));
        $strReturn .= $this->objToolkit->formInputText("mail_subject", $this->getText("mail_subject"), $this->getParam("mail_subject"));
        $strReturn .= $this->objToolkit->formInputTextArea("mail_body", $this->getText("mail_body"), $this->getParam("mail_body"), "inputTextareaLarge");
        $strReturn .= $this->objToolkit->formInputSubmit($this->getText("send"));
        $strReturn .= $this->objToolkit->formClose();

        $strReturn .= $this->objToolkit->setBrowserFocus("mail_body");

        return $strReturn;
    }

    /**
     * Sends an email. In most cases this mail was generated using the form
     * provided by actionMailForm
     *
     * @return string
     * @since 3.4
     * @permissions view
     */
    protected function actionSendMail() {
        if(!$this->validateForm())
            return $this->actionMailForm();

        $this->setArrModuleEntry("template", "/folderview.tpl");
        $objUser = new class_module_user_user($this->objSession->getUserID());

        $objEmail = new class_mail();

        $objEmail->setSender($objUser->getStrEmail());

        $arrRecipients = explode(",", $this->getParam("mail_recipient"));
        foreach($arrRecipients as $strOneRecipient)
            if(checkEmailaddress($strOneRecipient))
                $objEmail->addTo($strOneRecipient);

        $arrRecipients = explode(",", $this->getParam("mail_cc"));
        foreach($arrRecipients as $strOneRecipient)
            if(checkEmailaddress($strOneRecipient))
                $objEmail->addCc($strOneRecipient);


        $objEmail->setSubject($this->getParam("mail_subject"));
        $objEmail->setText($this->getParam("mail_body"));

        if($objEmail->sendMail())
            return $this->getText("mail_send_success");
        else
            return $this->getText("mail_send_success");
    }



    /**
     * Loads the data for one module
     *
     * @param int $intModuleID
     * @param bool $bitZeroIsSystem
     * @return mixed
     */
	private function getModuleDataID($intModuleID, $bitZeroIsSystem = false) {
		$arrModules = class_module_system_module::getAllModules();

		if($intModuleID != 0 || !$bitZeroIsSystem) {
    		foreach($arrModules as $objOneModule) {
    		    if($objOneModule->getIntNr() == $intModuleID)
                    return $objOneModule;
    		}
		}
		elseif ($intModuleID == 0 && $bitZeroIsSystem) {
            foreach($arrModules as $objOneModule) {
    		    if($objOneModule->getStrName() == "system")
                    return $objOneModule;
    		}
		}
        return null;
	}

}

