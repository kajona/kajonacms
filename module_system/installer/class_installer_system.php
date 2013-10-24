<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                        *
********************************************************************************************************/


/**
 * Installer for the system-module
 *
 * @package module_system
 * @moduleId _system_modul_id_
 */
class class_installer_system extends class_installer_base implements interface_installer {

    private $strContentLanguage;

    public function __construct() {
        parent::__construct();

        //set the correct language
        $this->strContentLanguage = $this->objSession->getAdminLanguage();
    }

    public function install() {
        $strReturn = "";

        // System table ---------------------------------------------------------------------------------
        $strReturn .= "Installing table system...\n";

        $arrFields = array();
        $arrFields["system_id"] = array("char20", false);
        $arrFields["system_prev_id"] = array("char20", false);
        $arrFields["system_module_nr"] = array("int", false);
        $arrFields["system_sort"] = array("int", true);
        $arrFields["system_owner"] = array("char20", true);
        $arrFields["system_create_date"] = array("long", true);
        $arrFields["system_lm_user"] = array("char20", true);
        $arrFields["system_lm_time"] = array("int", true);
        $arrFields["system_lock_id"] = array("char20", true);
        $arrFields["system_lock_time"] = array("int", true);
        $arrFields["system_status"] = array("int", true);
        $arrFields["system_class"] = array("char254", true);
        $arrFields["system_comment"] = array("char254", true);

        if(!$this->objDB->createTable("system", $arrFields, array("system_id"), array("system_prev_id", "system_module_nr", "system_sort", "system_owner", "system_create_date", "system_status", "system_lm_time", "system_lock_time")))
            $strReturn .= "An error occured! ...\n";

        //Rights table ----------------------------------------------------------------------------------
        $strReturn .= "Installing table system_right...\n";

        $arrFields = array();
        $arrFields["right_id"] = array("char20", false);
        $arrFields["right_inherit"] = array("int", true);
        $arrFields["right_view"] = array("text", true);
        $arrFields["right_edit"] = array("text", true);
        $arrFields["right_delete"] = array("text", true);
        $arrFields["right_right"] = array("text", true);
        $arrFields["right_right1"] = array("text", true);
        $arrFields["right_right2"] = array("text", true);
        $arrFields["right_right3"] = array("text", true);
        $arrFields["right_right4"] = array("text", true);
        $arrFields["right_right5"] = array("text", true);

        if(!$this->objDB->createTable("system_right", $arrFields, array("right_id")))
            $strReturn .= "An error occured! ...\n";

        // Modul table ----------------------------------------------------------------------------------
        $strReturn .= "Installing table system_module...\n";

        $arrFields = array();
        $arrFields["module_id"] = array("char20", false);
        $arrFields["module_nr"] = array("int", true);
        $arrFields["module_name"] = array("char254", true);
        $arrFields["module_filenameportal"] = array("char254", true);
        $arrFields["module_xmlfilenameportal"] = array("char254", true);
        $arrFields["module_filenameadmin"] = array("char254", true);
        $arrFields["module_xmlfilenameadmin"] = array("char254", true);
        $arrFields["module_version"] = array("char254", true);
        $arrFields["module_date"] = array("int", true);
        $arrFields["module_navigation"] = array("int", true);
        $arrFields["module_aspect"] = array("char254", true);

        if(!$this->objDB->createTable("system_module", $arrFields, array("module_id")))
            $strReturn .= "An error occured! ...\n";

        // Date table -----------------------------------------------------------------------------------
        $strReturn .= "Installing table system_date...\n";

        $arrFields = array();
        $arrFields["system_date_id"] = array("char20", false);
        $arrFields["system_date_start"] = array("long", true);
        $arrFields["system_date_end"] = array("long", true);
        $arrFields["system_date_special"] = array("long", true);

        if(!$this->objDB->createTable("system_date", $arrFields, array("system_date_id"), array("system_date_start", "system_date_end", "system_date_special")))
            $strReturn .= "An error occured! ...\n";

        // Config table ---------------------------------------------------------------------------------
        $strReturn .= "Installing table system_config...\n";

        $arrFields = array();
        $arrFields["system_config_id"] = array("char20", false);
        $arrFields["system_config_name"] = array("char254", true);
        $arrFields["system_config_value"] = array("char254", true);
        $arrFields["system_config_type"] = array("int", true);
        $arrFields["system_config_module"] = array("int", true);

        if(!$this->objDB->createTable("system_config", $arrFields, array("system_config_id")))
            $strReturn .= "An error occured! ...\n";


        // User table -----------------------------------------------------------------------------------
        $strReturn .= "Installing table user...\n";

        $arrFields = array();
        $arrFields["user_id"] = array("char20", false);
        $arrFields["user_username"] = array("char254", true);
        $arrFields["user_subsystem"] = array("char254", true);
        $arrFields["user_logins"] = array("int", true);
        $arrFields["user_lastlogin"] = array("int", true);
        $arrFields["user_active"] = array("int", true);
        $arrFields["user_admin"] = array("int", true);
        $arrFields["user_portal"] = array("int", true);
        $arrFields["user_admin_skin"] = array("char254", true);
        $arrFields["user_admin_language"] = array("char254", true);
        $arrFields["user_authcode"] = array("char20", true);

        if(!$this->objDB->createTable("user", $arrFields, array("user_id")))
            $strReturn .= "An error occured! ...\n";

        // User table kajona subsystem  -----------------------------------------------------------------
        $strReturn .= "Installing table user_kajona...\n";

        $arrFields = array();
        $arrFields["user_id"] = array("char20", false);
        $arrFields["user_pass"] = array("char254", true);
        $arrFields["user_salt"] = array("char20", true);
        $arrFields["user_email"] = array("char254", true);
        $arrFields["user_forename"] = array("char254", true);
        $arrFields["user_name"] = array("char254", true);
        $arrFields["user_street"] = array("char254", true);
        $arrFields["user_postal"] = array("char254", true);
        $arrFields["user_city"] = array("char254", true);
        $arrFields["user_tel"] = array("char254", true);
        $arrFields["user_mobile"] = array("char254", true);
        $arrFields["user_date"] = array("long", true);

        if(!$this->objDB->createTable("user_kajona", $arrFields, array("user_id")))
            $strReturn .= "An error occured! ...\n";

        // User group table -----------------------------------------------------------------------------
        $strReturn .= "Installing table user_group...\n";

        $arrFields = array();
        $arrFields["group_id"] = array("char20", false);
        $arrFields["group_name"] = array("char254", true);
        $arrFields["group_subsystem"] = array("char254", true);

        if(!$this->objDB->createTable("user_group", $arrFields, array("group_id")))
            $strReturn .= "An error occured! ...\n";


        $strReturn .= "Installing table user_group_kajona...\n";

        $arrFields = array();
        $arrFields["group_id"] = array("char20", false);
        $arrFields["group_desc"] = array("char254", true);


        if(!$this->objDB->createTable("user_group_kajona", $arrFields, array("group_id")))
            $strReturn .= "An error occured! ...\n";


        // User group_members table ---------------------------------------------------------------------
        $strReturn .= "Installing table user_kajona_members...\n";

        $arrFields = array();
        $arrFields["group_member_group_kajona_id"] = array("char20", false);
        $arrFields["group_member_user_kajona_id"] = array("char20", false);

        if(!$this->objDB->createTable("user_kajona_members", $arrFields, array("group_member_group_kajona_id", "group_member_user_kajona_id")))
            $strReturn .= "An error occured! ...\n";


        // User log table -------------------------------------------------------------------------------
        $strReturn .= "Installing table user_log...\n";

        $arrFields = array();
        $arrFields["user_log_id"] = array("char20", false);
        $arrFields["user_log_userid"] = array("char254", true);
        $arrFields["user_log_date"] = array("long", true);
        $arrFields["user_log_status"] = array("int", true);
        $arrFields["user_log_ip"] = array("char20", true);
        $arrFields["user_log_sessid"]  = array("char20", true);
        $arrFields["user_log_enddate"] = array("long", true);

        if(!$this->objDB->createTable("user_log", $arrFields, array("user_log_id"), array("user_log_sessid")))
            $strReturn .= "An error occured! ...\n";

        // Sessionmgtm ----------------------------------------------------------------------------------
        $strReturn .= "Installing table session...\n";

        $arrFields = array();
        $arrFields["session_id"] = array("char20", false);
        $arrFields["session_phpid"] = array("char254", true);
        $arrFields["session_userid"] = array("char20", true);
        $arrFields["session_groupids"] = array("text", true);
        $arrFields["session_releasetime"] = array("int", true);
        $arrFields["session_loginstatus"] = array("char254", true);
        $arrFields["session_loginprovider"] = array("char20", true);
        $arrFields["session_lasturl"] = array("char500", true);

        if(!$this->objDB->createTable("session", $arrFields, array("session_id"), array("session_phpid")))
            $strReturn .= "An error occured! ...\n";

        // caching --------------------------------------------------------------------------------------
        $strReturn .= "Installing table cache...\n";

        $arrFields = array();
        $arrFields["cache_id"] = array("char20", false);
        $arrFields["cache_source"] = array("char254", true);
        $arrFields["cache_hash1"] = array("char254", true);
        $arrFields["cache_hash2"] = array("char254", true);
        $arrFields["cache_language"] = array("char20", true);
        $arrFields["cache_content"] = array("longtext", true);
        $arrFields["cache_leasetime"] = array("int", true);
        $arrFields["cache_hits"] = array("int", true);

        if(!$this->objDB->createTable("cache", $arrFields, array("cache_id"), array("cache_source", "cache_hash1", "cache_leasetime", "cache_language"), false))
            $strReturn .= "An error occured! ...\n";

        //languages -------------------------------------------------------------------------------------
        $strReturn .= "Installing table languages...\n";

        $arrFields = array();
        $arrFields["language_id"] = array("char20", false);
        $arrFields["language_name"] = array("char254", true);
        $arrFields["language_default"] = array("int", true);

        if(!$this->objDB->createTable("languages", $arrFields, array("language_id")))
            $strReturn .= "An error occured! ...\n";

        $strReturn .= "Installing table languages_languageset...\n";

        $arrFields = array();
        $arrFields["languageset_id"] = array("char20", false);
        $arrFields["languageset_language"] = array("char20", true);
        $arrFields["languageset_systemid"] = array("char20", true);

        if(!$this->objDB->createTable("languages_languageset", $arrFields, array("languageset_id", "languageset_systemid")))
            $strReturn .= "An error occured! ...\n";

        //aspects --------------------------------------------------------------------------------------
        $strReturn .= "Installing table aspects...\n";

        $arrFields = array();
        $arrFields["aspect_id"] = array("char20", false);
        $arrFields["aspect_name"] = array("char254", true);
        $arrFields["aspect_default"] = array("int", true);

        if(!$this->objDB->createTable("aspects", $arrFields, array("aspect_id")))
            $strReturn .= "An error occured! ...\n";

        //changelog -------------------------------------------------------------------------------------
        $strReturn .= "Installing table changelog...\n";
        $this->installChangeTables();

        //messages
        $strReturn .= "Installing table messages...\n";

        $arrFields = array();
        $arrFields["message_id"] = array("char20", false);
        $arrFields["message_title"] = array("char254", true);
        $arrFields["message_body"] = array("text", true);
        $arrFields["message_read"] = array("int", true);
        $arrFields["message_user"] = array("char20", true);
        $arrFields["message_provider"] = array("char254", true);
        $arrFields["message_internalidentifier"] = array("char254", true);

        if(!$this->objDB->createTable("messages", $arrFields, array("message_id"), array("message_user", "message_read")))
            $strReturn .= "An error occured! ...\n";

        $arrFields = array();
        $arrFields["config_id"] = array("char20", false);
        $arrFields["config_provider"] = array("char254", true);
        $arrFields["config_user"] = array("char20", true);
        $arrFields["config_enabled"] = array("int", true);
        $arrFields["config_bymail"] = array("int", true);

        if(!$this->objDB->createTable("messages_cfg", $arrFields, array("config_id")))
            $strReturn .= "An error occured! ...\n";


        //Now we have to register module by module

        //The Systemkernel
        $this->registerModule("system", _system_modul_id_, "", "class_module_system_admin.php", $this->objMetadata->getStrVersion(), true, "", "class_module_system_admin_xml.php");
        //The Rightsmodule
        $this->registerModule("right", _system_modul_id_, "", "class_module_right_admin.php", $this->objMetadata->getStrVersion(), false);
        //The Usermodule
        $this->registerModule("user", _user_modul_id_, "", "class_module_user_admin.php", $this->objMetadata->getStrVersion(), true);
        //languages
        $this->registerModule("languages", _languages_modul_id_, "class_modul_languages_portal.php", "class_module_languages_admin.php", $this->objMetadata->getStrVersion(), true);
        //messaging
        $this->registerModule("messaging", _messaging_module_id_, "", "class_module_messaging_admin.php", $this->objMetadata->getStrVersion(), true);


        //Registering a few constants
        $strReturn .= "Registering system-constants...\n";
        //Number of rows in the login-log
        $this->registerConstant("_user_log_nrofrecords_", "50", 1, _user_modul_id_);

        //And the default skin
        $this->registerConstant("_admin_skin_default_", "kajona_v4", class_module_system_setting::$int_TYPE_STRING, _user_modul_id_);

        //and a few system-settings
        $this->registerConstant("_system_portal_disable_", "false", class_module_system_setting::$int_TYPE_BOOL, _system_modul_id_);
        $this->registerConstant("_system_portal_disablepage_", "", class_module_system_setting::$int_TYPE_PAGE, _system_modul_id_);

        //New in 3.0: Number of db-dumps to hold
        $this->registerConstant("_system_dbdump_amount_", 5, class_module_system_setting::$int_TYPE_INT, _system_modul_id_);
        //new in 3.0: mod-rewrite on / off
        $this->registerConstant("_system_mod_rewrite_", "false", class_module_system_setting::$int_TYPE_BOOL, _system_modul_id_);
        //New Constant: Max time to lock records
        $this->registerConstant("_system_lock_maxtime_", 7200, class_module_system_setting::$int_TYPE_INT, _system_modul_id_);
        //Email to send error-reports
        $this->registerConstant("_system_admin_email_", $this->objSession->getSession("install_email"), class_module_system_setting::$int_TYPE_STRING, _system_modul_id_);

        //3.0.2: user are allowed to change their settings?
        $this->registerConstant("_user_selfedit_", "true", class_module_system_setting::$int_TYPE_BOOL, _user_modul_id_);

        //3.1: nr of rows in admin
        $this->registerConstant("_admin_nr_of_rows_", 15, class_module_system_setting::$int_TYPE_INT, _system_modul_id_);
        $this->registerConstant("_admin_only_https_", "false", class_module_system_setting::$int_TYPE_BOOL, _system_modul_id_);
        $this->registerConstant("_system_use_dbcache_", "true", class_module_system_setting::$int_TYPE_BOOL, _system_modul_id_);

        //3.1: remoteloader max cachtime --> default 60 min
        $this->registerConstant("_remoteloader_max_cachetime_", 60 * 60, class_module_system_setting::$int_TYPE_INT, _system_modul_id_);

        //3.2: max session duration
        $this->registerConstant("_system_release_time_", 3600, class_module_system_setting::$int_TYPE_INT, _system_modul_id_);
        //3.4: cache buster to be able to flush the browsers cache (JS and CSS files)
        $this->registerConstant("_system_browser_cachebuster_", 0, class_module_system_setting::$int_TYPE_INT, _system_modul_id_);
        //3.4: Adding constant _system_graph_type_ indicating the chart-engine to use
        $this->registerConstant("_system_graph_type_", "jqplot", class_module_system_setting::$int_TYPE_STRING, _system_modul_id_);
        //3.4: Enabling or disabling the internal changehistory
        $this->registerConstant("_system_changehistory_enabled_", "false", class_module_system_setting::$int_TYPE_BOOL, _system_modul_id_);

        $this->registerConstant("_system_timezone_", "", class_module_system_setting::$int_TYPE_STRING, _system_modul_id_);


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
        $this->objDB->flushQueryCache();

        $strReturn .= "Modified root-rights....\n";
        $this->objRights->rebuildRightsStructure();
        $strReturn .= "Rebuilded rights structures...\n";

        //Creating an admin-user
        $strUsername = "admin";
        $strPassword = "kajona";
        $strEmail = "";
        //Login-Data given from installer?
        if($this->objSession->getSession("install_username") !== false && $this->objSession->getSession("install_username") != "" &&
            $this->objSession->getSession("install_password") !== false && $this->objSession->getSession("install_password") != ""
        ) {
            $strUsername = ($this->objSession->getSession("install_username"));
            $strPassword = ($this->objSession->getSession("install_password"));
            $strEmail = ($this->objSession->getSession("install_email"));
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

        //creating a new default-aspect
        $strReturn .= "Registering new default aspects...\n";
        $objAspect = new class_module_system_aspect();
        $objAspect->setStrName("content");
        $objAspect->setBitDefault(true);
        $objAspect->updateObjectToDb();
        class_module_system_aspect::setCurrentAspectId($objAspect->getSystemid());

        $objAspect = new class_module_system_aspect();
        $objAspect->setStrName("management");
        $objAspect->updateObjectToDb();

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



        $strReturn .= "Assigning modules to default aspects...\n";
        $objModule = class_module_system_module::getModuleByName("system");
        $objModule->setStrAspect(class_module_system_aspect::getAspectByName("management")->getSystemid());
        $objModule->updateObjectToDb();

        $objModule = class_module_system_module::getModuleByName("user");
        $objModule->setStrAspect(class_module_system_aspect::getAspectByName("management")->getSystemid());
        $objModule->updateObjectToDb();

        $objModule = class_module_system_module::getModuleByName("languages");
        $objModule->setStrAspect(class_module_system_aspect::getAspectByName("management")->getSystemid());
        $objModule->updateObjectToDb();


        $strReturn .= "Trying to copy the *.root files to top-level...\n";
        $arrFiles = array(
            "index.php", "image.php", "xml.php", ".htaccess", "v3_v4_postupdate.php"
        );
        foreach($arrFiles as $strOneFile) {
            if(!file_exists(_realpath_."/".$strOneFile) && is_file(_realpath_."/core/module_system/".$strOneFile.".root")) {
                if(!copy(_realpath_."/core/module_system/".$strOneFile.".root", _realpath_."/".$strOneFile))
                    $strReturn .= "<b>Copying the ".$strOneFile.".root to top level failed!!!</b>";
            }
        }



        $strReturn .= "Setting messaging to pos 1 in navigation.../n";
        $objModule = class_module_system_module::getModuleByName("messaging");
        $objModule->setAbsolutePosition(1);

        return $strReturn;
    }


    public function installChangeTables() {
        $strReturn = "";

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


        $arrTables = array("changelog");
        $arrProvider = class_module_system_changelog::getAdditionalProviders();
        foreach($arrProvider as $objOneProvider) {
            $arrTables[] = $objOneProvider->getTargetTable();
        }

        $arrDbTables = $this->objDB->getTables();
        foreach($arrTables as $strOneTable) {
            if(!in_array(_dbprefix_.$strOneTable, $arrDbTables)) {
                if(!$this->objDB->createTable($strOneTable, $arrFields, array("change_id"), array("change_date", "change_user", "change_systemid", "change_property"), false))
                    $strReturn .= "An error occured! ...\n";
            }
        }

        return $strReturn;

    }

    protected function updateModuleVersion($strModuleName, $strVersion) {
        parent::updateModuleVersion("system", $strVersion);
        parent::updateModuleVersion("right", $strVersion);
        parent::updateModuleVersion("user", $strVersion);
        parent::updateModuleVersion("languages", $strVersion);
        parent::updateModuleVersion("messaging", $strVersion);
    }

    public function update() {
        $strReturn = "";
        //check installed version and to which version we can update
        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        $strReturn .= "Version found:\n\t Module: ".$arrModul["module_name"].", Version: ".$arrModul["module_version"]."\n\n";


        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.2" || $arrModul["module_version"] == "3.4.2.2") {
            $strReturn .= $this->update_342_349();
            $this->objDB->flushQueryCache();
            $this->objDB->flushPreparedStatementsCache();
            $strReturn .= "<b>Temporary breaking update, please retrigger update sequence...</b>\n";
            return $strReturn;
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.9") {
            $strReturn .= $this->update_349_3491();
            $this->objDB->flushQueryCache();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.9.1") {
            $strReturn .= $this->update_3491_3492();
            $this->objDB->flushQueryCache();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.9.2") {
            $strReturn .= $this->update_3492_3493();
            $this->objDB->flushQueryCache();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "3.4.9.3") {
            $strReturn .= $this->update_3493_40();
            $this->objDB->flushQueryCache();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.0") {
            $strReturn .= $this->update_40_401();
            $this->objDB->flushQueryCache();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.0.1") {
            $strReturn .= $this->update_401_41();
            $this->objDB->flushQueryCache();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.1") {
            $strReturn .= $this->update_41_411();
            $this->objDB->flushQueryCache();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.1.1") {
            $strReturn .= $this->update_411_42();
            $this->objDB->flushQueryCache();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.2") {
            $strReturn .= "Updating 4.2 to 4.3...\n";
            $strReturn .= "Updating module-versions...\n";
            $this->updateModuleVersion("", "4.3");
            $this->objDB->flushQueryCache();
        }

        $arrModul = class_module_system_module::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModul["module_version"] == "4.3") {
            $strReturn .= $this->update_43_431();
            $this->objDB->flushQueryCache();
        }

