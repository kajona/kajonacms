<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/


//base class
include_once(_adminpath_."/class_admin.php");
//Interface
include_once(_adminpath_."/interface_admin.php");
//model
include_once(_systempath_."/class_modul_system_module.php");
include_once(_systempath_."/class_modul_system_setting.php");
include_once(_systempath_."/class_modul_user_user.php");
include_once(_systempath_."/class_modul_user_group.php");

/**
 * Class to handle infos about the system and to set systemwide properties
 *
 * @package modul_system
 */
class class_modul_system_admin extends class_admin implements interface_admin {

    private $strUpdateServer = "updatecheck.kajona.de";
    private $strUpdateUrl = "/updates.php";

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
        $arrModul = array();
		$arrModul["name"]		 		= "modul_system";
		$arrModul["author"] 			= "sidler@mulchprod.de";
		$arrModul["moduleId"] 			= _system_modul_id_;
		$arrModul["modul"]				= "system";
		$arrModul["table"]				= _dbprefix_."system_module";

		parent::__construct($arrModul);
	}

	/**
	 * Method to decide, what to do
	 *
	 * @param stirng $strAction
	 */
	public function action($strAction = "") {
		if($strAction == "")
			$strAction = "moduleList";

		$strReturn = "";

		if($strAction == "moduleList")
			$strReturn = $this->actionModuleList();

		if($strAction == "moduleSortUp") {
		    $this->actionSortModule("upwards");
		    $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
		}

		if($strAction == "moduleSortDown") {
		    $this->actionSortModule("downwards");
		    $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
		}

		if($strAction == "moduleStatus") {
            //status: for setting the status of modules, you have to be member of the admin-group
            $objUser = new class_modul_user_user($this->objSession->getUserID());
            $objAdminGroup = new class_modul_user_group(_admin_gruppe_id_);
   		    if($this->objRights->rightEdit($this->getSystemid()) && $objAdminGroup->isUserMemberInGroup($objUser)) {
    		    $this->setStatus();
    		    $this->adminReload(getLinkAdminHref($this->arrModule["modul"]));
   		    }
		}

		if($strAction == "systemInfo")
			$strReturn = $this->actionSystemInfo();

		if($strAction == "systemSettings")
			$strReturn = $this->actionSystemSettings();

		if($strAction == "systemSessions")
			$strReturn = $this->actionSessions();

		if($strAction == "systemTasks")
		    $strReturn = $this->actionSystemtasks();

		if($strAction == "systemlog")
		    $strReturn = $this->actionSystemlog();

		if($strAction == "updateCheck")
		    $strReturn = $this->actionCheckUpdates();

		if($strAction == "about")
		    $strReturn = $this->actionAboutKajona();

		$this->strTemp = $strReturn;
	}

	public function getOutputContent() {
		return $this->strTemp;
	}

	public function getOutputModuleNavi() {
	    $arrReturn = array();
	    $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getText("modul_rechte"), "", "", true, "adminnavi"));
	    $arrReturn[] = array("right", getLinkAdmin("right", "change", "&systemid=0",  $this->getText("modul_rechte_root"), "", "", true, "adminnavi"));
		$arrReturn[] = array("", "");
  	    $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "moduleList", "", $this->getText("module_liste"), "", "", true, "adminnavi"));
		$arrReturn[] = array("edit", getLinkAdmin($this->arrModule["modul"], "systemInfo", "", $this->getText("system_info"), "", "", true, "adminnavi"));
	    $arrReturn[] = array("right1", getLinkAdmin($this->arrModule["modul"], "systemSettings", "", $this->getText("system_settings"), "", "", true, "adminnavi"));
		$arrReturn[] = array("right2", getLinkAdmin($this->arrModule["modul"], "systemTasks", "", $this->getText("systemTasks"), "", "", true, "adminnavi"));
	    $arrReturn[] = array("right3", getLinkAdmin($this->arrModule["modul"], "systemlog", "", $this->getText("systemlog"), "", "", true, "adminnavi"));
	    $arrReturn[] = array("right1", getLinkAdmin($this->arrModule["modul"], "systemSessions", "", $this->getText("system_sessions"), "", "", true, "adminnavi"));
		$arrReturn[] = array("right4", getLinkAdmin($this->arrModule["modul"], "updateCheck", "", $this->getText("updatecheck"), "", "", true, "adminnavi"));
		$arrReturn[] = array("", "");
		$arrReturn[] = array("", getLinkAdmin($this->arrModule["modul"], "about", "", $this->getText("about"), "", "", true, "adminnavi"));
		return $arrReturn;
	}


