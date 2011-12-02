<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$							*
********************************************************************************************************/


/**
 * admin-class of the system-module
 * Serves xml-requests, mostly general requests e.g. changing a records status or position in a list
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_module_system_admin_xml extends class_admin implements interface_xml_admin {


	/**
	 * Constructor
	 *
	 * @param mixed $arrElementData
	 */
	public function __construct() {
        $arrModule = array();
		$arrModule["name"] 				= "module_system";
		$arrModule["moduleId"] 			= _system_modul_id_;
		$arrModule["modul"]				= "system";

		parent::__construct($arrModule);
	}


	/**
	 * saves a post in the database an returns the post as html.
	 * In case of missing fields, the form is returned again
	 *
	 * @return string
	 */
	protected function actionSetAbsolutePosition() {
	    $strReturn = "";

		//check permissions
		if($this->objRights->rightEdit($this->getSystemid())) {

            $intNewPos = $this->getParam("listPos");

            //there is a different mode for page-elements, catch now
            $objCommon = new class_module_system_common($this->getSystemid());
            //store edit date
            $objCommon->updateObjectToDb();

            if($objCommon->getRecordModuleNr() == _pages_content_modul_id_ && $intNewPos != "") {
                $objElement = new class_module_pages_pageelement($this->getSystemid());
                $objElement->setAbsolutePosition($this->getSystemid(), $intNewPos);
            }
            else {

                if($intNewPos != "")
                    $this->setAbsolutePosition($this->getSystemid(), $intNewPos);
            }


		    $strReturn .= "<message>".$this->getSystemid()." - ".$this->getText("setAbsolutePosOk")."</message>";
		    $this->flushCompletePagesCache();
		}
		else {
            header(class_http_statuscodes::$strSC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getText("commons_error_permissions"))."</error></message>";
        }

        return $strReturn;
	}

	/**
	 * Changes the status of the current systemid
	 *
	 * @return string
	 */
	protected function actionSetStatus() {
	    $strReturn = "";
	    if($this->objRights->rightEdit($this->getSystemid())) {
    	    if(parent::setStatus()) {
    	        $strReturn .= "<message>".$this->getSystemid()." - ".$this->getText("setStatusOk")."</message>";
    	        $this->flushCompletePagesCache();
    	    }
    	    else
                $strReturn .= "<error>".$this->getSystemid()." - ".$this->getText("setStatusError")."</error>";
	    }
	    else {
            header(class_http_statuscodes::$strSC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getText("commons_error_permissions"))."</error></message>";
        }

	    return $strReturn;
	}

    /**
     * Executes a systemtask.
     * Returns the progress-info or the error-/success message and the reload-infos using a
     * custom xml-structure:
     * <statusinfo></statusinfo><reloadurl></reloadurl>
     *
     * @return string
     */
    protected function actionExecuteSystemTask() {
        $strReturn = "";
        $strTaskOutput = "";
        if($this->objRights->rightRight2($this->getModuleSystemid($this->arrModule["modul"]))) {

            if($this->getParam("task") != "") {
                //include the list of possible tasks
                $objFilesystem = new class_filesystem();
                $arrFiles = class_resourceloader::getInstance()->getFolderContent(_adminpath_."/systemtasks/", array(".php"));
                asort($arrFiles);
                //search for the matching task
                foreach ($arrFiles as $strOneFile) {
                    if($strOneFile != "class_systemtask_base.php" && $strOneFile != "interface_admin_systemtask.php" ) {

                        //instantiate the current task
                        $strClassname = uniStrReplace(".php", "", $strOneFile);
                        $objTask = new $strClassname();
                        if($objTask instanceof interface_admin_systemtask && $objTask->getStrInternalTaskname() == $this->getParam("task")) {

                            class_logger::getInstance()->addLogRow("executing task ".$objTask->getStrInternalTaskname(), class_logger::$levelInfo);

                            //let the work begin...
                            $strTempOutput = trim($objTask->executeTask());

                            //progress information?
                            if($objTask->getStrProgressInformation() != "")
                                $strTaskOutput .= $objTask->getStrProgressInformation();

                            if(is_numeric($strTempOutput) && ($strTempOutput >= 0 && $strTempOutput <= 100) ) {
                                $strTaskOutput .= "<br />".$this->getText("systemtask_progress")."<br />".$this->objToolkit->percentBeam($strTempOutput, 400);
                            }
                            else {
                                $strTaskOutput .= $strTempOutput;
                            }

                            //create response-content
                            $strReturn .= "<statusinfo>".$strTaskOutput."</statusinfo>\n";

                            //reload requested by worker?
                            if($objTask->getStrReloadUrl() != "")
                                $strReturn .= "<reloadurl>".("&task=".$this->getParam("task").$objTask->getStrReloadParam())."</reloadurl>";

                            break;
                        }
                    }
                }
            }

        }
	    else {
            header(class_http_statuscodes::$strSC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getText("commons_error_permissions"))."</error></message>";
        }

	    return $strReturn;
    }

    /**
     * Creates the lastest entries from the current systemlog.
     * The entries can be limited by the optional param latestEntry.
     * If given, only entries created after the passed date will be returned.
     * The format of latestEntry is similar to the date returned, so YYYY-MM-DD HH:MM:SS
     * The structure is returned like:
     * <entries>
     *   <entry>
     *      <level></level>
     *      <date></date>
     *      <session></session>
     *      <content></content>
     *   </entry>
     * </entries>
     *
     * @return string
     */
    protected function actionSystemLog() {
        $strReturn = "";

        if($this->objRights->rightRight3($this->getModuleSystemid($this->arrModule["modul"]))) {


            $intStartDate = false;
            if($this->getParam("latestEntry") != "")
                $intStartDate = strtotime($this->getParam("latestEntry"));

            //read the last few lines
            $objFile = new class_filesystem();
            $arrDetails = $objFile->getFileDetails("/system/debug/systemlog.log");

            $intOffset = 0;
            $bitSkip = false;
            if($arrDetails["filesize"] > 20000) {
                $intOffset = $arrDetails["filesize"] - 20000;
                $bitSkip = true;
            }

            $objFile->openFilePointer("/system/debug/systemlog.log", "r");

            //forward to the new offset, skip entry
            if($intOffset > 0)
                $objFile->setFilePointerOffset($intOffset);

            $arrRows = array();

            $strRow = $objFile->readLineFromFile();
            while($strRow !== false) {
                if(!$bitSkip && trim($strRow) > 0)
                    $arrRows[] = $strRow;

                $bitSkip = false;
                $strRow = $objFile->readLineFromFile();
            }

            $objFile->closeFilePointer();

            $strReturn .= "<entries>\n";
            $arrRows = array_reverse($arrRows);
            foreach($arrRows as $strSingleRow) {

                //parse entry
                $strDate = uniSubstr($strSingleRow, 0, 19);
                $strSingleRow = uniSubstr($strSingleRow, 20);

                $intTempPos = uniStrpos($strSingleRow, " ");
                $strLevel = uniSubstr($strSingleRow, 0, $intTempPos);
                $strSingleRow = uniSubstr($strSingleRow, $intTempPos+1);

                $intTempPos = uniStrpos($strSingleRow, ")")+1;
                $strSession = uniSubstr($strSingleRow, 0, $intTempPos);

                $strLogEntry = uniSubstr($strSingleRow, $intTempPos);

                if($intStartDate !== false) {
                    $intCurDate = strtotime($strDate);
                    if($intStartDate >= $intCurDate)
                        continue;
                }

                $strReturn .= "\t<entry>\n";
                $strReturn .= "\t\t<level>".$strLevel."</level>\n";
                $strReturn .= "\t\t<date>".$strDate."</date>\n";
                $strReturn .= "\t\t<session>".$strSession."</session>\n";
                $strReturn .= "\t\t<content>".  xmlSafeString(strip_tags($strLogEntry))."</content>\n";

                $strReturn .= "\t</entry>\n";
            }

            $strReturn .= "</entries>";


        }
	    else {
            header(class_http_statuscodes::$strSC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getText("commons_error_permissions"))."</error></message>";
        }

        return $strReturn;
    }


    /**
     * Generates a xml-based set of information about the current system and evironment
     *
     * Returned structure:
     * <info>
     *    <infoset name="xx">
     *      <entry>
     *        <key></key>
     *        <value></value>
     *      </entry>
     *    </infoset>
     * </info>
     *
     * @return string
     */
    protected function actionSystemInfo() {
        $strReturn = "";
        if($this->objRights->rightEdit($this->getModuleSystemid($this->arrModule["modul"]))) {

            $objCommon = new class_module_system_common();

            $strReturn .= "<info>";

            $arrInfos = $objCommon->getPHPInfo();
            $strReturn .= "<infoset name=\"PHP\">";
            foreach($arrInfos as $strKey => $strValue) {
                $strReturn .= "<entry>";
                $strReturn .= "<key>".  xmlSafeString($strKey)."</key>";
                $strReturn .= "<value>".  xmlSafeString($strValue)."</value>";
                $strReturn .= "</entry>";
            }
            $strReturn .= "</infoset>";

            $arrInfos = $objCommon->getWebserverInfos();
            $strReturn .= "<infoset name=\"Webserver\">";
            foreach($arrInfos as $strKey => $strValue) {
                $strReturn .= "<entry>";
                $strReturn .= "<key>".  xmlSafeString($strKey)."</key>";
                $strReturn .= "<value>".  xmlSafeString($strValue)."</value>";
                $strReturn .= "</entry>";
            }
            $strReturn .= "</infoset>";

            $arrInfos = $objCommon->getDatabaseInfos();
            $strReturn .= "<infoset name=\"Database\">";
            foreach($arrInfos as $strKey => $strValue) {
                $strReturn .= "<entry>";
                $strReturn .= "<key>".  xmlSafeString($strKey)."</key>";
                $strReturn .= "<value>".  xmlSafeString($strValue)."</value>";
                $strReturn .= "</entry>";
            }
            $strReturn .= "</infoset>";

            $arrInfos = $objCommon->getGDInfos();
            $strReturn .= "<infoset name=\"GD Lib\">";
            foreach($arrInfos as $strKey => $strValue) {
                $strReturn .= "<entry>";
                $strReturn .= "<key>".  xmlSafeString($strKey)."</key>";
                $strReturn .= "<value>".  xmlSafeString($strValue)."</value>";
                $strReturn .= "</entry>";
            }
            $strReturn .= "</infoset>";

            $strReturn .= "</info>";

        }
	    else {
            header(class_http_statuscodes::$strSC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getText("commons_error_permissions"))."</error></message>";
        }

        return $strReturn;
    }

    /**
     * Generates the list of modules installed.
     * Returned structure:
     * <modules>
     *    <module>
     *      <name></name>
     *      <version></version>
     *    <module>
     * </modules>
     *
     * @return string
     */
    protected function actionModuleList() {
        $strReturn = "";

		if($this->objRights->rightView($this->getModuleSystemid($this->arrModule["modul"]))) {

            $strReturn .= "<modules>";
			//Loading the modules
			$arrModules = class_module_system_module::getAllModules();
			foreach($arrModules as $objSingleModule) {
                $strReturn .= "<module>";
                $strReturn .= "<name>".  xmlSafeString($objSingleModule->getStrName())."</name>";
                $strReturn .= "<version>".  xmlSafeString($objSingleModule->getStrVersion())."</version>";
                $strReturn .= "</module>";
			}

			$strReturn .= "</modules>";
		}
		else {
            header(class_http_statuscodes::$strSC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getText("commons_error_permissions"))."</error></message>";
        }

		return $strReturn;
    }



    /**
     * Creates a table filled with the sessions currently registered.
     * Returned structure:
     * <sessions>
     *    <session>
     *        <username></username>
     *        <loginstatus></loginstatus>
     *        <releasetime></releasetime>
     *        <activity></activity>
     *    </session>
     * </sessions>
     *
     * @return string
     */
    protected function actionSystemSessions() {
        $strReturn = "";
        //check needed rights
        if($this->objRights->rightRight1($this->getModuleSystemid($this->arrModule["modul"]))) {

            $arrSessions = class_module_system_session::getAllActiveSessions();

            $strReturn .= "<sessions>";

            foreach ($arrSessions as $objOneSession) {

                $strReturn .= "<session>";

                $strUsername = "";
                if($objOneSession->getStrUserid() != "") {
                    $objUser = new class_modul_user_user($objOneSession->getStrUserid());
                    $strUsername = $objUser->getStrUsername();
                }

                $strLoginStatus = "";
                if($objOneSession->getStrLoginstatus() == class_module_system_session::$LOGINSTATUS_LOGGEDIN)
                    $strLoginStatus = $this->getText("session_loggedin");
                else
                    $strLoginStatus = $this->getText("session_loggedout");

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

                $strReturn .= "<username>".  xmlSafeString($strUsername)."</username>";
                $strReturn .= "<loginstatus>".  xmlSafeString($strLoginStatus)."</loginstatus>";
                $strReturn .= "<releasetime>".  xmlSafeString(timeToString($objOneSession->getIntReleasetime()))."</releasetime>";
                $strReturn .= "<activity>".  xmlSafeString($strActivity)."</activity>";

                $strReturn .= "</session>";
            }

            $strReturn .= "</sessions>";


        }
        else {
            header(class_http_statuscodes::$strSC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getText("commons_error_permissions"))."</error></message>";
        }

        return $strReturn;
    }
}
