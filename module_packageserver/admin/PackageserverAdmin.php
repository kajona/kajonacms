<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

namespace Kajona\Packageserver\Admin;

use Kajona\Mediamanager\Admin\MediamanagerAdmin;
use Kajona\Mediamanager\System\MediamanagerFile;
use Kajona\Packagemanager\Admin\PackagemanagerAdmin;
use Kajona\Packagemanager\System\PackagemanagerManager;
use Kajona\Packagemanager\System\PackagemanagerMetadata;
use Kajona\Packageserver\System\PackageserverLog;
use Kajona\System\Admin\AdminBatchaction;
use Kajona\System\Admin\AdminController;
use Kajona\System\Admin\AdminInterface;
use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\ArraySectionIterator;
use Kajona\System\System\Link;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\StringUtil;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;
use Kajona\System\System\Zip;


/**
 * Admin-GUI of the packageserver.
 * Provides all interfaces to manage the packages available for other systems
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 *
 * @module packageserver
 * @moduleId _packageserver_module_id_
 */
class PackageserverAdmin extends MediamanagerAdmin implements AdminInterface
{


    /**
     * @return array
     */
    public function getOutputModuleNavi()
    {
        $arrReturn = array();
        $arrReturn[] = array("view", Link::getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("action_list"), "", "", true, "adminnavi"));
        $arrReturn[] = array("edit", Link::getLinkAdmin($this->getArrModule("modul"), "logs", "", $this->getLang("action_logs"), "", "", true, "adminnavi"));