// -- Module --------------------------------------------------------------------------------------------

	/**
	 * Creates a list of all installed modules
	 *
	 * @return string
	 */
	private function actionModuleList() {
		$strReturn = "";
		$strListId = generateSystemid();
		if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {
			//Loading the modules
			$arrModules = class_modul_system_module::getAllModules();
			$intI = 0;
			$strReturn .= $this->objToolkit->dragableListHeader($strListId);
			foreach($arrModules as $objSingleModule) {
				$strActions = "";
				$strCenter = "V ".$objSingleModule->getStrVersion()." &nbsp;(".timeToString($objSingleModule->getIntDate(), true).")";
		   		$intModuleSystemID= $this->getModuleSystemid($objSingleModule->getStrName());
		   		if($intModuleSystemID != "") {
		   		    //sort-icons
                    if($this->objRights->rightEdit($intModuleSystemID)) {
                        $strActions .= $this->objToolkit->listButton(getLinkAdmin("system", "moduleSortUp", "&systemid=".$intModuleSystemID, "", $this->getText("modul_sortup"), "icon_arrowUp.gif"));
                        $strActions .= $this->objToolkit->listButton(getLinkAdmin("system", "moduleSortDown", "&systemid=".$intModuleSystemID, "", $this->getText("modul_sortdown"), "icon_arrowDown.gif"));
                    }
                    //status: for setting the status of modules, you have to be member of the admin-group
                    $objUser = new class_modul_user_user($this->objSession->getUserID());
                    $objAdminGroup = new class_modul_user_group(_admin_gruppe_id_);
		   		    if($this->objRights->rightEdit($intModuleSystemID) && $objAdminGroup->isUserMemberInGroup($objUser)) {
		   		        if($objSingleModule->getStrName() == "system")
		   			        $strActions .= $this->objToolkit->listButton(getLinkAdmin("system", "moduleList", "", "", $this->getText("modul_status_system"), "icon_enabled.gif"));
		   		        else if($objSingleModule->getStatus() == 0)
		   			        $strActions .= $this->objToolkit->listButton(getLinkAdmin("system", "moduleStatus", "&systemid=".$intModuleSystemID, "", $this->getText("modul_status_disabled"), "icon_disabled.gif"));
		   			    else
		   			        $strActions .= $this->objToolkit->listButton(getLinkAdmin("system", "moduleStatus", "&systemid=".$intModuleSystemID, "", $this->getText("modul_status_enabled"), "icon_enabled.gif"));
		   		    }
		   		    //rights
		   		    if($this->objRights->rightRight($intModuleSystemID))
		   			    $strActions .= $this->objToolkit->listButton(getLinkAdmin("right", "change", "&changemodule=".$objSingleModule->getStrName(), "", $this->getText("modul_rechte"), getRightsImageAdminName($intModuleSystemID)));
		   		}
		   		$strReturn .= $this->objToolkit->listRow3($objSingleModule->getStrName(), $strCenter, $strActions, getImageAdmin("icon_module.gif"), $intI++, $objSingleModule->getSystemid());
			}
			$strReturn .= $this->objToolkit->dragableListFooter($strListId);
		}
		else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;
	}


	/**
	 * Shifts modules ins the sort-ordner
	 *
	 * @param string $strDirection
	 */
	private function actionSortModule($strDirection) {
	    $this->setPosition($this->getSystemid(), $strDirection);
	}

