<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

namespace Kajona\Packagemanager\Admin;

use Kajona\Packagemanager\System\PackagemanagerManager;
use Kajona\Packagemanager\System\PackagemanagerMetadata;
use Kajona\Packagemanager\System\PackagemanagerPackagemanagerInterface;
use Kajona\Packagemanager\System\PackagemanagerTemplate;
use Kajona\Packagemanager\System\ServiceProvider;
use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\Admin\AdminInterface;
use Kajona\System\Admin\AdminSimple;
use Kajona\System\Admin\Formentries\FormentryCheckbox;
use Kajona\System\Admin\Formentries\FormentryHeadline;
use Kajona\System\Admin\Formentries\FormentryPlaintext;
use Kajona\System\Admin\Formentries\FormentryText;
use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\ArrayIterator;
use Kajona\System\System\ArraySectionIterator;
use Kajona\System\System\Carrier;
use Kajona\System\System\Classloader;
use Kajona\System\System\Exception;
use Kajona\System\System\Filesystem;
use Kajona\System\System\History;
use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\Image2;
use Kajona\System\System\Imageplugins\ImageScale;
use Kajona\System\System\Link;
use Kajona\System\System\Model;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\StringUtil;
use Kajona\System\System\SystemSetting;

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
class PackagemanagerAdmin extends AdminSimple implements AdminInterface
{

    private $STR_FILTER_SESSION_KEY = "PACKAGELIST_FILTER_SESSION_KEY";

