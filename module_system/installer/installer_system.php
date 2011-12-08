<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                        *
********************************************************************************************************/


/**
 * Installer for the system-module
 *
 * @package module_system
 */
class class_installer_system extends class_installer_base implements interface_installer {

    private $strContentLanguage;

	public function __construct() {

        $this->setArrModuleEntry("version", "3.4.9");
        $this->setArrModuleEntry("moduleId", _system_modul_id_);
        $this->setArrModuleEntry("name", "system");
        $this->setArrModuleEntry("name_lang", "System Kernel");

		parent::__construct();

		//set the correct language
		$this->strContentLanguage = $this->objSession->getAdminLanguage();
	}


	public function getMinSystemVersion() {
	    return "";
	}

	public function getNeededModules() {
	    return array();
	}

	public function hasPostInstalls() {
        //check, if not already existing
	    $objElement = null;
		try {
		    $objElement = class_module_pages_element::getElement("languageswitch");
		}
		catch (class_exception $objEx)  {
		}
        if($objElement == null)
            return true;

        return false;
	}

	public function postInstall() {
	    //Register the element
		$strReturn = "Registering languageswitch-element...\n";

        //check, if not already existing
        $objElement = null;
		try {
		    $objElement = class_module_pages_element::getElement("languageswitch");
		}
		catch (class_exception $objEx)  {
		}
		if($objElement == null) {
		    $objElement = new class_module_pages_element();
		    $objElement->setStrName("languageswitch");
		    $objElement->setStrClassAdmin("class_element_languageswitch_admin.php");
		    $objElement->setStrClassPortal("class_element_languageswitch_portal.php");
		    $objElement->setIntCachetime(3600*24*30);
		    $objElement->setIntRepeat(0);
            $objElement->setStrVersion($this->getVersion());
			$objElement->updateObjectToDb();
			$strReturn .= "Element registered...\n";
		}
		else {
			$strReturn .= "Element already installed!...\n";
		}

		return $strReturn;
	}


