<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                                *
********************************************************************************************************/
//includes...
include_once("../system/includes.php");


/**
 * Class representing a graphical installer.
 * Loads all subinstallers
 *
 * @author sidler@mulchprod.de
 * @package modul_system
 */
class class_installer {

	private $arrInstaller;
	private $strOutput = "";
	private $strLogfile = "";
	private $strForwardLink = "";
	private $strBackwardLink = "";

	private $strVersion = "V 3.1.1";

	/**
	 * Instance of template-engine
	 *
	 * @var class_template
	 */
	private $objTemplates;

	/**
	 * text-object
	 *
	 * @var class_texte
	 */
	private $objTexte;

	/**
	 * session
	 *
	 * @var class_session
	 */
	private $objSession;


	public function __construct() {
	    //start up system
		class_carrier::getInstance();
		$this->objTemplates = class_carrier::getInstance()->getObjTemplate();
		$this->objTexte = class_carrier::getInstance()->getObjText();
		//init session-support
		$this->objSession = class_carrier::getInstance()->getObjSession();

		include_once(_systempath_."/class_modul_system_setting.php");

		//set a different language?
		if(issetGet("language")) {
		    if(in_array(getGet("language"), explode(",", class_carrier::getInstance()->getObjConfig()->getConfig("adminlangs"))))
		        $this->objTexte->setStrTextLanguage(getGet("language"));
		        //and save to a cookie
		        require_once(_systempath_."/class_cookie.php");
        	    $objCookie = new class_cookie();
        	    $objCookie->setCookie("adminlanguage", getGet("language"));
		}
        else {
		  //init correct text-file handling as in admins
		  $this->objTexte->setStrTextLanguage($this->objSession->getAdminLanguage(true));
        }
        
	}


	/**
	 * Action-Block to control the behaviour
	 *
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


        elseif ($_GET["step"] == "config" || !$this->checkDefaultValues()) {
            $this->configWizard();
        }

        elseif ($_GET["step"] == "loginData") {
            $this->adminLoginData();
        }

        elseif ($_GET["step"] == "install") {
            $this->createModuleInstalls();
        }

        elseif ($_GET["step"] == "postInstall") {
            $this->createModulePostInstalls();
        }

        elseif ($_GET["step"] == "samplecontent") {
            $this->installSamplecontent();
        }

        elseif ($_GET["step"] == "finish") {
            $this->finish();
        }
	}

	/**
	 * Makes a few checks on files and settings for a correct webserver
	 *
	 */
	public function checkPHPSetting() {
	    $strReturn = "";

	    $arrFilesAndFolders = array("/system/config/config.php",
	                                "/system/dbdumps",
	                                "/system/debug",
	                                "/portal/pics/cache",
	                                "/portal/pics/upload");

	    $arrModules = array("mbstring",
	                        "gd",
	                        "xml");

	    $strReturn .= $this->getText("installer_phpcheck_intro");
	    $strReturn .= $this->getText("installer_phpcheck_lang");

	    //link to different languages
	    foreach (explode(",", class_carrier::getInstance()->getObjConfig()->getConfig("adminlangs")) as $strOneLang) {
            $strReturn .= "<a href=\""._webpath_."/installer/installer.php?language=".$strOneLang."\">".class_carrier::getInstance()->getObjText()->getText("lang_".$strOneLang, "user", "admin")."</a><br />";
	    }

	    $strReturn .= $this->getText("installer_phpcheck_intro2");

	    foreach ($arrFilesAndFolders as $strOneFile) {
    	    $strReturn .= $this->getText("installer_phpcheck_folder").$strOneFile."...<br />";
    	    if(is_writable(_realpath_.$strOneFile))
    	       $strReturn .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...<span class=\"green\">".$this->getText("installer_given")."</span>.<br />";
    	    else
    	       $strReturn .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...<span class=\"red\">".$this->getText("installer_missing")."</span>!<br />";
	    }

	    foreach($arrModules as $strOneModule) {
    	    $strReturn .= $this->getText("installer_phpcheck_module").$strOneModule."...<br />";
    	    if(in_array($strOneModule, get_loaded_extensions()))
    	       $strReturn .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...<span class=\"green\">".$this->getText("installer_loaded")."</span>.<br />";
    	    else
    	       $strReturn .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...<span class=\"red\">".$this->getText("installer_nloaded")."</span>!<br />";
	    }

	    $this->strForwardLink = $this->getForwardLink(_webpath_."/installer/installer.php?step=config");
        $this->strBackwardLink = "";
	    $this->strOutput = $strReturn;
	}

