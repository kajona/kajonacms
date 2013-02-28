<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
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
class class_module_system_admin extends class_admin_simple implements interface_admin {

    /**
     * Constructor

     */
    public function __construct() {
        $this->setArrModuleEntry("modul", "system");
        $this->setArrModuleEntry("moduleId", _system_modul_id_);
        parent::__construct();

    }


    public function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("action_list"), "", "", true, "adminnavi"));
        $arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "systemInfo", "", $this->getLang("action_system_info"), "", "", true, "adminnavi"));
        $arrReturn[] = array("right1", getLinkAdmin($this->arrModule["modul"], "systemSettings", "", $this->getLang("action_system_settings"), "", "", true, "adminnavi"));
        $arrReturn[] = array("right2", getLinkAdmin($this->arrModule["modul"], "systemTasks", "", $this->getLang("action_system_tasks"), "", "", true, "adminnavi"));
        $arrReturn[] = array("right3", getLinkAdmin($this->arrModule["modul"], "systemlog", "", $this->getLang("action_systemlog"), "", "", true, "adminnavi"));
        if(_system_changehistory_enabled_ != "false")
            $arrReturn[] = array("right3", getLinkAdmin($this->arrModule["modul"], "genericChangelog", "&bitBlockFolderview=true", $this->getLang("action_changelog"), "", "", true, "adminnavi"));
        $arrReturn[] = array("right5", getLinkAdmin($this->arrModule["modul"], "aspects", "", $this->getLang("action_aspects"), "", "", true, "adminnavi"));
        $arrReturn[] = array("right1", getLinkAdmin($this->arrModule["modul"], "systemSessions", "", $this->getLang("action_system_sessions"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("", getLinkAdmin($this->arrModule["modul"], "about", "", $this->getLang("action_about"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"], $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&systemid=0", $this->getLang("modul_rechte_root"), "", "", true, "adminnavi"));
        return $arrReturn;
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
        if($objCommon->rightEdit() && in_array(_admins_group_id_, $arrGroups)) {
            $this->setStatus();
            $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
        }
    }

    /**
     * Renders the form to create a new entry
     *
     * @return string
     */
    protected function actionNew() {
        // TODO: Implement actionNew() method.
    }

    /**
     * Renders the form to edit an existing entry
     *
     * @return string
     */
    protected function actionEdit() {

        $objInstance = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objInstance instanceof class_module_system_aspect)
            $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "editAspect", "&systemid=".$objInstance->getSystemid()));

        if($objInstance instanceof class_module_system_module)
            $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "list"));
    }


    /**
     * Creates a list of all installed modules
     *
     * @return string
     * @permissions view
     * @autoTestable
     */
    protected function actionList() {

        $objIterator = new class_array_section_iterator(class_module_system_module::getObjectCount());
        $objIterator->setPageNumber($this->getParam("pv"));
        $objIterator->setIntElementsPerPage(class_module_system_module::getObjectCount());
        $objIterator->setArraySection(class_module_system_module::getAllModules($objIterator->calculateStartPos(), $objIterator->calculateEndPos()));

        return $this->renderList($objIterator, true, "moduleList");

    }

    protected function renderAdditionalActions(class_model $objListEntry) {
        if($objListEntry instanceof class_module_system_module) {
            $arrReturn = array();
            $arrReturn[] = $this->objToolkit->listButton(getLinkAdmin("system", "moduleAspect", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("modul_aspectedit"), "icon_aspect.png"));

            if($objListEntry->rightEdit() && in_array(_admins_group_id_, $this->objSession->getGroupIdsAsArray())) {
                if($objListEntry->getStrName() == "system")
                    $arrReturn[] = $this->objToolkit->listButton(getLinkAdmin("system", "moduleList", "", "", $this->getLang("modul_status_system"), "icon_enabled.png"));
                else if($objListEntry->getStatus() == 0)
                    $arrReturn[] = $this->objToolkit->listButton(getLinkAdmin("system", "moduleStatus", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("modul_status_disabled"), "icon_disabled.png"));
                else
                    $arrReturn[] = $this->objToolkit->listButton(getLinkAdmin("system", "moduleStatus", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("modul_status_enabled"), "icon_enabled.png"));
            }

            return $arrReturn;
        }
        return parent::renderAdditionalActions($objListEntry);
    }

    protected function renderStatusAction(class_model $objListEntry) {
        if($objListEntry instanceof class_module_system_module)
            return "";

        return parent::renderStatusAction($objListEntry);
    }


    protected function renderEditAction(class_model $objListEntry, $bitDialog = false) {
        if($objListEntry instanceof class_module_system_module)
            return "";

        return parent::renderEditAction($objListEntry);
    }


    protected function renderDeleteAction(interface_model $objListEntry) {
        if($objListEntry instanceof class_module_system_module)
            return "";

        if($objListEntry instanceof class_module_system_aspect && $objListEntry->rightDelete())
            return $this->objToolkit->listDeleteButton($objListEntry->getStrName(), $this->getLang("aspect_delete_question"), getLinkAdminHref($this->getArrModule("modul"), "deleteAspect", "&systemid=".$objListEntry->getSystemid()));

        return parent::renderDeleteAction($objListEntry);
    }

    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {
        if($strListIdentifier == "moduleList")
            return "";

        if($strListIdentifier == "aspectList" && $this->getObjModule()->rightEdit())
            return $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "newAspect", "", $this->getLang("aspect_create"), $this->getLang("aspect_create"), "icon_new.png"));

        return parent::getNewEntryAction($strListIdentifier);
    }

    protected function renderCopyAction(class_model $objListEntry) {
        return "";
    }


    /**
     * Creates the form to manipulate the aspects of a single module
     *
     * @return string
     * @permissions right5
     */
    protected function actionModuleAspect() {
        $strReturn = "";
        $objModule = class_module_system_module::getModuleBySystemid($this->getSystemid());
        $strReturn .= $this->objToolkit->formHeadline($objModule->getStrName());
        $arrAspectsSet = explode(",", $objModule->getStrAspect());
        $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "saveModuleAspect"));
        $arrAspects = class_module_system_aspect::getObjectList();
        foreach($arrAspects as $objOneAspect)
            $strReturn .= $this->objToolkit->formInputCheckbox("aspect_".$objOneAspect->getSystemid(), $objOneAspect->getStrName(), in_array($objOneAspect->getSystemid(), $arrAspectsSet));

        $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
        $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
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

        $objModule = class_module_system_module::getModuleBySystemid($this->getSystemid());
        $objModule->setStrAspect(implode(",", $arrParams));

        $objModule->updateObjectToDb();
        $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
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
            $strPHP .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang($strKey), "", "", $intI++, $strValue);
        }
        $strPHP .= $this->objToolkit->listFooter();
        //And put it into a fieldset
        $strPHP = $this->objToolkit->getFieldset($this->getLang("php"), $strPHP);

        //Webserver
        $arrWebserver = $objCommon->getWebserverInfos();
        $intI = 0;
        $strServer = $this->objToolkit->listHeader();
        foreach($arrWebserver as $strKey => $strValue) {
            $strServer .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang($strKey), "", "", $intI++, $strValue);
        }
        $strServer .= $this->objToolkit->listFooter();
        //And put it into a fieldset
        $strServer = $this->objToolkit->getFieldset($this->getLang("server"), $strServer);

        //database
        $arrDatabase = $objCommon->getDatabaseInfos();
        $intI = 0;
        $strDB = $this->objToolkit->listHeader();
        foreach($arrDatabase as $strKey => $strValue) {
            $strDB .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang($strKey), "", "", $intI++, $strValue);
        }
        $strDB .= $this->objToolkit->listFooter();
        //And put it into a fieldset
        $strDB = $this->objToolkit->getFieldset($this->getLang("db"), $strDB);

        //GD-Lib info
        $arrGd = $objCommon->getGDInfos();
        $intI = 0;
        $strGD = $this->objToolkit->listHeader();
        foreach($arrGd as $strKey => $strValue) {
            $strGD .= $this->objToolkit->genericAdminList(generateSystemid(), $this->getLang($strKey), "", "", $intI++, $strValue);
        }
        $strGD .= $this->objToolkit->listFooter();
        //And put it into a fieldset
        $strGD = $this->objToolkit->getFieldset($this->getLang("gd"), $strGD);

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
            $strReturn .= $this->objToolkit->warningBox($this->getLang("warnung_settings"));

            $arrTabs = array();

            $arrSettings = class_module_system_setting::getAllConfigValues();
            $objCurrentModule = null;
            $strRows = "";
            foreach($arrSettings as $objOneSetting) {
                if($objCurrentModule === null || $objCurrentModule->getIntNr() != $objOneSetting->getIntModule()) {
                    $objTemp = $this->getModuleDataID($objOneSetting->getIntModule(), true);
                    if($objTemp !== null) {
                        //In the first loop, ignore the output
                        if($objCurrentModule !== null) {
                            //Build a form to return
                            $strTabContent = $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "systemSettings"));
                            $strTabContent .= $strRows;
                            $strTabContent .= $this->objToolkit->formInputHidden("save", "true");
                            $strTabContent .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
                            $strTabContent .= $this->objToolkit->formClose();
                            $arrTabs[$this->getLang("modul_titel", $objCurrentModule->getStrName())] = $strTabContent;
                        }
                        $strRows = "";
                        $objCurrentModule = $objTemp;
                    }
                }
                //Build the rows
                //Print a help-text?
                $strHelper = $this->getLang($objOneSetting->getStrName()."hint", $objCurrentModule->getStrName());
                if($strHelper != "!".$objOneSetting->getStrName()."hint!")
                    $strRows .= $this->objToolkit->formTextRow($strHelper);

                //The input element itself
                if($objOneSetting->getIntType() == 0) {
                    $arrDD = array();
                    $arrDD["true"] = $this->getLang("commons_yes");
                    $arrDD["false"] = $this->getLang("commons_no");
                    $strRows .= $this->objToolkit->formInputDropdown("set[".$objOneSetting->getSystemid()."]", $arrDD, $this->getLang($objOneSetting->getStrName(), $objCurrentModule->getStrName()), $objOneSetting->getStrValue());
                }
                elseif($objOneSetting->getIntType() == 3) {
                    $strRows .= $this->objToolkit->formInputPageSelector("set[".$objOneSetting->getSystemid()."]", $this->getLang($objOneSetting->getStrName(), $objCurrentModule->getStrName()), $objOneSetting->getStrValue());
                }
                else {
                    $strRows .= $this->objToolkit->formInputText("set[".$objOneSetting->getSystemid()."]", $this->getLang($objOneSetting->getStrName(), $objCurrentModule->getStrName()), $objOneSetting->getStrValue());
                }
            }
            //Build a form to return -> include the last module
            $strTabContent = $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "systemSettings"));
            $strTabContent .= $strRows;
            $strTabContent .= $this->objToolkit->formInputHidden("save", "true");
            $strTabContent .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
            $strTabContent .= $this->objToolkit->formClose();

            $arrTabs[$this->getLang("modul_titel", $objCurrentModule->getStrName())] = $strTabContent;

            $strReturn .= $this->objToolkit->getTabbedContent($arrTabs);
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
            $strReturn .= $this->objToolkit->warningBox($this->getLang("settings_updated"));
        }

        return $strReturn;
    }


    /**
     * Loads the list of all systemtasks available and creates the form required to trigger a task
     *
     * @return string
     * @autoTestable
     * @permissions right2
     */
    protected function actionSystemTasks() {
        $strReturn = "";
        $strTaskOutput = "";

        //include the list of possible tasks
        $arrFiles = class_resourceloader::getInstance()->getFolderContent("/admin/systemtasks/", array(".php"));
        asort($arrFiles);

        //react on special task-commands?
        if($this->getParam("task") != "") {
            //search for the matching task
            foreach($arrFiles as $strPath => $strOneFile) {
                if($strOneFile != "class_systemtask_base.php" && $strOneFile != "interface_admin_systemtask.php") {

                    //instantiate the current task
                    $strClassname = uniStrReplace(".php", "", $strOneFile);
                    /** @var $objTask interface_admin_systemtask */
                    $objTask = new $strClassname();
                    if($objTask instanceof interface_admin_systemtask && $objTask->getStrInternalTaskname() == $this->getParam("task")) {

                        //execute the task or show the form?
                        if($this->getParam("execute") == "true") {
                            $strTaskOutput = "
                                <script type=\"text/javascript\">
                                $(function() {
                                   setTimeout(function() {
                                   KAJONA.admin.systemtask.executeTask('".$objTask->getStrInternalTaskname()."', '".$objTask->getSubmitParams()."');
                                   KAJONA.admin.systemtask.setName('".$this->getLang("systemtask_runningtask")." ".$objTask->getStrTaskName()."');
                                   }, 500);
                                 });
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
        foreach($arrFiles as $strOneFile) {
            if($strOneFile != "class_systemtask_base.php" && $strOneFile != "interface_admin_systemtask.php") {

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


            $strReturn .= $this->objToolkit->formHeadline($this->getLang("systemtask_group_".$strGroupName));
            $strReturn .= $this->objToolkit->listHeader();
            /** @var $objOneTask interface_admin_systemtask */
            foreach($arrTasks as $objOneTask) {

                //generate the link to execute the task
                $strLink = "";
                if($objOneTask->generateAdminForm() != "") {
                    $strLink = getLinkAdmin(
                        "system",
                        "systemTasks",
                        "&task=".$objOneTask->getStrInternalTaskName(),
                        $objOneTask->getStrTaskname(),
                        $this->getLang("systemtask_run"),
                        "icon_accept.png"
                    );
                }
                else {
                    $strLink = getLinkAdminManual(
                        "href=\"#\" onclick=\"KAJONA.admin.systemtask.executeTask('".$objOneTask->getStrInternalTaskName()."', ''); KAJONA.admin.systemtask.setName('".$this->getLang("systemtask_runningtask")." ".$objOneTask->getStrTaskName()."');return false;\"",
                        "",
                        $this->getLang("systemtask_run"),
                        "icon_accept.png"
                    );
                }

                $strReturn .= $this->objToolkit->genericAdminList(
                    generateSystemid(),
                    $objOneTask->getStrTaskname(),
                    getImageAdmin("icon_systemtask.png"),
                    $this->objToolkit->listButton($strLink),
                    $intI++
                );
            }
            $strReturn .= $this->objToolkit->listFooter();
        }

        $strReturn .= $this->objToolkit->jsDialog(0);

        //include js-code & stuff to handle executions
        $strDialogContent = "<div id=\"systemtaskLoadingDiv\" class=\"loadingContainer\"></div><br /><b id=\"systemtaskNameDiv\"></b><br /><br /><div id=\"systemtaskStatusDiv\"></div><br /><input id=\"systemtaskCancelButton\" type=\"submit\" value=\"".$this->getLang("systemtask_cancel_execution")."\" class=\"btn inputSubmit\" /><br />";
        $strReturn .= "<script type=\"text/javascript\">
            var KAJONA_SYSTEMTASK_TITLE = '".$this->getLang("systemtask_dialog_title")."';
            var KAJONA_SYSTEMTASK_TITLE_DONE = '".$this->getLang("systemtask_dialog_title_done")."';
            var KAJONA_SYSTEMTASK_CLOSE = '".$this->getLang("systemtask_close_dialog")."';
            var kajonaSystemtaskDialogContent = '".$strDialogContent."';
            </script>";
        $strReturn = $strTaskOutput.$strReturn;

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
        $arrHeader[1] = $this->getLang("session_username");
        $arrHeader[2] = $this->getLang("session_valid");
        $arrHeader[3] = $this->getLang("session_status");
        $arrHeader[4] = $this->getLang("session_activity");
        $arrHeader[5] = "";
        /** @var $objOneSession class_module_system_session */
        foreach($arrSessions as $objOneSession) {
            $arrRowData = array();
            $strUsername = "";
            if($objOneSession->getStrUserid() != "") {
                $objUser = new class_module_user_user($objOneSession->getStrUserid());
                $strUsername = $objUser->getStrUsername();
            }
            $arrRowData[0] = getImageAdmin("icon_user.png");
            $arrRowData[1] = $strUsername;
            $arrRowData[2] = timeToString($objOneSession->getIntReleasetime());
            if($objOneSession->getStrLoginstatus() == class_module_system_session::$LOGINSTATUS_LOGGEDIN)
                $arrRowData[3] = $this->getLang("session_loggedin");
            else
                $arrRowData[3] = $this->getLang("session_loggedout");

            //find out what the user is doing...
            $strLastUrl = $objOneSession->getStrLasturl();
            if(uniStrpos($strLastUrl, "?") !== false)
                $strLastUrl = uniSubstr($strLastUrl, uniStrpos($strLastUrl, "?"));
            $strActivity = "";

            if(uniStrpos($strLastUrl, "admin=1") !== false) {
                $strActivity .= $this->getLang("session_admin");
                foreach(explode("&amp;", $strLastUrl) as $strOneParam) {
                    $arrUrlParam = explode("=", $strOneParam);
                    if($arrUrlParam[0] == "module")
                        $strActivity .= $arrUrlParam[1];
                }
            }
            else {
                $strActivity .= $this->getLang("session_portal");
                if($strLastUrl == "")
                    $strActivity .= _pages_indexpage_;
                else {
                    foreach(explode("&amp;", $strLastUrl) as $strOneParam) {
                        $arrUrlParam = explode("=", $strOneParam);
                        if($arrUrlParam[0] == "page")
                            $strActivity .= $arrUrlParam[1];
                    }

                    if($strActivity == $this->getLang("session_portal") && uniSubstr($strLastUrl, 0, 5) == "image") {
                        $strActivity .= $this->getLang("session_portal_imagegeneration");
                    }
                }
            }

            $arrRowData[4] = $strActivity;
            if($objOneSession->getStrLoginstatus() == class_module_system_session::$LOGINSTATUS_LOGGEDIN)
                $arrRowData[5] = getLinkAdmin("system", "systemSessions", "&logout=true&systemid=".$objOneSession->getSystemid(), "", $this->getLang("session_logout"), "icon_delete.png");
            else
                $arrRowData[5] = getImageAdmin("icon_deleteDisabled.png");
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

        //load logfiles available
        $objFilesystem = new class_filesystem();
        $arrFiles = $objFilesystem->getFilelist(_projectpath_."/log", array(".log"));

        $arrTabs = array();

        foreach($arrFiles as $strName) {
            $objFilesystem->openFilePointer(_projectpath_."/log/".$strName, "r");
            $strLogContent = $objFilesystem->readLastLinesFromFile();
            $strLogContent = str_replace(array("INFO", "ERROR"), array("INFO   ", "ERROR  "), $strLogContent);
            $arrLogEntries = explode("\r", $strLogContent);
            $objFilesystem->closeFilePointer();

            $arrTabs[$strName] = $this->objToolkit->getPreformatted($arrLogEntries);
        }

        return $this->objToolkit->getTabbedContent($arrTabs);
    }

    /**
     * Renders the list of changes for the passed systemrecord.
     * May be called from other modules in order to get the rendered list for a single record.
     * In most cases rendered as a overlay, so in folderview mode
     *
     * @param string $strSystemid sytemid to filter
     * @param string $strSourceModule source-module, required for a working pageview
     * @param string $strSourceAction source-action, required for a working pageview
     * @param bool $bitBlockFolderview
     *
     * @return string
     * @since 3.4.0
     * @autoTestable
     * @permissions right3
     */
    public function actionGenericChangelog($strSystemid = "", $strSourceModule = "system", $strSourceAction = "genericChangelog", $bitBlockFolderview = false) {

        if(!$bitBlockFolderview && $this->getParam("bitBlockFolderview") == "")
            $this->setArrModuleEntry("template", "/folderview.tpl");

        if($strSystemid == "")
            $strSystemid = $this->getSystemid();

        $strReturn = "";
        //check needed rights - done twice since public and callable by not only the controller
        if(!class_carrier::getInstance()->getObjRights()->validatePermissionString("right3", $this->getObjModule()))
            return $this->getLang("commons_error_permissions");

        //showing a list using the pageview
        $objArraySectionIterator = new class_array_section_iterator(class_module_system_changelog::getLogEntriesCount($strSystemid));
        $objArraySectionIterator->setIntElementsPerPage(_admin_nr_of_rows_);
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(class_module_system_changelog::getLogEntries($strSystemid, $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        $arrPageViews = $this->objToolkit->getSimplePageview($objArraySectionIterator, $strSourceModule, $strSourceAction, "&systemid=".$strSystemid."&bitBlockFolderview=".$this->getParam("bitBlockFolderview"));
        $arrLogs = $arrPageViews["elements"];

        $arrData = array();
        $arrHeader = array();
        $arrHeader[] = $this->getLang("commons_date");
        $arrHeader[] = $this->getLang("change_user");
        if($strSystemid == "")
            $arrHeader[] = $this->getLang("change_module");
        if($strSystemid == "")
            $arrHeader[] = $this->getLang("change_record");
        $arrHeader[] = $this->getLang("change_action");
        $arrHeader[] = $this->getLang("change_property");
        $arrHeader[] = $this->getLang("change_oldvalue");
        $arrHeader[] = $this->getLang("change_newvalue");

        /** @var $objOneEntry class_changelog_container */
        foreach($arrLogs as $objOneEntry) {
            $arrRowData = array();

            /** @var interface_versionable|class_model $objTarget */
            $objTarget = $objOneEntry->getObjTarget();

            $strOldValue = $objOneEntry->getStrOldValue();
            $strNewValue = $objOneEntry->getStrNewValue();

            if($objTarget != null) {
                $strOldValue = $objTarget->renderVersionValue($objOneEntry->getStrProperty(), $strOldValue);
                $strNewValue = $objTarget->renderVersionValue($objOneEntry->getStrProperty(), $strNewValue);
            }

            $arrRowData[] = dateToString($objOneEntry->getObjDate());
            $arrRowData[] = $this->objToolkit->getTooltipText(uniStrTrim($objOneEntry->getStrUsername(), 15), $objOneEntry->getStrUsername());
            if($strSystemid == "")
                $arrRowData[] = $objTarget != null ? $objTarget->getArrModule("modul") : "";
            if($strSystemid == "")
                $arrRowData[] = $objTarget != null ? $this->objToolkit->getTooltipText(uniStrTrim($objTarget->getVersionRecordName(), 20), $objTarget->getVersionRecordName()." ".$objOneEntry->getStrSystemid()) : "";
            $arrRowData[] = $objTarget != null ? $this->objToolkit->getTooltipText(uniStrTrim($objTarget->getVersionActionName($objOneEntry->getStrAction()), 15), $objTarget->getVersionActionName($objOneEntry->getStrAction())) : "";
            $arrRowData[] = $objTarget != null ? $this->objToolkit->getTooltipText(uniStrTrim($objTarget->getVersionPropertyName($objOneEntry->getStrProperty()), 20), $objTarget->getVersionPropertyName($objOneEntry->getStrProperty())) : "";
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
     * Renders the list of aspects available
     *
     * @return string
     * @autoTestable
     * @permissions right5
     */
    protected function actionAspects() {

        $objIterator = new class_array_section_iterator(class_module_system_aspect::getObjectCount());
        $objIterator->setPageNumber($this->getParam("pv"));
        $objIterator->setArraySection(class_module_system_aspect::getObjectList(false, $objIterator->calculateStartPos(), $objIterator->calculateEndPos()));

        return $this->renderList($objIterator, false, "aspectList");
    }

    /**
     * Delegate to actionNewAspect
     *
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
     * @param class_admin_formgenerator $objFormManager
     *
     * @return string
     * @permissions right5
     */
    protected function actionNewAspect($strMode = "new", class_admin_formgenerator $objFormManager = null) {

        $objAspect = null;
        if($strMode == "new") {
            $objAspect = new class_module_system_aspect();
        }
        else if($strMode == "edit") {
            $objAspect = new class_module_system_aspect($this->getSystemid());
            if(!$objAspect->rightEdit())
                $objAspect = null;
        }

        if($objAspect != null) {

            if($objFormManager == null)
                $objFormManager = $this->getFormForAspect($objAspect);

            $objFormManager->addField(new class_formentry_hidden("", "mode"))->setStrValue($strMode);
            $strReturn = $objFormManager->renderForm(getLinkAdminHref($this->arrModule["modul"], "saveAspect"));

        }
        else
            $strReturn = $this->getLang("commons_error_permissions");

        return $strReturn;
    }

    /**
     * Creates the admin-form to edit / create an aspect
     *
     * @param class_module_system_aspect $objAspect
     *
     * @return class_admin_formgenerator
     */
    private function getFormForAspect(class_module_system_aspect $objAspect) {
        $objFormManager = new class_admin_formgenerator("aspect", $objAspect);
        $objFormManager->generateFieldsFromObject();
        return $objFormManager;
    }

    /**
     * saves the submitted form-data as a new aspect or updates an existing one
     *
     * @throws class_exception
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

            $objFormManager = $this->getFormForAspect($objAspect);

            if(!$objFormManager->validateForm())
                return $this->actionNewAspect($this->getParam("mode"), $objFormManager);

            $objFormManager->updateSourceObject();

            if(!$objAspect->updateObjectToDb())
                throw new class_exception("Error creating new aspect", class_exception::$level_ERROR);
        }
        $this->adminReload(getLinkAdminHref($this->arrModule["modul"], "aspects"));
        return "";
    }

    /**
     * Deletes an aspect
     *
     * @throws class_exception
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
            return $this->getLang("commons_error_permissions");
        return "";
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
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("about_part1"));
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("about_part2_header"));
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("about_part2"));
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("about_part2a_header"));
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("about_part2a"));
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("about_part2b_header"));
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("about_part2b"));
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("about_part3_header"));
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("about_part3"));
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("about_part4"));
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
        $strReturn .= $this->objToolkit->formInputText("mail_recipient", $this->getLang("mail_recipient"), $this->getParam("mail_recipient"));
        $strReturn .= $this->objToolkit->formInputText("mail_cc", $this->getLang("mail_cc"), $this->getParam("mail_cc"));
        $strReturn .= $this->objToolkit->formInputText("mail_subject", $this->getLang("mail_subject"), $this->getParam("mail_subject"));
        $strReturn .= $this->objToolkit->formInputTextArea("mail_body", $this->getLang("mail_body"), $this->getParam("mail_body"), "inputTextareaLarge");
        $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("send"));
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

        $objValidator = new class_text_validator();
        $objMailValidator = new class_email_validator();

        foreach(array("mail_recipient", "mail_subject", "mail_body") as $strOneField) {
            if(!$objValidator->validate($this->getParam($strOneField)))
                $this->addValidationError($strOneField, $this->getLang($strOneField));
        }

        if(count($this->getValidationErrors()) > 0)
            return $this->actionMailForm();

        $this->setArrModuleEntry("template", "/folderview.tpl");
        $objUser = new class_module_user_user($this->objSession->getUserID());

        $objEmail = new class_mail();

        $objEmail->setSender($objUser->getStrEmail());

        $arrRecipients = explode(",", $this->getParam("mail_recipient"));
        foreach($arrRecipients as $strOneRecipient)
            if($objMailValidator->validate($strOneRecipient))
                $objEmail->addTo($strOneRecipient);

        $arrRecipients = explode(",", $this->getParam("mail_cc"));
        foreach($arrRecipients as $strOneRecipient)
            if($objMailValidator->validate($strOneRecipient))
                $objEmail->addCc($strOneRecipient);


        $objEmail->setSubject($this->getParam("mail_subject"));
        $objEmail->setText($this->getParam("mail_body"));

        if($objEmail->sendMail())
            return $this->getLang("mail_send_success");
        else
            return $this->getLang("mail_send_success");
    }


    /**
     * Loads the data for one module
     *
     * @param int $intModuleID
     * @param bool $bitZeroIsSystem
     *
     * @return class_module_system_module
     */
    private function getModuleDataID($intModuleID, $bitZeroIsSystem = false) {
        $arrModules = class_module_system_module::getAllModules();

        if($intModuleID != 0 || !$bitZeroIsSystem) {
            foreach($arrModules as $objOneModule) {
                if($objOneModule->getIntNr() == $intModuleID)
                    return $objOneModule;
            }
        }
        elseif($intModuleID == 0 && $bitZeroIsSystem) {
            foreach($arrModules as $objOneModule) {
                if($objOneModule->getStrName() == "system")
                    return $objOneModule;
            }
        }
        return null;
    }

}

