<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

namespace Kajona\System\Admin;

use Kajona\Packagemanager\System\PackagemanagerManager;
use Kajona\System\Admin\Formentries\FormentryHidden;
use Kajona\System\Admin\Formentries\FormentryText;
use Kajona\System\Admin\Formentries\FormentryTextarea;
use Kajona\System\Admin\Formentries\FormentryUser;
use Kajona\System\Admin\Systemtasks\AdminSystemtaskInterface;
use Kajona\System\Admin\Systemtasks\SystemtaskBase;
use Kajona\System\System\AdminListableInterface;
use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\ArraySectionIterator;
use Kajona\System\System\Carrier;
use Kajona\System\System\ChangelogContainer;
use Kajona\System\System\Date;
use Kajona\System\System\Exception;
use Kajona\System\System\Filesystem;
use Kajona\System\System\Filters\DeletedRecordsFilter;
use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\HttpStatuscodes;
use Kajona\System\System\Lang;
use Kajona\System\System\Link;
use Kajona\System\System\Lockmanager;
use Kajona\System\System\Logger;
use Kajona\System\System\Mail;
use Kajona\System\System\Model;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\OrmBase;
use Kajona\System\System\OrmDeletedhandlingEnum;
use Kajona\System\System\Pluginmanager;
use Kajona\System\System\Reflection;
use Kajona\System\System\RequestEntrypointEnum;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\StringUtil;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemChangelog;
use Kajona\System\System\SystemChangelogHelper;
use Kajona\System\System\SystemChangelogRestorer;
use Kajona\System\System\SystemCommon;
use Kajona\System\System\SysteminfoInterface;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSession;
use Kajona\System\System\SystemSetting;
use Kajona\System\System\SystemWorker;
use Kajona\System\System\Validators\EmailValidator;
use Kajona\System\System\VersionableInterface;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Shared_Date;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;


/**
 * Class to handle infos about the system and to set systemwide properties
 *
 * @package module_system
 * @author sidler@mulchprod.de
 *
 * @module system
 * @moduleId _system_modul_id_
 *
 * @objectListAspect Kajona\System\System\SystemAspect
 * @objectEditAspect Kajona\System\System\SystemAspect
 * @objectNewAspect Kajona\System\System\SystemAspect
 *
 * @autoTestable listAspect
 */
class SystemAdmin extends AdminEvensimpler implements AdminInterface
{

    /**
     * @return array
     */
    public function getOutputModuleNavi()
    {
        $arrReturn = array();
        $arrReturn[] = array("view", Link::getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("action_list"), "", "", true, "adminnavi"));
        $arrReturn[] = array("edit", Link::getLinkAdmin($this->getArrModule("modul"), "systemInfo", "", $this->getLang("action_system_info"), "", "", true, "adminnavi"));
        $arrReturn[] = array("right1", Link::getLinkAdmin($this->getArrModule("modul"), "systemSettings", "", $this->getLang("action_system_settings"), "", "", true, "adminnavi"));
        $arrReturn[] = array("right2", Link::getLinkAdmin($this->getArrModule("modul"), "systemTasks", "", $this->getLang("action_system_tasks"), "", "", true, "adminnavi"));
        $arrReturn[] = array("right3", Link::getLinkAdmin($this->getArrModule("modul"), "systemlog", "", $this->getLang("action_systemlog"), "", "", true, "adminnavi"));
        if (SystemSetting::getConfigValue("_system_changehistory_enabled_") != "false") {
            $arrReturn[] = array("right3", Link::getLinkAdmin($this->getArrModule("modul"), "genericChangelog", "&bitBlockFolderview=true", $this->getLang("action_changelog"), "", "", true, "adminnavi"));
        }
        $arrReturn[] = array("right5", Link::getLinkAdmin($this->getArrModule("modul"), "listAspect", "", $this->getLang("action_list_aspect"), "", "", true, "adminnavi"));
        $arrReturn[] = array("right1", Link::getLinkAdmin($this->getArrModule("modul"), "systemSessions", "", $this->getLang("action_system_sessions"), "", "", true, "adminnavi"));
        $arrReturn[] = array("right1", Link::getLinkAdmin($this->getArrModule("modul"), "lockedRecords", "", $this->getLang("action_locked_records"), "", "", true, "adminnavi"));
        $arrReturn[] = array("right1", Link::getLinkAdmin($this->getArrModule("modul"), "deletedRecords", "", $this->getLang("action_deleted_records"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("", Link::getLinkAdmin($this->getArrModule("modul"), "about", "", $this->getLang("action_about"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right", Link::getLinkAdmin("right", "change", "&systemid=0", $this->getLang("modul_rechte_root"), "", "", true, "adminnavi"));
        return $arrReturn;
    }

    /**
     * Sets the status of a module.
     * Therefore you have to be member of the admin-group.
     *
     * @permissions edit
     */
    protected function actionModuleStatus()
    {
        //status: for setting the status of modules, you have to be member of the admin-group
        $objModule = new SystemModule($this->getSystemid());
        if ($objModule->rightEdit() && Carrier::getInstance()->getObjSession()->isSuperAdmin()) {
            $objModule->setIntRecordStatus($objModule->getIntRecordStatus() == 0 ? 1 : 0);
            $objModule->updateObjectToDb();
            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul")));
        }
    }



    /**
     * Renders the form to edit an existing entry
     *
     * @return string
     */
    protected function actionEdit()
    {

        $objInstance = Objectfactory::getInstance()->getObject($this->getSystemid());
        if ($objInstance instanceof SystemAspect) {
            $this->setStrCurObjectTypeName("Aspect");
            $this->setCurObjectClassName(SystemAspect::class);
            return parent::actionEdit();
        }

        if ($objInstance instanceof SystemModule) {
            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "list"));
        }
    }


    /**
     * Creates a list of all installed modules
     *
     * @return string
     * @permissions view
     * @autoTestable
     */
    protected function actionList()
    {
        if ($this->getParam("action") == "listAspect") {
            $this->setStrCurObjectTypeName("Aspect");
            $this->setCurObjectClassName(SystemAspect::class);
            return parent::actionList();
        }

        $objIterator = new ArraySectionIterator(SystemModule::getObjectCountFiltered());
        $objIterator->setPageNumber($this->getParam("pv"));
        $objIterator->setArraySection(SystemModule::getAllModules($objIterator->calculateStartPos(), $objIterator->calculateEndPos()));

        return $this->renderList($objIterator, true, "moduleList");

    }

    /**
     * @param Model $objListEntry
     *
     * @return array
     */
    protected function renderAdditionalActions(Model $objListEntry)
    {
        if ($objListEntry instanceof SystemModule) {
            $arrReturn = array();
            $arrReturn[] = $this->objToolkit->listButton(Link::getLinkAdminDialog("system", "moduleAspect", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("modul_aspectedit"), "icon_aspect", $this->getLang("modul_aspectedit")));

            if ($objListEntry->rightEdit() && Carrier::getInstance()->getObjSession()->isSuperAdmin()) {
                if ($objListEntry->getStrName() == "system") {
                    $arrReturn[] = $this->objToolkit->listButton(Link::getLinkAdmin("system", "moduleList", "", "", $this->getLang("modul_status_system"), "icon_enabled"));
                } elseif ($objListEntry->getIntRecordStatus() == 0) {
                    $arrReturn[] = $this->objToolkit->listButton(Link::getLinkAdmin("system", "moduleStatus", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("modul_status_disabled"), "icon_disabled"));
                } else {
                    $arrReturn[] = $this->objToolkit->listButton(Link::getLinkAdmin("system", "moduleStatus", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("modul_status_enabled"), "icon_enabled"));
                }
            }

            return $arrReturn;
        }
        return parent::renderAdditionalActions($objListEntry);
    }

    /**
     * @param Model $objListEntry
     * @param string $strAltActive tooltip text for the icon if record is active
     * @param string $strAltInactive tooltip text for the icon if record is inactive
     *
     * @return string
     */
    protected function renderStatusAction(Model $objListEntry, $strAltActive = "", $strAltInactive = "")
    {
        if ($objListEntry instanceof SystemModule) {
            return "";
        }

        return parent::renderStatusAction($objListEntry, $strAltActive, $strAltInactive);
    }


    /**
     * @param Model $objListEntry
     * @param bool $bitDialog
     *
     * @return string
     */
    protected function renderEditAction(Model $objListEntry, $bitDialog = false)
    {
        if ($objListEntry instanceof SystemModule) {
            return "";
        }

        return parent::renderEditAction($objListEntry);
    }


    /**
     * @param ModelInterface $objListEntry
     *
     * @return string
     */
    protected function renderDeleteAction(ModelInterface $objListEntry)
    {
        if ($objListEntry instanceof SystemModule) {
            return "";
        }

        return parent::renderDeleteAction($objListEntry);
    }

    /**
     * @param string $strListIdentifier
     * @param bool $bitDialog
     *
     * @return array|string
     */
    protected function getNewEntryAction($strListIdentifier, $bitDialog = false)
    {
        if ($strListIdentifier == "moduleList") {
            return "";
        }

        return parent::getNewEntryAction($strListIdentifier);
    }

    /**
     * @param Model $objListEntry
     *
     * @return string
     */
    protected function renderCopyAction(Model $objListEntry)
    {
        return "";
    }


    /**
     * Creates the form to manipulate the aspects of a single module
     *
     * @return string
     * @permissions right5
     */
    protected function actionModuleAspect()
    {
        $this->setArrModuleEntry("template", "/folderview.tpl");
        $strReturn = "";
        $objModule = SystemModule::getModuleBySystemid($this->getSystemid());
        $strReturn .= $this->objToolkit->formHeadline($objModule->getStrName());
        $arrAspectsSet = explode(",", $objModule->getStrAspect());
        $strReturn .= $this->objToolkit->formHeader(Link::getLinkAdminHref($this->getArrModule("modul"), "saveModuleAspect"));
        $arrAspects = SystemAspect::getObjectListFiltered();
        foreach ($arrAspects as $objOneAspect) {
            $strReturn .= $this->objToolkit->formInputCheckbox("aspect_".$objOneAspect->getSystemid(), $objOneAspect->getStrName(), in_array($objOneAspect->getSystemid(), $arrAspectsSet));
        }

        $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getSystemid());
        $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_save"));
        $strReturn .= $this->objToolkit->formClose();

        return $strReturn;
    }

    /**
     * @return string
     * @permissions right5
     */
    protected function actionSaveModuleAspect()
    {
        $arrParams = array();
        foreach ($this->getAllParams() as $strName => $intValue) {
            if (StringUtil::indexOf($strName, "aspect_") !== false) {
                $arrParams[] = StringUtil::substring($strName, 7);
            }
        }

        $objModule = SystemModule::getModuleBySystemid($this->getSystemid());
        $objModule->setStrAspect(implode(",", $arrParams));

        $objModule->updateObjectToDb();
        $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "list", "peClose=1&blockAction=1"));
    }


