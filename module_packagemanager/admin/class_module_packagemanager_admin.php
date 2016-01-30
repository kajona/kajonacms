<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/
use Kajona\System\System\StringUtil;

/**
 * Admin-GUI of the packagemanager.
 * The packagemanager provides a way to handle the template-packs available.
 * In addition, setting packs as the current active-one is supported, too.
 *
 * @package module_packagemanager
 * @author sidler@mulchprod.de
 * @since 4.0
 *
 * @module packagemanager
 * @moduleId _packagemanager_module_id_
 */
class class_module_packagemanager_admin extends class_admin_simple implements interface_admin
{

    private $STR_FILTER_SESSION_KEY = "PACKAGELIST_FILTER_SESSION_KEY";

    /**
     * @return array
     */
    public function getOutputModuleNavi()
    {
        $arrReturn = array();
        $arrReturn[] = array("view", class_link::getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("action_list"), "", "", true, "adminnavi"));
        $arrReturn[] = array("view", class_link::getLinkAdmin($this->getArrModule("modul"), "listTemplates", "", $this->getLang("action_list_templates"), "", "", true, "adminnavi"));
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

        if ($this->getParam("doFilter") != "") {
            $this->objSession->setSession($this->STR_FILTER_SESSION_KEY, $this->getParam("packagelist_filter"));
            $this->setParam("pv", 1);

            $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul"), "list"));
            return "";
        }

        class_module_packagemanager_template::syncTemplatepacks();

        $strReturn = "";
        $strReturn .= $this->objToolkit->formHeader(class_link::getLinkAdminHref($this->getArrModule("modul")), "list");
        $strReturn .= $this->objToolkit->formInputText("packagelist_filter", $this->getLang("packagelist_filter"), $this->objSession->getSession($this->STR_FILTER_SESSION_KEY));
        $strReturn .= $this->objToolkit->formInputSubmit();
        $strReturn .= $this->objToolkit->formInputHidden("doFilter", "1");
        $strReturn .= $this->objToolkit->formClose();


        $objManager = new class_module_packagemanager_manager();
        $arrPackages = $objManager->getAvailablePackages($this->objSession->getSession($this->STR_FILTER_SESSION_KEY));
        $arrPackages = $objManager->sortPackages($arrPackages);


        $objArrayIterator = new class_array_iterator($arrPackages);
        $objArrayIterator->getElementsOnPage((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));

        $objArraySectionIterator = new class_array_section_iterator(count($arrPackages));
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection($objArrayIterator->getElementsOnPage((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1)));

