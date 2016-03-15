<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


namespace Kajona\Installer;

use Kajona\Packagemanager\System\PackagemanagerManager;
use Kajona\Packagemanager\System\PackagemanagerMetadata;
use Kajona\System\System\Carrier;
use Kajona\System\System\Classloader;
use Kajona\System\System\Cookie;
use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\Exception;
use Kajona\System\System\Lang;
use Kajona\System\System\RequestEntrypointEnum;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\ScriptletHelper;
use Kajona\System\System\Session;
use Kajona\System\System\SystemEventidentifier;
use Kajona\System\System\SystemModule;
use Kajona\System\System\Template;

/**
 * Class representing a graphical installer.
 * Loads all sub-installers
 *
 * @author sidler@mulchprod.de
 * @package module_system
 */
class Installer {

    private $STR_PROJECT_CONFIG_FILE = "";

    /**
     * @var PackagemanagerMetadata[]
     */
    private $arrMetadata;
    private $strOutput = "";
    private $strLogfile = "";
    private $strForwardLink = "";
    private $strBackwardLink = "";

    private $strVersion = "V 4.7";

    /**
     * Instance of template-engine
     *
     * @var Template
     */
    private $objTemplates;

    /**
     * text-object
     *
     * @var Lang
     */
    private $objLang;

    /**
     * session
     *
     * @var Session
     */
    private $objSession;


    public function __construct() {
        //start up system
        $this->objTemplates = Carrier::getInstance()->getObjTemplate();
        $this->objLang = Carrier::getInstance()->getObjLang();
        //init session-support
        $this->objSession = Carrier::getInstance()->getObjSession();

        //set a different language?
        if(issetGet("language")) {
            if(in_array(getGet("language"), explode(",", Carrier::getInstance()->getObjConfig()->getConfig("adminlangs")))) {
                $this->objLang->setStrTextLanguage(getGet("language"));
                //and save to a cookie
                $objCookie = new Cookie();
                $objCookie->setCookie("adminlanguage", getGet("language"));

            }
        }
        else {
            //init correct text-file handling as in admins
            $this->objLang->setStrTextLanguage($this->objSession->getAdminLanguage(true));
        }

        $this->STR_PROJECT_CONFIG_FILE = _realpath_."/project/module_system/system/config/config.php";
    }


    /**
     * Action block to control the behaviour

     */
    public function action() {

        //check if needed values are given
        if(!$this->checkDefaultValues())
            $this->configWizard();

        //load a list of available installers
        $this->loadInstaller();

        //step one: needed php-values
        if(!isset($_GET["step"]))
            $this->checkPHPSetting();


        elseif($_GET["step"] == "config" || !$this->checkDefaultValues()) {
            $this->configWizard();
        }

        elseif($_GET["step"] == "loginData") {
            $this->adminLoginData();
        }

        elseif($_GET["step"] == "modeSelect") {
            $this->modeSelect();
        }

        elseif($_GET["step"] == "install") {
            $this->createModuleInstalls();
        }

        elseif($_GET["step"] == "samplecontent") {
            $this->installSamplecontent();
        }

        elseif($_GET["step"] == "finish") {
            $this->finish();
        }

        $strContent = $this->strOutput;
        if($this->strOutput != "") {
            $strContent = $this->renderOutput();
        }
        ResponseObject::getInstance()->setStrContent($strContent);
    }