// -- Systeminfos ---------------------------------------------------------------------------------------

	/**
	 * Shows infos about the current system
	 *
	 * @return string
	 */
	private function actionSystemInfo() {
		$strReturn = "";
        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {
    		$arrTemplate = array("php" => "", "webserver" => "", "datenbank" => "", "gd" => "");

    		//Phpinfos abhandeln
    		$arrPHP = $this->loadPhpInfos();
    		$intI = 0;
    		$strPHP = $this->objToolkit->listHeader();
    		foreach($arrPHP as $strKey => $strValue) {
    			$strPHP .= $this->objToolkit->listRow2($strKey, $strValue, $intI++, "_b");
    		}
    		$strPHP .= $this->objToolkit->listFooter();
    		//And put it into a fieldset
            $strPHP = $this->objToolkit->getFieldset($this->getText("php"), $strPHP);

    		//Webserverinfos
    		$arrWebserver = $this->loadWebserverInfos();
    		$intI = 0;
    		$strServer = $this->objToolkit->listHeader();
    		foreach($arrWebserver as $strKey => $strValue) {
    			$strServer .= $this->objToolkit->listRow2($strKey, $strValue, $intI++, "_b");
    		}
    		$strServer .= $this->objToolkit->listFooter();
            //And put it into a fieldset
            $strServer = $this->objToolkit->getFieldset($this->getText("server"), $strServer);

    		//Datenbankinfos
    		$arrDatabase = $this->loadDatabaseInfos();
    		$intI = 0;
    		$strDB = $this->objToolkit->listHeader();
    		foreach($arrDatabase as $strKey => $strValue) {
    			$strDB .= $this->objToolkit->listRow2($strKey, $strValue, $intI++, "_b");
    		}
    		$strDB .= $this->objToolkit->listFooter();
            //And put it into a fieldset
            $strDB = $this->objToolkit->getFieldset($this->getText("db"), $strDB);

    		//GD-Lib infos
    		$arrGd = $this->loadGDInfos();
    		$intI = 0;
    		$strGD = $this->objToolkit->listHeader();
    		foreach($arrGd as $strKey => $strValue) {
    			$strGD .= $this->objToolkit->listRow2($strKey, $strValue, $intI++, "_b");
    		}
    		$strGD .= $this->objToolkit->listFooter();
            //And put it into a fieldset
            $strGD = $this->objToolkit->getFieldset($this->getText("gd"), $strGD);

    		$strReturn .= $strPHP.$strServer.$strDB.$strGD;
        }
		else
			$strReturn = $this->getText("fehler_recht");
		return $strReturn;
	}

// -- SystemSettings ------------------------------------------------------------------------------------

    /**
     * Creates a form to edit systemsettings or updates them
     *
     * @return string "" in case of success
     */
    private function actionSystemSettings() {
        $strReturn = "";
        //Check for needed rights
        if($this->objRights->rightRight1($this->getModuleSystemid($this->arrModule["modul"]))) {
            if($this->getParam("save") != "true") {
                //Create a warning before doing s.th.
                $strReturn .= $this->objToolkit->warningBox($this->getText("warnung_settings"));

                $arrSettings = class_modul_system_setting::getAllConfigValues();
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
                                $strFieldset .= $this->objToolkit->formInputSubmit($this->getText("speichern"));
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
                        $arrDD["true"] = $this->getText("settings_true");
                        $arrDD["false"] = $this->getText("settings_false");
                        $strRows .= $this->objToolkit->formInputDropdown("set[".$objOneSetting->getStrName()."]", $arrDD, $this->getText($objOneSetting->getStrName(), $objCurrentModule->getStrName()), $objOneSetting->getStrValue());
                    }
                    elseif ($objOneSetting->getIntType() == 3) {
                        $strRows .= $this->objToolkit->formInputPageSelector("set[".$objOneSetting->getStrName()."]", $this->getText($objOneSetting->getStrName(), $objCurrentModule->getStrName()), $objOneSetting->getStrValue());
                    }
                    else {
                        $strRows .= $this->objToolkit->formInputText("set[".$objOneSetting->getStrName()."]", $this->getText($objOneSetting->getStrName(), $objCurrentModule->getStrName()), $objOneSetting->getStrValue());
                    }
                }
                //Build a form to return -> include the last module
                $strFieldset = $this->objToolkit->formHeader(getLinkAdminHref($this->arrModule["modul"], "systemSettings"));
                $strFieldset .= $strRows;
                $strFieldset .= $this->objToolkit->formInputHidden("save", "true");
                $strFieldset .= $this->objToolkit->formInputSubmit($this->getText("speichern"));
                $strFieldset .= $this->objToolkit->formClose();

                $strReturn .= $this->objToolkit->getFieldset($this->getText("modul_titel", $objCurrentModule->getStrName()), $strFieldset);
                $strRows = "";
            }
            else {
                //Seems we have to update a few records
                $arrSettings = $this->getAllParams();
                foreach($arrSettings["set"] as $strKey => $strValue) {
                    $objSetting = class_modul_system_setting::getConfigByName($strKey);
                    $objSetting->setStrValue($strValue);
                    $objSetting->updateObjectToDb();
                }
                $strReturn .= $this->objToolkit->warningBox($this->getText("settings_updated"));
            }
        }
        else
			$strReturn = $this->getText("fehler_recht");

		return $strReturn;

    }