        $strReturn .= $this->objToolkit->listHeader();
        $intI = 0;
        /** @var class_module_packagemanager_metadata $objOneMetadata */
        foreach ($objArraySectionIterator as $objOneMetadata) {

            $strActions = "";
            $objHandler = $objManager->getPackageManagerForPath($objOneMetadata->getStrPath());

            if ($objHandler->isInstallable()) {
                $strActions .= $this->objToolkit->listButton(
                    class_link::getLinkAdminDialog(
                        $this->getArrModule("modul"),
                        "processPackage",
                        "&package=".$objOneMetadata->getStrPath(),
                        $this->getLang("package_install"),
                        $this->getLang("package_installocally"),
                        "icon_downloads",
                        $this->getLang("package_install")
                    )
                );
            }

            if(!$objOneMetadata->getBitIsPhar()) {
                $strActions .= $this->objToolkit->listButton(
                    class_link::getLinkAdmin($this->getArrModule("modul"), "downloadAsPhar", "&package=".$objOneMetadata->getStrTitle(), $this->getLang("package_downloadasphar"), $this->getLang("package_downloadasphar"), "icon_downloads")
                );
            }

            $strActions .= $this->objToolkit->listButton(
                class_link::getLinkAdminDialog($this->getArrModule("modul"), "showInfo", "&package=".$objOneMetadata->getStrTitle(), $this->getLang("package_info"), $this->getLang("package_info"), "icon_lens", $objOneMetadata->getStrTitle())
            );

            if ($this->getObjModule()->rightDelete()) {
                if ($objHandler->isRemovable($objOneMetadata)) {
                    $strActions .= $this->objToolkit->listDeleteButton($objOneMetadata->getStrTitle(), $this->getLang("package_delete_question"), class_link::getLinkAdminHref($this->getArrModule("modul"), "deletePackage", "&package=".$objOneMetadata->getStrTitle()));
                }
                else {
                    $strActions .= $this->objToolkit->listButton(class_adminskin_helper::getAdminImage("icon_deleteLocked", $this->getLang("package_delete_locked")));
                }
            }


            $strActions .= $this->objToolkit->listButton(
                "<span id=\"updateWrapper".createFilename($objOneMetadata->getStrTitle(), true)."\">".class_adminskin_helper::getAdminImage("loadingSmall", $this->getLang("package_searchupdate"))."</span>"
            );
            $strActions .= "<script type='text/javascript'>
            $(function() {
                KAJONA.admin.loader.loadFile('".class_resourceloader::getInstance()->getCorePathForModule("module_packagemanager")."/module_packagemanager/admin/scripts/packagemanager.js', function() {
                    KAJONA.admin.packagemanager.addPackageToTest('".$objOneMetadata->getStrTitle()."', '".createFilename($objOneMetadata->getStrTitle(), true)."');
                }); });
            </script>";

            $strReturn .= $this->objToolkit->simpleAdminList($objOneMetadata, $strActions, $intI++);
        }