    /**
     * Makes a few checks on files and settings for a correct webserver

     */
    public function checkPHPSetting() {
        $strReturn = "";


        $arrFilesAndFolders = array(
            "/project/module_system/system/config",
            "/project/dbdumps",
            "/project/log",
            "/project/temp",
            "/files/cache",
            "/files/images",
            "/files/public",
            "/files/downloads",
            "/templates/default"
        );
        $arrFilesAndFolders = array_merge($arrFilesAndFolders, array_map(function($strValue) { return "/".$strValue; }, Classloader::getInstance()->getCoreDirectories()));

        $arrModules = array(
            "mbstring",
            "gd",
            "xml",
            "zip",
            "openssl"
        );

        $strReturn .= $this->getLang("installer_phpcheck_intro");
        $strReturn .= $this->getLang("installer_phpcheck_lang");

        //link to different languages
        $arrLangs = explode(",", Carrier::getInstance()->getObjConfig()->getConfig("adminlangs"));
        $intLangCount = 1;
        foreach($arrLangs as $strOneLang) {
            $strReturn .= "<a href=\""._webpath_."/installer.php?language=".$strOneLang."\">".Carrier::getInstance()->getObjLang()->getLang("lang_".$strOneLang, "user")."</a>";
            if($intLangCount++ < count($arrLangs)) {
                $strReturn .= " | ";
            }
        }

        $strReturn .= "<br />".$this->getLang("installer_phpcheck_intro2")."<ul>";

        foreach($arrFilesAndFolders as $strOneFile) {
            $strReturn .= "<li>".$this->getLang("installer_phpcheck_folder").$strOneFile." ";
            if(is_writable(_realpath_.$strOneFile))
                $strReturn .= "<span class=\"label label-success\">".$this->getLang("installer_given")."</span>.";
            else
                $strReturn .= "<span class=\"label label-danger\">".$this->getLang("installer_missing")."</span>!";
            $strReturn .= "</li>";
        }

        foreach($arrModules as $strOneModule) {
            $strReturn .= "<li>".$this->getLang("installer_phpcheck_module").$strOneModule." ";
            if(in_array($strOneModule, get_loaded_extensions()))
                $strReturn .= " <span class=\"label label-success\">".$this->getLang("installer_loaded")."</span>.";
            else
                $strReturn .= " <span class=\"label label-danger\">".$this->getLang("installer_nloaded")."</span>!";

            $strReturn .= "</li>";
        }

        $strReturn .= "</ul>";
        $this->strForwardLink = $this->getForwardLink(_webpath_."/installer.php?step=config");
        $this->strBackwardLink = "";
        $this->strOutput = $strReturn;
    }