        return $strReturn."\n\n";
    }


    private function update_342_349() {
        $strReturn = "Updating 3.4.2 to 3.4.9...\n";

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
            $this->objDB->_pQuery($strQuery, array(get_class($objOneModule), $objOneModule->getSystemid()));
        }

        $strReturn .= "Languages\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."languages, "._dbprefix_."system WHERE system_id = language_id", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array('class_module_languages_language', $arrOneRow["system_id"]));
        }

        $strReturn .= "Languages\n";
        $arrRows = $this->objDB->getPArray("SELECT system_id FROM "._dbprefix_."aspects, "._dbprefix_."system WHERE system_id = aspect_id", array());
        foreach($arrRows as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."system SET system_class = ? where system_id = ?";
            $this->objDB->_pQuery($strQuery, array('class_module_system_aspect', $arrOneRow["system_id"]));
        }


        $strReturn .= "Adding index to table system\n";
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."system")." ADD INDEX ( ".$this->objDB->encloseColumnName("system_sort")." ) ", array());
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."system")." ADD INDEX ( ".$this->objDB->encloseColumnName("system_owner")." ) ", array());
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."system")." ADD INDEX ( ".$this->objDB->encloseColumnName("system_create_date")." ) ", array());
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."system")." ADD INDEX ( ".$this->objDB->encloseColumnName("system_status")." ) ", array());
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."system")." ADD INDEX ( ".$this->objDB->encloseColumnName("system_lm_time")." ) ", array());

        $strReturn .= "Adding index to table system_date\n";
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."system_date")." ADD INDEX ( ".$this->objDB->encloseColumnName("system_date_start")." ) ", array());
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."system_date")." ADD INDEX ( ".$this->objDB->encloseColumnName("system_date_end")." ) ", array());
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."system_date")." ADD INDEX ( ".$this->objDB->encloseColumnName("system_date_special")." ) ", array());

        $strReturn .= "Adding index to table changelog\n";
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."changelog")." ADD INDEX ( ".$this->objDB->encloseColumnName("change_date")." ) ", array());
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."changelog")." ADD INDEX ( ".$this->objDB->encloseColumnName("change_user")." ) ", array());
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."changelog")." ADD INDEX ( ".$this->objDB->encloseColumnName("change_systemid")." ) ", array());
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."changelog")." ADD INDEX ( ".$this->objDB->encloseColumnName("change_property")." ) ", array());


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.4.9");
        return $strReturn;
    }


    private function update_349_3491() {
        $strReturn = "Updating 3.4.9 to 3.4.9.1...\n";


        //messages
        $strReturn .= "Installing table messages...\n";

        $arrFields = array();
        $arrFields["message_id"] = array("char20", false);
        $arrFields["message_title"] = array("char254", true);
        $arrFields["message_body"] = array("text", true);
        $arrFields["message_read"] = array("int", true);
        $arrFields["message_user"] = array("char20", true);
        $arrFields["message_provider"] = array("char254", true);
        $arrFields["message_internalidentifier"] = array("char254", true);

        if(!$this->objDB->createTable("messages", $arrFields, array("message_id"), array("message_user", "message_read")))
            $strReturn .= "An error occured! ...\n";

        $arrFields = array();
        $arrFields["config_id"] = array("char20", false);
        $arrFields["config_provider"] = array("char254", true);
        $arrFields["config_user"] = array("char20", true);
        $arrFields["config_enabled"] = array("int", true);
        $arrFields["config_bymail"] = array("int", true);

        if(!$this->objDB->createTable("messages_cfg", $arrFields, array("config_id")))
            $strReturn .= "An error occured! ...\n";

        $strReturn .= "Registering module...\n";
        $this->registerModule("messaging", _messaging_module_id_, "", "class_module_messaging_admin.php", $this->objMetadata->getStrVersion(), true);

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.4.9.1");
        return $strReturn;
    }

    private function update_3491_3492() {
        $strReturn = "Updating 3.4.9.1 to 3.4.9.2...\n";

        //messages
        $strReturn .= "Updating table user_kajona...\n";
        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."user_kajona")."
                            ADD ".$this->objDB->encloseColumnName("user_salt")." ".$this->objDB->getDatatype("char20")." NULL";
        if(!$this->objDB->_pQuery($strQuery, array()))
            $strReturn .= "An error occured! ...\n";


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.4.9.2");
        return $strReturn;
    }



    private function update_3492_3493() {
        $strReturn = "Updating 3.4.9.2 to 3.4.9.3...\n";

        //messages
        $strReturn .= "Updating table user_log...\n";
        $strQuery = "ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."user_log")."
                            ADD ".$this->objDB->encloseColumnName("user_log_sessid")." ".$this->objDB->getDatatype("char20")." NULL,
                            ADD ".$this->objDB->encloseColumnName("user_log_enddate")." ".$this->objDB->getDatatype("long")." NULL,
                            CHANGE ".$this->objDB->encloseColumnName("user_log_date")." ".$this->objDB->encloseColumnName("user_log_date")." ".$this->objDB->getDatatype("long")." NULL";
        if(!$this->objDB->_pQuery($strQuery, array()))
            $strReturn .= "An error occured! ...\n";

        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."user_log")." ADD INDEX ( ".$this->objDB->encloseColumnName("user_log_sessid")." ) ", array());

        $strReturn .= "Creating default aspects...\n";

        $arrAspects = class_module_system_aspect::getObjectList();

        if(
            (count($arrAspects) == 0 || (count($arrAspects) == 1 && $arrAspects[0]->getStrName() == "default"))
            && class_module_system_aspect::getAspectByName("management") == null
            && class_module_system_aspect::getAspectByName("content") == null
        ) {

            if(count($arrAspects) == 1 && $arrAspects[0]->getStrName() == "default")
                $objAspect = $arrAspects[0];
            else
                $objAspect = new class_module_system_aspect();


            $objAspect->setStrName("content");
            $objAspect->updateObjectToDb();
            $objAspect = new class_module_system_aspect();
            $objAspect->setStrName("management");
            $objAspect->updateObjectToDb();

            $strReturn .= "Assigning modules to default aspects...\n";
            $objModule = class_module_system_module::getModuleByName("system");
            $objModule->setStrAspect(class_module_system_aspect::getAspectByName("management")->getSystemid());
            $objModule->updateObjectToDb();

            $objModule = class_module_system_module::getModuleByName("user");
            $objModule->setStrAspect(class_module_system_aspect::getAspectByName("management")->getSystemid());
            $objModule->updateObjectToDb();

            $objModule = class_module_system_module::getModuleByName("languages");
            $objModule->setStrAspect(class_module_system_aspect::getAspectByName("management")->getSystemid());
            $objModule->updateObjectToDb();
        }


        $strReturn .= "Updating default skin...\n";
        $objSetting = class_module_system_setting::getConfigByName("_admin_skin_default_");
        if($objSetting->getStrValue() == "kajona_v3") {
            $objSetting->setStrValue("kajona_v4");
            $objSetting->updateObjectToDb();
        }


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "3.4.9.3");
        return $strReturn;
    }

    private function update_3493_40() {
        $strReturn = "Updating 3.4.9.3 to 4.0...\n";

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "4.0");
        return $strReturn;
    }

    private function update_40_401() {

        $strReturn = "Updating 40 to 4.0.1...\n";

        $strReturn .= "updating change tables...\n";
        $strReturn .= $this->installChangeTables();

        $strReturn .= "moving changes-entries...\n";
        $strQuery = "INSERT INTO "._dbprefix_."changelog_setting
            (change_id, change_date, change_user, change_systemid, change_system_previd, change_class, change_action, change_property, change_oldvalue, change_newvalue)
            SELECT change_id, change_date, change_user, change_systemid, change_system_previd, change_class, change_action, change_property, change_oldvalue, change_newvalue
            FROM "._dbprefix_."changelog WHERE change_class = ?";
        $this->objDB->_pQuery($strQuery, array("class_modul_system_setting"));
        $this->objDB->_pQuery($strQuery, array("class_module_system_setting"));

        $strReturn .= "deleting original rows...\n";
        $strQuery = "DELETE FROM "._dbprefix_."changelog WHERE change_class = ?";
        $this->objDB->_pQuery($strQuery, array("class_modul_system_setting"));
        $this->objDB->_pQuery($strQuery, array("class_module_system_setting"));


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "4.0.1");
        return $strReturn;
    }

    private function update_401_41() {

        $strReturn = "Updating 4.0.1 to 4.1...\n";

        $strReturn .= "Please note: the .htaccess file changed!\n";
        $strReturn .= "You have to merge changes manually!\n";
        $strReturn .= "Change line ~57 from:\n";
        $strReturn .= "#RewriteRule ^(([a-z]{2})/)(.*/)?([0-9a-z\_\-]+)\.(.*)\.([a-zA-Z]*)\.([0-9a-z]*)\.([a-z]{2})\.html  index.php?page=$4&action=$6&systemid=$7&language=$2 [QSA,L]\n";
        $strReturn .= "to\n";
        $strReturn .= "#RewriteRule ^(([a-z]{2})/)(.*/)?([0-9a-z\_\-]+)\.(.*)\.([a-zA-Z]*)\.([0-9a-z]*)\.html  index.php?page=$4&action=$6&systemid=$7&language=$2 [QSA,L]\n\n";
        $strReturn .= "See http://www.kajona.de/update_40_to_41.html for more information.\n\n";

        $strReturn .= "Adding the timezone config...\n";
        if(!defined("_system_timezone_"))
            $this->registerConstant("_system_timezone_", "", class_module_system_setting::$int_TYPE_STRING, _system_modul_id_);


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "4.1");
        return $strReturn;
    }

    private function update_41_411() {

        $strReturn = "Updating 4.1 to 4.1.1...\n";

        $strReturn .= "Patching current bootstrap.php\n";
        $objFileystem = new class_filesystem();
        if(!$objFileystem->isWritable("/core/bootstrap.php")) {
            $strReturn .= "Error! /core/bootstrap.php is not writable. Please set up write permissions for the update-procedure.\nAborting update.";
            return $strReturn;
        }

        $objFileystem->fileCopy("/core/module_system/installer/bootstrap.php_411", "/core/bootstrap.php", true);


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "4.1.1");
        return $strReturn;
    }

    private function update_411_42() {
        $strReturn = "Updating 4.1.1 to 4.2...\n";
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "4.2");
        return $strReturn;
    }

    private function update_43_431() {
        $strReturn = "Updating 4.3 to 4.3.1...\n";
        $strReturn .= "This update removes the flot chart module and replaces it with the jqplot chart module...\n\n";

    //1. install module jqplot
        $strReturn .= "Installing module jqplot if not exist...\n";
        $objManager = new class_module_packagemanager_manager();
        $objExistingJqPlotPackage = $objManager->getPackage("jqplot");

        //if jqplot is not installed, install it
        if($objExistingJqPlotPackage === null) {
            $objContentProvider = new class_module_packagemanager_contentprovider_kajona();
            $arrPackageMetaData = $objContentProvider->searchPackage("jqplot");

            //if a package was found
            if($arrPackageMetaData !== null && count($arrPackageMetaData) == 1) {
                //upload the package to projects/temp
                class_carrier::getInstance()->setParam("systemid", $arrPackageMetaData[0]["systemid"]);
                $strFile = $objContentProvider->processPackageUpload();

                if($objManager->validatePackage($strFile)) {
                    if(uniSubstr($strFile, -4) == ".zip") {
                        //now extract the zip file and......
                        $objHandler = $objManager->extractPackage($strFile);
                        $objFilesystem = new class_filesystem();
                        $objFilesystem->fileDelete($strFile);
                        //move the created folder to /core
                        $objHandler->move2Filesystem();
                    }
                }
                else {
                    $strReturn .= "Package file is not valid...\n";
                    $strReturn .= "Update to version 4.3.1 cancelled...\n";
                    return $strReturn;
                }
            }
            else {
                $strReturn = "Module jqplot was not found via the packagemanager...\n";
                $strReturn .= "Update to version 4.3.1 cancelled...\n";
                return $strReturn;
            }
        }

    //2. uninstall module flot
        $strReturn .= "Removing module flotchart if exists...\n";
        $objFlotPackage = $objManager->getPackage("flotchart");
        if($objFlotPackage !== null) {
            //uninstall flot
            $class_filesystem = new class_filesystem();
            $class_filesystem->folderDeleteRecursive($objFlotPackage->getStrPath());
        }


    //3. set jqplot as standard chart library
        $strReturn .= "Set jqplot as standard chart library if flot was selected standard chart library...\n";
        $objSetting = class_module_system_setting::getConfigByName("_system_graph_type_");
        if($objSetting->getStrValue() == "flot") {
            $objSetting->setStrValue("jqplot");
            $objSetting->updateObjectToDb();
        }

    //4. update version to 4.3.1
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion("", "4.3.1");


    //5. relaod classloader etc.
        class_resourceloader::getInstance()->flushCache();
        class_classloader::getInstance()->flushCache();
        class_reflection::flushCache();

        return $strReturn;
    }
}