// --- Systemtasks --------------------------------------------------------------------------------------

    private function actionSystemtasks() {
        $strReturn = "";
        $strTaskOutput = "";

        //check needed rights
        if($this->objRights->rightRight2($this->getModuleSystemid($this->arrModule["modul"]))) {

        	//include the list of possible tasks
            include_once(_systempath_."/class_filesystem.php");
            $objFilesystem = new class_filesystem();
            $arrFiles = $objFilesystem->getFilelist(_adminpath_."/systemtasks/", array(".php"));
            asort($arrFiles);


        	//react on special task-commands?
            if($this->getParam("task") != "") {
                //search for the matching task
                foreach ($arrFiles as $strOneFile) {
                    if($strOneFile != "class_systemtask_base.php" && $strOneFile != "interface_admin_systemtask.php" ) {

                        //instantiate the current task
                        include_once(_adminpath_."/systemtasks/".$strOneFile);
                        $strClassname = uniStrReplace(".php", "", $strOneFile);
                        $objTask = new $strClassname();
                        if($objTask instanceof interface_admin_systemtask && $objTask->getStrInternalTaskname() == $this->getParam("task")) {


                        	//fire the task or display a form?
                        	if($this->getParam("work") == "true") {
                        		 class_logger::getInstance()->addLogRow("executing task ".$objTask->getStrInternalTaskname(), class_logger::$levelInfo);
                        		 //let the work begin...
                        		 $strTaskOutput .= $objTask->executeTask();
                        	}
                        	else {
	                        	//any form to display?
	                        	$strForm = $objTask->generateAdminForm();
	                        	if($strForm != "") {
	                        	   $strReturn .= $strForm;
	                        	}
	                        	else {
	                        		//reload the task an fire the action
	                        		$this->adminReload(getLinkAdminHref($this->arrModule["modul"], "systemTasks", "work=true&task=".$objTask->getStrInternalTaskname()));
	                        	}
                        	}
                            break;
                        }
                    }
                }
            }

        	$intI = 0;
            $strReturn .= $this->objToolkit->listHeader();

        	//loop over the found files
        	foreach ($arrFiles as $strOneFile) {
        		if($strOneFile != "class_systemtask_base.php" && $strOneFile != "interface_admin_systemtask.php" ) {

        			//instantiate the current task
        			include_once(_adminpath_."/systemtasks/".$strOneFile);
        			$strClassname = uniStrReplace(".php", "", $strOneFile);
        			$objTask = new $strClassname();

        			if($objTask instanceof interface_admin_systemtask ) {
	                    $strReturn .= $this->objToolkit->listRow2Image(getImageAdmin("icon_dot.gif"),
	                                                                   $objTask->getStrTaskname(),
	                                                                   $this->objToolkit->listButton(
	                                                                        getLinkAdmin("system",
	                                                                                     "systemTasks",
	                                                                                     "&task=".$objTask->getStrInternalTaskName(),
	                                                                                      $objTask->getStrTaskname(),
	                                                                                      $this->getText("systemtask_run"),
	                                                                                      "icon_accept.gif")),
	                                                                   $intI++);
        			}
        		}
        	}
            $strReturn .= $this->objToolkit->listFooter();



        	if($strTaskOutput != "") {
        	   $strReturn = $strTaskOutput.$this->objToolkit->divider().$strReturn;
        	}

        }
        else
            $strReturn = $this->getText("fehler_recht");

        return $strReturn;
    }