    /**
     * Shows a form to write the values to the config files

     */
    public function configWizard() {
        $strReturn = "";

        if($this->checkDefaultValues()) {
            ResponseObject::getInstance()->setStrRedirectUrl(_webpath_."/installer.php?step=loginData");
            return;
        }

        $bitCxCheck = true;

        if(isset($_POST["write"]) && $_POST["write"] == "true") {


            //try to validate the data passed
            $bitCxCheck = Carrier::getInstance()->getObjDB()->validateDbCxData(
                $_POST["driver"],
                $_POST["hostname"],
                $_POST["username"],
                $_POST["password"],
                $_POST["dbname"],
                $_POST["port"]
            );


            if($bitCxCheck) {
                $strFileContent = "<?php\n";
                $strFileContent .= "/*\n Kajona V5 config-file.\n If you want to overwrite additional settings, copy them from /core/module_system/system/config/config.php into this file.\n*/";
                $strFileContent .= "\n\n\n";
                $strFileContent .= "  \$config['dbhost']               = '".$_POST["hostname"]."';                   //Server name \n";
                $strFileContent .= "  \$config['dbusername']           = '".$_POST["username"]."';                   //Username \n";
                $strFileContent .= "  \$config['dbpassword']           = '".$_POST["password"]."';                   //Password \n";
                $strFileContent .= "  \$config['dbname']               = '".$_POST["dbname"]."';                     //Database name \n";
                $strFileContent .= "  \$config['dbdriver']             = '".$_POST["driver"]."';                     //DB-Driver \n";
                $strFileContent .= "  \$config['dbprefix']             = '".$_POST["dbprefix"]."';                   //Table-prefix \n";
                $strFileContent .= "  \$config['dbport']               = '".$_POST["port"]."';                       //Database port \n";

                $strFileContent .= "\n";
                //and save to file
                file_put_contents($this->STR_PROJECT_CONFIG_FILE, $strFileContent);

                // flush cache after config was written
                Classloader::getInstance()->flushCache();

                // and reload
                ResponseObject::getInstance()->setStrRedirectUrl(_webpath_."/installer.php?step=loginData");
                $this->strOutput = "";
                return;
            }
        }


        //check for available modules
        $strMysqliInfo = "";
        $strSqlite3Info = "";
        $strPostgresInfo = "";
        $strOci8Info = "";
        if(!in_array("mysqli", get_loaded_extensions())) {
            $strMysqliInfo = "<div class=\"alert alert-danger\">".$this->getLang("installer_dbdriver_na")." mysqli</div>";
        }
        if(!in_array("pgsql", get_loaded_extensions())) {
            $strPostgresInfo = "<div class=\"alert alert-danger\">".$this->getLang("installer_dbdriver_na")." postgres</div>";
        }
        if(in_array("sqlite3", get_loaded_extensions())) {
            $strSqlite3Info = "<div class=\"alert alert-info\">".$this->getLang("installer_dbdriver_sqlite3")."</div>";
        }
        else {
            $strSqlite3Info = "<div class=\"alert alert-danger\">".$this->getLang("installer_dbdriver_na")." sqlite3</div>";
        }
        if(in_array("oci8", get_loaded_extensions())) {
            $strOci8Info = "<div class=\"alert alert-info\">".$this->getLang("installer_dbdriver_oci8")."</div>";
        }
        else {
            $strOci8Info = "<div class=\"alert alert-danger\">".$this->getLang("installer_dbdriver_na")." oci8</div>";
        }

        $strCxWarning = "";
        if(!$bitCxCheck) {
            $strCxWarning = "<div class=\"alert alert-danger\">".$this->getLang("installer_dbcx_error")."</div>";
        }

        //configwizard_form
        $strReturn .= $this->objTemplates->fillTemplateFile(
            array(
                "mysqliInfo"       => $strMysqliInfo,
                "sqlite3Info"      => $strSqlite3Info,
                "postgresInfo"     => $strPostgresInfo,
                "oci8Info"         => $strOci8Info,
                "cxWarning"        => $strCxWarning,
                "postHostname"     => isset($_POST["hostname"]) ? $_POST["hostname"] : "",
                "postUsername"     => isset($_POST["username"]) ? $_POST["username"] : "",
                "postDbname"       => isset($_POST["dbname"]) ? $_POST["dbname"] : "",
                "postDbport"       => isset($_POST["port"]) ? $_POST["port"] : "",
                "postDbdriver"     => isset($_POST["driver"]) ? $_POST["driver"] : "",
                "postPrefix"       => isset($_POST["dbprefix"]) != "" ? $_POST["dbprefix"] : "kajona_"
            ),
            "/module_installer/installer.tpl", "configwizard_form"
        );
        $this->strBackwardLink = $this->getBackwardLink(_webpath_."/installer.php");


        $this->strOutput = $strReturn;
    }

    /**
     * Collects the data required to create a valid admin-login
     */
    public function adminLoginData() {
        $bitShowForm = true;
        $this->strOutput .= $this->getLang("installer_login_intro");


        if($this->isInstalled()) {
            $bitShowForm = false;
            $this->strOutput .= "<div class=\"alert alert-success\">".$this->getLang("installer_login_installed")."</div>";
        }
        if(isset($_POST["write"]) && $_POST["write"] == "true") {
            $strUsername = $_POST["username"];
            $strPassword = $_POST["password"];
            $strEmail = $_POST["email"];
            //save to session
            if($strUsername != "" && $strPassword != "" && checkEmailaddress($strEmail)) {
                $this->objSession->setSession("install_username", $strUsername);
                $this->objSession->setSession("install_password", $strPassword);
                $this->objSession->setSession("install_email", $strEmail);
                $this->strOutput = "";
                ResponseObject::getInstance()->setStrRedirectUrl(_webpath_."/installer.php?step=modeSelect");
                return;
            }
        }

        if($bitShowForm) {
            $this->strOutput .= $this->objTemplates->fillTemplateFile(array(), "/module_installer/installer.tpl", "loginwizard_form");
        }

        $this->strBackwardLink = $this->getBackwardLink(_webpath_."/installer.php");
        if($this->isInstalled())
            $this->strForwardLink = $this->getForwardLink(_webpath_."/installer.php?step=modeSelect");
    }