	/**
	 * Shows a form to write the values to the config files
	 *
	 */
	public function configWizard() {
        $strReturn = "";

        if($this->checkDefaultValues())
            header("Location: "._webpath_."/installer/installer.php?step=loginData");

        if(!isset($_POST["write"])) {

            //configwizard_form
            $strTemplateID = $this->objTemplates->readTemplate("installer/installer.tpl", "configwizard_form", true);
	        $strReturn .= $this->objTemplates->fillTemplate(array("config_intro" => $this->getText("installer_config_intro"),
	                                                              "config_hostname"  => $this->getText("installer_config_dbhostname"),
                                                                  "config_username"  => $this->getText("installer_config_dbusername"),
                                                                  "config_password"  => $this->getText("installer_config_dbpassword"),
                                                                  "config_port"  => $this->getText("installer_config_dbport"),
                                                                  "config_portinfo"  => $this->getText("installer_config_dbportinfo"),
                                                                  "config_driver"  => $this->getText("installer_config_dbdriver"),
                                                                  "config_dbname"  => $this->getText("installer_config_dbname"),
                                                                  "config_prefix"  => $this->getText("installer_config_dbprefix"),
                                                                  "config_save"  => $this->getText("installer_config_write")
	        ), $strTemplateID);
	        $this->strBackwardLink = $this->getBackwardLink(_webpath_."/installer/installer.php");

        }
        elseif ($_POST["write"] == "true") {
            //check vor values
            if($_POST["hostname"] == "" || $_POST["username"] == "" || $_POST["password"] == "" || $_POST["dbname"] == "" || $_POST["driver"] == "") {
                header("Location: "._webpath_."/installer/installer.php");
                return;
            }

                //collect data
               $arrSearch = array(
                   "%%defaulthost%%",
                   "%%defaultusername%%",
                   "%%defaultpassword%%",
                   "%%defaultdbname%%",
                   "%%defaultprefix%%",
                   "%%defaultdriver%%",
                   "%%defaultport%%"
               );
               $arrReplace = array(
                   $_POST["hostname"],
                   $_POST["username"],
                   $_POST["password"],
                   $_POST["dbname"],
                   $_POST["dbprefix"],
                   $_POST["driver"],
                   $_POST["port"]
               );
            //load config file
            $strConfig = file_get_contents(_systempath_."/config/config.php");
            //insert values
            $strConfig = str_replace($arrSearch, $arrReplace, $strConfig);
            //and save to file
            file_put_contents(_systempath_."/config/config.php", $strConfig);
            // and reload
            header("Location: "._webpath_."/installer/installer.php?step=loginData");
        }

        $this->strOutput = $strReturn;
	}

	/**
	 * Collects the data required to create a valid admin-login
	 *
	 */
	public function adminLoginData() {
        $bitUserInstalled = false;
	    $bitShowForm = true;
	    $this->strOutput .= $this->getText("installer_login_intro");

	    //if user-moduls is already installed, skip this step
	    try {
	        include_once(_systempath_."/class_modul_system_module.php");
	        $objUser = class_modul_system_module::getModuleByName("user");
	        if($objUser != null) {
	            $bitUserInstalled = true;
	        }
	    }
	    catch (class_exception $objE) {
	    }


        if($bitUserInstalled) {
            $bitShowForm = false;
            $this->strOutput .= "<span class=\"green\">".$this->getText("installer_login_installed")."</span>";
        }
	    if(isset($_POST["write"]) && $_POST["write"] == "true") {
            $strUsername = $_POST["username"];
            $strPassword = $_POST["password"];
            //save to session
            if($strUsername != "" && $strPassword != "") {
                $bitShowForm = false;
                $this->objSession->setSession("install_username", $strUsername);
                $this->objSession->setSession("install_password", $strPassword);
                header("Location: "._webpath_."/installer/installer.php?step=install");
            }
	    }

	    if($bitShowForm){
	        $strTemplateID = $this->objTemplates->readTemplate("installer/installer.tpl", "loginwizard_form", true);
	        $this->strOutput .= $this->objTemplates->fillTemplate(array("login_username" => $this->getText("installer_login_username"),
	                                                                    "login_password" => $this->getText("installer_login_password"),
	                                                                    "login_save" => $this->getText("installer_login_save")
	                                                              ), $strTemplateID);
	    }

	    $this->strBackwardLink = $this->getBackwardLink(_webpath_."/installer/installer.php");
	    if($bitUserInstalled || ($this->objSession->getSession("install_username") !== false && $this->objSession->getSession("install_password") !== false))
	        $this->strForwardLink = $this->getForwardLink(_webpath_."/installer/installer.php?step=install");
	}