    /**
     * Shows information about the current system
     *
     * @return string
     * @permissions edit
     */
    protected function actionSystemInfo()
    {
        $strReturn = "";

        $objPluginmanager = new Pluginmanager(SysteminfoInterface::STR_EXTENSION_POINT);
        /** @var SysteminfoInterface[] $arrPlugins */
        $arrPlugins = $objPluginmanager->getPlugins();

        foreach ($arrPlugins as $objOnePlugin) {
            $strContent = $this->objToolkit->dataTable(array(), $objOnePlugin->getArrContent());
            $strReturn .= $this->objToolkit->getFieldset($objOnePlugin->getStrTitle(), $strContent);
        }

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
    protected function actionSystemSettings()
    {
        $strReturn = "";
        //Create a warning before doing s.th.
        $strReturn .= $this->objToolkit->warningBox($this->getLang("warnung_settings"));

        $arrTabs = array();

        $arrSettings = SystemSetting::getObjectListFiltered();
        /** @var SystemModule $objCurrentModule */
        $objCurrentModule = null;
        $strRows = "";
        foreach ($arrSettings as $objOneSetting) {
            if ($objCurrentModule === null || $objCurrentModule->getIntNr() != $objOneSetting->getIntModule()) {
                $objTemp = $this->getModuleDataID($objOneSetting->getIntModule(), true);
                if ($objTemp !== null) {
                    //In the first loop, ignore the output
                    if ($objCurrentModule !== null) {
                        //Build a form to return
                        $strTabContent = $this->objToolkit->formHeader(Link::getLinkAdminHref($this->getArrModule("modul"), "systemSettings"));
                        $strTabContent .= $strRows;
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
            if ($strHelper != "!".$objOneSetting->getStrName()."hint!") {
                $strRows .= $this->objToolkit->formTextRow($strHelper);
            }

            //The input element itself
            if ($objOneSetting->getIntType() == 0) {
                $arrDD = array();
                $arrDD["true"] = $this->getLang("commons_yes");
                $arrDD["false"] = $this->getLang("commons_no");
                $strRows .= $this->objToolkit->formInputDropdown("set[".$objOneSetting->getSystemid()."]", $arrDD, $this->getLang($objOneSetting->getStrName(), $objCurrentModule->getStrName()), $objOneSetting->getStrValue(), "", true, "", "", "", $objOneSetting->getSystemid()."#strValue");
            } elseif ($objOneSetting->getIntType() == 3) {
                $strRows .= $this->objToolkit->formInputPageSelector("set[".$objOneSetting->getSystemid()."]", $this->getLang($objOneSetting->getStrName(), $objCurrentModule->getStrName()), $objOneSetting->getStrValue(), "", false, true, "", $objOneSetting->getSystemid()."#strValue");
            } else {
                $strRows .= $this->objToolkit->formInputText("set[".$objOneSetting->getSystemid()."]", $this->getLang($objOneSetting->getStrName(), $objCurrentModule->getStrName()), $objOneSetting->getStrValue(), "", "", false, $objOneSetting->getSystemid()."#strValue");
            }
        }
        //Build a form to return -> include the last module
        $strTabContent = $this->objToolkit->formHeader(Link::getLinkAdminHref($this->getArrModule("modul"), "systemSettings"));
        $strTabContent .= $strRows;
        $strTabContent .= $this->objToolkit->formClose();

        $arrTabs[$this->getLang("modul_titel", $objCurrentModule->getStrName())] = $strTabContent;

        $strReturn .= $this->objToolkit->getTabbedContent($arrTabs);

        $strReturn .= "<script type='text/javascript'>require(['instantSave'], function(is) {is.init()});</script>";


        return $strReturn;
    }


    /**
     * Loads the list of all systemtasks available and creates the form required to trigger a task
     *
     * @return string
     * @autoTestable
     * @permissions right2
     */
    protected function actionSystemTasks()
    {
        $strReturn = "";
        $strTaskOutput = "";

        //include the list of possible tasks
        $arrFiles = SystemtaskBase::getAllSystemtasks();

        //react on special task-commands?
        if ($this->getParam("task") != "") {
            //search for the matching task
            /** @var $objTask SystemtaskBase */
            foreach ($arrFiles as $objTask) {
                if ($objTask->getStrInternalTaskname() == $this->getParam("task")) {
                    $strTaskOutput .= self::getTaskDialogExecuteCode($this->getParam("execute") == "true", $objTask, "system", "systemTasks", $this->getParam("executedirectly") == "true");
                    break;
                }
            }
        }

        //loop over the found files and group them
        $arrTaskGroups = array();
        /** @var AdminSystemtaskInterface|SystemtaskBase $objTask */
        foreach ($arrFiles as $objTask) {
            if (!isset($arrTaskGroups[$objTask->getGroupIdentifier()])) {
                $arrTaskGroups[$objTask->getGroupIdentifier()] = array();
            }

            $arrTaskGroups[$objTask->getGroupIdentifier()][] = $objTask;
        }

        ksort($arrTaskGroups);

        foreach ($arrTaskGroups as $strGroupName => $arrTasks) {
            if ($strGroupName == "") {
                $strGroupName = "default";
            }


            $strReturn .= $this->objToolkit->formHeadline($this->getLang("systemtask_group_".$strGroupName));
            $strReturn .= $this->objToolkit->listHeader();
            /** @var $objOneTask AdminSystemtaskInterface */
            foreach ($arrTasks as $objOneTask) {
                //generate the link to execute the task
                $strLink = Link::getLinkAdmin(
                    "system",
                    "systemTasks",
                    "&task=".$objOneTask->getStrInternalTaskName(),
                    $objOneTask->getStrTaskname(),
                    $this->getLang("systemtask_run"),
                    "icon_accept"
                );

                $strReturn .= $this->objToolkit->genericAdminList(
                    generateSystemid(),
                    $objOneTask->getStrTaskname(),
                    AdminskinHelper::getAdminImage("icon_systemtask"),
                    $this->objToolkit->listButton($strLink)
                );
            }
            $strReturn .= $this->objToolkit->listFooter();
        }

        //include js-code & stuff to handle executions
        $strReturn .= self::getTaskDialogCode();
        $strReturn = $strTaskOutput.$strReturn;

        return $strReturn;
    }

    /**
     * Renders the code to run and execute a systemtask. You only need this if you want to provide the user an additional place
     * to run a systemtask besides the common place at module system / admin.
     *
     * @param bool $bitExecute
     * @param SystemtaskBase $objTask
     * @param string $strModule
     * @param string $strAction
     * @param bool $bitExecuteDirectly
     *
     * @return string
     */
    public static function getTaskDialogExecuteCode($bitExecute, SystemtaskBase $objTask, $strModule = "", $strAction = "", $bitExecuteDirectly = false)
    {
        $objLang = Carrier::getInstance()->getObjLang();
        $strTaskOutput = "";


        //If the task is going to be executed, validate the form first (if form is of type AdminFormgenerator)
        $objAdminForm = null;
        if ($bitExecute) {
            $objAdminForm = $objTask->getAdminForm();
            if ($objAdminForm !== null && $objAdminForm instanceof AdminFormgenerator) {
                if (!$objAdminForm->validateForm()) {
                    $bitExecute = false;
                }
            }
        }

        //execute the task or show the form?
        if ($bitExecute) {
            if ($bitExecuteDirectly) {
                $strTaskOutput .= $objTask->executeTask();
            } else {
                $strTaskOutput .= "
                <script type=\"text/javascript\">
                require(['systemTask'], function(systemtask) {
                    systemtask.executeTask('".$objTask->getStrInternalTaskname()."', '".$objTask->getSubmitParams()."');
                    systemtask.setName('".$objLang->getLang("systemtask_runningtask", "system")." ".$objTask->getStrTaskName()."');
                });
                </script>";
            }
        } else {
            $strForm = $objTask->generateAdminForm($strModule, $strAction, $objAdminForm);
            if ($strForm != "") {
                $strTaskOutput .= $strForm;
            } else {
                $strLang = Carrier::getInstance()->getObjLang()->getLang("systemtask_runningtask", "system");
                $strTaskJS = <<<JS
                require(['systemTask'], function(systemtask) {
                    systemtask.executeTask('{$objTask->getStrInternalTaskName()}', '');
                    systemtask.setName('{$strLang} {$objTask->getStrTaskName()}');
                });
JS;
                $strTaskOutput .= "<script type='text/javascript'>".$strTaskJS."</script>";
            }
        }

        return $strTaskOutput;
    }

    /**
     * Renders the code to wrap systemtask
     *
     * @return string
     */
    public static function getTaskDialogCode()
    {
        $objLang = Carrier::getInstance()->getObjLang();
        $strDialogContent = "<div id=\"systemtaskLoadingDiv\" class=\"loadingContainer loadingContainerBackground\"></div><br /><b id=\"systemtaskNameDiv\"></b><br /><br /><div id=\"systemtaskStatusDiv\"></div><br /><input id=\"systemtaskCancelButton\" type=\"submit\" value=\"".$objLang->getLang("systemtask_cancel_execution", "system")."\" class=\"btn inputSubmit\" /><br />";
        return "<script type=\"text/javascript\">
            var KAJONA_SYSTEMTASK_TITLE = '".$objLang->getLang("systemtask_dialog_title", "system")."';
            var KAJONA_SYSTEMTASK_TITLE_DONE = '".$objLang->getLang("systemtask_dialog_title_done", "system")."';
            var KAJONA_SYSTEMTASK_CLOSE = '".$objLang->getLang("systemtask_close_dialog", "system")."';
            var kajonaSystemtaskDialogContent = '".$strDialogContent."';
            </script>";
    }

    /**
     * Renders a list of records currently locked
     *
     * @permissions right1
     * @return string
     * @autoTestable
     */
    protected function actionLockedRecords()
    {
        $objArraySectionIterator = new ArraySectionIterator(Lockmanager::getLockedRecordsCount());
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(Lockmanager::getLockedRecords($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        $strReturn = "";
        if (!$objArraySectionIterator->valid()) {
            $strReturn .= $this->getLang("commons_list_empty");
        }

        $strReturn .= $this->objToolkit->listHeader();

        foreach ($objArraySectionIterator as $objOneRecord) {
            $strImage = "";
            if ($objOneRecord instanceof AdminListableInterface) {
                $strImage = $objOneRecord->getStrIcon();
                if (is_array($strImage)) {
                    $strImage = AdminskinHelper::getAdminImage($strImage[0], $strImage[1]);
                } else {
                    $strImage = AdminskinHelper::getAdminImage($strImage);
                }
            }

            $strActions = $this->objToolkit->listButton(Link::getLinkAdmin($this->getArrModule("modul"), "lockedRecords", "&unlockid=".$objOneRecord->getSystemid(), $this->getLang("action_unlock_record"), $this->getLang("action_unlock_record"), "icon_lockerOpen"));
            $objLockUser = Objectfactory::getInstance()->getObject($objOneRecord->getLockManager()->getLockId());

            $strReturn .= $this->objToolkit->genericAdminList(
                $objOneRecord->getSystemid(),
                $objOneRecord instanceof ModelInterface ? $objOneRecord->getStrDisplayName() : get_class($objOneRecord),
                $strImage,
                $strActions,
                get_class($objOneRecord),
                $this->getLang("locked_record_info", array(dateToString(new Date($objOneRecord->getIntLockTime())), $objLockUser->getStrDisplayName()))
            );
        }

        $strReturn .= $this->objToolkit->listFooter();

        $strReturn .= $this->objToolkit->getPageview($objArraySectionIterator, "system", "lockedRecords");

        return $strReturn;
    }

    /**
     * Renders a list of logically deleted records
     *
     * @permissions right1
     * @return string
     * @autoTestable
     */
    protected function actionDeletedRecords()
    {

        $strReturn = "";
        /** @var  DeletedRecordsFilter $objFilter */
        $objFilter = DeletedRecordsFilter::getOrCreateFromSession();
        $strFilterForm = $this->renderFilter($objFilter);
        if ($strFilterForm === AdminFormgeneratorFilter::STR_FILTER_REDIRECT) {
            return "";
        }
        $strReturn .= $strFilterForm;

        $objArraySectionIterator = new ArraySectionIterator(DeletedRecordsFilter::getDeletedRecordsCount($objFilter));
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(DeletedRecordsFilter::getDeletedRecords($objFilter, $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        if (!$objArraySectionIterator->valid()) {
            $strReturn .= $this->getLang("commons_list_empty");
        }

        $strReturn .= $this->objToolkit->listHeader();

        /** @var Model $objOneRecord */
        foreach ($objArraySectionIterator as $objOneRecord) {
            $strImage = "";
            if ($objOneRecord instanceof AdminListableInterface) {
                $strImage = $objOneRecord->getStrIcon();
                if (is_array($strImage)) {
                    $strImage = AdminskinHelper::getAdminImage($strImage[0], $strImage[1]);
                } else {
                    $strImage = AdminskinHelper::getAdminImage($strImage);
                }
            }

            $strActions = "";
            if ($objOneRecord->rightDelete()) {
                $strActions .= $this->objToolkit->listButton(
                    Link::getLinkAdmin($this->getArrModule("modul"), "finalDeleteRecord", "&systemid=".$objOneRecord->getSystemid(), $this->getLang("action_final_delete_record"), $this->getLang("action_final_delete_record"), "icon_delete")
                );
            }

            if ($objOneRecord->isRestorable()) {
                $strActions .= $this->objToolkit->listButton(
                    Link::getLinkAdmin($this->getArrModule("modul"), "restoreRecord", "&systemid=".$objOneRecord->getSystemid(), $this->getLang("action_restore_record"), $this->getLang("action_restore_record"), "icon_undo")
                );
            } else {
                $strActions .= $this->objToolkit->listButton(AdminskinHelper::getAdminImage("icon_undoDisabled", $this->getLang("action_restore_record_blocked")));
            }

            $strReturn .= $this->objToolkit->genericAdminList(
                $objOneRecord->getSystemid(),
                $objOneRecord instanceof ModelInterface ? $objOneRecord->getStrDisplayName() : get_class($objOneRecord),
                $strImage,
                $strActions,
                "Systemid / Previd: ".$objOneRecord->getStrSystemid()." / ".$objOneRecord->getStrPrevId()
            );
        }

        $strReturn .= $this->objToolkit->listFooter();

        $strReturn .= $this->objToolkit->getPageview($objArraySectionIterator, "system", "deletedRecords");

        return $strReturn;
    }

    /**
     * Restores a single object
     *
     * @permissions right1
     * @return string
     * @throws Exception
     */
    protected function actionRestoreRecord()
    {
        OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::INCLUDED);
        $objRecord = Objectfactory::getInstance()->getObject($this->getSystemid());
        if ($objRecord !== null && !$objRecord->isRestorable()) {
            throw new Exception("Record is not restoreable", Exception::$level_ERROR);
        }

        $objRecord->restoreObject();
        $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "deletedRecords"));
        return "";
    }

    /**
     * Restores a single object
     *
     * @permissions right1,delete
     * @return string
     * @throws Exception
     */
    protected function actionFinalDeleteRecord()
    {
        if ($this->getParam("delete") == "") {
            OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::INCLUDED);
            $objRecord = Objectfactory::getInstance()->getObject($this->getSystemid());
            $strReturn = $this->objToolkit->formHeader(Link::getLinkAdminHref($this->getArrModule("modul"), "finalDeleteRecord"));
            $strReturn .= $this->objToolkit->warningBox($this->getLang("final_delete_question", array($objRecord->getStrDisplayName())), "alert-danger");
            $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("final_delete_submit"));
            $strReturn .= $this->objToolkit->formInputHidden("systemid", $this->getParam("systemid"));
            $strReturn .= $this->objToolkit->formInputHidden("delete", "1");
            $strReturn .= $this->objToolkit->formClose();
            return $strReturn;
        } else {
            OrmBase::setObjHandleLogicalDeletedGlobal(OrmDeletedhandlingEnum::INCLUDED);
            $objRecord = Objectfactory::getInstance()->getObject($this->getSystemid());
            if ($objRecord !== null && !$objRecord->rightDelete()) {
                throw new Exception($this->getLang("commons_error_permissions"), Exception::$level_ERROR);
            }

            $objRecord->deleteObjectFromDatabase();
            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "deletedRecords"));
        }
        return "";
    }


    /**
     * Creates a table filled with the sessions currently registered
     *
     * @autoTestable
     * @return string
     * @permissions right1
     */
    protected function actionSystemSessions()
    {
        $strReturn = "";
        //react on commands?
        if ($this->getParam("logout") == "true") {
            $objSession = new SystemSession($this->getSystemid());
            $objSession->setStrLoginstatus(SystemSession::$LOGINSTATUS_LOGGEDOUT);
            $objSession->updateObjectToDb();
            Carrier::getInstance()->getObjDB()->flushQueryCache();
        }

        //showing a list using the pageview
        $objArraySectionIterator = new ArraySectionIterator(SystemSession::getNumberOfActiveSessions());
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(SystemSession::getAllActiveSessions($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        $arrData = array();
        $arrHeader = array();
        $arrHeader[0] = "";
        $arrHeader[1] = $this->getLang("session_username");
        $arrHeader[2] = $this->getLang("session_valid");
        $arrHeader[3] = $this->getLang("session_status");
        $arrHeader[4] = $this->getLang("session_activity");
        $arrHeader[5] = "";
        /** @var $objOneSession SystemSession */
        foreach ($objArraySectionIterator as $objOneSession) {
            $arrRowData = array();
            $strUsername = "";
            if ($objOneSession->getStrUserid() != "") {
                $objUser = Objectfactory::getInstance()->getObject($objOneSession->getStrUserid());
                $strUsername = $objUser->getStrUsername();
            }
            $arrRowData[0] = AdminskinHelper::getAdminImage("icon_user");
            $arrRowData[1] = $strUsername;
            $arrRowData[2] = timeToString($objOneSession->getIntReleasetime());
            if ($objOneSession->getStrLoginstatus() == SystemSession::$LOGINSTATUS_LOGGEDIN) {
                $arrRowData[3] = $this->getLang("session_loggedin");
            } else {
                $arrRowData[3] = $this->getLang("session_loggedout");
            }

            //find out what the user is doing...
            $strLastUrl = $objOneSession->getStrLasturl();
            if (StringUtil::indexOf($strLastUrl, "?") !== false) {
                $strLastUrl = StringUtil::substring($strLastUrl, StringUtil::indexOf($strLastUrl, "?"));
            }
            $strActivity = "";

            if (StringUtil::indexOf($strLastUrl, "admin=1") !== false) {
                $strActivity .= $this->getLang("session_admin");
                foreach (explode("&amp;", $strLastUrl) as $strOneParam) {
                    $arrUrlParam = explode("=", $strOneParam);
                    if ($arrUrlParam[0] == "module") {
                        $strActivity .= $arrUrlParam[1];
                    }
                }
            } else {
                $strActivity .= $this->getLang("session_portal");
                if ($strLastUrl == "") {
                    $strActivity .= SystemSetting::getConfigValue("_pages_indexpage_") != "" ? SystemSetting::getConfigValue("_pages_indexpage_") : "";
                } else {
                    foreach (explode("&amp;", $strLastUrl) as $strOneParam) {
                        $arrUrlParam = explode("=", $strOneParam);
                        if ($arrUrlParam[0] == "page") {
                            $strActivity .= $arrUrlParam[1];
                        }
                    }

                    if ($strActivity == $this->getLang("session_portal") && StringUtil::substring($strLastUrl, 0, 5) == "image") {
                        $strActivity .= $this->getLang("session_portal_imagegeneration");
                    }
                }
            }

            $arrRowData[4] = $strActivity;
            if ($objOneSession->getStrLoginstatus() == SystemSession::$LOGINSTATUS_LOGGEDIN) {
                $arrRowData[5] = Link::getLinkAdmin("system", "systemSessions", "&logout=true&systemid=".$objOneSession->getSystemid(), "", $this->getLang("session_logout"), "icon_delete");
            } else {
                $arrRowData[5] = AdminskinHelper::getAdminImage("icon_deleteDisabled");
            }
            $arrData[] = $arrRowData;
        }
        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrData);
        $strReturn .= $this->objToolkit->getPageview($objArraySectionIterator, "system", "systemSessions");

        return $strReturn;
    }


    /**
     * Fetches the entries from the system-log an prints them as preformatted text
     *
     * @return string
     * @autoTestable
     * @permissions right3
     */
    protected function actionSystemlog()
    {

        //load logfiles available
        $objFilesystem = new Filesystem();
        $arrFiles = $objFilesystem->getFilelist(_projectpath_."/log", array(".log"));

        $arrTabs = array();

        foreach ($arrFiles as $strName) {
            $objFilesystem->openFilePointer(_projectpath_."/log/".$strName, "r");
            $strLogContent = $objFilesystem->readLastLinesFromFile(20);
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
     * @permissions changelog
     */
    public function actionGenericChangelog($strSystemid = "", $strSourceModule = "system", $strSourceAction = "genericChangelog", $bitBlockFolderview = false)
    {

        if (!$bitBlockFolderview && $this->getParam("bitBlockFolderview") == "") {
            $this->setArrModuleEntry("template", "/folderview.tpl");
        }

        if ($strSystemid == "") {
            $strSystemid = $this->getSystemid();
        }

        if (!validateSystemid($strSystemid) && $this->getObjModule()->rightChangelog()) {
            $strReturn = $this->objToolkit->warningBox($this->getLang("generic_changelog_no_systemid"));
            $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref("system", "genericChangeLog", "bitBlockFolderview=1"));
            $strReturn .= $this->objToolkit->formInputText("systemid", "systemid");
            $strReturn .= $this->objToolkit->formInputSubmit();
            $strReturn .= $this->objToolkit->formClose();

            return $strReturn;
        }

        /** @var VersionableInterface $objObject */
        $objObject = Objectfactory::getInstance()->getObject($strSystemid);

        if (!$objObject instanceof VersionableInterface) {
            return $this->objToolkit->warningBox($this->getLang("generic_changelog_not_versionable"));
        }

        $strReturn = "";
        //showing a list using the pageview
        $objArraySectionIterator = new ArraySectionIterator(SystemChangelog::getLogEntriesCount($strSystemid));
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(SystemChangelog::getLogEntries($strSystemid, $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        $arrData = array();
        $arrHeader = array();
        $arrHeader[] = $this->getLang("commons_date");
        $arrHeader[] = $this->getLang("change_user");
        if ($strSystemid == "") {
            $arrHeader[] = $this->getLang("change_module");
        }
        if ($strSystemid == "") {
            $arrHeader[] = $this->getLang("change_record");
        }
        $arrHeader[] = $this->getLang("change_action");
        $arrHeader[] = $this->getLang("change_property");
        $arrHeader[] = $this->getLang("change_oldvalue");
        $arrHeader[] = $this->getLang("change_newvalue");

        /** @var $objOneEntry ChangelogContainer */
        foreach ($objArraySectionIterator as $objOneEntry) {
            $arrRowData = array();

            /** @var VersionableInterface|Model $objTarget */
            $objTarget = $objOneEntry->getObjTarget();

            $strOldValue = $objOneEntry->getStrOldValue();
            $strNewValue = $objOneEntry->getStrNewValue();

            //render some properties directly
            if (in_array($objOneEntry->getStrProperty(), array("rightView", "rightEdit", "rightDelete", "rightRight", "rightRight1", "rightRight2", "rightRight3", "rightRight4", "rightRight5", "rightChangelog", "strPrevId", "strOwner"))) {
                $strOldValue = SystemChangelogHelper::getStrValueForObjects($strOldValue);
                $strNewValue = SystemChangelogHelper::getStrValueForObjects($strNewValue);
            } elseif ($objTarget != null) {
                $strOldValue = $objTarget->renderVersionValue($objOneEntry->getStrProperty(), $strOldValue);
                $strNewValue = $objTarget->renderVersionValue($objOneEntry->getStrProperty(), $strNewValue);
            }

            $strOldValue = htmlStripTags($strOldValue);
            $strNewValue = htmlStripTags($strNewValue);

            $arrRowData[] = dateToString($objOneEntry->getObjDate());
            $arrRowData[] = $this->objToolkit->getTooltipText(StringUtil::truncate($objOneEntry->getStrUsername(), 15), $objOneEntry->getStrUsername());
            if ($strSystemid == "") {
                $arrRowData[] = $objTarget != null ? $objTarget->getArrModule("modul") : "";
            }
            if ($strSystemid == "") {
                $arrRowData[] = $objTarget != null ? $this->objToolkit->getTooltipText(StringUtil::truncate($objTarget->getVersionRecordName(), 20), $objTarget->getVersionRecordName()." ".$objOneEntry->getStrSystemid()) : "";
            }
            $arrRowData[] = $objTarget != null ? $this->objToolkit->getTooltipText(StringUtil::truncate($objTarget->getVersionActionName($objOneEntry->getStrAction()), 15), $objTarget->getVersionActionName($objOneEntry->getStrAction())) : "";
            $arrRowData[] = $objTarget != null ? $this->objToolkit->getTooltipText(StringUtil::truncate($objTarget->getVersionPropertyName($objOneEntry->getStrProperty()), 20), $objTarget->getVersionPropertyName($objOneEntry->getStrProperty())) : "";
            $arrRowData[] = $this->objToolkit->getTooltipText(StringUtil::truncate($strOldValue, 20), $strOldValue);
            $arrRowData[] = $this->objToolkit->getTooltipText(StringUtil::truncate($strNewValue, 20), $strNewValue);

            $arrData[] = $arrRowData;
        }

        $objManager = new PackagemanagerManager();
        $arrToolbar = array();
        if ($objManager->getPackage("phpexcel") != null) {
            $arrToolbar[] = Link::getLinkAdmin($this->getArrModule("modul"), "genericChangelogExportExcel", "&systemid=".$strSystemid, AdminskinHelper::getAdminImage("icon_excel")." ".$this->getLang("change_export_excel"), "", "", false);
        }

        $arrToolbar[] = Link::getLinkAdmin($this->getArrModule("modul"), "changelogDiff", "&systemid=".$strSystemid."&bitBlockFolderview=".$this->getParam("bitBlockFolderview"), AdminskinHelper::getAdminImage("icon_aspect")." ".$this->getLang("change_diff"), "", "", false);

        $strReturn .= $this->objToolkit->getContentToolbar($arrToolbar);

        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrData);

        $strReturn .= $this->objToolkit->getPageview($objArraySectionIterator, $strSourceModule, $strSourceAction, "&systemid=".$strSystemid."&bitBlockFolderview=".$this->getParam("bitBlockFolderview"));

        return $strReturn;
    }

    /**
     * Provides an option to compare the current record with a state from a different time
     *
     * @permissions changelog
     * @since 5.1
     * @return string
     */
    protected function actionChangelogDiff()
    {

        if ($this->getParam("bitBlockFolderview") == "") {
            $this->setArrModuleEntry("template", "/folderview.tpl");
        }

        $strSystemId = $this->getSystemid();
        /** @var VersionableInterface $objObject */
        $objObject = Objectfactory::getInstance()->getObject($strSystemId);

        if (!$objObject instanceof VersionableInterface) {
            return $this->objToolkit->warningBox($this->getLang("generic_changelog_not_versionable"));
        }

        $objNow = new Date();
        $objNow->setEndOfDay();
        $objYearAgo = new Date();
        $objYearAgo->setPreviousYear()->setEndOfDay();

        $arrDates = SystemChangelog::getDatesForSystemid($strSystemId, $objYearAgo, $objNow);

        $arrResult = array();
        foreach ($arrDates as $arrDate) {
            $objDate = new Date($arrDate["change_date"]);
            $arrResult[substr($objDate->getLongTimestamp(), 0, 8)] = $objDate->getLongTimestamp();
        }
        ksort($arrResult);

        $objRightDate = new Date(array_pop($arrResult));
        $strRightDate = $objRightDate->setEndOfDay()->getLongTimestamp();
        $objLeftDate = new Date(array_pop($arrResult));
        $strLeftDate = $objLeftDate->setEndOfDay()->getLongTimestamp();

        $strReturn = "";
        $strReturn .= $this->objToolkit->getContentToolbar(array(
            Link::getLinkAdmin($this->getArrModule("modul"), "genericChangelog", "&systemid=".$objObject->getStrSystemid()."&bitBlockFolderview=".$this->getParam("bitBlockFolderview"), AdminskinHelper::getAdminImage("icon_history")." ".$this->getLang("commons_edit_history"), "", "", false),
        ));

        $arrTemplate = array(
            "strSystemId"   => $strSystemId,
            "strLeftDate"   => $strLeftDate,
            "strRightDate"  => $strRightDate,
            "strDateFormat" => $this->getLang("dateStyleShort"),
            "strLang"       => json_encode(array(
                "months"            => $this->getLang("toolsetCalendarMonthShort"),
                "days"              => $this->getLang("toolsetCalendarWeekdayShort"),
                "tooltipUnit"       => $this->getLang("changelog_tooltipUnit"),
                "tooltipUnitPlural" => $this->getLang("changelog_tooltipUnitPlural"),
                "tooltipHtml"       => $this->getLang("changelog_tooltipHtml"),
                "tooltipColumn"     => $this->getLang("changelog_tooltipColumn"),
            )),
        );

        $strReturn .= $this->objTemplate->fillTemplateFile($arrTemplate, "/elements.tpl", "changelog_heatmap");

        $objReflection = new Reflection($objObject);
        $arrProps = $objReflection->getPropertiesWithAnnotation(SystemChangelog::ANNOTATION_PROPERTY_VERSIONABLE);
        $arrData = array();

        foreach ($arrProps as $strPropertyName => $strValue) {
            $strGetter = $objReflection->getGetter($strPropertyName);
            if (!empty($strGetter)) {
                $strPropertyLabel = $objObject->getVersionPropertyName($strPropertyName);

                $arrRow = array();
                $arrRow['0 border-right'] = $strPropertyLabel;
                $arrRow['1 border-right'] = "<div id='property_".$strPropertyName."_left' class='changelog_property changelog_property_left' data-name='".$strPropertyName."'></div>";
                $arrRow[] = "<div id='property_".$strPropertyName."_right' class='changelog_property changelog_property_right' data-name='".$strPropertyName."'></div>";
                $arrData[] = $arrRow;
            }
        }

        $arrHeader = array(
            '0 border-right'                     => $this->getLang("change_property"),
            '1 border-right" style="width:30%;"' => "<div id='date_left'></div>",
            '2" style="width:30%;"'              => "<div id='date_right'></div>",
        );

        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrData);

        return $strReturn;
    }

    /**
     * Generates an excel sheet based on the changelog entries from the given systemid
     *
     * @param string $strSystemid
     *
     * @since 4.6.6
     * @permissions changelog
     * @return string
     */
    protected function actionGenericChangelogExportExcel($strSystemid = "")
    {

        $objManager = new PackagemanagerManager();
        if ($objManager->getPackage("phpexcel") == null) {
            return $this->getLang("commons_error_permissions");
        }
        // include phpexcel
        require_once Resourceloader::getInstance()->getAbsolutePathForModule("module_phpexcel").'/vendor/autoload.php';
        $objPHPExcel = new PHPExcel();

        // get system id
        if ($strSystemid == "") {
            $strSystemid = $this->getSystemid();
        }

        // get data
        $arrLogEntries = SystemChangelog::getLogEntries($strSystemid);

        // create excel
        $objPHPExcel->getProperties()->setCreator("Kajona")
            ->setLastModifiedBy(Carrier::getInstance()->getObjSession()->getUsername())
            ->setTitle($this->getLang("change_report_title"))
            ->setSubject($this->getLang("change_report_title"));

        $objDataSheet = $objPHPExcel->getActiveSheet();
        $objDataSheet->setTitle($this->getLang("change_report_title"));
        $objDataSheet->setAutoFilter('A1:F'.(count($arrLogEntries) + 1));

        // style
        $arrStyles = $this->getStylesArray();

        $objDataSheet->getStyle("A1:F1")->applyFromArray($arrStyles["header_1"]);
        $objDataSheet->getDefaultColumnDimension()->setWidth(24);

        // add header
        $arrHeader = array();
        $arrHeader[] = $this->getLang("commons_date");
        $arrHeader[] = $this->getLang("change_user");
        if ($strSystemid == "") {
            $arrHeader[] = $this->getLang("change_module");
        }
        if ($strSystemid == "") {
            $arrHeader[] = $this->getLang("change_record");
        }
        $arrHeader[] = $this->getLang("change_action");
        $arrHeader[] = $this->getLang("change_property");
        $arrHeader[] = $this->getLang("change_oldvalue");
        $arrHeader[] = $this->getLang("change_newvalue");

        $intCol = 0;
        $intRow = 1;

        foreach ($arrHeader as $strHeader) {
            $objDataSheet->setCellValueByColumnAndRow($intCol++, $intRow, $strHeader);
        }

        $intRow++;

        // add body
        $arrData = array();

        /** @var $objOneEntry ChangelogContainer */
        foreach ($arrLogEntries as $objOneEntry) {
            $arrRowData = array();

            /** @var VersionableInterface|Model $objTarget */
            $objTarget = $objOneEntry->getObjTarget();

            $strOldValue = $objOneEntry->getStrOldValue();
            $strNewValue = $objOneEntry->getStrNewValue();

            if ($objTarget != null) {
                $strOldValue = $objTarget->renderVersionValue($objOneEntry->getStrProperty(), $strOldValue);
                $strNewValue = $objTarget->renderVersionValue($objOneEntry->getStrProperty(), $strNewValue);
            }

            $strOldValue = htmlStripTags($strOldValue);
            $strNewValue = htmlStripTags($strNewValue);

            $arrRowData[] = PHPExcel_Shared_Date::PHPToExcel($objOneEntry->getObjDate()->getTimeInOldStyle());
            $arrRowData[] = $objOneEntry->getStrUsername();
            if ($strSystemid == "") {
                $arrRowData[] = $objTarget != null ? $objTarget->getArrModule("modul") : "";
            }
            if ($strSystemid == "") {
                $arrRowData[] = $objTarget != null ? $objTarget->getVersionRecordName()." ".$objOneEntry->getStrSystemid() : "";
            }
            $arrRowData[] = $objTarget != null ? $objTarget->getVersionActionName($objOneEntry->getStrAction()) : "";
            $arrRowData[] = $objTarget != null ? $objTarget->getVersionPropertyName($objOneEntry->getStrProperty()) : "";
            $arrRowData[] = $strOldValue;
            $arrRowData[] = $strNewValue;

            $arrData[] = $arrRowData;
        }

        foreach ($arrData as $arrRow) {
            $intCol = 0;
            foreach ($arrRow as $strValue) {
                $objDataSheet->setCellValueByColumnAndRow($intCol++, $intRow, html_entity_decode(strip_tags($strValue), ENT_COMPAT, "UTF-8"));
            }

            // format first column as date
            $objDataSheet->getStyle('A'.$intRow)->getNumberFormat()->setFormatCode('dd.mm.yyyy hh:mm');

            $intRow++;
        }

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.createFilename($this->getLang("change_report_title").'.xlsx').'"');
        header('Pragma: private');
        header('Cache-control: private, must-revalidate');
        //header('Cache-Control : No Store');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        //and pass everything back to the browser
        $objWriter->save('php://output');
        flush();
        die();
    }


    /**
     * About Kajona, credits and co
     *
     * @return string
     * @autoTestable
     * @permissions view
     */
    protected function actionAbout()
    {
        $strReturn = "";
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("about_part1"));
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("about_part2_header"));
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("about_part2"));
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("about_part2a_header"));
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("about_part2a"));
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("about_part2b_header"));
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("about_part2b"));
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("about_part5_header"));
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("about_part5"));
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("about_part3_header"));
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("about_part3"));
        $strReturn .= $this->objToolkit->getTextRow($this->getLang("about_part4"));
        return $strReturn;
    }


    /**
     * Creates a form to send mails to specific users.
     *
     * @return AdminFormgenerator
     */
    private function getMailForm()
    {
        $objFormgenerator = new AdminFormgenerator("mail", new SystemCommon());
        $objFormgenerator->addField(new FormentryText("mail", "recipient"))->setStrLabel($this->getLang("mail_recipient"))->setBitMandatory(true)->setObjValidator(new EmailValidator());
        $objFormgenerator->addField(new FormentryUser("mail", "cc"))->setStrLabel($this->getLang("mail_cc"));
        $objFormgenerator->addField(new FormentryText("mail", "subject"))->setStrLabel($this->getLang("mail_subject"))->setBitMandatory(true);
        $objFormgenerator->addField(new FormentryTextarea("mail", "body"))->setStrLabel($this->getLang("mail_body"))->setBitMandatory(true);
        return $objFormgenerator;
    }


    /**
     * Generates a form in order to send an email.
     * This form is generic, so it may be called from several places.
     * If a mail-address was passed by param "mail_recipient", the form tries to send the message by mail,
     * otherwise (default) the message is delivered using the messaging. Therefore the param mail_to_id is expected when being
     * triggered externally.
     *
     * @param AdminFormgenerator $objForm
     *
     * @return string
     * @since 3.4
     * @autoTestable
     * @permissions view
     */
    protected function actionMailForm(AdminFormgenerator $objForm = null)
    {
        $this->setArrModuleEntry("template", "/folderview.tpl");

        if ($objForm == null) {
            $objForm = $this->getMailForm();
        }

        return $objForm->renderForm(Link::getLinkAdminHref($this->getArrModule("modul"), "sendMail"));
    }

    /**
     * Sends an email. In most cases this mail was generated using the form
     * provided by actionMailForm
     *
     * @return string
     * @since 3.4
     * @permissions view
     */
    protected function actionSendMail()
    {

        $objForm = $this->getMailForm();

        if (!$objForm->validateForm()) {
            return $this->actionMailForm($objForm);
        }

        $this->setArrModuleEntry("template", "/folderview.tpl");
        $objUser = $this->objSession->getUser();

        //mail or internal message?
        $objMailValidator = new EmailValidator();
        $objEmail = new Mail();

        $objEmail->setSender($objUser->getStrEmail());
        $arrRecipients = explode(",", $this->getParam("mail_recipient"));
        foreach ($arrRecipients as $strOneRecipient) {
            if ($objMailValidator->validate($strOneRecipient)) {
                $objEmail->addTo($strOneRecipient);
            }
        }

        if ($objForm->getField("mail_cc")->getStrValue() != "") {
            $objUser = Objectfactory::getInstance()->getObject($objForm->getField("mail_cc")->getStrValue());
            $objEmail->addCc($objUser->getStrEmail());
        }

        $objEmail->setSubject($objForm->getField("mail_subject")->getStrValue());
        $objEmail->setText($objForm->getField("mail_body")->getStrValue());

        if ($objEmail->sendMail()) {
            return $this->getLang("mail_send_success");
        } else {
            return $this->getLang("mail_send_error");
        }
    }


    /**
     * Loads the data for one module
     *
     * @param int $intModuleID
     * @param bool $bitZeroIsSystem
     *
     * @return SystemModule
     */
    private function getModuleDataID($intModuleID, $bitZeroIsSystem = false)
    {
        $arrModules = SystemModule::getAllModules();

        if ($intModuleID != 0 || !$bitZeroIsSystem) {
            foreach ($arrModules as $objOneModule) {
                if ($objOneModule->getIntNr() == $intModuleID) {
                    return $objOneModule;
                }
            }
        } elseif ($intModuleID == 0 && $bitZeroIsSystem) {
            foreach ($arrModules as $objOneModule) {
                if ($objOneModule->getStrName() == "system") {
                    return $objOneModule;
                }
            }
        }
        return null;
    }

    /**
     * Returns the style parameters for the changelog excel export
     *
     * @return array
     */
    private function getStylesArray()
    {
        $arrStlyes = array();

        $arrStlyes["header_1"] = array(
            'fill'      => array(
                'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array('rgb' => 'EBF1DE'),
                'endcolor'   => array('rgb' => 'EBF1DE')
            ),
            'borders'   => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => '000000'
                    )
                ),
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_BOTTOM,
                'rotation'   => 0,
                'wrap'       => true
            )
        );

        return $arrStlyes;
    }

    /**
     * Unlocks a record if currently locked by the current user
     *
     * @return string
     */
    protected function actionUnlockRecord()
    {
        $objRecord = Objectfactory::getInstance()->getObject($this->getSystemid());

        if ($objRecord !== null) {
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

        $objObject = Objectfactory::getInstance()->getObject($this->getSystemid());
        $intNewPos = $this->getParam("listPos");
        //check permissions
        if ($objObject != null && $objObject->rightEdit() && $intNewPos != "") {
            //store edit date
            $objObject->updateObjectToDb();
            $objObject->setAbsolutePosition($intNewPos);
            $strReturn .= "<message>".$objObject->getStrDisplayName()." - ".$this->getLang("setAbsolutePosOk")."</message>";
            $this->flushCompletePagesCache();
        } else {
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

            try {
                $objCommon->setIntRecordStatus($intNewStatus);
                $objCommon->updateObjectToDb();
                $strReturn .= "<message>".$objCommon->getStrDisplayName()." - ".$this->getLang("setStatusOk")."<newstatus>".$intNewStatus."</newstatus></message>";
                $this->flushCompletePagesCache();
            } catch (\Exception $objE) {
                ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_INTERNAL_SERVER_ERROR);
                $strReturn .= "<message><error>".xmlSafeString($objE->getMessage())."</error></message>";
            }
        } else {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_FORBIDDEN);
            $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
        }

        return $strReturn;
    }

    /**
     * Updates a single property of an obejct. used by the js-insite-editor.
     * @permissions edit
     * @return string
     */
    protected function actionUpdateObjectProperty()
    {
        //get the object to update
        $objObject = Objectfactory::getInstance()->getObject($this->getSystemid());
        if ($objObject->rightEdit()) {
            //any other object - try to find the matching property and write the value
            if ($this->getParam("property") == "") {
                ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_BADREQUEST);
                return "<message><error>missing property param</error></message>";
            }

            $objReflection = new Reflection($objObject);
            $strSetter = $objReflection->getSetter($this->getParam("property"));
            if ($strSetter == null) {
                ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_BADREQUEST);
                return "<message><error>setter not found</error></message>";
            }

            $objObject->{$strSetter}($this->getParam("value"));
            if ($objObject->updateObjectToDb()) {
                $strReturn = "<message><success>object update succeeded</success></message>";
            } else {
                $strReturn = "<message><error>object update failed</error></message>";
            }

        } else {
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_UNAUTHORIZED);
            $strReturn = "<message><error>".$this->getLang("ds_gesperrt").".".$this->getLang("commons_error_permissions")."</error></message>";
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
        if (ResponseObject::getInstance()->getObjEntrypoint()->equals(RequestEntrypointEnum::XML())) {
            $strReturn = "";
            $objCommon = Objectfactory::getInstance()->getObject($this->getSystemid());
            if ($objCommon != null && $objCommon->rightDelete() && $objCommon->getLockManager()->isAccessibleForCurrentUser()) {
                $strName = $objCommon->getStrDisplayName();
                if ($objCommon->deleteObject()) {
                    $strReturn .= "<message>".$strName." - ".$this->getLang("commons_delete_ok")."</message>";
                    $this->flushCompletePagesCache();
                } else {
                    $strReturn .= "<error>".$strName." - ".$this->getLang("commons_delete_error")."</error>";
                }
            } else {
                ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_FORBIDDEN);
                $strReturn .= "<message><error>".xmlSafeString($this->getLang("commons_error_permissions"))."</error></message>";
            }

            return $strReturn;
        } else {
            parent::actionDelete();
        }
        return "";
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
        } else {
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
                    Logger::getInstance(Logger::ADMINTASKS)->warning("executing task ".$objTask->getStrInternalTaskname());

                    //let the work begin...
                    $strTempOutput = trim($objTask->executeTask());

                    //progress information?
                    if ($objTask->getStrProgressInformation() != "") {
                        $strTaskOutput .= $objTask->getStrProgressInformation();
                    }

                    if (is_numeric($strTempOutput) && ($strTempOutput >= 0 && $strTempOutput <= 100)) {
                        $strTaskOutput .= "<br />".$this->getLang("systemtask_progress")."<br />".$this->objToolkit->percentBeam($strTempOutput);
                    } else {
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
     * Returns all properties for the given module
     *
     * @return string
     * @responseType json
     */
    public function actionFetchProperty()
    {
        $strTargetModule = $this->getParam("target_module");
        $strReturn = Lang::getInstance()->getProperties($strTargetModule);

        return json_encode($strReturn);
    }

    /**
     * Returns the properties of an object for a specific date json encoded
     *
     * @return string
     * @permissions changelog
     * @throws Exception
     * @responseType json
     */
    protected function actionChangelogPropertiesForDate()
    {
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
                    $strValue = $objObject->$strGetter();
                    if (is_array($strValue)) {
                        $strValue = implode(", ", $strValue);
                    }
                    $arrData[$strPropertyName] = strval($objObject->renderVersionValue($strPropertyName, $strValue));
                }
            }

            return json_encode(array(
                "systemid"   => $objObject->getStrSystemid(),
                "date"       => date("d.m.Y", $strDate->getTimeInOldStyle()),
                "properties" => $arrData,
            ));
        } else {
            throw new Exception("Invalid object type", Exception::$level_ERROR);
        }
    }

    /**
     * @permissions changelog
     * @since 5.1
     * @return string
     * @responseType json
     */
    protected function actionChangelogChartData()
    {
        $objNow = new Date($this->getParam("now"));
        $objYearAgo = new Date($this->getParam("yearAgo"));
        $strSystemId = $this->getSystemid();

        $arrDates = SystemChangelog::getDatesForSystemid($strSystemId, $objYearAgo, $objNow);

        $arrResult = array();
        $arrChart = array();
        foreach ($arrDates as $arrDate) {
            $objDate = new Date($arrDate["change_date"]);
            $strDate = substr($objDate->getLongTimestamp(), 0, 8);
            $arrResult[$objDate->getLongTimestamp()] = date("d.m.Y", $objDate->getTimeInOldStyle());
            if (isset($arrChart[$strDate])) {
                $arrChart[$strDate]++;
            } else {
                $arrChart[$strDate] = 1;
            }
        }

        return json_encode($arrChart);
    }
}