    /**
     * The form to select the installer mode - everything automatically or a manual selection
     */
    public function modeSelect() {

        if($this->isInstalled()) {
            ResponseObject::getInstance()->setStrRedirectUrl(_webpath_."/installer.php?step=install");
            return;
        }

        $this->strOutput .= $this->objTemplates->fillTemplateFile(
            array(
                "link_autoinstall" => _webpath_."/installer.php?step=finish&autoInstall=true",
                "link_manualinstall" => _webpath_."/installer.php?step=install"
            ),
            "/module_installer/installer.tpl", "modeselect_content"
        );

        $this->strBackwardLink = $this->getBackwardLink(_webpath_."/installer.php");

    }

    /**
     * Loads all installers available to this->arrInstaller

     */
    public function loadInstaller() {

        $objManager = new PackagemanagerManager();
        $arrModules = $objManager->getAvailablePackages();

        $this->arrMetadata = array();
        foreach($arrModules as $objOneModule)
            if($objOneModule->getBitProvidesInstaller())
                $this->arrMetadata[] = $objOneModule;

        $this->arrMetadata = $objManager->sortPackages($this->arrMetadata, true);

    }

    /**
     * Loads all installers and requests a install / update link, if available
     */
    public function createModuleInstalls() {
        $strReturn = "";
        $strInstallLog = "";

        $objManager = new PackagemanagerManager();

        //module-installs to loop?
        if(isset($_POST["moduleInstallBox"]) && is_array($_POST["moduleInstallBox"])) {
            $arrModulesToInstall = $_POST["moduleInstallBox"];
            foreach($arrModulesToInstall as $strOneModule => $strValue) {

                //search the matching modules
                foreach($this->arrMetadata as $objOneMetadata) {
                    if($strOneModule == "installer_".$objOneMetadata->getStrTitle()) {
                        $objHandler = $objManager->getPackageManagerForPath($objOneMetadata->getStrPath());
                        $strInstallLog .= $objHandler->installOrUpdate();
                    }
                }

            }

        }

        Carrier::getInstance()->flushCache(Carrier::INT_CACHE_TYPE_DBQUERIES | Carrier::INT_CACHE_TYPE_ORMCACHE | Carrier::INT_CACHE_TYPE_OBJECTFACTORY | Carrier::INT_CACHE_TYPE_MODULES);
        $this->loadInstaller();


        $this->strLogfile = $strInstallLog;
        $strReturn .= $this->getLang("installer_modules_found");

        $strRows = "";
        //Loading each installer
        foreach($this->arrMetadata as $objOneMetadata) {

            //skip samplecontent
            if($objOneMetadata->getStrTitle() == "samplecontent")
                continue;

            $objHandler = $objManager->getPackageManagerForPath($objOneMetadata->getStrPath());

            $arrTemplate = array();
            $arrTemplate["module_name"] = $objHandler->getObjMetadata()->getStrTitle();
            $arrTemplate["module_nameShort"] = $objHandler->getObjMetadata()->getStrTitle();
            $arrTemplate["module_version"] = $objHandler->getObjMetadata()->getStrVersion();

            //generate the hint
            $arrTemplate["module_hint"] = "";

            if($objHandler->getVersionInstalled() !== null) {
                $arrTemplate["module_hint"] .= $this->getLang("installer_versioninstalled").$objHandler->getVersionInstalled()."<br />";
            }

            //check missing modules
            $arrModules = $objHandler->getObjMetadata()->getArrRequiredModules();
            foreach($arrModules as $strOneModule => $strVersion) {
                if(trim($strOneModule) != "" && SystemModule::getModuleByName(trim($strOneModule)) === null) {

                    //check if a corresponding module is available
                    $objPackagemanager = new PackagemanagerManager();
                    $objPackage = $objPackagemanager->getPackage($strOneModule);

                    if($objPackage === null || $objPackage->getBitProvidesInstaller() || version_compare($strVersion, $objPackage->getStrVersion(), ">")) {
                        $arrTemplate["module_hint"] .= $this->getLang("installer_systemversion_needed").$strOneModule." >= ".$strVersion."<br />";
                    }

                }

                else if(version_compare($strVersion, SystemModule::getModuleByName(trim($strOneModule))->getStrVersion(), ">")) {
                    $arrTemplate["module_hint"] .= $this->getLang("installer_systemversion_needed").$strOneModule." >= ".$strVersion."<br />";
                }
            }




            if($objHandler->isInstallable()) {
                $strRows .= $this->objTemplates->fillTemplateFile($arrTemplate, "/module_installer/installer.tpl", "installer_modules_row_installable");
            }
            else {
                $strRows .= $this->objTemplates->fillTemplateFile($arrTemplate, "/module_installer/installer.tpl", "installer_modules_row");
            }

        }

        //wrap in form
        $strReturn .= $this->objTemplates->fillTemplateFile(array("module_rows" => $strRows), "/module_installer/installer.tpl", "installer_modules_form");

        $this->strOutput .= $strReturn;
        if($this->isInstalled())
            $this->strBackwardLink = $this->getBackwardLink(_webpath_."/installer.php?step=loginData");
        else
            $this->strBackwardLink = $this->getBackwardLink(_webpath_."/installer.php?step=modeSelect");
        $this->strForwardLink = $this->getForwardLink(_webpath_."/installer.php?step=samplecontent");
    }


