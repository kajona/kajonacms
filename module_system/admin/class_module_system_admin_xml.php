<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
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
 *
 * @module system
 * @moduleId _system_modul_id_
 */
class class_module_system_admin_xml extends class_admin_controller implements interface_xml_admin {


    /**
     * Updates the aboslute position of a single record, relative to its siblings
     *
     * @return string
     * @permissions edit
     */
    protected function actionSetAbsolutePosition() {
        $strReturn = "";

        $objCommon = class_objectfactory::getInstance()->getObject($this->getSystemid());
        $intNewPos = $this->getParam("listPos");
        //check permissions
        if($objCommon != null && $objCommon->rightEdit() && $intNewPos != "") {

            //there is a different mode for page-elements, catch now
            //store edit date
            $objCommon->updateObjectToDb();

            if($objCommon instanceof class_module_pages_pageelement) {
                $objElement = new class_module_pages_pageelement($this->getSystemid());
                $objElement->setAbsolutePosition($intNewPos);
            }
            else {
                $objCommon->setAbsolutePosition($intNewPos);
            }

            $strReturn .= "<message>".$objCommon->getStrDisplayName()." - ".$this->getLang("setAbsolutePosOk")."</message>";
            $this->flushCompletePagesCache();
        }
        else {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
        }

        return $strReturn;
    }

    /**
     * Changes the status of the current systemid
     *
     * @return string
     * @permissions edit
     */
    protected function actionSetStatus() {
        $strReturn = "";
        $objCommon = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objCommon != null && $objCommon->rightEdit()) {

            $intNewStatus = $this->getParam("status");
            if($intNewStatus == "")
                $intNewStatus = $objCommon->getIntRecordStatus() == 0 ? 1 : 0;

            $objCommon->setIntRecordStatus($intNewStatus);
            $objCommon->updateObjectToDb();
            $strReturn .= "<message>".$objCommon->getStrDisplayName()." - ".$this->getLang("setStatusOk")."<newstatus>".$intNewStatus."</newstatus></message>";
            $this->flushCompletePagesCache();
        }
        else {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
        }

