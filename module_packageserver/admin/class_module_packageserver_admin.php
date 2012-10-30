<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * Admin-GUI of the packageserver.
 * Provides all interfaces to manage the packages available for other systems
 *
 * @package module_packageserver
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_module_packageserver_admin extends class_module_mediamanager_admin implements interface_admin {

    /**
     * Constructor
     */
    public function __construct() {

        parent::__construct();
        $this->setStrLangBase("packageserver");
        $this->setArrModuleEntry("modul", "packageserver");
        $this->setArrModuleEntry("moduleId", _packageserver_module_id_);
    }

    public function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("actionList"), "", "", true, "adminnavi"));
        $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "logs", "", $this->getLang("actionLogs"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));

        return $arrReturn;
    }

    /**
     * Generic list of all packages available on the local filesystem
     * @return string
     * @permissions view
     * @autoTestable
     */
    protected function actionList() {
        return $this->actionOpenFolder();
    }


    /**
     * Generic list of all packages available on the local filesystem
     * @return string
     * @permissions view
     * @autoTestable
     */
    protected function actionOpenFolder() {

        if(validateSystemid(_packageserver_repo_id_)) {
            if($this->getSystemid() == "")
                $this->setSystemid(_packageserver_repo_id_);

            $objIterator = new class_array_section_iterator(class_module_mediamanager_file::getFileCount($this->getSystemid(), false, false, true));
            $objIterator->setPageNumber($this->getParam("pv"));
            $objIterator->setArraySection(class_module_mediamanager_file::loadFilesDB($this->getSystemid(), false, false, $objIterator->calculateStartPos(), $objIterator->calculateEndPos(), true));

        }
        else {
            $objIterator = new class_array_section_iterator(class_module_mediamanager_file::getFlatPackageListCount(false, false));
            $objIterator->setPageNumber($this->getParam("pv"));
            $objIterator->setArraySection(class_module_mediamanager_file::getFlatPackageList(false, false, $objIterator->calculateStartPos(), $objIterator->calculateEndPos(), true));
        }

        return $this->renderList($objIterator);
    }


    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {
        return "";
    }

    protected function renderEditAction(class_model $objListEntry, $bitDialog = false) {
        return "";
    }

    protected function renderAdditionalActions(class_model $objListEntry) {

        if($objListEntry instanceof class_module_mediamanager_file && $objListEntry->getIntType() == class_module_mediamanager_file::$INT_TYPE_FOLDER)
            return array($this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "openFolder", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("actionOpenFolder", "mediamanager"), "icon_folderActionOpen.png")));


        else if($objListEntry instanceof class_module_mediamanager_file && $objListEntry->getIntType() == class_module_mediamanager_file::$INT_TYPE_FILE) {
            return array(
                $this->objToolkit->listButton(
                    getLinkAdmin($this->getArrModule("modul"), "showInfo", "&systemid=".$objListEntry->getSystemid(), $this->getLang("package_info"), $this->getLang("package_info"), "icon_lens.png")
                )
            );
        }

        return array();
    }


    /**
     * @return string
     * @permissions edit
     */
    protected function actionEdit() {
        return $this->getLang("commons_error_permissions");
    }

    /**
     *
     * @return string
     * @permissions edit
     */
    protected function actionNew() {
        return $this->getLang("commons_error_permissions");
    }

    /**
     * Creates a small print-view of the current package, rendering all relevant key-value-pairs
     * @permissions view
     * @return string
     */
    protected function actionShowInfo() {
        $strReturn = "";

        /** @var $objPackage class_module_mediamanager_file */
        $objPackage = class_objectfactory::getInstance()->getObject($this->getSystemid());
        if($objPackage instanceof class_module_mediamanager_file && $objPackage->rightView()) {

            $objManager = new class_module_packagemanager_manager();
            $objHandler = $objManager->getPackageManagerForPath($objPackage->getStrFilename());


            $strReturn .= $this->objToolkit->formHeadline($objHandler->getObjMetadata()->getStrTitle());
            $strReturn .= $this->objToolkit->getTextRow($objHandler->getObjMetadata()->getStrDescription());
            $strReturn .= $this->objToolkit->getTextRow($this->getLang("package_type")." ".$objHandler->getObjMetadata()->getStrType());
            $strReturn .= $this->objToolkit->getTextRow($this->getLang("package_version")." ".$objHandler->getObjMetadata()->getStrVersion());
            $strReturn .= $this->objToolkit->getTextRow($this->getLang("package_author")." ".$objHandler->getObjMetadata()->getStrAuthor());
            $strReturn .= $this->objToolkit->getTextRow($this->getLang("package_modules"));
            foreach($objHandler->getObjMetadata()->getArrRequiredModules() as $strOneModule => $strVersion) {
                $strReturn .= $this->objToolkit->getTextRow($strOneModule." >= ".$strVersion);
            }

        }

        return $strReturn;

    }


    /**
     * Show a log of all queries
     *
     * @return string
     * @permissions edit
     * @autoTestable
     */
    protected function actionLogs() {
        $strReturn = "";

        $intNrOfRecordsPerPage = 25;

        $objLog = new class_module_packageserver_log();
        $objArraySectionIterator = new class_array_section_iterator($objLog->getLogDataCount());
        $objArraySectionIterator->setIntElementsPerPage($intNrOfRecordsPerPage);
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection($objLog->getLogData($objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos()));

        $arrPageViews = $this->objToolkit->getSimplePageview($objArraySectionIterator, $this->getArrModule("modul"), "logs");

        $arrLogsRaw = $arrPageViews["elements"];
        $arrLogs = array();
        foreach($arrLogsRaw as $intKey => $arrOneLog) {
            $arrLogs[$intKey][0] = dateToString(new class_date($arrOneLog["log_date"]));
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
        $strReturn .= $arrPageViews["pageview"];

        return $strReturn;
    }



    /**
     * Generates a path-navigation
     *
     * @return array
     */
    protected function getArrOutputNaviEntries() {
        $arrEntries = class_admin::getArrOutputNaviEntries();

        $arrPath = $this->getPathArray();

        array_shift($arrPath);

        foreach($arrPath as $strOneSystemid) {
            $objPoint = class_objectfactory::getInstance()->getObject($strOneSystemid);
            $arrEntries[] = getLinkAdmin($this->getArrModule("modul"), "openFolder", "&systemid=".$strOneSystemid, $objPoint->getStrDisplayName());
        }

        return $arrEntries;

    }
}