// --- Sessionmanagement --------------------------------------------------------------------------------

    /**
     * Creates a table filled with the sessions currently registered
     *
     * @return string
     */
    private function actionSessions() {
        $strReturn = "";
        //check needed rights
        if($this->objRights->rightRight1($this->getModuleSystemid($this->arrModule["modul"]))) {

            //react on commands?
            if($this->getParam("logout") == "true") {
                $objSession = new class_modul_system_session($this->getSystemid());
                $objSession->setStrLoginstatus(class_modul_system_session::$LOGINSTATUS_LOGGEDOUT);
                $objSession->updateObjectToDb();
                $this->objDB->flushQueryCache();
            }

            include_once(_systempath_."/class_modul_system_session.php");
            $arrSessions = class_modul_system_session::getAllActiveSessions();
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
                    $objUser = new class_modul_user_user($objOneSession->getStrUserid());
                    $strUsername = $objUser->getStrUsername();
                }
                $arrRowData[0] = getImageAdmin("icon_user.gif");
                $arrRowData[1] = $strUsername;
                $arrRowData[2] = timeToString($objOneSession->getIntReleasetime());
                if($objOneSession->getStrLoginstatus() == class_modul_system_session::$LOGINSTATUS_LOGGEDIN)
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
                    }
                }

                $arrRowData[4] = $strActivity;
                if($objOneSession->getStrLoginstatus() == class_modul_system_session::$LOGINSTATUS_LOGGEDIN)
                    $arrRowData[5] = getLinkAdmin("system", "systemSessions", "&logout=true&systemid=".$objOneSession->getSystemid(), "", $this->getText("session_logout"), "icon_ton.gif");
                else
                    $arrRowData[5] = getImageAdmin("icon_tonDisabled.gif");
                $arrData[] = $arrRowData;
            }
            $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrData);

        }
        else
			$strReturn = $this->getText("fehler_recht");
        return $strReturn;
    }



// --- Systemlog ---------------------------------------------------------------------------------------.

    /**
     * Fetches the entries from the system-log an prints them as preformatted text
     *
     * @return string
     */
    private function actionSystemlog() {
        $strReturn = "";
        //check needed rights
        if($this->objRights->rightRight3($this->getModuleSystemid($this->arrModule["modul"]))) {
            $strLogContent = class_logger::getInstance()->getLogFileContent();
            $strPhpLogContent = "";
            if(is_file(_systempath_."/debug/php.log"))
                $strPhpLogContent = file_get_contents(_systempath_."/debug/php.log");
                
            if(uniStrlen($strLogContent) != 0) {
                //create columns with same width
                $strLogContent = str_replace(array("INFO", "ERROR"), array("INFO   ", "ERROR  "), $strLogContent);
                $arrLogEntries = explode("\n", $strLogContent);
                //Reverse array
                $arrLogEntries = array_reverse($arrLogEntries);
                //and print the log to buffer
                $strReturn .= $this->objToolkit->getPreformatted($arrLogEntries, 100);
            }
            else
                $strReturn .= $this->getText("log_empty");

            if($strPhpLogContent != "") {
                $arrLogEntries = explode("\n", $strPhpLogContent);
                $arrLogEntries = array_reverse($arrLogEntries);
                $strReturn .= $this->objToolkit->getPreformatted($arrLogEntries, 100);
            }

        }
        else
			$strReturn = $this->getText("fehler_recht");
        return $strReturn;
    }