        return $strReturn;
    }


    /**
     * Changes the status of the current systemid
     *
     * @return string
     * @permissions delete
     */
    protected function actionDelete() {
        $strReturn = "";
        $objCommon = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objCommon != null && $objCommon->rightDelete()) {
            $strName = $objCommon->getStrDisplayName();
            if($objCommon->deleteObject()) {
                $strReturn .= "<message>".$strName." - ".$this->getLang("commons_delete_ok")."</message>";
                $this->flushCompletePagesCache();
            }
            else
                $strReturn .= "<error>".$strName." - ".$this->getLang("commons_delete_error")."</error>";
        }
        else {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
        }

        return $strReturn;
    }

    /**
     * Sets the prev-id of a record.
     * expects the param prevId
     *
     * @return string
     * @permissions edit
     */
    protected function actionSetPrevid() {
        $strReturn = "";

        $objRecord = class_objectfactory::getInstance()->getObject($this->getSystemid());
        $strNewPrevId = $this->getParam("prevId");
        //check permissions
        if($objRecord != null && $objRecord->rightEdit() && validateSystemid($strNewPrevId)) {

            if($objRecord->getStrPrevId() != $strNewPrevId) {
                $objRecord->updateObjectToDb($strNewPrevId);
            }

            $strReturn .= "<message>".$objRecord->getStrDisplayName()." - ".$this->getLang("setPrevIdOk")."</message>";
            $this->flushCompletePagesCache();
        }
        else {
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_UNAUTHORIZED);
            $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
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
     * @permissions right2
     */
    protected function actionExecuteSystemTask() {
        $strReturn = "";
        $strTaskOutput = "";

        if($this->getParam("task") != "") {
            //include the list of possible tasks

            //TODO: move to common helper, see class_module_system_admin
            $arrFiles = class_resourceloader::getInstance()->getFolderContent("/admin/systemtasks/", array(".php"), false, function(&$strOneFile) {
                if($strOneFile != "class_systemtask_base.php" && $strOneFile != "interface_admin_systemtask.php") {
                    $strOneFile = uniSubstr($strOneFile, 0, -4);
                    $strOneFile = new $strOneFile();

                    if($strOneFile instanceof interface_admin_systemtask)
                        return true;
                }

                return false;
            });

            //search for the matching task
            /** @var interface_admin_systemtask|class_systemtask_base $objTask */
            foreach($arrFiles as $objTask) {

                //instantiate the current task
                if($objTask->getStrInternalTaskname() == $this->getParam("task")) {

                    class_logger::getInstance(class_logger::ADMINTASKS)->addLogRow("executing task ".$objTask->getStrInternalTaskname(), class_logger::$levelWarning);

                    //let the work begin...
                    $strTempOutput = trim($objTask->executeTask());

                    //progress information?
                    if($objTask->getStrProgressInformation() != "")
                        $strTaskOutput .= $objTask->getStrProgressInformation();

                    if(is_numeric($strTempOutput) && ($strTempOutput >= 0 && $strTempOutput <= 100)) {
                        $strTaskOutput .= "<br />".$this->getLang("systemtask_progress")."<br />".$this->objToolkit->percentBeam($strTempOutput, 400);
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

        return $strReturn;
    }

    /**
     * Fetches the latest entries from the current systemlog.
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
     * @permissions right3
     */
    protected function actionSystemLog() {
        $strReturn = "";

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
            $strSingleRow = uniSubstr($strSingleRow, $intTempPos + 1);

            $intTempPos = uniStrpos($strSingleRow, ")") + 1;
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
            $strReturn .= "\t\t<content>".xmlSafeString(strip_tags($strLogEntry))."</content>\n";

            $strReturn .= "\t</entry>\n";
        }

        $strReturn .= "</entries>";


        return $strReturn;
    }


    /**
     * Generates a xml-based set of information about the current system and evironment
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
     * @permissions edit
     */
    protected function actionSystemInfo() {
        $strReturn = "<info>";

        $objPluginmanager = new class_pluginmanager(interface_systeminfo::STR_EXTENSION_POINT);
        /** @var interface_systeminfo[] $arrPlugins */
        $arrPlugins = $objPluginmanager->getPlugins();

        foreach($arrPlugins as $objOnePlugin) {
            $strReturn .= "<infoset name=\"".$objOnePlugin->getStrTitle()."\">";

            foreach($objOnePlugin->getArrContent() as $arrValue) {
                $strReturn .= "<entry>";
                $strReturn .= "<key>".xmlSafeString($arrValue[0])."</key>";
                $strReturn .= "<value>".xmlSafeString($arrValue[1])."</value>";
                $strReturn .= "</entry>";
            }

            $strReturn .= "</infoset>";
        }

        $strReturn .= "</info>";

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
     * @permissions view
     */
    protected function actionModuleList() {
        $strReturn = "";

        $strReturn .= "<modules>";
        //Loading the modules
        $arrModules = class_module_system_module::getAllModules();
        foreach($arrModules as $objSingleModule) {
            $strReturn .= "<module>";
            $strReturn .= "<name>".xmlSafeString($objSingleModule->getStrName())."</name>";
            $strReturn .= "<version>".xmlSafeString($objSingleModule->getStrVersion())."</version>";
            $strReturn .= "</module>";
        }

        $strReturn .= "</modules>";

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
     * @permissions right1
     */
    protected function actionSystemSessions() {
        $strReturn = "";
        //check needed rights

        $arrSessions = class_module_system_session::getAllActiveSessions();
        $strReturn .= "<sessions>";

        foreach($arrSessions as $objOneSession) {

            $strReturn .= "<session>";

            $strUsername = "";
            if($objOneSession->getStrUserid() != "") {
                $objUser = new class_module_user_user($objOneSession->getStrUserid());
                $strUsername = $objUser->getStrUsername();
            }

            $strLoginStatus = "";
            if($objOneSession->getStrLoginstatus() == class_module_system_session::$LOGINSTATUS_LOGGEDIN)
                $strLoginStatus = $this->getLang("session_loggedin");
            else
                $strLoginStatus = $this->getLang("session_loggedout");

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
                    $strActivity .= class_module_system_setting::getConfigValue("_pages_indexpage_");
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

            $strReturn .= "<username>".xmlSafeString($strUsername)."</username>";
            $strReturn .= "<loginstatus>".xmlSafeString($strLoginStatus)."</loginstatus>";
            $strReturn .= "<releasetime>".xmlSafeString(timeToString($objOneSession->getIntReleasetime()))."</releasetime>";
            $strReturn .= "<activity>".xmlSafeString($strActivity)."</activity>";

            $strReturn .= "</session>";
        }

        $strReturn .= "</sessions>";

        return $strReturn;
    }
}