	public function install() {
	    $strReturn = "";

		// System table ---------------------------------------------------------------------------------
		$strReturn .= "Installing table system...\n";

		$arrFields = array();
		$arrFields["system_id"]             = array("char20", false);
		$arrFields["system_prev_id"]        = array("char20", false);
		$arrFields["system_module_nr"]      = array("int", false);
		$arrFields["system_sort"]           = array("int", true);
		$arrFields["system_owner"]          = array("char20", true);
		$arrFields["system_create_date"]    = array("long", true);
		$arrFields["system_lm_user"]        = array("char20", true);
		$arrFields["system_lm_time"]        = array("int", true);
		$arrFields["system_lock_id"]        = array("char20", true);
		$arrFields["system_lock_time"]  	= array("int", true);
		$arrFields["system_status"]         = array("int", true);
		$arrFields["system_class"]          = array("char254", true);
		$arrFields["system_comment"]        = array("char254", true);

		if(!$this->objDB->createTable("system", $arrFields, array("system_id"), array("system_prev_id", "system_module_nr")))
			$strReturn .= "An error occured! ...\n";

		//Rights table ----------------------------------------------------------------------------------
		$strReturn .= "Installing table system_right...\n";

		$arrFields = array();
		$arrFields["right_id"] 		= array("char20", false);
		$arrFields["right_inherit"] = array("int", true);
		$arrFields["right_view"] 	= array("text", true);
		$arrFields["right_edit"] 	= array("text", true);
		$arrFields["right_delete"] 	= array("text", true);
		$arrFields["right_right"] 	= array("text", true);
		$arrFields["right_right1"] 	= array("text", true);
		$arrFields["right_right2"] 	= array("text", true);
		$arrFields["right_right3"] 	= array("text", true);
		$arrFields["right_right4"] 	= array("text", true);
		$arrFields["right_right5"] 	= array("text", true);

		if(!$this->objDB->createTable("system_right", $arrFields, array("right_id")))
			$strReturn .= "An error occured! ...\n";

		// Modul table ----------------------------------------------------------------------------------
		$strReturn .= "Installing table system_module...\n";

		$arrFields = array();
		$arrFields["module_id"] 			    = array("char20", false);
		$arrFields["module_nr"]					= array("int", true);
		$arrFields["module_name"] 				= array("char254", true);
		$arrFields["module_filenameportal"] 	= array("char254", true);
		$arrFields["module_xmlfilenameportal"] 	= array("char254", true);
		$arrFields["module_filenameadmin"] 		= array("char254", true);
		$arrFields["module_xmlfilenameadmin"] 	= array("char254", true);
		$arrFields["module_version"] 			= array("char254", true);
		$arrFields["module_date"] 				= array("int", true);
		$arrFields["module_navigation"] 		= array("int", true);
		$arrFields["module_aspect"] 		    = array("char254", true);

		if(!$this->objDB->createTable("system_module", $arrFields, array("module_id")))
			$strReturn .= "An error occured! ...\n";

		// Date table -----------------------------------------------------------------------------------
		$strReturn .= "Installing table system_date...\n";

		$arrFields = array();
		$arrFields["system_date_id"] 		= array("char20", false);
		$arrFields["system_date_start"]		= array("long", true);
		$arrFields["system_date_end"] 		= array("long", true);
		$arrFields["system_date_special"] 	= array("long", true);

		if(!$this->objDB->createTable("system_date", $arrFields, array("system_date_id")))
			$strReturn .= "An error occured! ...\n";

		// Config table ---------------------------------------------------------------------------------
		$strReturn .= "Installing table system_config...\n";

		$arrFields = array();
		$arrFields["system_config_id"] 		= array("char20", false);
		$arrFields["system_config_name"]	= array("char254", true);
		$arrFields["system_config_value"] 	= array("char254", true);
		$arrFields["system_config_type"] 	= array("int", true);
		$arrFields["system_config_module"] 	= array("int", true);

		if(!$this->objDB->createTable("system_config", $arrFields, array("system_config_id")))
			$strReturn .= "An error occured! ...\n";


		// User table -----------------------------------------------------------------------------------
		$strReturn .= "Installing table user...\n";

		$arrFields = array();
		$arrFields["user_id"] 			= array("char20", false);
		$arrFields["user_username"]	    = array("char254", true);
		$arrFields["user_subsystem"]	= array("char254", true);
		$arrFields["user_logins"] 		= array("int", true);
		$arrFields["user_lastlogin"] 	= array("int", true);
		$arrFields["user_active"] 		= array("int", true);
		$arrFields["user_admin"] 		= array("int", true);
		$arrFields["user_portal"] 		= array("int", true);
		$arrFields["user_admin_skin"] 	= array("char254", true);
		$arrFields["user_admin_language"]=array("char254", true);
		$arrFields["user_authcode"]     =array("char20", true);

		if(!$this->objDB->createTable("user", $arrFields, array("user_id")))
			$strReturn .= "An error occured! ...\n";

        // User table kajona subsystem  -----------------------------------------------------------------
		$strReturn .= "Installing table user_kajona...\n";

		$arrFields = array();
		$arrFields["user_id"] 			= array("char20", false);
		$arrFields["user_pass"] 		= array("char254", true);
		$arrFields["user_email"] 		= array("char254", true);
		$arrFields["user_forename"] 	= array("char254", true);
		$arrFields["user_name"] 		= array("char254", true);
		$arrFields["user_street"] 		= array("char254", true);
		$arrFields["user_postal"] 		= array("char254", true);
		$arrFields["user_city"] 		= array("char254", true);
		$arrFields["user_tel"] 			= array("char254", true);
		$arrFields["user_mobile"] 		= array("char254", true);
		$arrFields["user_date"] 		= array("long", true);

		if(!$this->objDB->createTable("user_kajona", $arrFields, array("user_id")))
			$strReturn .= "An error occured! ...\n";

		// User group table -----------------------------------------------------------------------------
		$strReturn .= "Installing table user_group...\n";

		$arrFields = array();
		$arrFields["group_id"] 			= array("char20", false);
		$arrFields["group_name"]	    = array("char254", true);
		$arrFields["group_subsystem"]	= array("char254", true);

        if(!$this->objDB->createTable("user_group", $arrFields, array("group_id")))
			$strReturn .= "An error occured! ...\n";


        $strReturn .= "Installing table user_group_kajona...\n";

		$arrFields = array();
		$arrFields["group_id"] 			= array("char20", false);
		$arrFields["group_desc"]		= array("char254", true);


		if(!$this->objDB->createTable("user_group_kajona", $arrFields, array("group_id")))
			$strReturn .= "An error occured! ...\n";


		// User group_members table ---------------------------------------------------------------------
		$strReturn .= "Installing table user_kajona_members...\n";

		$arrFields = array();
		$arrFields["group_member_group_kajona_id"]      = array("char20", false);
		$arrFields["group_member_user_kajona_id"]		= array("char20", false);

		if(!$this->objDB->createTable("user_kajona_members", $arrFields, array("group_member_group_kajona_id", "group_member_user_kajona_id")))
			$strReturn .= "An error occured! ...\n";


		// User log table -------------------------------------------------------------------------------
		$strReturn .= "Installing table user_log...\n";

		$arrFields = array();
		$arrFields["user_log_id"] 		= array("char20", false);
		$arrFields["user_log_userid"]	= array("char254", true);
		$arrFields["user_log_date"] 	= array("int", true);
		$arrFields["user_log_status"] 	= array("int", true);
		$arrFields["user_log_ip"] 		= array("char20", true);

		if(!$this->objDB->createTable("user_log", $arrFields, array("user_log_id")))
			$strReturn .= "An error occured! ...\n";

		// Sessionmgtm ----------------------------------------------------------------------------------
		$strReturn .= "Installing table session...\n";

		$arrFields = array();
		$arrFields["session_id"] 		      = array("char20", false);
		$arrFields["session_phpid"]	          = array("char254", true);
		$arrFields["session_userid"] 	      = array("char20", true);
		$arrFields["session_groupids"] 	      = array("text", true);
		$arrFields["session_releasetime"]     = array("int", true);
		$arrFields["session_loginstatus"]     = array("char254", true);
		$arrFields["session_loginprovider"]   = array("char20", true);
		$arrFields["session_lasturl"] 		  = array("char500", true);

		if(!$this->objDB->createTable("session", $arrFields, array("session_id"), array("session_phpid")))
			$strReturn .= "An error occured! ...\n";

        // caching --------------------------------------------------------------------------------------
        $strReturn .= "Installing table cache...\n";

		$arrFields = array();
		$arrFields["cache_id"]                = array("char20", false);
		$arrFields["cache_source"]	          = array("char254", true);
		$arrFields["cache_hash1"]	          = array("char254", true);
		$arrFields["cache_hash2"]	          = array("char254", true);
		$arrFields["cache_language"]	      = array("char20", true);
		$arrFields["cache_content"]           = array("longtext", true);
		$arrFields["cache_leasetime"]         = array("int", true);
		$arrFields["cache_hits"]              = array("int", true);

		if(!$this->objDB->createTable("cache", $arrFields, array("cache_id"), array("cache_source", "cache_hash1", "cache_leasetime", "cache_language")))
			$strReturn .= "An error occured! ...\n";

		//Filemanager -----------------------------------------------------------------------------------
		$strReturn .= "Installing table filemanager...\n";

		$arrFields = array();
		$arrFields["filemanager_id"] 			= array("char20", false);
		$arrFields["filemanager_path"]			= array("char254", true);
		$arrFields["filemanager_name"] 			= array("char254", true);
		$arrFields["filemanager_upload_filter"] = array("char254", true);
		$arrFields["filemanager_view_filter"] 	= array("char254", true);
        $arrFields["filemanager_foreign_id"] 	= array("char20", true);

		if(!$this->objDB->createTable("filemanager", $arrFields, array("filemanager_id")))
			$strReturn .= "An error occured! ...\n";

        //dashboard & widgets ---------------------------------------------------------------------------
		$strReturn .= "Installing table dashboard...\n";

		$arrFields = array();
		$arrFields["dashboard_id"] 			= array("char20", false);
		$arrFields["dashboard_column"]		= array("char254", true);
		$arrFields["dashboard_user"] 		= array("char20", true);
		$arrFields["dashboard_widgetid"] 	= array("char20", true);
		$arrFields["dashboard_aspect"] 	    = array("char254", true);

		if(!$this->objDB->createTable("dashboard", $arrFields, array("dashboard_id")))
			$strReturn .= "An error occured! ...\n";

		$strReturn .= "Installing table adminwidget...\n";

		$arrFields = array();
		$arrFields["adminwidget_id"] 		= array("char20", false);
		$arrFields["adminwidget_class"]		= array("char254", true);
		$arrFields["adminwidget_content"] 	= array("text", true);

		if(!$this->objDB->createTable("adminwidget", $arrFields, array("adminwidget_id")))
			$strReturn .= "An error occured! ...\n";

        //languages -------------------------------------------------------------------------------------
        $strReturn .= "Installing table languages...\n";

		$arrFields = array();
		$arrFields["language_id"] 		= array("char20", false);
		$arrFields["language_name"] 	= array("char254", true);
		$arrFields["language_default"]  = array("int", true);

		if(!$this->objDB->createTable("languages", $arrFields, array("language_id")))
			$strReturn .= "An error occured! ...\n";

        $strReturn .= "Installing table languages_languageset...\n";

		$arrFields = array();
		$arrFields["languageset_id"] 		= array("char20", false);
		$arrFields["languageset_language"] 	= array("char20", true);
		$arrFields["languageset_systemid"]  = array("char20", true);

		if(!$this->objDB->createTable("languages_languageset", $arrFields, array("languageset_id", "languageset_systemid")))
			$strReturn .= "An error occured! ...\n";

         //aspects --------------------------------------------------------------------------------------
        $strReturn .= "Installing table aspects...\n";

		$arrFields = array();
		$arrFields["aspect_id"] 		= array("char20", false);
		$arrFields["aspect_name"]       = array("char254", true);
		$arrFields["aspect_default"]    = array("int", true);

		if(!$this->objDB->createTable("aspects", $arrFields, array("aspect_id")))
			$strReturn .= "An error occured! ...\n";

        //changelog -------------------------------------------------------------------------------------
        $strReturn .= "Installing table changelog...\n";

		$arrFields = array();
		$arrFields["change_id"]             = array("char20", false);
		$arrFields["change_date"]           = array("long", true);
		$arrFields["change_user"]           = array("char20", true);
		$arrFields["change_systemid"]       = array("char20", true);
		$arrFields["change_system_previd"]  = array("char20", true);
		$arrFields["change_class"]          = array("char254", true);
		$arrFields["change_action"]         = array("char254", true);
		$arrFields["change_property"]       = array("char254", true);
		$arrFields["change_oldvalue"]       = array("text", true);
		$arrFields["change_newvalue"]       = array("text", true);

		if(!$this->objDB->createTable("changelog", $arrFields, array("change_id"), array(), false))
			$strReturn .= "An error occured! ...\n";


		//Now we have to register module by module

		//The Systemkernel
		$this->registerModule("system", _system_modul_id_, "", "class_module_system_admin.php", $this->arrModule["version"], true, "", "class_module_system_admin_xml.php" );
		//The Rightsmodule
		$this->registerModule("right", _system_modul_id_, "", "class_module_right_admin.php", $this->arrModule["version"], false );
		//The Usermodule
		$this->registerModule("user", _user_modul_id_, "", "class_module_user_admin.php", $this->arrModule["version"], true );
        //The filemanagermodule
		$this->registerModule("filemanager", _filemanager_modul_id_, "", "class_module_filemanager_admin.php", $this->arrModule["version"], true, "", "class_module_filemanager_admin_xml.php");
        //the dashboard
        $this->registerModule("dashboard", _dashboard_modul_id_, "", "class_module_dashboard_admin.php", $this->arrModule["version"], false, "", "class_module_dashboard_admin_xml.php");
        //languages
        $this->registerModule("languages", _languages_modul_id_, "class_modul_languages_portal.php", "class_module_languages_admin.php", $this->arrModule["version"] , true);



		//Registering a few constants
		$strReturn .= "Registering system-constants...\n";
		//Number of rows in the login-log
		$this->registerConstant("_user_log_nrofrecords_", "50", 1, _user_modul_id_);

        //And the default skin
        $this->registerConstant("_admin_skin_default_", "kajona_v3", class_module_system_setting::$int_TYPE_STRING, _user_modul_id_);

        //and a few system-settings
        $this->registerConstant("_system_portal_disable_", "false", class_module_system_setting::$int_TYPE_BOOL, _system_modul_id_);
        $this->registerConstant("_system_portal_disablepage_", "", class_module_system_setting::$int_TYPE_PAGE, _system_modul_id_);

        //New in 3.0: Number of db-dumps to hold
	    $this->registerConstant("_system_dbdump_amount_", 5, class_module_system_setting::$int_TYPE_INT, _system_modul_id_);
	    //new in 3.0: mod-rewrite on / off
        $this->registerConstant("_system_mod_rewrite_", "false", class_module_system_setting::$int_TYPE_BOOL, _system_modul_id_);
        //New Constant: Max time to lock records
	    $this->registerConstant("_system_lock_maxtime_", 7200, class_module_system_setting::$int_TYPE_INT, _system_modul_id_);
        //Filemanger settings
        $this->registerConstant("_filemanager_foldersize_", "true", class_module_system_setting::$int_TYPE_BOOL, _filemanager_modul_id_);
        //Email to send error-reports
	    $this->registerConstant("_system_admin_email_", $this->objSession->getSession("install_email"), class_module_system_setting::$int_TYPE_STRING, _system_modul_id_);

	    //3.0.2: user are allowed to change their settings?
	    $this->registerConstant("_user_selfedit_", "true", class_module_system_setting::$int_TYPE_BOOL, _user_modul_id_);

	    //3.1: nr of rows in admin
	    $this->registerConstant("_admin_nr_of_rows_", 15, class_module_system_setting::$int_TYPE_INT, _system_modul_id_);
	    $this->registerConstant("_admin_only_https_", "false", class_module_system_setting::$int_TYPE_BOOL, _system_modul_id_);
        $this->registerConstant("_system_use_dbcache_", "true", class_module_system_setting::$int_TYPE_BOOL, _system_modul_id_);

        //3.1: remoteloader max cachtime --> default 30 min
        $this->registerConstant("_remoteloader_max_cachetime_", 30*60, class_module_system_setting::$int_TYPE_INT, _system_modul_id_);

        //3.2: max session duration
        $this->registerConstant("_system_release_time_", 3600, class_module_system_setting::$int_TYPE_INT, _system_modul_id_);
        //3.2: filemanager hidden repos
        $this->registerConstant("_filemanager_show_foreign_", "false", class_module_system_setting::$int_TYPE_BOOL, _filemanager_modul_id_);

        //3.3: filemanager repo-ids for an image- and a file-browser - values set lateron via the filemanager samplecontent installer
        $this->registerConstant("_filemanager_default_imagesrepoid_", "", class_module_system_setting::$int_TYPE_STRING, _filemanager_modul_id_);
        $this->registerConstant("_filemanager_default_filesrepoid_", "", class_module_system_setting::$int_TYPE_STRING, _filemanager_modul_id_);

        //3.4: cache buster to be able to flush the browsers cache (JS and CSS files)
        $this->registerConstant("_system_browser_cachebuster_", 0, class_module_system_setting::$int_TYPE_INT, _system_modul_id_);
        //3.4: Adding constant _system_graph_type_ indicating the chart-engine to use
        $this->registerConstant("_system_graph_type_", "ezc", class_module_system_setting::$int_TYPE_STRING, _system_modul_id_);
        //3.4: Enabling or disabling the internal changehistory
        $this->registerConstant("_system_changehistory_enabled_", "false", class_module_system_setting::$int_TYPE_BOOL, _system_modul_id_);



        //Creating the admin & guest groups
        $objAdminGroup = new class_module_user_group();
        $objAdminGroup->setStrName("Admins");
        $objAdminGroup->updateObjectToDb();
        $strReturn .= "Registered Group Admins...\n";

        $objGuestGroup = new class_module_user_group();
        $objGuestGroup->setStrName("Guests");
        $objGuestGroup->updateObjectToDb();
        $strReturn .= "Registered Group Guests...\n";

        //Systemid of guest-user & admin group
        $strGuestID = $objGuestGroup->getSystemid();
        $strAdminID = $objAdminGroup->getSystemid();
        $this->registerConstant("_guests_group_id_", $strGuestID, class_module_system_setting::$int_TYPE_STRING, _user_modul_id_);
        $this->registerConstant("_admins_group_id_", $strAdminID, class_module_system_setting::$int_TYPE_STRING, _user_modul_id_);

        //Create an root-record for the tree
        $this->createSystemRecord(0, "System Rights Root", true, _system_modul_id_, "0", "1", "class_module_system_common");
		//BUT: We have to modify the right-record of the system
		$strGroupsAll = "'".$strGuestID.",".$strAdminID."'";
		$strGroupsAdmin = "'".$strAdminID."'";

		$strQuery = "UPDATE "._dbprefix_."system_right SET
						right_inherit	= 0,
					   	right_view	 	= ".$strGroupsAll.",
					   	right_edit 		= ".$strGroupsAdmin.",
					   	right_delete	= ".$strGroupsAdmin.",
					   	right_right		= ".$strGroupsAdmin.",
					   	right_right1 	= ".$strGroupsAdmin.",
					   	right_right2 	= ".$strGroupsAdmin.",
					   	right_right3  	= ".$strGroupsAdmin.",
					   	right_right4    = ".$strGroupsAdmin.",
					   	right_right5  	= ".$strGroupsAdmin."
					   	WHERE right_id='0'";

		$this->objDB->_query($strQuery);
		$strReturn .= "Modified root-rights....\n";
        $this->objRights->rebuildRightsStructure();
        $strReturn .= "Rebuilded rights structures...\n";

		//Creating an admin-user
        $strUsername = "admin";
        $strPassword = "kajona";
        $strEmail = "";
		//Login-Data given from installer?
		if($this->objSession->getSession("install_username") !== false && $this->objSession->getSession("install_username") != "" &&
		   $this->objSession->getSession("install_password") !== false && $this->objSession->getSession("install_password") != "")
		   {
            $strUsername = dbsafeString($this->objSession->getSession("install_username"));
            $strPassword = dbsafeString($this->objSession->getSession("install_password"));
            $strEmail = dbsafeString($this->objSession->getSession("install_email"));
		}

        //create a default language
		$strReturn .= "Creating new default-language\n";
        $objLanguage = new class_module_languages_language();

        if($this->strContentLanguage == "de")
            $objLanguage->setStrName("de");
        else
            $objLanguage->setStrName("en");

        $objLanguage->setBitDefault(true);
        $objLanguage->updateObjectToDb();
        $strReturn .= "ID of new language: ".$objLanguage->getSystemid()."\n";

		//the admin-language
		$strAdminLanguage = $this->objSession->getAdminLanguage();

        $objUser = new class_module_user_user();
        $objUser->setStrUsername($strUsername);
        $objUser->setIntActive(1);
        $objUser->setIntAdmin(1);
        $objUser->setStrAdminlanguage($strAdminLanguage);
        $objUser->updateObjectToDb();
        $objUser->getObjSourceUser()->setStrPass($strPassword);
        $objUser->getObjSourceUser()->setStrEmail($strEmail);
        $objUser->getObjSourceUser()->updateObjectToDb();
		$strReturn .= "Created User Admin: <strong>Username: ".$strUsername.", Password: ***********</strong> ...\n";

		//The Admin should belong to the admin-Group
        $objAdminGroup->getObjSourceGroup()->addMember($objUser->getObjSourceUser());
		$strReturn .= "Registered Admin in Admin-Group...\n";

        //creating a new default-aspect
        $strReturn .= "Registering new default aspect...\n";
        $objAspect = new class_module_system_aspect();
        $objAspect->setStrName("default");
        $objAspect->setBitDefault(true);
        $objAspect->updateObjectToDb();



		return $strReturn;
	}