// --- UpdateCheck --------------------------------------------------------------------------------------

    /**
     * Looks for possible updates of the installed modules
     *
     * @return string
     */
    private function actionCheckUpdates() {
        $strReturn = "";
        //check needed rights
        if($this->objRights->rightRight4($this->getModuleSystemid($this->arrModule["modul"]))) {
            $strChecksum = md5(urldecode(_webpath_)."getVersions");
            $strQueryString = $this->strUpdateUrl."?action=getVersions&domain=".urlencode(_webpath_)."&checksum=".urlencode($strChecksum);
            $strXmlVersionList = false;

            //try to load the xml-file with a list of available updates
            try {
                include_once(_systempath_."/class_remoteloader.php");
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
                include_once(_systempath_."/class_xml_parser.php");
                $objXmlParser = new class_xml_parser();
                if($objXmlParser->loadString($strXmlVersionList)) {
                    $arrRemoteModules = $objXmlParser->getNodesAttributesAsArray("module");
                    //Do a little clean up
                    $arrCleanModules = array();
                    foreach ($arrRemoteModules as $arrOneRemoteModule) {
                        $arrCleanModules[$arrOneRemoteModule[0]["value"]] = $arrOneRemoteModule[1]["value"];
                    }
                    //Get all installed modules
                    include_once(_systempath_."/class_modul_system_module.php");
                    $arrModules = class_modul_system_module::getAllModules();
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
        }
        else
			$strReturn = $this->getText("fehler_recht");
        return $strReturn;
    }

// -- Helferfunktionen ----------------------------------------------------------------------------------

    /**
     * About kajona, credits and co
     *
     * @return string
     */
    private function actionAboutKajona() {
        $strReturn = "";
        $strReturn .= $this->objToolkit->getTextRow($this->getText("about"));
        $strReturn .= $this->objToolkit->getTextRow($this->getText("about_part1"));
        $strReturn .= $this->objToolkit->getTextRow($this->getText("about_part2"));
        $strReturn .= $this->objToolkit->getTextRow($this->getText("about_part3"));
        $strReturn .= $this->objToolkit->getTextRow($this->getText("about_part4"));
        return $strReturn;
    }

	/**
	 * Creates infos about the current php version
	 *
	 * @return mixed
	 *
	 */
	private function loadPhpInfos() {
		$arrReturn = array();
		$arrReturn[$this->getText("version")] = phpversion();
		$arrReturn[$this->getText("geladeneerweiterungen")] = implode(", ", get_loaded_extensions());
		$arrReturn[$this->getText("executiontimeout")] = class_carrier::getInstance()->getObjConfig()->getPhpIni("max_execution_time") ."s";
		$arrReturn[$this->getText("inputtimeout")] = class_carrier::getInstance()->getObjConfig()->getPhpIni("max_input_time") ."s";
		$arrReturn[$this->getText("memorylimit")] = bytesToString(ini_get("memory_limit"), true);
		$arrReturn[$this->getText("errorlevel")] = class_carrier::getInstance()->getObjConfig()->getPhpIni("error_reporting");
        $arrReturn[$this->getText("systeminfo_php_safemode")] = (ini_get("safe_mode") ? $this->getText("systeminfo_yes") : $this->getText("systeminfo_no"));
        $arrReturn[$this->getText("systeminfo_php_urlfopen")] = (ini_get("allow_url_fopen") ? $this->getText("systeminfo_yes") : $this->getText("systeminfo_no"));
        $arrReturn[$this->getText("systeminfo_php_regglobal")] = (ini_get("register_globals") ? $this->getText("systeminfo_yes") : $this->getText("systeminfo_no"));
		$arrReturn[$this->getText("postmaxsize")] = bytesToString(ini_get("post_max_size"), true);
		$arrReturn[$this->getText("uploadmaxsize")] = bytesToString(ini_get("upload_max_filesize"), true);
		$arrReturn[$this->getText("uploads")] = (class_carrier::getInstance()->getObjConfig()->getPhpIni("file_uploads") == 1 ? $this->getText("systeminfo_yes") : $this->getText("systeminfo_no"));

		return $arrReturn;
	}

	/**
	 * Creates information about the webserver
	 *
	 * @return mixed
	 */
	private function loadWebserverInfos() {
		$arrReturn = array();
		$arrReturn[$this->getText("operatingsystem")] = php_uname();
		if (@disk_total_space(_realpath_)) {
		    $arrReturn[$this->getText("speicherplatz")] = bytesToString(@disk_free_space(_realpath_)) ."/". bytesToString(@disk_total_space(_realpath_)) . $this->getText("diskspace_free");
		}
		return $arrReturn;
	}

	/**
	 * Creates Infos about the GDLib
	 *
	 * @return unknown
	 */
	private function loadGDInfos() {
		$arrReturn = array();
		if(function_exists("gd_info")) 	{
			$arrGd = gd_info();
			$arrReturn[$this->getText("version")] = $arrGd["GD Version"];
			$arrReturn[$this->getText("gifread")] = ($arrGd["GIF Read Support"] ? $this->getText("systeminfo_yes") : $this->getText("systeminfo_no"));
			$arrReturn[$this->getText("gifwrite")] = ($arrGd["GIF Create Support"] ? $this->getText("systeminfo_yes") : $this->getText("systeminfo_no"));
			$arrReturn[$this->getText("jpg")] = ($arrGd["JPG Support"] ? $this->getText("systeminfo_yes") : $this->getText("systeminfo_no"));
			$arrReturn[$this->getText("png")] = ($arrGd["PNG Support"] ? $this->getText("systeminfo_yes") : $this->getText("systeminfo_no"));
		}
		else
			$arrReturn[""] = $this->getText("keinegd");
		return $arrReturn;
	}

	/**
	 * Creates Infos about the database
	 *
	 * @return mixed
	 */
	private function loadDatabaseInfos() {
		$arrReturn = array();
		//Momentan werden nur mysql / mysqli unterstuetzt
		$arrTables = $this->objDB->getTables(true);
		$intNumber = 0;
		$intSizeData = 0;
		$intSizeIndex = 0;
		//Bestimmen der Datenbankgroesse
		switch($this->objConfig->getConfig("dbdriver")) {
		case "mysql":
			foreach($arrTables as $arrTable) {
				$intNumber++;
				$intSizeData += $arrTable["Data_length"];
				$intSizeIndex += $arrTable["Index_length"];
			}
			$arrInfo = $this->objDB->getDbInfo();
			$arrReturn[$this->getText("datenbanktreiber")] = $arrInfo["dbdriver"];
			$arrReturn[$this->getText("datenbankserver")] = $arrInfo["dbserver"];
			$arrReturn[$this->getText("datenbankclient")] = $arrInfo["dbclient"];
			$arrReturn[$this->getText("datenbankverbindung")] = $arrInfo["dbconnection"];
			$arrReturn[$this->getText("anzahltabellen")] = $intNumber;
			$arrReturn[$this->getText("groessegesamt")] = bytesToString($intSizeData + $intSizeIndex);
			$arrReturn[$this->getText("groessedaten")] = bytesToString($intSizeData);
			#$arrReturn["Groesse Indizes"] = bytes_to_string($int_groesse_index);
			break;

		case "mysqli":
			foreach($arrTables as $arrTable) {
				$intNumber++;
				$intSizeData += $arrTable["Data_length"];
				$intSizeIndex += $arrTable["Index_length"];
			}
			$arrInfo = $this->objDB->getDbInfo();
			$arrReturn[$this->getText("datenbanktreiber")] = $arrInfo["dbdriver"];
			$arrReturn[$this->getText("datenbankserver")] = $arrInfo["dbserver"];
			$arrReturn[$this->getText("datenbankclient")] = $arrInfo["dbclient"];
			$arrReturn[$this->getText("datenbankverbindung")] = $arrInfo["dbconnection"];
			$arrReturn[$this->getText("anzahltabellen")] = $intNumber;
			$arrReturn[$this->getText("groessegesamt")] = bytesToString($intSizeData + $intSizeIndex);
			$arrReturn[$this->getText("groessedaten")] = bytesToString($intSizeData);
			#$arrReturn["Groesse Indizes"] = bytes_to_string($int_groesse_index);
			break;

		case "postgres":
			foreach($arrTables as $arrTable) {
				$intNumber++;
				//$intSizeData += $arrTable["Data_length"];
				//$intSizeIndex += $arrTable["Index_length"];
			}
			$arrInfo = $this->objDB->getDbInfo();
			$arrReturn[$this->getText("datenbanktreiber")] = $arrInfo["dbdriver"];
			$arrReturn[$this->getText("datenbankserver")] = $arrInfo["dbserver"];
			$arrReturn[$this->getText("datenbankclient")] = $arrInfo["dbclient"];
			$arrReturn[$this->getText("datenbankverbindung")] = $arrInfo["dbconnection"];
			$arrReturn[$this->getText("anzahltabellen")] = $intNumber;
			$arrReturn[$this->getText("groessegesamt")] = bytesToString($intSizeData + $intSizeIndex);
			$arrReturn[$this->getText("groessedaten")] = bytesToString($intSizeData);
			#$arrReturn["Groesse Indizes"] = bytes_to_string($int_groesse_index);
			break;
		}


		return $arrReturn;
	}

//---Helpers---------------------------------------------------------------------------------------------

	/**
	 * Loads the data for one module
	 *
	 * @param int $intModuleID
	 * @package bool $bitZeroIsSystem
	 * @return mixed
	 */
	private function getModuleDataID($intModuleID, $bitZeroIsSystem = false) {
		$arrModules = class_modul_system_module::getAllModules();

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

}//class_modul_system_admin

?>