	/**
	 * Loads all installers avaliable to this->arrInstaller
	 *
	 */
	public function loadInstaller() {
		//File-Objekt
		include_once(_systempath_."/class_filesystem.php");
		$objFilesystem = new class_filesystem();
		//Ladend der Dateien
		$this->arrInstaller = $objFilesystem->getFilelist("/installer");

		foreach($this->arrInstaller as $intKey => $strFile)
			if(strpos($strFile, ".php") === false || $strFile == "installer.php" || $strFile == "class_installer_base.php" || $strFile == "interface_installer.php")
				unset($this->arrInstaller[$intKey]);

		asort($this->arrInstaller);
	}

    /**
     * Loads all installers and requests a install / update link, if available
     *
     */
	public function createModuleInstalls() {
		$strReturn = "";
		$strInstallLog = "";
		$strReturn .= "";

		//Is there a module to be installed or updated?
		if(isset($_GET["install"]) || isset($_GET["update"])) {
		    if(isset($_GET["install"]))
			    $strClass = $_GET["install"].".php";
			else
			    $strClass = $_GET["update"].".php";

			include_once(_realpath_."/installer/".$strClass);
		    $strClass = "class_".str_replace(".php", "", $strClass);
		    $objInstaller = new $strClass();

		    if(isset($_GET["install"]))
		        $strInstallLog = $objInstaller->doModuleInstall();
		    elseif (isset($_GET["update"]))
		        $strInstallLog = $objInstaller->doModuleUpdate();

		}
        $this->strLogfile = $strInstallLog;
		$strReturn .= $this->getText("installer_modules_found");

		//Loading each installer
		foreach($this->arrInstaller as $strInstaller) {
			include_once(_realpath_."/installer/".$strInstaller);
			//Creating an object....
			$strClass = "class_".str_replace(".php", "", $strInstaller);
			$objInstaller = new $strClass();

			if($objInstaller instanceof interface_installer && $strInstaller != "installer_samplecontent.php" && strpos($strInstaller, "element") === false )
			   $strReturn .= $objInstaller->getModuleInstallLink()."<br />";
		}
		$this->strOutput .= $strReturn;
		$this->strBackwardLink = $this->getBackwardLink(_webpath_."/installer/installer.php?step=loginData");
		$this->strForwardLink = $this->getForwardLink(_webpath_."/installer/installer.php?step=postInstall");
	}

	/**
     * Loads all installers and requests a post-install link, if available
     *
     */
	public function createModulePostInstalls() {
		$strReturn = "";
		$strInstallLog = "";
		$strReturn .= "";

		//Is there a module to be installed or updated?
		if(isset($_GET["postInstall"])) {
		    $strClass = $_GET["postInstall"].".php";

			include_once(_realpath_."/installer/".$strClass);
		    $strClass = "class_".str_replace(".php", "", $strClass);
		    $objInstaller = new $strClass();

		    $strInstallLog = $objInstaller->doPostInstall();
		}
        $this->strLogfile = $strInstallLog;
		$strReturn .= $this->getText("installer_elements_found");

		//Loading each installer
		foreach($this->arrInstaller as $strInstaller) {
			include_once(_realpath_."/installer/".$strInstaller);
			//Creating an object....
			$strClass = "class_".str_replace(".php", "", $strInstaller);
			$objInstaller = new $strClass();

			if($objInstaller instanceof interface_installer )
			   $strReturn .= $objInstaller->getModulePostInstallLink()."<br />";
		}
		$this->strOutput .= $strReturn;
		$this->strBackwardLink = $this->getBackwardLink(_webpath_."/installer/installer.php?step=install");
		$this->strForwardLink = $this->getForwardLink(_webpath_."/installer/installer.php?step=samplecontent");
	}


	/**
	 * Installs, if available, the samplecontent
	 *
	 */
	public function installSamplecontent() {
        $strReturn = "";
		$strInstallLog = "";
		$strReturn .= "\n\n";

		//Is there a module to be installed or updated?
		if(isset($_GET["install"]) || isset($_GET["update"])) {
		    if(isset($_GET["install"]))
			    $strClass = $_GET["install"].".php";
			else
			    $strClass = $_GET["update"].".php";

			include_once(_realpath_."/installer/".$strClass);
		    $strClass = "class_".str_replace(".php", "", $strClass);
		    $objInstaller = new $strClass();

		    if(isset($_GET["install"]))
		        $strInstallLog = $objInstaller->doModuleInstall();
		    elseif (isset($_GET["update"]))
		        $strInstallLog = $objInstaller->doModuleUpdate();
		}
        $this->strLogfile = $strInstallLog;
		$strReturn .= $this->getText("installer_samplecontent");

		//Loading each installer
		$bitInstallerFound = false;
		foreach($this->arrInstaller as $strInstaller) {
			include_once(_realpath_."/installer/".$strInstaller);
			//Creating an object....
			$strClass = "class_".str_replace(".php", "", $strInstaller);
			$objInstaller = new $strClass();

			if($objInstaller instanceof interface_installer && $strInstaller == "installer_samplecontent.php" && strpos($strInstaller, "element") === false ) {
			   $strReturn .= $objInstaller->getModuleInstallLink()."\n";
			   $bitInstallerFound = true;
			}
		}

		if(!$bitInstallerFound)
		    header("Location: "._webpath_."/installer/installer.php?step=finish");

		$this->strOutput .= $strReturn;
		$this->strBackwardLink = $this->getBackwardLink(_webpath_."/installer/installer.php?step=postInstall");
		$this->strForwardLink = $this->getForwardLink(_webpath_."/installer/installer.php?step=finish");
	}