	protected function updateModuleVersion($strModuleName, $strVersion) {
		parent::updateModuleVersion("system", $strVersion);
        parent::updateModuleVersion("right", $strVersion);
        parent::updateModuleVersion("user", $strVersion);
        parent::updateModuleVersion("filemanager", $strVersion);
        parent::updateModuleVersion("dashboard", $strVersion);
        parent::updateModuleVersion("languages", $strVersion);
	}

	public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModul = $this->getModuleData($this->arrModule["name"], false);

        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.0") {
            $strReturn .= $this->update_320_3209();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.0.9") {
            $strReturn .= $this->update_3209_321();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.1") {
            $strReturn .= $this->update_321_3291();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.91") {
            $strReturn .= $this->update_3291_3292();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.92") {
            $strReturn .= $this->update_3292_3293();
        }

	    $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.93") {
            $strReturn .= $this->update_3293_3294();
        }

	    $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.94") {
            $strReturn .= $this->update_3294_3295();
        }

	    $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.95") {
            $strReturn .= $this->update_3295_3296();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.2.96") {
            $strReturn .= $this->update_3296_330();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.0") {
            $strReturn .= $this->update_330_3301();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.0.1") {
            $strReturn .= $this->update_3301_331();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.1") {
            $strReturn .= $this->update_331_3311();
        }

	    $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.1.1") {
            $strReturn .= $this->update_3311_3312();
            $this->objDB->flushQueryCache();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.1.2") {
            $strReturn .= $this->update_3312_3313();
            $this->objDB->flushQueryCache();
            $strReturn .= "<b>Please retrigger update-sequence to complete kernel-update...</b>\n\n";
            return $strReturn;
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.1.3") {
            $strReturn .= $this->update_3313_3314();
            $this->objDB->flushQueryCache();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.1.4") {
            $strReturn .= $this->update_3314_3315();
            $this->objDB->flushQueryCache();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.1.5") {
            $strReturn .= $this->update_3315_3318();
            $this->objDB->flushQueryCache();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.1.8") {
            $strReturn .= $this->update_3318_3319();
            $this->objDB->flushQueryCache();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.1.9") {
            $strReturn .= $this->update_3319_33110();
            $this->objDB->flushQueryCache();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.3.1.10") {
            $strReturn .= $this->update_33110_340();
            $this->objDB->flushQueryCache();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.4.0") {
            $strReturn .= $this->update_340_3401();
            $this->objDB->flushQueryCache();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.4.0.1") {
            $strReturn .= $this->update_3401_3402();
            $this->objDB->flushQueryCache();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.4.0.2") {
            $strReturn .= $this->update_3402_341();
            $this->objDB->flushQueryCache();
        }

        $arrModul = $this->getModuleData($this->arrModule["name"], false);
        if($arrModul["module_version"] == "3.4.1") {
            $strReturn .= $this->update_341_349();
            $this->objDB->flushQueryCache();
        }

        return $strReturn."\n\n";
	}


    private function update_320_3209() {
        $strReturn = "Updating 3.2.0 to 3.2.0.9...\n";

        $strReturn .= "Adding system_owner column to db-schema...\n";
        $strSql = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."system")."
        	               ADD ".$this->objDB->encloseColumnName("system_owner")." VARCHAR( 20 ) NULL ";

        if(!$this->objDB->_query($strSql))
            $strReturn .= "An error occured!\n";

        $strReturn .= "Updating owner-fields...\n";
        $arrRecords = $this->objDB->getArray("SELECT system_id FROM ".$this->objDB->encloseTableName(_dbprefix_."system"));
        foreach($arrRecords as $strOneSysId) {
            $objRecord = new class_module_system_common($strOneSysId["system_id"]);
            $objRecord->setOwnerId($objRecord->getLastEditUserId());
        }

        $strReturn .= "Adding user_authcode column to db-schema...\n";
        $strSql = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."user")."
        	               ADD ".$this->objDB->encloseColumnName("user_authcode")." VARCHAR( 20 ) NULL ";

        if(!$this->objDB->_query($strSql))
            $strReturn .= "An error occured!\n";

        $strReturn .= "Changing type of user_date column to long ...\n";

        $strReturn .= "Set default values...\n";
        $strSql = "UPDATE ".$this->objDB->encloseTableName(_dbprefix_."user")."
                      SET user_date = NULL where user_date = ''";

        if(!$this->objDB->_query($strSql))
            $strReturn .= "An error occured!\n";

        $strReturn = "Altering user-table...\n";
        $strSql = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."user")."
                        CHANGE ".$this->objDB->encloseColumnName("user_date")." ".$this->objDB->encloseColumnName("user_date")." ".$this->objDB->getDatatype("long")." NULL DEFAULT NULL";

        if(!$this->objDB->_query($strSql))
            $strReturn .= "An error occured!\n";

        $strReturn = "Altering element-table...\n";
        $arrTables = $this->objDB->getTables();

        if(in_array(_dbprefix_."element", $arrTables)) {
            $strSql = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."element")."
                           ADD ".$this->objDB->encloseColumnName("element_version")." ".$this->objDB->getDatatype("char20")." NULL DEFAULT NULL";

            if(!$this->objDB->_query($strSql))
                $strReturn .= "An error occured!\n";


            $strReturn .= "Updating element-versions...\n";
            $arrElements = class_module_pages_element::getAllElements();
            foreach($arrElements as $objOneElement) {
                $objOneElement->setStrVersion("3.2.0.9");
                $objOneElement->updateObjectToDb();
            }



        }

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.2.0.9");
        return $strReturn;
    }


    private function update_3209_321() {
        $strReturn = "";
        $strReturn .= "Updating 3.2.0.9 to 3.2.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.2.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("languageswitch", "3.2.1");
        return $strReturn;
    }

    private function update_321_3291() {
        $strReturn = "Updating 3.2.1 to 3.2.91...\n";

        $strReturn .= "Reorganizing filemanager repositories...\n";

        $strQuery = "SELECT module_id
                       FROM "._dbprefix_."system_module
                      WHERE module_nr = "._filemanager_modul_id_."";
        $arrEntries = $this->objDB->getRow($strQuery);
        $strModuleId = $arrEntries["module_id"];

        $strQuery = "SELECT system_id
                       FROM "._dbprefix_."system, "._dbprefix_."filemanager
                      WHERE system_id=filemanager_id
                        AND system_prev_id = '0'";
        $arrEntries = $this->objDB->getArray($strQuery);

        foreach($arrEntries as $arrSingleRow) {
            $strReturn .= " ...updating repo ".$arrSingleRow["system_id"]."";
            $strQuery = "UPDATE "._dbprefix_."system
                            SET system_prev_id = '".dbsafeString($strModuleId)."'
                          WHERE system_id = '".dbsafeString($arrSingleRow["system_id"])."'";
            if($this->objDB->_query($strQuery))
                $strReturn .= " ...ok\n";
            else
                $strReturn .= " ...failed!!!\n";
        }



        $strReturn .= "Reorganizing languages repositories...\n";

        $strQuery = "SELECT module_id
                       FROM "._dbprefix_."system_module
                      WHERE module_nr = "._languages_modul_id_."";
        $arrEntries = $this->objDB->getRow($strQuery);
        $strModuleId = $arrEntries["module_id"];

        $strQuery = "SELECT system_id
                       FROM "._dbprefix_."system, "._dbprefix_."languages
                      WHERE system_id=language_id
                        AND system_prev_id = '0'";
        $arrEntries = $this->objDB->getArray($strQuery);

        foreach($arrEntries as $arrSingleRow) {
            $strReturn .= " ...updating language ".$arrSingleRow["system_id"]."";
            $strQuery = "UPDATE "._dbprefix_."system
                            SET system_prev_id = '".dbsafeString($strModuleId)."'
                          WHERE system_id = '".dbsafeString($arrSingleRow["system_id"])."'";
            if($this->objDB->_query($strQuery))
                $strReturn .= " ...ok\n";
            else
                $strReturn .= " ...failed!!!\n";
        }




        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.2.91");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("languageswitch", "3.2.91");
        return $strReturn;
    }



    private function update_3291_3292() {
        $strReturn = "Updating 3.2.91 to 3.2.92...\n";

        $strReturn.= "Checking number of nodes on second level compared to number of modules installed...\n";

        $strQuery = "SELECT system_id FROM "._dbprefix_."system WHERE system_prev_id = '0' AND system_id != '0'";
        $arrNodes = $this->objDB->getArray($strQuery);

        $strQuery = "SELECT module_id FROM "._dbprefix_."system_module";
        $arrModules = $this->objDB->getArray($strQuery);

        if(count($arrNodes) != count($arrModules)) {
            $strReturn .= "<b>Error</b>\n";
            $strReturn.= count($arrNodes)." nodes vs. ".count($arrModules)." modules.\n";

            $arrFlatModules = array();
            foreach($arrModules as $arrSingleModule) {
                $arrFlatModules[] = $arrSingleModule["module_id"];
            }

            foreach($arrNodes as $arrSingleNode) {
                if(!in_array($arrSingleNode["system_id"], $arrFlatModules))
                    $strReturn .= "node ".$arrSingleNode["system_id"]." not in list of modules! \n";
            }

            $strReturn .= "<b>Please upgrade other modules before.\n<b>Aborting update!</b>\n";
            return $strReturn;
        }
        else
            $strReturn .= " ...numbers are matching.\n";

        $strReturn .= "Rebuilding rights tables...";
        if(class_carrier::getInstance()->getObjRights()->rebuildRightsStructure()) {
            $strReturn .= " ok.\n";
        }
        else {
            $strReturn .= " failed.\n";
            return $strReturn;
        }



        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.2.92");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("languageswitch", "3.2.92");


        return $strReturn;
    }

    private function update_3292_3293() {
        $strReturn = "Updating 3.2.92 to 3.2.93...\n";


        $strReturn = "Altering system-date-table...\n";
        $strSql = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."system_date")."
                        CHANGE ".$this->objDB->encloseColumnName("system_date_start")." ".$this->objDB->encloseColumnName("system_date_start")." ".$this->objDB->getDatatype("long")." NULL DEFAULT NULL,
                        CHANGE ".$this->objDB->encloseColumnName("system_date_end")." ".$this->objDB->encloseColumnName("system_date_end")." ".$this->objDB->getDatatype("long")." NULL DEFAULT NULL,
                        CHANGE ".$this->objDB->encloseColumnName("system_date_special")." ".$this->objDB->encloseColumnName("system_date_special")." ".$this->objDB->getDatatype("long")." NULL DEFAULT NULL";

        if(!$this->objDB->_query($strSql))
            $strReturn .= "An error occured!\n";

        $strReturn .= "Updating saved timestamps...\n";

        $strReturn .= "... start dates...\n";
        $strQuery = "SELECT system_date_start, system_date_id
                       FROM ".$this->objDB->encloseTableName(_dbprefix_."system_date")."
                      WHERE system_date_start IS NOT NULL
                        AND system_date_start != 0 ";
        $arrEntries = $this->objDB->getArray($strQuery);
        foreach($arrEntries as $arrSingleEntry) {
            $objDate = new class_date($arrSingleEntry["system_date_start"]);
            $strQuery = "UPDATE ".$this->objDB->encloseTableName(_dbprefix_."system_date")."
                           SET system_date_start = ".$objDate->getLongTimestamp()."
                         WHERE system_date_id = '".$arrSingleEntry["system_date_id"]."' ";

            if(!$this->objDB->_query($strQuery))
                $strReturn .= "An error occured!\n";
        }

        $strReturn .= "... end dates...\n";
        $strQuery = "SELECT system_date_end, system_date_id
                       FROM ".$this->objDB->encloseTableName(_dbprefix_."system_date")."
                      WHERE system_date_end IS NOT NULL
                        AND system_date_end != 0 ";
        $arrEntries = $this->objDB->getArray($strQuery);
        foreach($arrEntries as $arrSingleEntry) {
            $objDate = new class_date($arrSingleEntry["system_date_end"]);
            $strQuery = "UPDATE ".$this->objDB->encloseTableName(_dbprefix_."system_date")."
                           SET system_date_end = ".$objDate->getLongTimestamp()."
                         WHERE system_date_id = '".$arrSingleEntry["system_date_id"]."' ";

            if(!$this->objDB->_query($strQuery))
                $strReturn .= "An error occured!\n";
        }

        $strReturn .= "... special dates...\n";
        $strQuery = "SELECT system_date_special, system_date_id
                       FROM ".$this->objDB->encloseTableName(_dbprefix_."system_date")."
                      WHERE system_date_special IS NOT NULL
                        AND system_date_special != 0 ";
        $arrEntries = $this->objDB->getArray($strQuery);
        foreach($arrEntries as $arrSingleEntry) {
            $objDate = new class_date($arrSingleEntry["system_date_special"]);
            $strQuery = "UPDATE ".$this->objDB->encloseTableName(_dbprefix_."system_date")."
                           SET system_date_special = ".$objDate->getLongTimestamp()."
                         WHERE system_date_id = '".$arrSingleEntry["system_date_id"]."' ";

            if(!$this->objDB->_query($strQuery))
                $strReturn .= "An error occured!\n";
        }



        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.2.93");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("languageswitch", "3.2.93");


        return $strReturn;
    }

    private function update_3293_3294() {
        $strReturn = "Updating 3.2.93 to 3.2.94...\n";

        $strReturn .= "Deleting old FCKeditor files since it's replaced by CKEditor...\n";
        $objFilesystem = new class_filesystem();
        if(!$objFilesystem->folderDeleteRecursive("/admin/scripts/fckeditor"))
           $strReturn .= "<b>Error deleting the folder \n /admin/scripts/fckeditor,\nplease delete manually</b>\n";

        $strReturn .= "Registering filemanager defaul repo-id-settings...\n";
        $this->registerConstant("_filemanager_default_imagesrepoid_", "", class_module_system_setting::$int_TYPE_STRING, _filemanager_modul_id_);
        $this->registerConstant("_filemanager_default_filesrepoid_", "", class_module_system_setting::$int_TYPE_STRING, _filemanager_modul_id_);


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.2.94");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("languageswitch", "3.2.94");
        return $strReturn;
    }

    private function update_3294_3295() {
        $strReturn = "Updating 3.2.94 to 3.2.95...\n";

        $strReturn .= "Installing table languages_languageset...\n";

		$arrFields = array();
		$arrFields["languageset_id"] 		= array("char20", false);
		$arrFields["languageset_language"] 	= array("char20", true);
		$arrFields["languageset_systemid"]  = array("char20", true);

		if(!$this->objDB->createTable("languages_languageset", $arrFields, array("languageset_id", "languageset_systemid")))
			$strReturn .= "An error occured! ...\n";


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.2.95");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("languageswitch", "3.2.95");
        return $strReturn;
    }

    private function update_3295_3296() {
        $strReturn = "Updating 3.2.95 to 3.2.96...\n";

        $strReturn .= "Removing setting _images_cachepath_...\n";

        $strQuery = "DELETE FROM ".$this->objDB->encloseTableName(_dbprefix_."system_config")."
                           WHERE ".$this->objDB->encloseColumnName("system_config_name")." = '_images_cachepath_'";

        if(!$this->objDB->_query($strQuery))
			$strReturn .= "An error occured! ...\n";


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.2.96");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("languageswitch", "3.2.96");
        return $strReturn;
    }

    private function update_3296_330() {
        $strReturn = "Updating 3.2.96 to 3.3.0...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.3.0");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("languageswitch", "3.3.0");
        return $strReturn;
    }

    private function update_330_3301() {
        $strReturn = "Updating 3.3.0 to 3.3.0.1...\n";

        $strReturn .= "Installing table cache...\n";

		$arrFields = array();
		$arrFields["cache_id"]                = array("char20", false);
		$arrFields["cache_source"]	          = array("char254", true);
		$arrFields["cache_hash1"]	          = array("char254", true);
		$arrFields["cache_hash2"]	          = array("char254", true);
		$arrFields["cache_language"]	      = array("char20", true);
		$arrFields["cache_content"]           = array("text", true);
		$arrFields["cache_leasetime"]         = array("int", true);
		$arrFields["cache_hits"]              = array("int", true);

		if(!$this->objDB->createTable("cache", $arrFields, array("cache_id"), array("cache_source", "cache_hash1", "cache_leasetime", "cache_language")))
			$strReturn .= "An error occured! ...\n";

        $strReturn .= "Dropping table remoteloader-cache...\n";
        $strQuery = "DROP TABLE "._dbprefix_."remoteloader_cache";
        if(!$this->objDB->_query($strQuery))
			$strReturn .= "An error occured! ...\n";

        $strReturn .= "Registering new system-setting for cache-debugging...\n";
        $this->registerConstant("_system_cache_stats_", "false", class_module_system_setting::$int_TYPE_BOOL, _system_modul_id_);

        $strReturn .= "Setting cache-timeouts for languageswitch-element...\n";
        $strQuery = "UPDATE "._dbprefix_."element
                        SET element_cachetime=".(3600*24*30)."
                      WHERE element_class_admin = 'class_element_languageswitch.php'";
        if(!$this->objDB->_query($strQuery))
			$strReturn .= "An error occured! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.3.0.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("languageswitch", "3.3.0.1");
        return $strReturn;
    }

    private function update_3301_331() {
        $strReturn = "Updating 3.3.0.1 to 3.3.1...\n";

        $strReturn .= "Deleting old systemtasks...\n";
        $objFilesystem = new class_filesystem();
        $objFilesystem->fileDelete("/admin/systemtasks/class_systemtask_flushremoteloadercache.php");


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.3.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("languageswitch", "3.3.1");
        return $strReturn;
    }

    private function update_331_3311() {
        $strReturn = "Updating 3.3.1 to 3.3.1.1...\n";

        $strReturn .= "Removing unused constant _system_cache_stats_...\n";
        $objConstant = class_module_system_setting::getConfigByName("_system_cache_stats_");
        $strQuery = "DELETE FROM "._dbprefix_."system_config WHERE system_config_id='".$objConstant->getSystemid()."'";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured! ...\n";



        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.3.1.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("languageswitch", "3.3.1.1");
        return $strReturn;
    }

    private function update_3311_3312() {
        $strReturn = "Updating 3.3.1.1 to 3.3.1.2...\n";

        $strReturn .= "Adding constant _system_browser_cachebuster_ to be able to flush the browsers cache (JS and CSS files)...\n";
        $this->registerConstant("_system_browser_cachebuster_", 0, class_module_system_setting::$int_TYPE_INT, _system_modul_id_);

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.3.1.2");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("languageswitch", "3.3.1.2");
        return $strReturn;
    }

    private function update_3312_3313() {
        $strReturn = "Updating 3.3.1.2 to 3.3.1.3...\n";

        $strReturn .= "Altering system-table...\n";
        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."system")."
                     ADD ".$this->objDB->encloseColumnName("system_create_date")." ".$this->objDB->getDatatype("long")." NULL DEFAULT NULL ";
        if(!$this->objDB->_query($strQuery))
             $strReturn .= "An error occured! ...\n";

        $strReturn .= "Altering cache-table...\n";
        class_cache::flushCache();
        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."cache")."
                    CHANGE ".$this->objDB->encloseColumnName("cache_content")." ".$this->objDB->encloseColumnName("cache_content")." ".$this->objDB->getDatatype("longtext")." NULL DEFAULT NULL ";
        if(!$this->objDB->_query($strQuery))
             $strReturn .= "An error occured! ...\n";


        $strReturn .= "Installing table aspects...\n";

		$arrFields = array();
		$arrFields["aspect_id"] 		= array("char20", false);
		$arrFields["aspect_name"]       = array("char254", true);
		$arrFields["aspect_default"]    = array("int", true);

		if(!$this->objDB->createTable("aspects", $arrFields, array("aspect_id")))
			$strReturn .= "An error occured! ...\n";

        //creating a new default-aspect
        $objAspect = new class_module_system_aspect();
        $objAspect->setStrName("default");
        $objAspect->setBitDefault(true);
        $objAspect->updateObjectToDb();

        $strDefaultAspectId = $objAspect->getSystemid();

        $strReturn .= "Altering module-table...\n";
        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."system_module")."
                     ADD ".$this->objDB->encloseColumnName("module_aspect")." ".$this->objDB->getDatatype("char254")." NULL DEFAULT NULL ";
        if(!$this->objDB->_query($strQuery))
             $strReturn .= "An error occured! ...\n";

        $strReturn .= "Altering dashboard-table...\n";
        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."dashboard")."
                     ADD ".$this->objDB->encloseColumnName("dashboard_aspect")." ".$this->objDB->getDatatype("char254")." NULL DEFAULT NULL ";
        if(!$this->objDB->_query($strQuery))
             $strReturn .= "An error occured! ...\n";

        $strReturn .= "Moving existing widgets to default aspect...\n";
        $strQuery = "UPDATE ".$this->objDB->encloseTableName(_dbprefix_."dashboard")."
                        SET ".$this->objDB->encloseColumnName("dashboard_aspect")." = '".dbsafeString($strDefaultAspectId)."'";

        if(!$this->objDB->_query($strQuery))
             $strReturn .= "An error occured! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.3.1.3");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("languageswitch", "3.3.1.3");
        $this->objDB->flushQueryCache();
        return $strReturn;
    }


    private function update_3313_3314() {
        $strReturn = "Updating 3.3.1.3 to 3.3.1.4...\n";

        $strReturn .= "Installing table changelog...\n";

		$arrFields = array();
		$arrFields["change_id"] 		= array("char20", false);
		$arrFields["change_date"]       = array("long", true);
		$arrFields["change_user"]       = array("char20", true);
		$arrFields["change_class"]      = array("char254", true);
		$arrFields["change_action"]     = array("char254", true);
		$arrFields["change_property"]   = array("char254", true);
		$arrFields["change_oldvalue"]   = array("text", true);
		$arrFields["change_newvalue"]   = array("text", true);
        $arrFields["change_systemid"]   = array("char20", true);

		if(!$this->objDB->createTable("changelog", $arrFields, array("change_id"), array(), false))
			$strReturn .= "An error occured! ...\n";


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.3.1.4");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("languageswitch", "3.3.1.4");
        return $strReturn;
    }

    private function update_3314_3315() {
        $strReturn = "Updating 3.3.1.4 to 3.3.1.5...\n";

        $strReturn .= "Adding constant _system_graph_type_ indicating the chart-engine to use...\n";
        $this->registerConstant("_system_graph_type_", "ezc", class_module_system_setting::$int_TYPE_STRING, _system_modul_id_);


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.3.1.5");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("languageswitch", "3.3.1.5");
        return $strReturn;
    }

    private function update_3315_3318() {
        $strReturn = "Updating 3.3.1.5 to 3.3.1.8...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.3.1.8");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("languageswitch", "3.3.1.8");
        return $strReturn;
    }

    private function update_3318_3319() {
        $strReturn = "Updating 3.3.1.8 to 3.3.1.9...\n";

        $this->registerConstant("_system_changehistory_enabled_", "false", class_module_system_setting::$int_TYPE_BOOL, _system_modul_id_);

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.3.1.9");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("languageswitch", "3.3.1.9");
        return $strReturn;
    }

    private function update_3319_33110() {
        $strReturn = "Updating 3.3.1.9 to 3.3.1.10...\n";

        $strReturn .= "Altering changehistory-table...\n";
        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."changelog")."
                     ADD ".$this->objDB->encloseColumnName("change_system_previd")." ".$this->objDB->getDatatype("char20")." NULL DEFAULT NULL ";
        if(!$this->objDB->_query($strQuery))
             $strReturn .= "An error occured! ...\n";


        $strReturn .= "Altering rights-table...\n";
        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."system_right")."
                    CHANGE ".$this->objDB->encloseColumnName("right_view")." ".$this->objDB->encloseColumnName("right_view")." ".$this->objDB->getDatatype("text")." NULL DEFAULT NULL,
                    CHANGE ".$this->objDB->encloseColumnName("right_edit")." ".$this->objDB->encloseColumnName("right_edit")." ".$this->objDB->getDatatype("text")." NULL DEFAULT NULL ,
                    CHANGE ".$this->objDB->encloseColumnName("right_delete")." ".$this->objDB->encloseColumnName("right_delete")." ".$this->objDB->getDatatype("text")." NULL DEFAULT NULL,
                    CHANGE ".$this->objDB->encloseColumnName("right_right")." ".$this->objDB->encloseColumnName("right_right")." ".$this->objDB->getDatatype("text")." NULL DEFAULT NULL,
                    CHANGE ".$this->objDB->encloseColumnName("right_right1")." ".$this->objDB->encloseColumnName("right_right1")." ".$this->objDB->getDatatype("text")." NULL DEFAULT NULL ,
                    CHANGE ".$this->objDB->encloseColumnName("right_right2")." ".$this->objDB->encloseColumnName("right_right2")." ".$this->objDB->getDatatype("text")." NULL DEFAULT NULL ,
                    CHANGE ".$this->objDB->encloseColumnName("right_right3")." ".$this->objDB->encloseColumnName("right_right3")." ".$this->objDB->getDatatype("text")." NULL DEFAULT NULL ,
                    CHANGE ".$this->objDB->encloseColumnName("right_right4")." ".$this->objDB->encloseColumnName("right_right4")." ".$this->objDB->getDatatype("text")." NULL DEFAULT NULL ,
                    CHANGE ".$this->objDB->encloseColumnName("right_right5")." ".$this->objDB->encloseColumnName("right_right5")." ".$this->objDB->getDatatype("text")." NULL DEFAULT NULL";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.3.1.10");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("languageswitch", "3.3.1.10");
        return $strReturn;
    }

    private function update_33110_340() {
        $strReturn = "Updating 3.3.1.10 to 3.4.0...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.4.0");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("languageswitch", "3.4.0");
        return $strReturn;
    }

     private function update_340_3401() {
        $strReturn = "Updating 3.4.0 to 3.4.0.1...\n";

        $strReturn .= "Deleting system_output_gzip constant... \n";
        $strQuery = "DELETE FROM "._dbprefix_."system_config WHERE system_config_name = ?";
        if(!$this->objDB->_pQuery($strQuery, array("_system_output_gzip_")))
            $strReturn .= "An error occured! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.4.0.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("languageswitch", "3.4.0.1");
        return $strReturn;
    }


    private function update_3401_3402() {
        $strReturn = "Updating 3.4.0.1 to 3.4.0.2...\n";

        $strReturn .= "Installing kajona-user-subsystem user-table...\n";
		$arrFields = array();
		$arrFields["user_id"] 			= array("char20", false);
		$arrFields["user_pass"] 		= array("char254", true);
		$arrFields["user_email"] 		= array("char254", true);
		$arrFields["user_forename"] 	= array("char254", true);
		$arrFields["user_name"] 		= array("char254", true);
		$arrFields["user_street"] 		= array("char254", true);
		$arrFields["user_postal"] 		= array("char254", true);
		$arrFields["user_city"] 		= array("char254", true);
		$arrFields["user_tel"] 			= array("char254", true);
		$arrFields["user_mobile"] 		= array("char254", true);
		$arrFields["user_date"] 		= array("long", true);

		if(!$this->objDB->createTable("user_kajona", $arrFields, array("user_id")))
			$strReturn .= "An error occured! ...\n";

        $strReturn .= "Installing kajona-user-subsystem group-table...\n";
        $arrFields = array();
		$arrFields["group_id"] 			= array("char20", false);
		$arrFields["group_desc"]		= array("char254", true);

		if(!$this->objDB->createTable("user_group_kajona", $arrFields, array("group_id")))
			$strReturn .= "An error occured! ...\n";


        $strReturn .= "Updating kajona-user-subsystem members-table...\n";
        $strQuery = "RENAME TABLE ".$this->objDB->encloseTableName(_dbprefix_."user_group_members")." TO ".$this->objDB->encloseTableName(_dbprefix_."user_kajona_members")."";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured! ...\n";

        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."user_kajona_members")."
                    CHANGE ".$this->objDB->encloseColumnName("group_member_group_id")." ".$this->objDB->encloseColumnName("group_member_group_kajona_id")." ".$this->objDB->getDatatype("char20")." NOT NULL,
                    CHANGE ".$this->objDB->encloseColumnName("group_member_user_id")." ".$this->objDB->encloseColumnName("group_member_user_kajona_id")." ".$this->objDB->getDatatype("char20")." NOT NULL";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured! ...\n";


        $strReturn .= "Migrating current groups to new kajona-user-subsystems...\n";
        $strQuery = "SELECT * FROM "._dbprefix_."user_group ORDER BY group_id DESC";
        $arrRows = $this->objDB->getPArray($strQuery, array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "INSERT INTO "._dbprefix_."user_group_kajona
                                        (group_id) VALUES (?) ";
            $this->objDB->_pQuery($strQuery, array($arrOneRow["group_id"]));
        }

        $strReturn .= "Migrating current users to new kajona-user-subsystems...\n";
        $strQuery = "SELECT * FROM "._dbprefix_."user ORDER BY user_id DESC";
        $arrRows = $this->objDB->getPArray($strQuery, array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "INSERT INTO "._dbprefix_."user_kajona
                                        (user_id, user_pass, user_email, user_forename, user_name,
                                        user_street, user_postal, user_city, user_tel, user_mobile, user_date) VALUES
                                        (?,?,?,?,?,?,?,?,?,?,?) ";
            $this->objDB->_pQuery($strQuery, array(
                $arrOneRow["user_id"], $arrOneRow["user_pass"], $arrOneRow["user_email"],
                $arrOneRow["user_forename"], $arrOneRow["user_name"], $arrOneRow["user_street"], $arrOneRow["user_postal"],
                $arrOneRow["user_city"], $arrOneRow["user_tel"], $arrOneRow["user_mobile"], $arrOneRow["user_date"]));

        }

        $strReturn .= "Updating old user-tables...\n";
		$strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."user")."
                    ADD ".$this->objDB->encloseColumnName("user_subsystem")." ".$this->objDB->getDatatype("char254")." NOT NULL,
                    DROP ".$this->objDB->encloseColumnName("user_pass").",
                    DROP ".$this->objDB->encloseColumnName("user_email").",
                    DROP ".$this->objDB->encloseColumnName("user_forename").",
                    DROP ".$this->objDB->encloseColumnName("user_name").",
                    DROP ".$this->objDB->encloseColumnName("user_street").",
                    DROP ".$this->objDB->encloseColumnName("user_postal").",
                    DROP ".$this->objDB->encloseColumnName("user_city").",
                    DROP ".$this->objDB->encloseColumnName("user_tel").",
                    DROP ".$this->objDB->encloseColumnName("user_mobile").",
                    DROP ".$this->objDB->encloseColumnName("user_date")." ";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured! ...\n";


        $strReturn .= "Updating old group-tables...\n";
        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."user_group")."
                    ADD ".$this->objDB->encloseColumnName("group_subsystem")." ".$this->objDB->getDatatype("char254")." NOT NULL";
        if(!$this->objDB->_query($strQuery))
            $strReturn .= "An error occured! ...\n";

        $strReturn .= "Reassigning current users to kajona usersubsystem...\n";
        $strQuery = "UPDATE "._dbprefix_."user SET user_subsystem = ?";
        if(!$this->objDB->_pQuery($strQuery, array('kajona')))
            $strReturn .= "An error occured! ...\n";

        $strReturn .= "Reassigning current groups to kajona usersubsystem...\n";
        $strQuery = "UPDATE "._dbprefix_."user_group SET group_subsystem = ?";
        if(!$this->objDB->_pQuery($strQuery, array('kajona')))
            $strReturn .= "An error occured! ...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.4.0.2");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("languageswitch", "3.4.0.2");
        return $strReturn;
    }

    private function update_3402_341() {
        $strReturn = "Updating 3.4.0.2 to 3.4.1...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.4.1");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("languageswitch", "3.4.1");
        return $strReturn;
    }


    private function update_341_349() {
        $strReturn = "Updating 3.4.1 to 3.4.9...\n";

        $strReturn .= "Updating model-classes...\n";
        $strQuery = "SELECT * FROM "._dbprefix_."system_module";
        $arrRows = $this->objDB->getPArray($strQuery, array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system_module SET
                                module_filenameadmin = ?,
                                module_xmlfilenameadmin = ?,
                                module_filenameportal = ?,
                                module_xmlfilenameportal = ?
                            WHERE module_id = ?";
            $arrParams = array();
            $arrParams[] = uniStrReplace("class_modul_", "class_module_", $arrOneRow["module_filenameadmin"]);
            $arrParams[] = uniStrReplace("class_modul_", "class_module_", $arrOneRow["module_xmlfilenameadmin"]);
            $arrParams[] = uniStrReplace("class_modul_", "class_module_", $arrOneRow["module_filenameportal"]);
            $arrParams[] = uniStrReplace("class_modul_", "class_module_", $arrOneRow["module_xmlfilenameportal"]);
            $arrParams[] = $arrOneRow["module_id"];

            $strReturn .= "Updated ".$arrOneRow["module_name"]."\n";
            $this->objDB->_pQuery($strQuery, $arrParams);
        }

        $strReturn .= "Updating system table...\n";
        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."system")."
                            ADD ".$this->objDB->encloseColumnName("system_class")." ".$this->objDB->getDatatype("char254")." NULL";
        if(!$this->objDB->_pQuery($strQuery, array()))
            $strReturn .= "An error occured! ...\n";



        $strReturn .= "Adding classes for existing records...\n";
        $strReturn .= "Modules\n";
        foreach(class_module_system_module::getAllModules() as $objOneModule) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( get_class($objOneModule), $objOneModule->getSystemid() ) );
        }

        $strReturn .= "Filemanager\n";
        foreach(class_module_filemanager_repo::getAllRepos(true) as $objOneEntry) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( get_class($objOneEntry), $objOneEntry->getSystemid() ) );
        }

        $strReturn .= "Widgets\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."adminwidget, "._dbprefix_."system WHERE system_id = adminwidget_id", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_system_adminwidget', $arrOneRow["system_id"] ) );
        }

        $strReturn .= "Dashboard\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."dashboard, "._dbprefix_."system WHERE system_id = dashboard_id", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_dashboard_widget', $arrOneRow["system_id"] ) );
        }

        $strReturn .= "Languages\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."languages, "._dbprefix_."system WHERE system_id = language_id", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_languages_language', $arrOneRow["system_id"] ) );
        }

        $strReturn .= "Languages\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."aspects, "._dbprefix_."system WHERE system_id = aspect_id", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array( 'class_module_system_aspect', $arrOneRow["system_id"] ) );
        }


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.4.9");
        $strReturn .= "Updating element-versions...\n";
        $this->updateElementVersion("languageswitch", "3.4.9");
        return $strReturn;
    }
}