    /**
     * Installs, if available, the samplecontent
     */
    public function installSamplecontent() {
        $strReturn = "";
        $strInstallLog = "";

        $objManager = new PackagemanagerManager();

        //Is there a module to be installed or updated?
        if(isset($_GET["update"])) {
            foreach($this->arrMetadata as $objOneMetadata) {
                if($objOneMetadata->getStrTitle() != "samplecontent")
                    continue;

                $objHandler = $objManager->getPackageManagerForPath($objOneMetadata->getStrPath());
                $strInstallLog .= $objHandler->installOrUpdate();
            }
        }

        //module-installs to loop?
        if(isset($_POST["moduleInstallBox"]) && is_array($_POST["moduleInstallBox"])) {
            foreach($this->arrMetadata as $objOneMetadata) {
                if($objOneMetadata->getStrTitle() != "samplecontent")
                    continue;

                $objHandler = $objManager->getPackageManagerForPath($objOneMetadata->getStrPath());
                $strInstallLog .= $objHandler->installOrUpdate();
            }
        }

        $this->strLogfile = $strInstallLog;
        $strReturn .= $this->getLang("installer_samplecontent");

        //Loading each installer
        $strRows = "";

        $bitInstallerFound = false;
        foreach($this->arrMetadata as $objOneMetadata) {

            if($objOneMetadata->getStrTitle() != "samplecontent")
                continue;

            $bitInstallerFound = true;

            $objHandler = $objManager->getPackageManagerForPath($objOneMetadata->getStrPath());

            $arrTemplate = array();
            $arrTemplate["module_nameShort"] = $objOneMetadata->getStrTitle();
            $arrTemplate["module_name"] = $objOneMetadata->getStrTitle();
            $arrTemplate["module_version"] = $objOneMetadata->getStrVersion();

            //generate the hint
            $arrTemplate["module_hint"] = "";

            if($objHandler->getVersionInstalled() !== null) {
                $arrTemplate["module_hint"] = $this->getLang("installer_versioninstalled").$objHandler->getVersionInstalled();
            }
            else {
                //check missing modules
                $strRequired = "";
                $arrModules = $objHandler->getObjMetadata()->getArrRequiredModules();
                foreach($arrModules as $strOneModule => $strVersion) {
                    if(trim($strOneModule) != "" && SystemModule::getModuleByName(trim($strOneModule)) === null)
                        $strRequired .= $strOneModule.", ";
                }

                if(trim($strRequired) != "")
                    $arrTemplate["module_hint"] = $this->getLang("installer_modules_needed").substr($strRequired, 0, -2);
            }

            if($objHandler->isInstallable())
                $strRows .= $this->objTemplates->fillTemplateFile($arrTemplate, "/module_installer/installer.tpl", "installer_modules_row_installable");
            else
                $strRows .= $this->objTemplates->fillTemplateFile($arrTemplate, "/module_installer/installer.tpl", "installer_modules_row");

        }

        if(!$bitInstallerFound) {
            $this->strOutput = "";
            ResponseObject::getInstance()->setStrRedirectUrl(_webpath_."/installer.php?step=finish");
            return;
        }

        //wrap in form
        $strReturn .= $this->objTemplates->fillTemplateFile(array("module_rows" => $strRows), "/module_installer/installer.tpl", "installer_samplecontent_form");

        $this->strOutput .= $strReturn;
        $this->strBackwardLink = $this->getBackwardLink(_webpath_."/installer.php?step=install");
        $this->strForwardLink = $this->getForwardLink(_webpath_."/installer.php?step=finish");
    }

