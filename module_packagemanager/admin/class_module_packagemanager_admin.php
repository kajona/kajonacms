<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * Admin-GUI of the packagemanager.
 * The packagemanager provides a way to handle the template-packs available.
 * In addition, setting packs as the current active-one is supported, too.
 *
 * @package module_packagemanager
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_module_packagemanager_admin extends class_admin_simple implements interface_admin {

    /**
     * Constructor
     */
    public function __construct() {
        $this->setArrModuleEntry("modul", "packagemanager");
        $this->setArrModuleEntry("moduleId", _packagemanager_module_id_);
        parent::__construct();


    }

    public function getOutputModuleNavi() {
        $arrReturn = array();
        $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("actionList"), "", "", true, "adminnavi"));
        $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "listTemplates", "", $this->getLang("actionListTemplates"), "", "", true, "adminnavi"));
        $arrReturn[] = array("", "");
        $arrReturn[] = array("right", getLinkAdmin("right", "change", "&changemodule=".$this->arrModule["modul"],  $this->getLang("commons_module_permissions"), "", "", true, "adminnavi"));

        return $arrReturn;
    }

    public function getRequiredFields() {
        if($this->getAction() == "copyPack") {
            return array("pack_name" => "string");
        }

        return parent::getRequiredFields();
    }

    /**
     * Generic list of all packages available on the local filesystem
     * @return string
     * @permissions view
     * @autoTestable
     */
    protected function actionList() {

        class_module_packagemanager_template::syncTemplatepacks();

        $strReturn = "";
        $objManager = new class_module_packagemanager_manager();
        $arrPackages = $objManager->getAvailablePackages();


        $objArrayIterator = new class_array_iterator($arrPackages);
        $objArrayIterator->getElementsOnPage((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));

        $objArraySectionIterator = new class_array_section_iterator(count($arrPackages));
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection($objArrayIterator->getElementsOnPage((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1)));

        $arrPageViews = $this->objToolkit->getSimplePageview($objArraySectionIterator, $this->getArrModule("modul"), $this->getAction());
        $arrIterables = $arrPageViews["elements"];



        $strReturn .= $this->objToolkit->listHeader();
        $intI = 0;
        foreach($arrIterables as $objOneMetadata) {

            $strActions = "";
            $objHandler = $objManager->getPackageManagerForPath($objOneMetadata->getStrPath());

            if($objHandler->isInstallable()) {
                $strActions .= $this->objToolkit->listButton(
                    getLinkAdmin($this->getArrModule("modul"), "processPackage", "&package=".$objOneMetadata->getStrPath(), $this->getLang("package_install"), $this->getLang("package_installocally"), "icon_downloads.png")
                );
            }


            $strActions .= $this->objToolkit->listButton("<span id=\"updateWrapper".$objOneMetadata->getStrTitle()."\">".getImageAdmin("loadingSmall.gif", $this->getLang("package_searchupdate"))."</span>");
            $strActions .= "<script type='text/javascript'>
            $(function() {
                KAJONA.admin.ajax.genericAjaxCall('packagemanager', 'getUpdateIcon', '&package=".$objOneMetadata->getStrTitle()."', function(data, status, jqXHR) {
                    if(status == 'success') { $('#updateWrapper".$objOneMetadata->getStrTitle()."').html(data); KAJONA.util.evalScript(data); }
                    else {KAJONA.admin.statusDisplay.messageError('<b>Request failed!</b><br />' + data);}
                }); });
            </script>";

            $strReturn .= $this->objToolkit->simpleAdminList($objOneMetadata, $strActions, $intI++);
        }

        $strAddActions = $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "addPackage", "", $this->getLang("actionUploadPackage"), $this->getLang("actionUploadPackage"), "icon_new.png"));
        $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), "", "", $strAddActions, $intI);


        $strReturn .= $this->objToolkit->listFooter();

        $strReturn .= $arrPageViews["pageview"];

        return $strReturn;
    }

    /**
     * Checks if an update is available for a single package.
     * Renders the matching icon and tooltip or the link to update a package.
     *
     * @xml
     * @permissions view,edit
     * @return string
     */
    protected function actionGetUpdateIcon() {

        $strPackage = $this->getParam("package");
        $objManager = new class_module_packagemanager_manager();
        $arrPackages = $objManager->getAvailablePackages();

        //close session to avoid blocking
        $this->objSession->sessionClose();

        class_response_object::getInstance()->setStResponseType(class_http_responsetypes::STR_TYPE_HTML);

        foreach($arrPackages as $objOneMetadata) {

            if($objOneMetadata->getStrTitle() == $strPackage) {

                $objHandler = $objManager->getPackageManagerForPath($objOneMetadata->getStrPath());
                $bitUpdateAvailable = $objManager->updateAvailable($objHandler);

                if($bitUpdateAvailable === null) {
                    return getImageAdmin("icon_updateError.png", $this->getLang("package_noversion"));
                }
                else {
                    //compare the version to trigger additional actions
                    $strLatestVersion = $objManager->searchLatestVersion($objHandler);
                    if($bitUpdateAvailable) {
                        return getLinkAdmin(
                            $this->getArrModule("modul"),
                            "initPackageUpdate",
                            "&package=".$objHandler->getObjMetadata()->getStrPath(),
                            $this->getLang("package_updatefound")." ".$strLatestVersion,
                            $this->getLang("package_updatefound")." ".$strLatestVersion,
                            "icon_update.png"
                        );
                    }
                    else {
                        return getImageAdmin("icon_updateDisabled.png", $this->getLang("package_noupdate")." ".$strLatestVersion);
                    }
                }

                break;
            }
        }

        return getImageAdmin("icon_updateError.png", $this->getLang("package_noversion"));
    }

    /**
     * Validates a local package, renders the metadata
     * and provides, if feasible, a button to start the installation.
     *
     * @permissions edit
     * @return string
     */
    protected function actionProcessPackage() {
        $strReturn = "";
        $strFile = $this->getParam("package");

        $objManager = new class_module_packagemanager_manager();
        $objHandler = $objManager->getPackageManagerForPath($strFile);

        if($objManager->validatePackage($strFile)) {

            $strReturn .= $this->objToolkit->formHeadline($objHandler->getObjMetadata()->getStrTitle());
            $strReturn .= $this->objToolkit->getTextRow($objHandler->getObjMetadata()->getStrDescription());
            $strReturn .= $this->objToolkit->getTextRow($this->getLang("package_type")." ".$this->getLang("type_".$objHandler->getObjMetadata()->getStrType()));
            $strReturn .= $this->objToolkit->getTextRow($this->getLang("package_version")." ".$objHandler->getObjMetadata()->getStrVersion());
            if($objHandler->getVersionInstalled() != null) {
                $strReturn .= $this->objToolkit->getTextRow($this->getLang("package_version_installed")." ".$objHandler->getVersionInstalled());

            }
            $strReturn .= $this->objToolkit->getTextRow($this->getLang("package_author")." ".$objHandler->getObjMetadata()->getStrAuthor());
            $strReturn .= $this->objToolkit->getTextRow($this->getLang("package_modules"));
            foreach($objHandler->getObjMetadata()->getArrRequiredModules() as $strOneModule => $strVersion) {
                $strReturn .= $this->objToolkit->getTextRow($strOneModule." >= ".$strVersion);
            }


            if(!$objHandler->getObjMetadata()->getBitProvidesInstaller() || $objHandler->isInstallable()) {

                if($objHandler->getVersionInstalled() != null)
                    $strReturn .= $this->objToolkit->getTextRow($this->getLang("package_target_writable")." ".$objHandler->getStrTargetPath());
                else
                    $strReturn .= $this->objToolkit->getTextRow($this->getLang("package_target_writable")." ".dirname($objHandler->getStrTargetPath()));

                $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->getArrModule("modul"), "installPackage"));
                $strReturn .= $this->objToolkit->formInputHidden("package", $strFile);
                $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("package_doinstall"));
                $strReturn .= $this->objToolkit->formClose();

            }
            else {
                $strReturn .= $this->objToolkit->warningBox($this->getLang("package_notinstallable"));
            }


        }
        else
            return $this->getLang("provider_error_package");

        return $strReturn;
    }

    /**
     * @permissions edit
     * @return string
     */
    protected function actionInstallPackage() {
        $strReturn = "";
        $strLog = "";
        $strFile = $this->getParam("package");

        $objManager = new class_module_packagemanager_manager();

        if($objManager->validatePackage($strFile)) {

            if(uniSubstr($strFile, -4) == ".zip") {
                $objHandler = $objManager->extractPackage($strFile);
                $objFilesystem = new class_filesystem();
                $objFilesystem->fileDelete($strFile);

                $strReturn .= $objHandler->move2Filesystem();

                class_resourceloader::getInstance()->flushCache();
                class_classloader::getInstance()->flushCache();
                class_reflection::flushCache();
            }
            else
                $objHandler = $objManager->getPackageManagerForPath($strFile);

            if($objHandler->getObjMetadata()->getBitProvidesInstaller())
                $strLog .= $objHandler->installOrUpdate();

            if($strLog !== "") {
                $strReturn .= $this->objToolkit->formHeadline($this->getLang("package_install_success"));
                $strReturn .= $this->objToolkit->getPreformatted(array($strLog));

                $strReturn .= $this->objToolkit->formHeader(getLinkAdminHref($this->getArrModule("modul"), "list"));
                $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_ok"));
                $strReturn .= $this->objToolkit->formClose();
            }
            else {
                if($objHandler instanceof class_module_packagemanager_packagemanager_template)
                    $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "listTemplates"));
                else
                    $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "list"));
            }
        }

        return $strReturn;
    }

    /**
     * Triggers the initial steps to start the update of a single package.
     * @permissions edit
     * @return string
     */
    protected function actionInitPackageUpdate() {
        $strPackage = $this->getParam("package");
        $objManager = new class_module_packagemanager_manager();
        $objHandler = $objManager->getPackageManagerForPath($strPackage);
        return $objManager->updatePackage($objHandler);

    }


    /**
     * Generates the gui to add new packages
     * @return string
     * @permissions edit
     * @autoTestable
     */
    protected function actionAddPackage() {
        $objManager = new class_module_packagemanager_manager();
        $arrContentProvider = $objManager->getContentproviders();
        if($this->getParam("provider") == "") {
            $strReturn = $this->objToolkit->listHeader();

            $intI = 0;
            foreach($arrContentProvider as $objOneProvider) {
                $strReturn .= $this->objToolkit->genericAdminList(
                    generateSystemid(),
                    $objOneProvider->getDisplayTitle(),
                    getImageAdmin("icon_dot.png"),
                    $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "addPackage", "&provider=".get_class($objOneProvider), $this->getLang("provider_select"), $this->getLang("provider_select"), "icon_accept.png")),
                    $intI++
                );
            }

            $strReturn .= $this->objToolkit->listFooter();
            return $strReturn;
        }

        $strProvider = $this->getParam("provider");
        $objProvider = null;
        foreach($arrContentProvider as $objOneProvider)
            if(get_class($objOneProvider) == $strProvider)
                $objProvider = $objOneProvider;

        if($objProvider == null)
            return $this->getLang("commons_error_permissions");

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
    protected function actionUploadPackage() {
        $objManager = new class_module_packagemanager_manager();
        $arrContentProvider = $objManager->getContentproviders();

        $strProvider = $this->getParam("provider");
        $objProvider = null;
        foreach($arrContentProvider as $objOneProvider)
            if(get_class($objOneProvider) == $strProvider)
                $objProvider = $objOneProvider;

        if($objProvider == null)
            return $this->getLang("commons_error_permissions");

        $strFile = $objProvider->processPackageUpload();

        if($strFile == null)
            return $this->getLang("provider_error_transfer", "packagemanager");

        if(!$objManager->validatePackage($strFile)) {
            $objFilesystem = new class_filesystem();
            $objFilesystem->fileDelete($strFile);
            return $this->getLang("provider_error_package", "packagemanager");
        }

        $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "processPackage", "&package=".$strFile));
        return "";
    }



    /**
     * @return string
     * @autoTestable
     * @permissions view
     */
    protected function actionListTemplates() {

        class_module_packagemanager_template::syncTemplatepacks();

        $objArraySectionIterator = new class_array_section_iterator(class_module_packagemanager_template::getObjectCount());
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(
            class_module_packagemanager_template::getObjectList("", $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos())
        );

        return $this->renderList($objArraySectionIterator);
    }

    protected function getNewEntryAction($strListIdentifier, $bitDialog = false) {
        $strReturn = "";
        if($this->getObjModule()->rightEdit()) {
            //$strReturn .= $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "download", "", $this->getLang("action_download"), $this->getLang("action_download"), "icon_install.png"));
            $strReturn .= $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "addPackage", "", $this->getLang("actionUploadPackage"), $this->getLang("actionUploadPackage"), "icon_upload.png"));
            $strReturn .= $this->objToolkit->listButton(getLinkAdmin($this->getArrModule("modul"), "new", "", $this->getLang("action_new_copy"), $this->getLang("action_new_copy"), "icon_new.png"));
        }

        return $strReturn;
    }

    protected function renderEditAction(class_model $objListEntry, $bitDialog = false) {
        return "";
    }

    protected function renderCopyAction(class_model $objListEntry) {
        return "";
    }


    protected function renderStatusAction(class_model $objListEntry) {
        if($objListEntry->rightEdit()) {
            if(_packagemanager_defaulttemplate_ == $objListEntry->getStrName()) {
                return $this->objToolkit->listButton(getImageAdmin("icon_enabled.png", $this->getLang("pack_active_no_status")));
            }
            else
                return $this->objToolkit->listStatusButton($objListEntry, true);
        }

        return "";
    }


    protected function renderDeleteAction(interface_model $objListEntry) {
        if($objListEntry->rightDelete()) {
            if(_packagemanager_defaulttemplate_ == $objListEntry->getStrName()) {
                return $this->objToolkit->listButton(getImageAdmin("icon_deleteDisabled.png", $this->getLang("pack_active_no_delete")));
            }
            else
                return $this->objToolkit->listDeleteButton(
                    $objListEntry->getStrDisplayName(), $this->getLang("delete_question"), getLinkAdminHref($this->getArrModule("modul"), "deleteTemplate", "&systemid=".$objListEntry->getSystemid()."")
                );
        }

        return "";
    }

    /**
     * Wrapper to delete a template-pack
     *
     * @return void
     */
    protected function actionDeleteTemplate() {
        parent::actionDelete();
        $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "listTemplates"));
    }


    /**
     * @return string
     * @permissions edit
     */
    protected function actionEdit() {
        return $this->getLang("commons_error_permissions");
    }

    /**
     * @param \class_admin_formgenerator|null $objForm
     *
     * @return string
     * @permissions edit
     */
    protected function actionNew(class_admin_formgenerator $objForm = null) {
        if($objForm == null)
            $objForm = $this->getPackAdminForm();

        return $objForm->renderForm(getLinkAdminHref($this->getArrModule("modul"), "copyPack"));
    }

    private function getPackAdminForm() {
        $objFormgenerator = new class_admin_formgenerator("pack", new class_module_system_common());
        $objFormgenerator->addField(new class_formentry_text("pack", "name"))->setStrLabel($this->getLang("pack_name"))->setBitMandatory(true)->setStrValue($this->getParam("pack_name"));
        $objFormgenerator->addField(new class_formentry_headline())->setStrValue($this->getLang("pack_copy_include"));
        $arrModules = class_resourceloader::getInstance()->getArrModules();
        foreach($arrModules as $strOneModule) {
            //validate if there's a template-folder existing
            if(is_dir(_corepath_."/".$strOneModule."/templates"))
                $objFormgenerator->addField(new class_formentry_checkbox("pack", "modules[".$strOneModule."]"))->setStrLabel($strOneModule)->setStrValue($strOneModule == "module_pages");
        }
        return $objFormgenerator;
    }

    /**
     * @permissions edit
     * @return string
     */
    protected function actionCopyPack() {
        $objForm = $this->getPackAdminForm();

        $strPackName = $this->getParam("pack_name");
        $strPackName = createFilename($strPackName, true);

        if(is_dir(_realpath_._templatepath_."/".$strPackName))
            $objForm->addValidationError("name", $this->getLang("pack_folder_existing"));

        if(!$objForm->validateForm())
            return $this->actionNew($objForm);


        $objFilesystem = new class_filesystem();
        $objFilesystem->folderCreate(_templatepath_."/".$strPackName);
        $objFilesystem->folderCreate(_templatepath_."/".$strPackName."/tpl");
        $objFilesystem->folderCreate(_templatepath_."/".$strPackName."/css");
        $objFilesystem->folderCreate(_templatepath_."/".$strPackName."/js");

        $arrModules = $this->getParam("pack_modules");
        foreach($arrModules as $strName => $strValue) {
            if($strValue != "") {
                $objFilesystem->folderCopyRecursive("/core/".$strName."/templates/default", _templatepath_."/".$strPackName);
            }
        }

        $this->adminReload(getLinkAdminHref($this->getArrModule("modul"), "listTemplates"));
        return "";
    }


}