	/**
	 * The last page of the installer, showing a few infos and links how to go on
	 *
	 */
	public function finish() {
	    $strReturn = "";

	    $this->objSession->sessionUnset("install_username");
	    $this->objSession->sessionUnset("install_password");

	    $strReturn .= $this->getText("installer_finish_intro");
	    $strReturn .= $this->getText("installer_finish_hints");
	    $strReturn .= $this->getText("installer_finish_closer");

	    $this->strOutput = $strReturn;
	    $this->strBackwardLink = $this->getBackwardLink(_webpath_."/installer/installer.php?step=samplecontent");
	}


	/**
	 * Generates the sourrounding layout and embedds the installer-output
	 *
	 * @return string
	 */
	public function getOutput() {
	    $arrTemp = array();
	    if($this->strLogfile != "") {
	        $strTemplateID = $this->objTemplates->readTemplate("installer/installer.tpl", "installer_log", true);
	        $this->strLogfile = $this->objTemplates->fillTemplate(array("log_content" => $this->strLogfile,
                                                                        "systemlog" => $this->getText("installer_systemlog")
                                                                  ), $strTemplateID);
	    }

	    $arrTemplate["installer_version"] = $this->strVersion;
	    $arrTemplate["installer_output"] = $this->strOutput;
	    $arrTemplate["installer_forward"] = $this->strForwardLink;
	    $arrTemplate["installer_backward"] = $this->strBackwardLink;
	    $arrTemplate["installer_logfile"] = $this->strLogfile;
	    $strTemplateID = $this->objTemplates->readTemplate("installer/installer.tpl", "installer_main", true);

		$strReturn = $this->objTemplates->fillTemplate($arrTemplate, $strTemplateID);
		$this->objTemplates->setTemplate($strReturn);
		$this->objTemplates->fillConstants();
		$this->objTemplates->deletePlaceholder();
		$strReturn = $this->objTemplates->getTemplate();
		return $strReturn;
	}

	/**
	 * Checks, if the config-file was filled with correct values
	 *
	 * @return bool
	 */
	public function checkDefaultValues() {
	    //use return true to diable config-check
	    //return true;
        //Load the conig to parse it
        $strConfig = file_get_contents(_systempath_."/config/config.php");
        //check all needed values
        if(   uniStrpos($strConfig, "%%defaulthost%%") !== false
           || uniStrpos($strConfig, "%%defaultusername%%") !== false
           || uniStrpos($strConfig, "%%defaultpassword%%") !== false
           || uniStrpos($strConfig, "%%defaultdbname%%") !== false
           || uniStrpos($strConfig, "%%defaultdriver%%") !== false
           || uniStrpos($strConfig, "%%defaultprefix%%") !== false
           || uniStrpos($strConfig, "%%defaultport%%") !== false
          )
            return false;
        else
            return true;
	}

	/**
	 * Creates a forward-link
	 *
	 * @param string $strHref
	 * @return string
	 */
	public function getForwardLink($strHref) {
	    $strTemplateID = $this->objTemplates->readTemplate("installer/installer.tpl", "installer_forward_link", true);
		return $this->objTemplates->fillTemplate(array("href" => $strHref, "text" => $this->getText("installer_next")), $strTemplateID);
	}

	/**
	 * Creates backward-link
	 *
	 * @param string $strHref
	 * @return string
	 */
	public function getBackwardLink($strHref) {
	    $strTemplateID = $this->objTemplates->readTemplate("installer/installer.tpl", "installer_backward_link", true);
		return $this->objTemplates->fillTemplate(array("href" => $strHref, "text" => $this->getText("installer_prev")), $strTemplateID);
	}

	/**
	 * Loads a text
	 *
	 * @param string $strKey
	 * @return string
	 */
	public function getText($strKey) {
	    return $this->objTexte->getText($strKey, "system", "admin");
	}
}


//Creating the Installer-Object
$objInstaller = new class_installer();
$objInstaller->action();
echo $objInstaller->getOutput();

?>