    /**
     * The last page of the installer, showing a few infos and links how to go on

     */
    public function finish() {
        $strReturn = "";

        if(isset($_GET["autoInstall"]) && $_GET["autoInstall"] == "true") {
            $this->strLogfile = $this->processAutoInstall();
        }


        $this->objSession->sessionUnset("install_username");
        $this->objSession->sessionUnset("install_password");

        $strReturn .= $this->getLang("installer_finish_intro");
        $strReturn .= $this->getLang("installer_finish_hints");
        $strReturn .= $this->getLang("installer_finish_hints_update");
        $strReturn .= $this->getLang("installer_finish_closer");

        $this->strOutput = $strReturn;
        $this->strBackwardLink = $this->getBackwardLink(_webpath_."/installer.php?step=samplecontent");
    }


    public function processAutoInstall() {
        $strReturn = "";

        $strReturn .= "Searching for packages to be installed...\n";
        $objManager = new PackagemanagerManager();
        $arrPackageMetadata = $objManager->getAvailablePackages();

        /** @var PackagemanagerMetadata[] $arrPackagesToInstall */
        $arrPackagesToInstall = array();
        $objSamplecontent = null;
        foreach($arrPackageMetadata as $objOneMetadata) {
            if($objOneMetadata->getStrTitle() == "samplecontent") {
                $objSamplecontent = $objOneMetadata;
            }
            else {
                $arrPackagesToInstall[] = $objOneMetadata;
            }
        }

        $strReturn .= "Number of packages found: ".count($arrPackagesToInstall)."\n";
        $strReturn .= "\n\n";

        $intMaxLoops = 0;
        $strReturn .= "starting installations...\n";
        while(count($arrPackagesToInstall) > 0 && ++$intMaxLoops < 100) {
            foreach($arrPackagesToInstall as $intKey => $objOneMetadata) {

                $strReturn .= "------------------------------\n";

                if(!$objOneMetadata->getBitProvidesInstaller()) {
                    $strReturn .= "skipping ".$objOneMetadata->getStrTitle().", no installer provided...\n";
                    unset($arrPackagesToInstall[$intKey]);
                    continue;
                }

                $strReturn .= "Installing ".$objOneMetadata->getStrTitle()."...\n";
                $objHandler = $objManager->getPackageManagerForPath($objOneMetadata->getStrPath());

                if(!$objHandler->isInstallable()) {
                    $strReturn .= "skipping ".$objOneMetadata->getStrTitle()." due to unresolved requirements\n";
                    continue;
                }

                $strReturn .= $objHandler->installOrUpdate();
                unset($arrPackagesToInstall[$intKey]);
                $strReturn .= "\n";
            }
        }


        $strReturn .= "Installing samplecontent...\n";
        if($objSamplecontent != null) {
            $objHandler = $objManager->getPackageManagerForPath($objSamplecontent->getStrPath());
            if ($objHandler->isInstallable()) {
                $strReturn .= $objHandler->installOrUpdate();
            }
        }


        return $strReturn;
    }