        $strAddActions = "";
        if ($this->getObjModule()->rightEdit()) {
            $strAddActions = $this->objToolkit->listButton(
                class_link::getLinkAdminDialog($this->getArrModule("modul"), "addPackage", "", $this->getLang("action_upload_package"), $this->getLang("action_upload_package"), "icon_new", $this->getLang("action_upload_package"))
            );
        }
        $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), "", "", $strAddActions, $intI);

        $strReturn .= $this->objToolkit->listFooter();

        $strCore = class_resourceloader::getInstance()->getCorePathForModule("module_packagemanager");
        $strReturn .= "<script type='text/javascript'>
            $(function() {
                KAJONA.admin.loader.loadFile('{$strCore}/module_packagemanager/admin/scripts/packagemanager.js', function() {

                    $(window.setTimeout(function() {
                        KAJONA.admin.packagemanager.triggerUpdateCheck();
                    }, 1000));
                });
            });
            </script>";

        $strReturn .= $this->objToolkit->getPageview($objArraySectionIterator, $this->getArrModule("modul"), $this->getAction());

        return $strReturn;
    }


    /**
     * Renders the summary of a single package
     *
     * @permissions view
     * @return string
     */
    protected function actionShowInfo()
    {
        $this->setArrModuleEntry("template", "/folderview.tpl");
        $objManager = new class_module_packagemanager_manager();
        $objHandler = $objManager->getPackage($this->getParam("package"));
        if ($objHandler !== null) {
            return $this->renderPackageDetails($objManager->getPackageManagerForPath($objHandler->getStrPath()), true);
        }

        return "";
    }


    /**
     * Checks if an update is available for a list of packages.
     * Renders the matching icon and tooltip or the link to update a package.
     *
     * @xml
     * @permissions view,edit
     * @return string
     */
    protected function actionGetUpdateIcons()
    {

        $strPackages = $this->getParam("packages");
        $arrPackagesToCheck = explode(",", $strPackages);
        $objManager = new class_module_packagemanager_manager();

        //close session to avoid blocking
        $this->objSession->sessionClose();
        $arrLatestVersion = $objManager->scanForUpdates();

        $arrReturn = array();
        foreach ($arrPackagesToCheck as $strOnePackage) {
            $objMetadata = $objManager->getPackage($strOnePackage);

            if ($objMetadata == null || !isset($arrLatestVersion[$strOnePackage])) {
                $arrReturn[$strOnePackage] = class_adminskin_helper::getAdminImage("icon_updateError", $this->getLang("package_noversion"));
                continue;
            }


            $objHandler = $objManager->getPackageManagerForPath($objMetadata->getStrPath());
            $bitUpdateAvailable = $objManager->updateAvailable($objHandler, $arrLatestVersion[$strOnePackage]);

            if ($bitUpdateAvailable === null) {
                $arrReturn[$strOnePackage] = class_adminskin_helper::getAdminImage("icon_updateError", $this->getLang("package_noversion"));
            }
            else {
                //compare the version to trigger additional actions
                $strLatestVersion = $arrLatestVersion[$strOnePackage];
                if ($bitUpdateAvailable) {
                    $arrReturn[$strOnePackage] = class_link::getLinkAdminDialog(
                        $this->getArrModule("modul"),
                        "initPackageUpdate",
                        "&package=".$objHandler->getObjMetadata()->getStrPath(),
                        $this->getLang("package_updatefound")." ".$strLatestVersion,
                        $this->getLang("package_updatefound")." ".$strLatestVersion,
                        "icon_update",
                        $objHandler->getObjMetadata()->getStrTitle()
                    );
                }
                else {
                    $arrReturn[$strOnePackage] = class_adminskin_helper::getAdminImage("icon_updateDisabled", $this->getLang("package_noupdate")." ".$strLatestVersion);
                }
            }
        }

        class_response_object::getInstance()->setStrResponseType(class_http_responsetypes::STR_TYPE_JSON);
        return json_encode($arrReturn);
    }

    /**
     * Validates a local package, renders the metadata
     * and provides, if feasible, a button to start the installation.
     *
     * @permissions edit
     * @return string
     */
    protected function actionProcessPackage()
    {
        $this->setArrModuleEntry("template", "/folderview.tpl");

        $strReturn = "";
        $strFile = $this->getParam("package");

        $objManager = new class_module_packagemanager_manager();
        $objHandler = $objManager->getPackageManagerForPath($strFile);

        if ($objManager->validatePackage($strFile)) {
            $strReturn .= $this->renderPackageDetails($objHandler);

            if (!$objHandler->getObjMetadata()->getBitProvidesInstaller() || $objHandler->isInstallable()) {

                $arrNotWritable = array();
                if ($objHandler->getVersionInstalled() != null) {
                    $strReturn .= $this->objToolkit->getTextRow($this->getLang("package_target_writable")." ".$objHandler->getStrTargetPath());
                    $this->checkWritableRecursive($objHandler->getStrTargetPath(), $arrNotWritable);
                }
                else {
                    $strReturn .= $this->objToolkit->getTextRow($this->getLang("package_target_writable")." ".dirname($objHandler->getStrTargetPath()));
                    if (!is_writable(_realpath_.dirname($objHandler->getStrTargetPath()))) {
                        $arrNotWritable[] = dirname($objHandler->getStrTargetPath());
                    }
                }

                if (count($arrNotWritable) > 0) {
                    $strWarning = $this->getLang("package_target_nonwritablelist");
                    $strWarning .= "<ul>";
                    foreach ($arrNotWritable as $strOnePath) {
                        $strWarning .= "<li>".$strOnePath."</li>";
                    }
                    $strWarning .= "</ul>";

                    $strReturn .= $this->objToolkit->warningBox($strWarning);
                }

                $strReturn .= $this->objToolkit->formHeader(class_link::getLinkAdminHref($this->getArrModule("modul"), "installPackage"));
                $strReturn .= $this->objToolkit->formInputHidden("package", $strFile);
                $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("package_doinstall"));
                $strReturn .= $this->objToolkit->formClose();

            }
            else {
                $strWarningText = $this->getLang("package_notinstallable");
                if ($objHandler->getVersionInstalled() != null) {
                    if ($objHandler->getVersionInstalled() == $objHandler->getObjMetadata()->getStrVersion()) {
                        $strWarningText .= "<br />".$this->getLang("package_noinstall_installed");
                    }
                }

                $strReturn .= $this->objToolkit->warningBox($strWarningText);
            }

        }
        else {
            $strError = $this->getLang("provider_error_package");
            $strError .= class_link::getLinkAdminManual('href=\'javascript:history.back();\'', $this->getLang('back'));
            $strReturn .= $this->objToolkit->warningBox($strError);
        }

        return $strReturn;
    }

    /**
     * Renders the summary of a single package
     *
     * @param interface_packagemanager_packagemanager $objHandler
     * @param bool $bitIncludeRequiredBy
     *
     * @return string
     */
    public function renderPackageDetails(interface_packagemanager_packagemanager $objHandler, $bitIncludeRequiredBy = false)
    {
        $objManager = new class_module_packagemanager_manager();

        $strReturn = $this->objToolkit->formHeadline($objHandler->getObjMetadata()->getStrTitle());
        $strReturn .= $this->objToolkit->getTextRow($objHandler->getObjMetadata()->getStrDescription());

        $arrRows = array();
        $arrRows[] = array($this->getLang("package_type"), $this->getLang("type_".$objHandler->getObjMetadata()->getStrType()));
        $arrRows[] = array($this->getLang("package_version"), $objHandler->getObjMetadata()->getStrVersion());

        if ($objHandler->getVersionInstalled() != null) {
            $arrRows[] = array($this->getLang("package_version_installed"), $objHandler->getVersionInstalled());
        }
        $arrRows[] = array($this->getLang("package_author"), $objHandler->getObjMetadata()->getStrAuthor());

        $arrRequiredRows = array();
        foreach ($objHandler->getObjMetadata()->getArrRequiredModules() as $strOneModule => $strVersion) {
            $strStatus = "";

            //validate the status
            $objRequired = $objManager->getPackage($strOneModule);
            if ($objRequired == null) {
                $strStatus = "<span class=\"label label-important\">".$this->getLang("package_missing")."</span>";
            }
            else {
                if (version_compare($objRequired->getStrVersion(), $strVersion, ">=")) {
                    $strStatus = "<span class=\"label label-success\">".$this->getLang("package_version_available")."</span>";
                }
                else {
                    $strStatus = "<span class=\"label label-important\">".$this->getLang("package_version_low")."</span>";
                }
            }

            $arrRequiredRows[] = array($strOneModule, " >= ".$strVersion, $strStatus);
        }
        $arrRows[] = array($this->getLang("package_modules"), $this->objToolkit->dataTable(null, $arrRequiredRows));


        if ($bitIncludeRequiredBy) {
            $arrRequiredBy = $objManager->getArrRequiredBy($objHandler->getObjMetadata());
            array_walk($arrRequiredBy, function (&$strOneModule) {
                $strOneModule = array($strOneModule);
            });

            $arrRows[] = array($this->getLang("package_required_by"), $this->objToolkit->dataTable(null, $arrRequiredBy));
        }

        $strImages = "";
        foreach ($objHandler->getObjMetadata()->getArrScreenshots() as $strOneScreenshot) {

            $strImage = "";
            if (uniSubstr($objHandler->getObjMetadata()->getStrPath(), 0, -5) == ".phar") {
                $strPharImage = "phar://"._realpath_."/".$objHandler->getObjMetadata()->getStrPath()."/".$strOneScreenshot;
                if (is_file($strPharImage)) {
                    $strImage = _images_cachepath_."/".generateSystemid().uniSubstr($strOneScreenshot, -4);
                    copy($strPharImage, _realpath_.$strImage);
                }
            }
            else {
                $strImage = $objHandler->getObjMetadata()->getStrPath()."/".$strOneScreenshot;
            }

            if ($strImage != "") {
                $strImages .= "<img src='"._webpath_."/image.php?image=".urlencode(StringUtil::replace(_realpath_, "", $strImage))."&maxWidth=300&maxHeight=200' alt='".$strOneScreenshot."' />&nbsp;";
            }
        }
        $arrRows[] = array($this->getLang("package_screenshots"), $strImages);

        $strReturn .= $this->objToolkit->dataTable(null, $arrRows);
        return $strReturn;
    }

    /**
     * Triggers the removal of a single package
     *
     * @permissions edit,delete
     * @throws class_exception
     * @return string
     */
    protected function actionDeletePackage()
    {
        $strReturn = "";

        //fetch the package
        $objManager = new class_module_packagemanager_manager();
        $objPackage = $objManager->getPackage($this->getParam("package"));

        if ($objPackage == null) {
            throw new class_exception("package not found", class_exception::$level_ERROR);
        }

        $strLog = $objManager->removePackage($objPackage);

        if ($strLog == "") {
            $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul"), "list"));
            return "";
        }

        $strReturn .= $this->objToolkit->formHeadline($this->getLang("package_removal_header"));
        $strReturn .= $this->objToolkit->getPreformatted(array($strLog));

        $strReturn .= $this->objToolkit->formHeader(class_link::getLinkAdminHref($this->getArrModule("modul"), "list"), "", "");
        $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_ok"));
        $strReturn .= $this->objToolkit->formClose();

        return $strReturn;
    }


    /**
     * Triggers the installation of a package
     *
     * @permissions edit
     * @return string
     */
    protected function actionInstallPackage()
    {
        $this->setArrModuleEntry("template", "/folderview.tpl");

        $strReturn = "";
        $strLog = "";
        $strFile = $this->getParam("package");

        $objManager = new class_module_packagemanager_manager();

        if ($objManager->validatePackage($strFile)) {

            if (\Kajona\System\System\StringUtil::indexOf($strFile, "/project") !== false) {
                $objHandler = $objManager->getPackageManagerForPath($strFile);
                $objHandler->move2Filesystem();

                class_classloader::getInstance()->flushCache();
                class_reflection::flushCache();
                class_resourceloader::getInstance()->flushCache();

                //reload the module-ids
                class_classloader::getInstance()->bootstrapIncludeModuleIds();

                //reload the current request in order to flush the class-loader
                $this->adminReload(class_link::getLinkAdminHref("packagemanager", "installPackage", "&package=".$objHandler->getStrTargetPath()));
                return;

            }
            else {
                $objHandler = $objManager->getPackageManagerForPath($strFile);
            }

            if ($objHandler->getObjMetadata()->getBitProvidesInstaller()) {
                $strLog .= $objHandler->installOrUpdate();
            }

            $strLog .= "Updating default template pack...\n";
            $objHandler->updateDefaultTemplate();


            $strOnSubmit = 'window.parent.parent.location.reload();';
            if ($strLog !== "") {
                $strReturn .= $this->objToolkit->formHeadline($this->getLang("package_install_success"));
                $strReturn .= $this->objToolkit->getPreformatted(array($strLog));

                $strReturn .= $this->objToolkit->formHeader(
                    class_link::getLinkAdminHref($this->getArrModule("modul"), "list"), "", "", "javascript:".$strOnSubmit
                );
                $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_ok"));
                $strReturn .= $this->objToolkit->formClose();
            }
            else {
                // break out of dialog and remove iframes by reloading main window
                $strReturn .= '<script>'.$strOnSubmit.'</script>';
            }
        }

        return $strReturn;
    }

    /**
     * Triggers the initial steps to start the update of a single package.
     *
     * @permissions edit
     * @return string
     */
    protected function actionInitPackageUpdate()
    {
        $strPackage = $this->getParam("package");
        $objManager = new class_module_packagemanager_manager();
        $objHandler = $objManager->getPackageManagerForPath($strPackage);
        return $objManager->updatePackage($objHandler);

    }


    /**
     * Generates the gui to add new packages
     *
     * @return string
     * @permissions edit
     * @autoTestable
     */
    protected function actionAddPackage()
    {
        $this->setArrModuleEntry("template", "/folderview.tpl");

        $strReturn = "";

        $objManager = new class_module_packagemanager_manager();
        $arrContentProvider = $objManager->getContentproviders();
        if ($this->getParam("provider") == "") {

            //todo: temporary switched back to a simple list until the problems with tabs height and the dropdowns width' are resolved completely.
            // in addition this reduces the workload on both, client, server and remote repositories

            $strReturn .= $this->objToolkit->listHeader();
            $intI = 0;
            foreach ($arrContentProvider as $objOneProvider) {
                $strReturn .= $this->objToolkit->genericAdminList(
                    generateSystemid(),
                    $objOneProvider->getDisplayTitle(),
                    class_adminskin_helper::getAdminImage("icon_systemtask"),
                    class_link::getLinkAdmin("packagemanager", "addPackage", "&provider=".get_class($objOneProvider), $this->getLang("provider_select"), $this->getLang("provider_select"), "icon_accept"),
                    $intI++
                );
            }
            $strReturn .= $this->objToolkit->listFooter();

            /* old tab code start ///////
            $arrTabs = array();
            foreach($arrContentProvider as $objOneProvider) {
                $strIFrameSrc = getLinkAdminHref($this->getArrModule("modul"), "addPackage", "&provider=".get_class($objOneProvider));

                $arrTabs[$objOneProvider->getDisplayTitle()] = $this->objToolkit->getIFrame($strIFrameSrc);
            }

            $strReturn .= $this->objToolkit->getTabbedContent($arrTabs, true);

            ///////old tab code end */
            return $strReturn;
        }


        $strProvider = $this->getParam("provider");
        $objProvider = null;
        foreach ($arrContentProvider as $objOneProvider) {
            if (get_class($objOneProvider) == $strProvider) {
                $objProvider = $objOneProvider;
            }
        }

        if ($objProvider == null) {
            return $this->renderError("commons_error_permissions");
        }

        try {
            $strReturn = $objProvider->renderPackageList();
        }
        catch (class_exception $objEx) {
            $strReturn = $this->objToolkit->warningBox($this->getLang("package_remote_errorloading")."<br />".$objEx->getMessage());
        }
        return $strReturn;
    }

    /**
     * @permissions edit
     * @return string
     */
    protected function actionUploadPackage()
    {
        $this->setArrModuleEntry("template", "/folderview.tpl");

        $objManager = new class_module_packagemanager_manager();
        $arrContentProvider = $objManager->getContentproviders();

        $strProvider = $this->getParam("provider");
        $objProvider = null;
        foreach ($arrContentProvider as $objOneProvider) {
            if (get_class($objOneProvider) == $strProvider) {
                $objProvider = $objOneProvider;
            }
        }

        if ($objProvider == null) {
            return $this->getLang("commons_error_permissions");
        }

        $strFile = $objProvider->processPackageUpload();

        if ($strFile == null) {
            return $this->renderError("provider_error_transfer", "packagemanager");
        }

        if (!$objManager->validatePackage($strFile)) {
            $objFilesystem = new class_filesystem();
            $objFilesystem->fileDelete($strFile);
            return $this->getLang("provider_error_package", "packagemanager");
        }

        $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul"), "processPackage", "&package=".$strFile));
        return "";
    }

    /**
     * @param string $strLangName
     * @param null $strLangModule
     *
     * @return string
     */
    protected function renderError($strLangName, $strLangModule = null)
    {
        $strError = $this->getLang($strLangName, $strLangModule);
        $objHistory = new class_history();
        $arrHistory = explode("&", $objHistory->getAdminHistory(0));

        if ($this->getArrModule("template") == "/folderview.tpl") {
            $strError .= ' '.class_link::getLinkAdminManual('href="javascript:window.parent.location.reload();"', $this->getLang('commons_back'));
        }
        else {
            $strError .= ' '.class_link::getLinkAdminManual("href=\"".$arrHistory[0]."&".$arrHistory[1]."\"", $this->getLang("commons_back"));
        }
        return $this->objToolkit->warningBox($strError);
    }

    /**
     * @return string
     * @autoTestable
     * @permissions view
     */
    protected function actionListTemplates()
    {

        class_module_packagemanager_template::syncTemplatepacks();

        $objArraySectionIterator = new class_array_section_iterator(class_module_packagemanager_template::getObjectCount());
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(
            class_module_packagemanager_template::getObjectList("", $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos())
        );

        return $this->renderList($objArraySectionIterator);
    }

    /**
     * @param string $strListIdentifier
     * @param bool $bitDialog
     *
     * @return array
     */
    protected function getNewEntryAction($strListIdentifier, $bitDialog = false)
    {
        $arrReturn = array();
        if ($this->getObjModule()->rightEdit()) {
            $arrReturn[] = $this->objToolkit->listButton(class_link::getLinkAdminDialog($this->getArrModule("modul"), "addPackage", "&systemid=", $this->getLang("action_upload_package"), $this->getLang("action_upload_package"), "icon_upload", $this->getLang("action_upload_package")));
            $arrReturn[] = $this->objToolkit->listButton(class_link::getLinkAdmin($this->getArrModule("modul"), "new", "", $this->getLang("action_new_copy"), $this->getLang("action_new_copy"), "icon_new"));
        }

        return $arrReturn;
    }

    /**
     * @param class_model $objListEntry
     * @param bool $bitDialog
     *
     * @return string
     */
    protected function renderEditAction(class_model $objListEntry, $bitDialog = false)
    {
        return "";
    }

    /**
     * @param class_model $objListEntry
     *
     * @return string
     */
    protected function renderCopyAction(class_model $objListEntry)
    {
        return "";
    }


    /**
     * @param class_model
     * @param string $strAltActive tooltip text for the icon if record is active
     * @param string $strAltInactive tooltip text for the icon if record is inactive
     *
     * @return string
     */
    protected function renderStatusAction(class_model $objListEntry, $strAltActive = "", $strAltInactive = "")
    {
        if ($objListEntry->rightEdit()) {
            if (class_module_system_setting::getConfigValue("_packagemanager_defaulttemplate_") == $objListEntry->getStrName()) {
                return $this->objToolkit->listButton(class_adminskin_helper::getAdminImage("icon_enabled", $this->getLang("pack_active_no_status")));
            }
            else {
                return $this->objToolkit->listStatusButton($objListEntry, true);
            }
        }

        return "";
    }


    /**
     * @param interface_model|class_root $objListEntry
     *
     * @return string
     */
    protected function renderDeleteAction(interface_model $objListEntry)
    {
        if ($objListEntry->rightDelete() && $this->getObjModule()->rightDelete()) {
            if (class_module_system_setting::getConfigValue("_packagemanager_defaulttemplate_") == $objListEntry->getStrName()) {
                return $this->objToolkit->listButton(class_adminskin_helper::getAdminImage("icon_deleteDisabled", $this->getLang("pack_active_no_delete")));
            }
            else {
                return $this->objToolkit->listDeleteButton(
                    $objListEntry->getStrDisplayName(), $this->getLang("delete_question"), class_link::getLinkAdminHref($this->getArrModule("modul"), "deleteTemplate", "&systemid=".$objListEntry->getSystemid()."")
                );
            }
        }

        return "";
    }

    /**
     * Wrapper to delete a template-pack
     *
     * @return void
     */
    protected function actionDeleteTemplate()
    {
        parent::actionDelete();
        $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul"), "listTemplates"));
    }

    /**
     * Triggers a phar-creation and download of the generated phar
     * @permissions view,edit
     */
    protected function actionDownloadAsPhar()
    {
        $objManager = new class_module_packagemanager_manager();
        $objHandler = $objManager->getPackage($this->getParam("package"));
        if ($objHandler !== null) {
            /** @var \Kajona\Packagemanager\System\PackagemanagerPharGeneratorInterface $objPharService */
            $objPharService = class_carrier::getInstance()->getContainer()->offsetGet("packagemanager_phargenerator");
            try {
                $objPharService->generateAndStreamPhar(_realpath_.$objHandler->getStrPath());
            }
            catch (class_exception $objEx) {
                return $this->objToolkit->warningBox($objEx->getMessage(), "alert-danger");
            }
        }

        return "";
    }

    /**
     * @return string
     * @permissions edit
     */
    protected function actionEdit()
    {
        return $this->renderError("commons_error_permissions");
    }

    /**
     * @param \class_admin_formgenerator|null $objForm
     *
     * @return string
     * @permissions edit
     */
    protected function actionNew(class_admin_formgenerator $objForm = null)
    {
        if ($objForm == null) {
            $objForm = $this->getPackAdminForm();
        }

        $strReturn = $objForm->renderForm(class_link::getLinkAdminHref($this->getArrModule("modul"), "copyPack"));
        return $strReturn;
    }

    /**
     * @return class_admin_formgenerator
     */
    private function getPackAdminForm()
    {
        $objFormgenerator = new class_admin_formgenerator("pack", new class_module_system_common());
        $objFormgenerator->addField(new class_formentry_text("pack", "name"))->setStrLabel($this->getLang("pack_name"))->setBitMandatory(true)->setStrValue($this->getParam("pack_name"));
        $objFormgenerator->addField(new class_formentry_headline())->setStrValue($this->getLang("pack_copy_include"));
        $arrModules = class_classloader::getInstance()->getArrModules();
        foreach ($arrModules as $strOneModule) {
            //validate if there's a template-folder existing
            if (is_dir(class_resourceloader::getInstance()->getAbsolutePathForModule($strOneModule)."/templates")) {
                $objFormgenerator->addField(new class_formentry_checkbox("pack", "modules[".$strOneModule."]"))->setStrLabel($strOneModule)->setStrValue($strOneModule == "module_pages");
            }

        }
        return $objFormgenerator;
    }

    /**
     * @permissions edit
     * @return string
     */
    protected function actionCopyPack()
    {
        $objForm = $this->getPackAdminForm();

        $strPackName = $this->getParam("pack_name");
        $strPackName = createFilename($strPackName, true);

        if ($strPackName != "" && is_dir(_realpath_._templatepath_."/".$strPackName)) {
            $objForm->addValidationError("name", $this->getLang("pack_folder_existing"));
        }

        if (!$objForm->validateForm()) {
            return $this->actionNew($objForm);
        }


        $objFilesystem = new class_filesystem();
        $objFilesystem->folderCreate(_templatepath_."/".$strPackName);
        $objFilesystem->folderCreate(_templatepath_."/".$strPackName."/tpl");
        $objFilesystem->folderCreate(_templatepath_."/".$strPackName."/css");
        $objFilesystem->folderCreate(_templatepath_."/".$strPackName."/js");

        $arrModules = $this->getParam("pack_modules");
        foreach ($arrModules as $strName => $strValue) {
            if ($strValue != "") {
                $objFilesystem->folderCopyRecursive(class_resourceloader::getInstance()->getAbsolutePathForModule($strName)."/templates/default", _templatepath_."/".$strPackName);
            }
        }

        class_resourceloader::getInstance()->flushCache();
        class_classloader::getInstance()->flushCache();
        class_reflection::flushCache();

        $this->adminReload(class_link::getLinkAdminHref($this->getArrModule("modul"), "listTemplates"));
        return "";
    }


    /**
     * Checks if all content of the passed folder is writable
     *
     * @param string $strFolder
     * @param string[] $arrErrors
     */
    private function checkWritableRecursive($strFolder, &$arrErrors)
    {

        if (!is_writable(_realpath_.$strFolder)) {
            $arrErrors[] = $strFolder;
        }

        $objFilesystem = new class_filesystem();
        $arrContent = $objFilesystem->getCompleteList($strFolder);

        foreach ($arrContent["files"] as $arrOneFile) {
            if (!is_writable(_realpath_.$strFolder."/".$arrOneFile["filename"])) {
                $arrErrors[] = $strFolder."/".$arrOneFile["filename"];
            }
        }

        foreach ($arrContent["folders"] as $strOneFolder) {
            $this->checkWritableRecursive($strFolder."/".$strOneFolder, $arrErrors);
        }

    }
}
