<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$							*
********************************************************************************************************/

namespace Kajona\System\Admin;

use Kajona\Pages\System\PagesPageelement;
use Kajona\System\Admin\Systemtasks\AdminSystemtaskInterface;
use Kajona\System\Admin\Systemtasks\SystemtaskBase;
use Kajona\System\System\Date;
use Kajona\System\System\Exception;
use Kajona\System\System\Filesystem;
use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\HttpStatuscodes;
use Kajona\System\System\Lang;
use Kajona\System\System\Logger;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\Pluginmanager;
use Kajona\System\System\Reflection;
use Kajona\System\System\ReflectionEnum;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\SystemChangelog;
use Kajona\System\System\SystemChangelogRestorer;
use Kajona\System\System\SysteminfoInterface;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSession;
use Kajona\System\System\SystemSetting;
use Kajona\System\System\UserUser;
use Kajona\System\System\VersionableInterface;


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
class SystemAdminXml extends AdminController implements XmlAdminInterface
{

    /**
     * Unlocks a record if currently locked by the current user
     * @return string
     */
    protected function actionUnlockRecord()
    {
        $objRecord = Objectfactory::getInstance()->getObject($this->getSystemid());

        if($objRecord !== null) {
            $objLockmanager = $objRecord->getLockManager();
            if ($objLockmanager->unlockRecord()) {
                return "<ok></ok>";
            }
        }
        ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_FORBIDDEN);
        return "<error></error>";
    }


    /**
     * Updates the aboslute position of a single record, relative to its siblings
     *
     * @return string
     * @permissions edit
     */
    protected function actionSetAbsolutePosition()
    {
        $strReturn = "";

        $objCommon = Objectfactory::getInstance()->getObject($this->getSystemid());
        $intNewPos = $this->getParam("listPos");
        //check permissions
        if ($objCommon != null && $objCommon->rightEdit() && $intNewPos != "") {

            //there is a different mode for page-elements, catch now
            //store edit date
            $objCommon->updateObjectToDb();

            if ($objCommon instanceof PagesPageelement) {
                $objElement = new PagesPageelement($this->getSystemid());
                $objElement->setAbsolutePosition($intNewPos);
            }
            else {
                $objCommon->setAbsolutePosition($intNewPos);
            }

            $strReturn .= "<message>".$objCommon->getStrDisplayName()." - ".$this->getLang("setAbsolutePosOk")."</message>";
            $this->flushCompletePagesCache();
        }
        else {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_UNAUTHORIZED);
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
    protected function actionSetStatus()
    {
        $strReturn = "";
        $objCommon = Objectfactory::getInstance()->getObject($this->getSystemid());
        if ($objCommon != null && $objCommon->rightEdit()) {

            $intNewStatus = $this->getParam("status");
            if ($intNewStatus == "") {
                $intNewStatus = $objCommon->getIntRecordStatus() == 0 ? 1 : 0;
            }

            $objCommon->setIntRecordStatus($intNewStatus);
            $objCommon->updateObjectToDb();
            $strReturn .= "<message>".$objCommon->getStrDisplayName()." - ".$this->getLang("setStatusOk")."<newstatus>".$intNewStatus."</newstatus></message>";
            $this->flushCompletePagesCache();
        }
        else {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_FORBIDDEN);
            $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
        }

        return $strReturn;
    }


    /**
     * Deletes are record identified by its systemid
     *
     * @return string
     * @permissions delete
     */
    protected function actionDelete()
    {
        $strReturn = "";
        $objCommon = Objectfactory::getInstance()->getObject($this->getSystemid());
        if ($objCommon != null && $objCommon->rightDelete() && $objCommon->getLockManager()->isAccessibleForCurrentUser()) {
            $strName = $objCommon->getStrDisplayName();
            if ($objCommon->deleteObject()) {
                $strReturn .= "<message>".$strName." - ".$this->getLang("commons_delete_ok")."</message>";
                $this->flushCompletePagesCache();
            }
            else {
                $strReturn .= "<error>".$strName." - ".$this->getLang("commons_delete_error")."</error>";
            }
        }
        else {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_FORBIDDEN);
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
    protected function actionSetPrevid()
    {
        $strReturn = "";

        $objRecord = Objectfactory::getInstance()->getObject($this->getSystemid());
        $strNewPrevId = $this->getParam("prevId");
        //check permissions
        if ($objRecord != null && $objRecord->rightEdit() && validateSystemid($strNewPrevId)) {

            if ($objRecord->getStrPrevId() != $strNewPrevId) {
                $objRecord->updateObjectToDb($strNewPrevId);
            }

            $strReturn .= "<message>".$objRecord->getStrDisplayName()." - ".$this->getLang("setPrevIdOk")."</message>";
            $this->flushCompletePagesCache();
        }
        else {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_FORBIDDEN);
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
     */
    protected function actionExecuteSystemTask()
    {
        $strReturn = "";
        $strTaskOutput = "";

        if ($this->getParam("task") != "") {
            //include the list of possible tasks
            $arrFiles = SystemtaskBase::getAllSystemtasks();

            //search for the matching task
            /** @var AdminSystemtaskInterface|SystemtaskBase $objTask */
            foreach ($arrFiles as $objTask) {

                //instantiate the current task
                if ($objTask->getStrInternalTaskname() == $this->getParam("task")) {

                    Logger::getInstance(Logger::ADMINTASKS)->addLogRow("executing task ".$objTask->getStrInternalTaskname(), Logger::$levelWarning);

                    //let the work begin...
                    $strTempOutput = trim($objTask->executeTask());

                    //progress information?
                    if ($objTask->getStrProgressInformation() != "") {
                        $strTaskOutput .= $objTask->getStrProgressInformation();
                    }

                    if (is_numeric($strTempOutput) && ($strTempOutput >= 0 && $strTempOutput <= 100)) {
                        $strTaskOutput .= "<br />".$this->getLang("systemtask_progress")."<br />".$this->objToolkit->percentBeam($strTempOutput);
                    }
                    else {
                        $strTaskOutput .= $strTempOutput;
                    }

                    //create response-content
                    $strReturn .= "<statusinfo>".$strTaskOutput."</statusinfo>\n";

                    //reload requested by worker?
                    if ($objTask->getStrReloadUrl() != "") {
                        $strReturn .= "<reloadurl>".("&task=".$this->getParam("task").$objTask->getStrReloadParam())."</reloadurl>";
                    }

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
    protected function actionSystemLog()
    {
        $strReturn = "";

        $intStartDate = false;
        if ($this->getParam("latestEntry") != "") {
            $intStartDate = strtotime($this->getParam("latestEntry"));
        }

        //read the last few lines
        $objFile = new Filesystem();
        $arrDetails = $objFile->getFileDetails("/system/debug/systemlog.log");

        $intOffset = 0;
        $bitSkip = false;
        if ($arrDetails["filesize"] > 20000) {
            $intOffset = $arrDetails["filesize"] - 20000;
            $bitSkip = true;
        }

        $objFile->openFilePointer("/system/debug/systemlog.log", "r");

        //forward to the new offset, skip entry
        if ($intOffset > 0) {
            $objFile->setFilePointerOffset($intOffset);
        }

        $arrRows = array();

        $strRow = $objFile->readLineFromFile();
        while ($strRow !== false) {
            if (!$bitSkip && trim($strRow) > 0) {
                $arrRows[] = $strRow;
            }

            $bitSkip = false;
            $strRow = $objFile->readLineFromFile();
        }

        $objFile->closeFilePointer();

        $strReturn .= "<entries>\n";
        $arrRows = array_reverse($arrRows);
        foreach ($arrRows as $strSingleRow) {

            //parse entry
            $strDate = uniSubstr($strSingleRow, 0, 19);
            $strSingleRow = uniSubstr($strSingleRow, 20);

            $intTempPos = uniStrpos($strSingleRow, " ");
            $strLevel = uniSubstr($strSingleRow, 0, $intTempPos);
            $strSingleRow = uniSubstr($strSingleRow, $intTempPos + 1);

            $intTempPos = uniStrpos($strSingleRow, ")") + 1;
            $strSession = uniSubstr($strSingleRow, 0, $intTempPos);

            $strLogEntry = uniSubstr($strSingleRow, $intTempPos);

            if ($intStartDate !== false) {
                $intCurDate = strtotime($strDate);
                if ($intStartDate >= $intCurDate) {
                    continue;
                }
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
    protected function actionSystemInfo()
    {
        $strReturn = "<info>";

        $objPluginmanager = new Pluginmanager(SysteminfoInterface::STR_EXTENSION_POINT);
        /** @var SysteminfoInterface[] $arrPlugins */
        $arrPlugins = $objPluginmanager->getPlugins();

        foreach ($arrPlugins as $objOnePlugin) {
            $strReturn .= "<infoset name=\"".$objOnePlugin->getStrTitle()."\">";

            foreach ($objOnePlugin->getArrContent() as $arrValue) {
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
    protected function actionModuleList()
    {
        $strReturn = "";

        $strReturn .= "<modules>";
        //Loading the modules
        $arrModules = SystemModule::getAllModules();
        foreach ($arrModules as $objSingleModule) {
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
    protected function actionSystemSessions()
    {
        $strReturn = "";
        //check needed rights

        $arrSessions = SystemSession::getAllActiveSessions();
        $strReturn .= "<sessions>";

        foreach ($arrSessions as $objOneSession) {

            $strReturn .= "<session>";

            $strUsername = "";
            if ($objOneSession->getStrUserid() != "") {
                $objUser = Objectfactory::getInstance()->getObject($objOneSession->getStrUserid());
                $strUsername = $objUser->getStrUsername();
            }

            $strLoginStatus = "";
            if ($objOneSession->getStrLoginstatus() == SystemSession::$LOGINSTATUS_LOGGEDIN) {
                $strLoginStatus = $this->getLang("session_loggedin");
            }
            else {
                $strLoginStatus = $this->getLang("session_loggedout");
            }

            //find out what the user is doing...
            $strLastUrl = $objOneSession->getStrLasturl();
            if (uniStrpos($strLastUrl, "?") !== false) {
                $strLastUrl = uniSubstr($strLastUrl, uniStrpos($strLastUrl, "?"));
            }
            $strActivity = "";

            if (uniStrpos($strLastUrl, "admin=1") !== false) {
                $strActivity .= $this->getLang("session_admin");
                foreach (explode("&amp;", $strLastUrl) as $strOneParam) {
                    $arrUrlParam = explode("=", $strOneParam);
                    if ($arrUrlParam[0] == "module") {
                        $strActivity .= $arrUrlParam[1];
                    }
                }
            }
            else {
                $strActivity .= $this->getLang("session_portal");
                if ($strLastUrl == "") {
                    $strActivity .= SystemSetting::getConfigValue("_pages_indexpage_");
                }
                else {
                    foreach (explode("&amp;", $strLastUrl) as $strOneParam) {
                        $arrUrlParam = explode("=", $strOneParam);
                        if ($arrUrlParam[0] == "page") {
                            $strActivity .= $arrUrlParam[1];
                        }
                    }

                    if ($strActivity == $this->getLang("session_portal") && uniSubstr($strLastUrl, 0, 5) == "image") {
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

    /**
     * Returns all properties for the given module
     *
     * @return string
     */
    public function actionFetchProperty() {
        ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_JSON);

        $strTargetModule = $this->getParam("target_module");
        $strReturn = Lang::getInstance()->getProperties($strTargetModule);

        return json_encode($strReturn);
    }

    /**
     * Returns the properties of an object for a specific date json encoded
     *
     * @return string
     * @permissions view
     * @throws Exception
     */
    protected function actionChangelogPropertiesForDate()
    {
        ResponseObject::getInstance()->setStrResponseType(HttpResponsetypes::STR_TYPE_JSON);

        $objObject = Objectfactory::getInstance()->getObject($this->getSystemid());
        $strDate = new Date($this->getParam("date"));

        if ($objObject instanceof VersionableInterface) {
            $objChangelog = new SystemChangelogRestorer();
            $objChangelog->restoreObject($objObject, $strDate);

            $objReflection = new Reflection($objObject);
            $arrProps = $objReflection->getPropertiesWithAnnotation(SystemChangelog::ANNOTATION_PROPERTY_VERSIONABLE);
            $arrData = array();

            foreach ($arrProps as $strPropertyName => $strValue) {
                $strGetter = $objReflection->getGetter($strPropertyName);
                if (!empty($strGetter)) {
                    $arrData[$strPropertyName] = $objObject->renderVersionValue($strPropertyName, $objObject->$strGetter());
                }
            }

            return json_encode(array(
                "systemid" => $objObject->getStrSystemid(),
                "date" => date("d.m.Y", $strDate->getTimeInOldStyle()),
                "properties" => $arrData,
            ));
        } else {
            throw new Exception("Invalid object type", Exception::$level_ERROR);
        }
    }
}