    /**
     * Generates the surrounding layout and embeds the installer-output
     *
     * @return string
     */
    private function renderOutput() {

        CoreEventdispatcher::getInstance()->notifyGenericListeners(SystemEventidentifier::EVENT_SYSTEM_REQUEST_ENDPROCESSING, array());

        if($this->strLogfile != "") {
            $this->strLogfile = $this->objTemplates->fillTemplateFile(
                array(
                    "log_content" => $this->strLogfile,
                    "systemlog"   => $this->getLang("installer_systemlog")
                ), "/module_installer/installer.tpl", "installer_log"
            );
        }


        //build the progress-entries
        $strCurrentCommand = (isset($_GET["step"]) ? $_GET["step"] : "");
        if($strCurrentCommand == "")
            $strCurrentCommand = "phpsettings";

        $arrProgressEntries = array(
            "phpsettings"   => $this->getLang("installer_step_phpsettings"),
            "config"        => $this->getLang("installer_step_dbsettings"),
            "loginData"     => $this->getLang("installer_step_adminsettings"),
            "modeSelect"     => $this->getLang("installer_step_modeselect"),
            "install"       => $this->getLang("installer_step_modules"),
            "samplecontent" => $this->getLang("installer_step_samplecontent"),
            "finish"        => $this->getLang("installer_step_finish"),
        );

        $strProgress = "";

        foreach($arrProgressEntries as $strKey => $strValue) {
            $arrTemplateEntry = array();
            $arrTemplateEntry["entry_name"] = $strValue;

            //choose the correct template section
            if($strCurrentCommand == $strKey) {
                $strProgress .= $this->objTemplates->fillTemplateFile($arrTemplateEntry, "/module_installer/installer.tpl", "installer_progress_entry_current");
            }
            else
                $strProgress .= $this->objTemplates->fillTemplateFile($arrTemplateEntry, "/module_installer/installer.tpl", "installer_progress_entry_done");

        }
        $arrTemplate = array();
        $arrTemplate["installer_progress"] = $strProgress;
        $arrTemplate["installer_version"] = $this->strVersion;
        $arrTemplate["installer_output"] = $this->strOutput;
        $arrTemplate["installer_forward"] = $this->strForwardLink;
        $arrTemplate["installer_backward"] = $this->strBackwardLink;
        $arrTemplate["installer_logfile"] = $this->strLogfile;

        $strReturn = $this->objTemplates->fillTemplateFile($arrTemplate, "/module_installer/installer.tpl", "installer_main");
        $strReturn = $this->callScriptlets($strReturn);
        return $strReturn;
    }


    /**
     * Calls the scriptlets in order to process additional tags and in order to enrich the content.
     *
     * @param $strContent
     *
     * @return string
     */
    private function callScriptlets($strContent) {
        $objHelper = new ScriptletHelper();
        return $objHelper->processString($strContent);
    }


    /**
     * Checks, if the config-file was filled with correct values
     *
     * @return bool
     */
    private function checkDefaultValues() {
        return is_file($this->STR_PROJECT_CONFIG_FILE);
    }

    /**
     * Creates a forward-link
     *
     * @param string $strHref
     *
     * @return string
     */
    private function getForwardLink($strHref) {
        return $this->objTemplates->fillTemplateFile(array("href" => $strHref, "text" => $this->getLang("installer_next")), "/module_installer/installer.tpl", "installer_forward_link");
    }

    /**
     * Creates backward-link
     *
     * @param string $strHref
     *
     * @return string
     */
    private function getBackwardLink($strHref) {
        return $this->objTemplates->fillTemplateFile(array("href" => $strHref, "text" => $this->getLang("installer_prev")), "/module_installer/installer.tpl", "installer_backward_link");
    }

    /**
     * Loads a text
     *
     * @param string $strKey
     * @param array $arrParameters
     *
     * @return string
     */
    private function getLang($strKey, $arrParameters = array()) {
        return $this->objLang->getLang($strKey, "installer", $arrParameters);
    }

    private function isInstalled() {
        try {
            $objUser = SystemModule::getModuleByName("user");
            if($objUser != null) {
                return true;
            }
        }
        catch(Exception $objE) {
        }

        return false;
    }
}


//set admin to false
define("_admin_", false);

//Creating the Installer-Object
$objInstaller = new Installer();
$objInstaller->action();
CoreEventdispatcher::getInstance()->notifyGenericListeners(SystemEventidentifier::EVENT_SYSTEM_REQUEST_ENDPROCESSING, array());
ResponseObject::getInstance()->sendHeaders();
ResponseObject::getInstance()->sendContent();
CoreEventdispatcher::getInstance()->notifyGenericListeners(SystemEventidentifier::EVENT_SYSTEM_REQUEST_AFTERCONTENTSEND, array(RequestEntrypointEnum::INSTALLER()));