        return $arrReturn;
    }

    /**
     * Generic list of all packages available on the local filesystem
     *
     * @return string
     * @permissions view
     * @autoTestable
     */
    protected function actionList()
    {
        return $this->actionOpenFolder();
    }


    /**
     * Generic list of all packages available on the local filesystem
     *
     * @return string
     * @permissions view
     * @autoTestable
     */
    protected function actionOpenFolder()
    {

        if (validateSystemid(SystemSetting::getConfigValue("_packageserver_repo_id_"))) {
            if ($this->getSystemid() == "") {
                $this->setSystemid(SystemSetting::getConfigValue("_packageserver_repo_id_"));
            }

            $objIterator = new ArraySectionIterator(MediamanagerFile::getFileCount($this->getSystemid(), false, false, true));
            $objIterator->setPageNumber($this->getParam("pv"));
            $objIterator->setArraySection(MediamanagerFile::loadFilesDB($this->getSystemid(), false, false, $objIterator->calculateStartPos(), $objIterator->calculateEndPos(), true));

        }
        else {
            $objIterator = new ArraySectionIterator(MediamanagerFile::getFlatPackageListCount(false, false));
            $objIterator->setPageNumber($this->getParam("pv"));
            $objIterator->setArraySection(MediamanagerFile::getFlatPackageList(false, false, $objIterator->calculateStartPos(), $objIterator->calculateEndPos()));
        }

        return $this->renderList($objIterator);
    }


    /**
     * @param string $strListIdentifier
     * @param bool $bitDialog
     *
     * @return string
     */
    protected function getNewEntryAction($strListIdentifier, $bitDialog = false)
    {
        return "";
    }

    /**
     * @param \Kajona\System\System\Model $objListEntry
     * @param bool $bitDialog
     *
     * @return string
     */
    protected function renderEditAction(\Kajona\System\System\Model $objListEntry, $bitDialog = false)
    {
        return "";
    }

    /**
     * @param \Kajona\System\System\Model $objListEntry
     *
     * @return array
     */
    protected function renderAdditionalActions(\Kajona\System\System\Model $objListEntry)
    {

        if ($objListEntry instanceof MediamanagerFile && $objListEntry->getIntType() == MediamanagerFile::$INT_TYPE_FOLDER) {
            return array(
                $this->objToolkit->listButton(Link::getLinkAdmin($this->getArrModule("modul"), "openFolder", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("action_open_folder", "mediamanager"), "icon_folderActionOpen"))
            );
        }


        elseif ($objListEntry instanceof MediamanagerFile && $objListEntry->getIntType() == MediamanagerFile::$INT_TYPE_FILE) {
            return array(
                $this->objToolkit->listButton(
                    Link::getLinkAdminDialog($this->getArrModule("modul"), "showInfo", "&systemid=".$objListEntry->getSystemid(), $this->getLang("package_info"), $this->getLang("package_info"), "icon_lens", $objListEntry->getStrDisplayName())
                )
            );
        }

        return array();
    }


    /**
     * @return string
     * @permissions edit
     */
    protected function actionEdit()
    {
        return $this->getLang("commons_error_permissions");
    }

    /**
     * Not supported
     *
     * @return string
     * @permissions edit
     */
    protected function actionNew()
    {
        return $this->getLang("commons_error_permissions");
    }

    /**
     * Creates a small print-view of the current package, rendering all relevant key-value-pairs
     *
     * @permissions view
     * @return string
     */
    protected function actionShowInfo()
    {
        $strReturn = "";
        $this->setArrModuleEntry("template", "/folderview.tpl");

        /** @var $objPackage MediamanagerFile */
        $objPackage = Objectfactory::getInstance()->getObject($this->getSystemid());
        if ($objPackage instanceof MediamanagerFile && $objPackage->rightView()) {

            $objManager = new PackagemanagerManager();
            $objHandler = $objManager->getPackageManagerForPath($objPackage->getStrFilename());

            /** @var PackagemanagerAdmin $objAdmin */
            $objAdmin = SystemModule::getModuleByName("packagemanager")->getAdminInstanceOfConcreteModule();
            $strReturn .= $objAdmin->renderPackageDetails($objHandler);
        }

        return $strReturn;

    }

    /**
     * Copies the metadata.xml content into the files properties.
     *
     * @permissions edit
     * @return string
     */
    protected function actionUpdateDataFromMetadata()
    {
        $objPackage = new MediamanagerFile($this->getSystemid());
        //updateObjectToDb triggers the update of the isPackage and the category flags
        $objPackage->updateObjectToDb();
        return "<message><success /></message>";
    }


    /**
     * Show a log of all queries
     *
     * @return string
     * @permissions edit
     * @autoTestable
     */
    protected function actionLogs()
    {
        $strReturn = "";

        $intNrOfRecordsPerPage = 25;

        $objLog = new PackageserverLog();
        $objArraySectionIterator = new ArraySectionIterator($objLog->getLogDataCount());
        $objArraySectionIterator->setIntElementsPerPage($intNrOfRecordsPerPage);
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection($objLog->getLogData($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        $arrLogs = array();
        foreach ($objArraySectionIterator as $intKey => $arrOneLog) {
            $arrLogs[$intKey][0] = dateToString(new \Kajona\System\System\Date($arrOneLog["log_date"]));
            $arrLogs[$intKey][1] = $arrOneLog["log_ip"];
            $arrLogs[$intKey][2] = $arrOneLog["log_hostname"];
            $arrLogs[$intKey][3] = $arrOneLog["log_query"];
        }
        //Create a data-table
        $arrHeader = array();
        $arrHeader[0] = $this->getLang("commons_date");
        $arrHeader[1] = $this->getLang("header_ip");
        $arrHeader[2] = $this->getLang("header_hostname");
        $arrHeader[3] = $this->getLang("header_query");
        $strReturn .= $this->objToolkit->dataTable($arrHeader, $arrLogs);
        $strReturn .= $this->objToolkit->getPageview($objArraySectionIterator, $this->getArrModule("modul"), "logs");

        return $strReturn;
    }


    /**
     * @param string $strListIdentifier
     *
     * @return array
     */
    protected function getBatchActionHandlers($strListIdentifier)
    {
        $arrDefault = array();
        $arrDefault[] = new AdminBatchaction(AdminskinHelper::getAdminImage("icon_text"), Link::getLinkAdminXml("packageserver", "updateDataFromMetadata", "&systemid=%systemid%"), $this->getLang("batchaction_metadata"));
        return $arrDefault;
    }

    /**
     * Generates a path-navigation
     *
     * @return array
     */
    protected function getArrOutputNaviEntries()
    {
        $arrEntries = AdminController::getArrOutputNaviEntries();

        $arrPath = $this->getPathArray();
        array_shift($arrPath);

        foreach ($arrPath as $strOneSystemid) {
            $objPoint = Objectfactory::getInstance()->getObject($strOneSystemid);
            $arrEntries[] = Link::getLinkAdmin($this->getArrModule("modul"), "openFolder", "&systemid=".$strOneSystemid, $objPoint->getStrDisplayName());
        }

        return $arrEntries;

    }
}