    /**
     * @return array
     */
    public function getOutputModuleNavi()
    {
        $arrReturn = array();
        $arrReturn[] = array("view", Link::getLinkAdmin($this->getArrModule("modul"), "list", "", $this->getLang("action_list"), "", "", true, "adminnavi"));
        $arrReturn[] = array("view", Link::getLinkAdmin($this->getArrModule("modul"), "listTemplates", "", $this->getLang("action_list_templates"), "", "", true, "adminnavi"));
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

            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "list"));
            return "";
        }

        PackagemanagerTemplate::syncTemplatepacks();

        $strReturn = "";
        $strReturn .= $this->objToolkit->formHeader(Link::getLinkAdminHref($this->getArrModule("modul")), "list");
        $strReturn .= $this->objToolkit->formInputText("packagelist_filter", $this->getLang("packagelist_filter"), $this->objSession->getSession($this->STR_FILTER_SESSION_KEY));
        $strReturn .= $this->objToolkit->formInputSubmit();
        $strReturn .= $this->objToolkit->formInputHidden("doFilter", "1");
        $strReturn .= $this->objToolkit->formClose();


        $objManager = new PackagemanagerManager();
        $arrPackages = $objManager->getAvailablePackages($this->objSession->getSession($this->STR_FILTER_SESSION_KEY));
        $arrPackages = $objManager->sortPackages($arrPackages);


        $objArrayIterator = new ArrayIterator($arrPackages);
        $objArrayIterator->getElementsOnPage((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));

        $objArraySectionIterator = new ArraySectionIterator(count($arrPackages));
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection($objArrayIterator->getElementsOnPage((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1)));

        $strReturn .= $this->objToolkit->listHeader();
        /** @var PackagemanagerMetadata $objOneMetadata */
        foreach ($objArraySectionIterator as $objOneMetadata) {
            $strActions = "";
            $objHandler = $objManager->getPackageManagerForPath($objOneMetadata->getStrPath());

            if ($objHandler->isInstallable()) {
                $strActions .= $this->objToolkit->listButton(
                    Link::getLinkAdminDialog(
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

            if (!$objOneMetadata->getBitIsPhar()) {
                $strActions .= $this->objToolkit->listButton(
                    Link::getLinkAdmin($this->getArrModule("modul"), "downloadAsPhar", "&package=".$objOneMetadata->getStrTitle(), $this->getLang("package_downloadasphar"), $this->getLang("package_downloadasphar"), "icon_phar")
                );
            }

            $strActions .= $this->objToolkit->listButton(
                Link::getLinkAdminDialog($this->getArrModule("modul"), "showInfo", "&package=".$objOneMetadata->getStrTitle(), $this->getLang("package_info"), $this->getLang("package_info"), "icon_lens", $objOneMetadata->getStrTitle())
            );

            if ($this->getObjModule()->rightDelete()) {
                if ($objHandler->isRemovable()) {
                    $strActions .= $this->objToolkit->listDeleteButton($objOneMetadata->getStrTitle(), $this->getLang("package_delete_question"), Link::getLinkAdminHref($this->getArrModule("modul"), "deletePackage", "&package=".$objOneMetadata->getStrTitle()));
                } else {
                    $strActions .= $this->objToolkit->listButton(AdminskinHelper::getAdminImage("icon_deleteLocked", $this->getLang("package_delete_locked")));
                }
            }


            $strActions .= $this->objToolkit->listButton(
                "<span id=\"updateWrapper".createFilename($objOneMetadata->getStrTitle(), true)."\">".AdminskinHelper::getAdminImage("loadingSmall", $this->getLang("package_searchupdate"))."</span>"
            );
            $strActions .= "<script type='text/javascript'>
                require(['packagemanager'], function(packagemanager) {
                    packagemanager.addPackageToTest('".$objOneMetadata->getStrTitle()."', '".createFilename($objOneMetadata->getStrTitle(), true)."');
                });
            </script>";

            $strReturn .= $this->objToolkit->simpleAdminList($objOneMetadata, $strActions);
        }

        $strAddActions = "";
        if ($this->getObjModule()->rightEdit()) {
            $strAddActions = $this->objToolkit->listButton(
                Link::getLinkAdminDialog($this->getArrModule("modul"), "addPackage", "", $this->getLang("action_upload_package"), $this->getLang("action_upload_package"), "icon_new", $this->getLang("action_upload_package"))
            );
        }
        $strReturn .= $this->objToolkit->genericAdminList(generateSystemid(), "", "", $strAddActions);

        $strReturn .= $this->objToolkit->listFooter();

        $strReturn .= "<script type='text/javascript'>
                require(['packagemanager'], function(packagemanager) {
                    window.setTimeout(function() {
                        packagemanager.triggerUpdateCheck();
                    }, 1000);
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
        $objManager = new PackagemanagerManager();
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
     * @permissions view,edit
     * @return string
     * @responseType json
     */
    protected function actionGetUpdateIcons()
    {

        $strPackages = $this->getParam("packages");
        $arrPackagesToCheck = explode(",", $strPackages);
        $objManager = new PackagemanagerManager();

        //close session to avoid blocking
        $this->objSession->sessionClose();
        $arrLatestVersion = $objManager->scanForUpdates();

        $arrReturn = array();
        foreach ($arrPackagesToCheck as $strOnePackage) {
            $objMetadata = $objManager->getPackage($strOnePackage);

            if ($objMetadata == null || !isset($arrLatestVersion[$strOnePackage])) {
                $arrReturn[$strOnePackage] = AdminskinHelper::getAdminImage("icon_updateError", $this->getLang("package_noversion"));
                continue;
            }


            $objHandler = $objManager->getPackageManagerForPath($objMetadata->getStrPath());
            $bitUpdateAvailable = $objManager->updateAvailable($objHandler, $arrLatestVersion[$strOnePackage]);

            if ($bitUpdateAvailable === null) {
                $arrReturn[$strOnePackage] = AdminskinHelper::getAdminImage("icon_updateError", $this->getLang("package_noversion"));
            } else {
                //compare the version to trigger additional actions
                $strLatestVersion = $arrLatestVersion[$strOnePackage];
                if ($bitUpdateAvailable) {
                    $arrReturn[$strOnePackage] = Link::getLinkAdminDialog(
                        $this->getArrModule("modul"),
                        "initPackageUpdate",
                        "&package=".$objHandler->getObjMetadata()->getStrPath(),
                        $this->getLang("package_updatefound")." ".$strLatestVersion,
                        $this->getLang("package_updatefound")." ".$strLatestVersion,
                        "icon_update",
                        $objHandler->getObjMetadata()->getStrTitle()
                    );
                } else {
                    $arrReturn[$strOnePackage] = AdminskinHelper::getAdminImage("icon_updateDisabled", $this->getLang("package_noupdate")." ".$strLatestVersion);
                }
            }
        }

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

        $objManager = new PackagemanagerManager();
        $objHandler = $objManager->getPackageManagerForPath($strFile);

        if ($objManager->validatePackage($strFile)) {
            $strReturn .= $this->renderPackageDetails($objHandler);

            if (!$objHandler->getObjMetadata()->getBitProvidesInstaller() || $objHandler->isInstallable()) {
                $arrNotWritable = array();
                if ($objHandler->getVersionInstalled() != null) {
                    $strReturn .= $this->objToolkit->getTextRow($this->getLang("package_target_writable")." ".$objHandler->getStrTargetPath());
                    $this->checkWritableRecursive($objHandler->getStrTargetPath(), $arrNotWritable);
                } else {
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


                $strWarningText = $this->getLang("package_notinstallable");
                if ($objHandler->getVersionInstalled() != null && version_compare($objHandler->getVersionInstalled(), $objHandler->getObjMetadata()->getStrVersion(), ">=")) {
                    $strWarningText .= "<br />".$this->getLang("package_noinstall_installed");
                    $strReturn .= $this->objToolkit->warningBox($strWarningText);
                } else {
                    $strReturn .= $this->objToolkit->formHeader(Link::getLinkAdminHref($this->getArrModule("modul"), "installPackage"));
                    $strReturn .= $this->objToolkit->formInputHidden("package", $strFile);
                    $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("package_doinstall"));
                    $strReturn .= $this->objToolkit->formClose();
                }

            } else {
                $strWarningText = $this->getLang("package_notinstallable");
                if ($objHandler->getVersionInstalled() != null) {
                    if ($objHandler->getVersionInstalled() == $objHandler->getObjMetadata()->getStrVersion()) {
                        $strWarningText .= "<br />".$this->getLang("package_noinstall_installed");
                    }
                }

                $strReturn .= $this->objToolkit->warningBox($strWarningText);
            }

        } else {
            $strError = $this->getLang("provider_error_package");
            $strError .= Link::getLinkAdminManual('href=\'javascript:history.back();\'', $this->getLang('back'));
            $strReturn .= $this->objToolkit->warningBox($strError);
        }

        return $strReturn;
    }

    /**
     * Renders the summary of a single package
     *
     * @param PackagemanagerPackagemanagerInterface $objHandler
     * @param bool $bitIncludeRequiredBy
     *
     * @return string
     */
    public function renderPackageDetails(PackagemanagerPackagemanagerInterface $objHandler, $bitIncludeRequiredBy = false)
    {
        $objManager = new PackagemanagerManager();

        $strReturn = $this->objToolkit->formHeadline($objHandler->getObjMetadata()->getStrTitle());
        $strReturn .= $this->objToolkit->getTextRow(nl2br($objHandler->getObjMetadata()->getStrDescription()));

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
            } else {
                if (version_compare($objRequired->getStrVersion(), $strVersion, ">=")) {
                    $strStatus = "<span class=\"label label-success\">".$this->getLang("package_version_available")."</span>";
                } else {
                    $strStatus = "<span class=\"label label-important\">".$this->getLang("package_version_low")."</span>";
                }
            }

            $arrRequiredRows[] = array($strOneModule, " >= ".$strVersion, $strStatus);
        }
        $arrRows[] = array($this->getLang("package_modules"), $this->objToolkit->dataTable(array(), $arrRequiredRows));


        if ($bitIncludeRequiredBy) {
            $arrRequiredBy = $objManager->getArrRequiredBy($objHandler->getObjMetadata());
            array_walk($arrRequiredBy, function (&$strOneModule) {
                $strOneModule = array($strOneModule);
            });

            $arrRows[] = array($this->getLang("package_required_by"), $this->objToolkit->dataTable(array(), $arrRequiredBy));
        }

        $strImages = "";
        foreach ($objHandler->getObjMetadata()->getArrScreenshots() as $strOneScreenshot) {
            if ($objHandler->getObjMetadata()->getBitIsPhar()) {
                $strImage = "phar://"._realpath_.$objHandler->getObjMetadata()->getStrPath()."/".$strOneScreenshot;
            } else {
                $strImage = _realpath_.$objHandler->getObjMetadata()->getStrPath()."/".$strOneScreenshot;
            }

            if ($strImage != "" && is_file($strImage)) {
                $objImage = new Image2();
                $objImage->load($strImage);
                $objImage->addOperation(new ImageScale(300, 300));
                $strImages .= "<img src='".$objImage->getAsBase64Src()."' alt='".$strOneScreenshot."' />&nbsp;";
            }
        }
        $arrRows[] = array($this->getLang("package_screenshots"), $strImages);

        $strReturn .= $this->objToolkit->dataTable(array(), $arrRows);
        return $strReturn;
    }

    /**
     * Triggers the removal of a single package
     *
     * @permissions edit,delete
     * @throws Exception
     * @return string
     */
    protected function actionDeletePackage()
    {
        $strReturn = "";

        //fetch the package
        $objManager = new PackagemanagerManager();
        $objPackage = $objManager->getPackage($this->getParam("package"));

        if ($objPackage == null) {
            throw new Exception("package not found", Exception::$level_ERROR);
        }

        $strLog = $objManager->removePackage($objPackage);

        if ($strLog == "") {
            $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "list"));
            return "";
        }

        $strReturn .= $this->objToolkit->formHeadline($this->getLang("package_removal_header"));
        $strReturn .= $this->objToolkit->getPreformatted(array($strLog));

        $strReturn .= $this->objToolkit->formHeader(Link::getLinkAdminHref($this->getArrModule("modul"), "list"), "", "");
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

        $objManager = new PackagemanagerManager();

        if ($objManager->validatePackage($strFile)) {
            if (StringUtil::indexOf($strFile, "/project") !== false) {
                $objHandler = $objManager->getPackageManagerForPath($strFile);
                $objHandler->move2Filesystem();
                $strUrlToLoad = Link::getLinkAdminHref("packagemanager", "installPackage", "&package=".$objHandler->getStrTargetPath());
                $strUrlToLoad = StringUtil::replace("&amp;", "&", $strUrlToLoad);

                //reload the current request in order to flush the class-loader
                //pass the reload header and quit to avoid other problems, e.g. due to undefined classes
                Classloader::getInstance()->flushCache();
                header("Location: ".$strUrlToLoad);
                die();
            } else {
                $objHandler = $objManager->getPackageManagerForPath($strFile);
            }

            $strLog .= $objHandler->installOrUpdate();

            $strOnSubmit = 'window.parent.parent.location.reload();';
            if ($strLog !== "") {
                $strReturn .= $this->objToolkit->formHeadline($this->getLang("package_install_success"));
                $strReturn .= $this->objToolkit->getPreformatted(array($strLog));

                $strReturn .= $this->objToolkit->formHeader(Link::getLinkAdminHref($this->getArrModule("modul"), "list"), "", "", "javascript:".$strOnSubmit);
                $strReturn .= $this->objToolkit->formInputSubmit($this->getLang("commons_ok"));
                $strReturn .= $this->objToolkit->formClose();
            } else {
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
        $objManager = new PackagemanagerManager();
        $objHandler = $objManager->getPackageManagerForPath($strPackage);
        return $objManager->updatePackage($objHandler);

    }


    /**
     * Generates the gui to add new packages
     *
     * @return string
     * @permissions edit
     */
    protected function actionAddPackage()
    {
        $this->setArrModuleEntry("template", "/folderview.tpl");

        $strReturn = "";

        $objManager = new PackagemanagerManager();
        $arrContentProvider = $objManager->getContentproviders();
        if ($this->getParam("provider") == "") {
            $strReturn .= $this->objToolkit->listHeader();
            foreach ($arrContentProvider as $objOneProvider) {
                $strReturn .= $this->objToolkit->genericAdminList(
                    generateSystemid(),
                    $objOneProvider->getDisplayTitle(),
                    AdminskinHelper::getAdminImage("icon_systemtask"),
                    Link::getLinkAdmin("packagemanager", "addPackage", "&provider=".get_class($objOneProvider), $this->getLang("provider_select"), $this->getLang("provider_select"), "icon_accept")
                );
            }
            $strReturn .= $this->objToolkit->listFooter();

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
        } catch (Exception $objEx) {
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

        $objManager = new PackagemanagerManager();
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
            $objFilesystem = new Filesystem();
            $objFilesystem->fileDelete($strFile);
            return $this->getLang("provider_error_package", "packagemanager");
        }

        $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "processPackage", "&package=".$strFile));
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
        $objHistory = new History();
        $arrHistory = explode("&", $objHistory->getAdminHistory(0));

        if ($this->getArrModule("template") == "/folderview.tpl") {
            $strError .= ' '.Link::getLinkAdminManual('href="javascript:window.parent.location.reload();"', $this->getLang('commons_back'));
        } else {
            $strError .= ' '.Link::getLinkAdminManual("href=\"".$arrHistory[0]."&".$arrHistory[1]."\"", $this->getLang("commons_back"));
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

        PackagemanagerTemplate::syncTemplatepacks();

        $objArraySectionIterator = new ArraySectionIterator(PackagemanagerTemplate::getObjectCountFiltered());
        $objArraySectionIterator->setPageNumber((int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1));
        $objArraySectionIterator->setArraySection(
            PackagemanagerTemplate::getObjectListFiltered(null, "", $objArraySectionIterator->calculateStartPos(), $objArraySectionIterator->calculateEndPos())
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
            $arrReturn[] = $this->objToolkit->listButton(Link::getLinkAdminDialog($this->getArrModule("modul"), "addPackage", "&systemid=", $this->getLang("action_upload_package"), $this->getLang("action_upload_package"), "icon_upload", $this->getLang("action_upload_package")));
            $arrReturn[] = $this->objToolkit->listButton(Link::getLinkAdmin($this->getArrModule("modul"), "new", "", $this->getLang("action_new_copy"), $this->getLang("action_new_copy"), "icon_new"));
        }

        return $arrReturn;
    }

    /**
     * @param \Kajona\System\System\Model $objListEntry
     * @param bool $bitDialog
     *
     * @return string
     */
    protected function renderEditAction(Model $objListEntry, $bitDialog = false)
    {
        return "";
    }

    /**
     * @param \Kajona\System\System\Model $objListEntry
     *
     * @return string
     */
    protected function renderCopyAction(Model $objListEntry)
    {
        return "";
    }

    /**
     * @inheritDoc
     */
    protected function renderAdditionalActions(Model $objListEntry)
    {
        $arrReturn = array();
        if ($objListEntry instanceof PackagemanagerTemplate) {
            if ($objListEntry->getMetadata() != null && !$objListEntry->getMetadata()->getBitIsPhar()) {
                $arrReturn[] = $this->objToolkit->listButton(
                    Link::getLinkAdmin($this->getArrModule("modul"), "downloadAsPhar", "&package=".$objListEntry->getMetadata()->getStrTitle(), $this->getLang("package_downloadasphar"), $this->getLang("package_downloadasphar"), "icon_phar")
                );
            }

            if ($objListEntry->getStrName() !== "default") {
                $arrReturn[] = $this->objToolkit->listButton(
                    Link::getLinkAdmin($this->getArrModule("modul"), "addTemplates", "&systemid=".$objListEntry->getSystemid(), $this->getLang("action_add_templates"), $this->getLang("action_add_templates"), "icon_new")
                );
            }
        }

        return $arrReturn;
    }


    /**
     * @param \Kajona\System\System\Model
     * @param string $strAltActive tooltip text for the icon if record is active
     * @param string $strAltInactive tooltip text for the icon if record is inactive
     *
     * @return string
     */
    protected function renderStatusAction(Model $objListEntry, $strAltActive = "", $strAltInactive = "")
    {
        if ($objListEntry->rightEdit()) {
            if (SystemSetting::getConfigValue("_packagemanager_defaulttemplate_") == $objListEntry->getStrName()) {
                return $this->objToolkit->listButton(AdminskinHelper::getAdminImage("icon_enabled", $this->getLang("pack_active_no_status")));
            } else {
                return $this->objToolkit->listStatusButton($objListEntry, true);
            }
        }

        return "";
    }


    /**
     * @param ModelInterface|Model $objListEntry
     *
     * @return string
     */
    protected function renderDeleteAction(ModelInterface $objListEntry)
    {
        if ($objListEntry->rightDelete() && $this->getObjModule()->rightDelete()) {
            if (SystemSetting::getConfigValue("_packagemanager_defaulttemplate_") == $objListEntry->getStrName() || $objListEntry->getStrName() === "default") {
                return $this->objToolkit->listButton(AdminskinHelper::getAdminImage("icon_deleteDisabled", $this->getLang("pack_active_no_delete")));
            } else {
                return $this->objToolkit->listDeleteButton($objListEntry->getStrDisplayName(), $this->getLang("delete_question"), Link::getLinkAdminHref($this->getArrModule("modul"), "deleteTemplate", "&systemid=".$objListEntry->getSystemid().""));
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
        $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "listTemplates"));
    }

    /**
     * Triggers a phar-creation and download of the generated phar
     *
     * @permissions view,edit
     */
    protected function actionDownloadAsPhar()
    {
        $objManager = new PackagemanagerManager();
        $objHandler = $objManager->getPackage($this->getParam("package"));
        if ($objHandler !== null) {
            /** @var \Kajona\Packagemanager\System\PackagemanagerPharGeneratorInterface $objPharService */
            $objPharService = Carrier::getInstance()->getContainer()->offsetGet(ServiceProvider::STR_PHARGENERATOR);
            try {
                $objPharService->generateAndStreamPhar(_realpath_.$objHandler->getStrPath());
            }
            catch (Exception $objEx) {
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
     * @return string
     * @throws Exception
     * @permissions edit
     */
    protected function actionAddTemplates()
    {
        $objPack = new PackagemanagerTemplate($this->getSystemid());
        $objForm = $this->getPackAdminForm($objPack);


        $objForm->addField(new FormentryPlaintext("hint"))->setStrValue($this->objToolkit->warningBox($this->getLang("add_templates_hint", array($objPack->getStrName(), _templatepath_."/".$objPack->getStrName()))));
        $objForm->setFieldToPosition("hint", 1);
        $objForm->removeField("pack_name");

        if ($objForm->getField("pack_modules[module_pages]") !== null) {
            $objForm->getField("pack_modules[module_pages]")->setStrValue(false);
        }

        $strReturn = $objForm->renderForm(Link::getLinkAdminHref($this->getArrModule("modul"), "addTemplateToPack"));
        return $strReturn;
    }

    protected function actionAddTemplateToPack()
    {
        $objPack = new PackagemanagerTemplate($this->getSystemid());
        $objFilesystem = new Filesystem();

        $arrModules = $this->getParam("pack_path");
        foreach ($arrModules as $strName => $strValue) {
            if ($strValue != "") {
                $strName = StringUtil::replace(StringUtil::toLowerCase(_realpath_), _realpath_, $strName);
                $strTarget = _templatepath_."/".$objPack->getStrName()."/".StringUtil::substring($strName, StringUtil::indexOf($strName, "/default/") + 9);
                $objFilesystem->fileCopy($strName, $strTarget);
            }
        }

        Carrier::getInstance()->flushCache(Carrier::INT_CACHE_TYPE_CLASSLOADER);
        $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "listTemplates"));
        return "";
    }

    /**
     * @param AdminFormgenerator|null $objForm
     *
     * @return string
     * @permissions edit
     */
    protected function actionNew(AdminFormgenerator $objForm = null)
    {
        if ($objForm == null) {
            $objForm = $this->getPackAdminForm();
        }

        $strReturn = $objForm->renderForm(Link::getLinkAdminHref($this->getArrModule("modul"), "copyPack"));
        return $strReturn;
    }

    /**
     * @return AdminFormgenerator
     */
    private function getPackAdminForm(PackagemanagerTemplate $objTargetObject = null)
    {
        $objFormgenerator = new AdminFormgenerator("pack", $objTargetObject);
        $objFormgenerator->addField(new FormentryText("pack", "name"))->setStrLabel($this->getLang("pack_name"))->setBitMandatory(true)->setStrValue($this->getParam("pack_name"));
        $objFormgenerator->addField(new FormentryPlaintext())->setStrValue($this->objToolkit->warningBox($this->getLang("pack_copy_include"), "alert-info"));
        $arrModules = Classloader::getInstance()->getArrModules();
        $objFilesystem = new Filesystem();

        foreach ($arrModules as $strOneModule) {
            //validate if there's a template-folder existing
            if (is_dir(Resourceloader::getInstance()->getAbsolutePathForModule($strOneModule)."/templates/default/tpl")) {
                $arrContent = $objFilesystem->getFilelist(Resourceloader::getInstance()->getAbsolutePathForModule($strOneModule)."/templates/default", array(".tpl"), true);

                if (count($arrContent) > 0) {
                    $objFormgenerator->addField(new FormentryHeadline())->setStrValue(StringUtil::replace("module_", "", $strOneModule));


                    foreach ($arrContent as $strPath => $strOneFile) {
                        $bitReadonly = false;
                        if ($objTargetObject != null) {
                            $strPath2 = _templatepath_."/".$objTargetObject->getStrName()."/".StringUtil::substring($strPath, StringUtil::indexOf($strPath, "/default/") + 9);
                            if (is_file(_realpath_.$strPath2)) {
                                $bitReadonly = true;
                            }
                        }

                        $strOneFileAppend = " <code>".StringUtil::substring($strPath, StringUtil::indexOf($strPath, "/tpl") + 4)."</code>";


                        $objFormgenerator->addField(new FormentryCheckbox("pack", "path[".$strPath."]"))->setStrLabel($strOneFile.$strOneFileAppend)->setStrValue($bitReadonly || ($objTargetObject == null && $strOneModule == "module_pages" && $strOneFile == "home.tpl" || $strOneFile == "standard.tpl"))
                            //->setStrHint(" <code>".$strPath."</code>")
                            ->setBitReadonly($bitReadonly);;

                    }
                }
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


        $objFilesystem = new Filesystem();
        $objFilesystem->folderCreate(_templatepath_."/".$strPackName);
        $objFilesystem->folderCreate(_templatepath_."/".$strPackName."/tpl");
        $objFilesystem->folderCreate(_templatepath_."/".$strPackName."/css");
        $objFilesystem->folderCreate(_templatepath_."/".$strPackName."/js");

        $arrModules = $this->getParam("pack_path");
        foreach ($arrModules as $strName => $strValue) {
            if ($strValue != "") {
                $strName = StringUtil::replace(StringUtil::toLowerCase(_realpath_), _realpath_, $strName);
                $strTarget = _templatepath_."/".$strPackName."/".StringUtil::substring($strName, StringUtil::indexOf($strName, "/default/") + 9);
                $objFilesystem->fileCopy($strName, $strTarget);
            }
        }

        Carrier::getInstance()->flushCache(Carrier::INT_CACHE_TYPE_CLASSLOADER);

        $this->adminReload(Link::getLinkAdminHref($this->getArrModule("modul"), "listTemplates"));
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

        $objFilesystem = new Filesystem();
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
