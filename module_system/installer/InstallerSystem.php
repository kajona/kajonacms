<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                        *
********************************************************************************************************/

namespace Kajona\System\Installer;

use Kajona\System\System\Carrier;
use Kajona\System\System\Classloader;
use Kajona\System\System\Date;
use Kajona\System\System\DbDatatypes;
use Kajona\System\System\Filesystem;
use Kajona\System\System\IdGenerator;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerInterface;
use Kajona\System\System\LanguagesLanguage;
use Kajona\System\System\MessagingConfig;
use Kajona\System\System\MessagingMessage;
use Kajona\System\System\OrmBase;
use Kajona\System\System\OrmSchemamanager;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\Rights;
use Kajona\System\System\Session;
use Kajona\System\System\SystemAspect;
use Kajona\System\System\SystemChangelog;
use Kajona\System\System\SystemCommon;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemPwchangehistory;
use Kajona\System\System\SystemSetting;
use Kajona\System\System\UserGroup;
use Kajona\System\System\UserUser;

/**
 * Installer for the system-module
 *
 * @package module_system
 * @moduleId _system_modul_id_
 */
class InstallerSystem extends InstallerBase implements InstallerInterface {

    private $strContentLanguage;

    public function __construct() {
        parent::__construct();

        //set the correct language
        $this->strContentLanguage = Carrier::getInstance()->getObjSession()->getAdminLanguage(true, true);
    }

    public function install() {
        $strReturn = "";
        $objManager = new OrmSchemamanager();

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
        $arrFields["system_deleted"] = array("int", true);

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
        $arrFields["right_changelog"] = array("text", true);

        if(!$this->objDB->createTable("system", $arrFields, array("system_id"), array("system_prev_id", "system_module_nr", "system_sort", "system_owner", "system_create_date", "system_status", "system_lm_time", "system_lock_time", "system_deleted")))
            $strReturn .= "An error occurred! ...\n";


        // Modul table ----------------------------------------------------------------------------------
        $strReturn .= "Installing table system_module...\n";
        $objManager->createTable(SystemModule::class);


        // Date table -----------------------------------------------------------------------------------
        $strReturn .= "Installing table system_date...\n";

        $arrFields = array();
        $arrFields["system_date_id"] = array("char20", false);
        $arrFields["system_date_start"] = array("long", true);
        $arrFields["system_date_end"] = array("long", true);
        $arrFields["system_date_special"] = array("long", true);

        if(!$this->objDB->createTable("system_date", $arrFields, array("system_date_id"), array("system_date_start", "system_date_end", "system_date_special")))
            $strReturn .= "An error occurred! ...\n";

        // Config table ---------------------------------------------------------------------------------
        $strReturn .= "Installing table system_config...\n";

        $arrFields = array();
        $arrFields["system_config_id"] = array("char20", false);
        $arrFields["system_config_name"] = array("char254", true);
        $arrFields["system_config_value"] = array("char254", true);
        $arrFields["system_config_type"] = array("int", true);
        $arrFields["system_config_module"] = array("int", true);

        if(!$this->objDB->createTable("system_config", $arrFields, array("system_config_id")))
            $strReturn .= "An error occurred! ...\n";


        // User table -----------------------------------------------------------------------------------
        $strReturn .= "Installing table user...\n";
        $objManager->createTable(UserUser::class);

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
        $arrFields["user_specialconfig"] = array("text", true);

        if(!$this->objDB->createTable("user_kajona", $arrFields, array("user_id")))
            $strReturn .= "An error occurred! ...\n";

        // User group table -----------------------------------------------------------------------------
        $strReturn .= "Installing table user_group...\n";
        $objManager->createTable(UserGroup::class);

        $strReturn .= "Installing table user_group_kajona...\n";

        $arrFields = array();
        $arrFields["group_id"] = array("char20", false);
        $arrFields["group_desc"] = array("char254", true);


        if(!$this->objDB->createTable("user_group_kajona", $arrFields, array("group_id")))
            $strReturn .= "An error occurred! ...\n";


        // User group_members table ---------------------------------------------------------------------
        $strReturn .= "Installing table user_kajona_members...\n";

        $arrFields = array();
        $arrFields["group_member_group_kajona_id"] = array("char20", false);
        $arrFields["group_member_user_kajona_id"] = array("char20", false);

        if(!$this->objDB->createTable("user_kajona_members", $arrFields, array("group_member_group_kajona_id", "group_member_user_kajona_id")))
            $strReturn .= "An error occurred! ...\n";


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
            $strReturn .= "An error occurred! ...\n";

        // Sessionmgtm ----------------------------------------------------------------------------------
        $strReturn .= "Installing table session...\n";

        $arrFields = array();
        $arrFields["session_id"] = array("char20", false);
        $arrFields["session_phpid"] = array("char254", true);
        $arrFields["session_releasetime"] = array("int", true);
        $arrFields["session_loginstatus"] = array("char254", true);
        $arrFields["session_loginprovider"] = array("char20", true);
        $arrFields["session_lasturl"] = array("text", true);
        $arrFields["session_userid"] = array("char20", true);

        if(!$this->objDB->createTable("session", $arrFields, array("session_id"), array("session_phpid", "session_releasetime")))
            $strReturn .= "An error occurred! ...\n";

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
            $strReturn .= "An error occurred! ...\n";

        //languages -------------------------------------------------------------------------------------
        $strReturn .= "Installing table languages...\n";
        $objManager->createTable(LanguagesLanguage::class);

        $strReturn .= "Installing table languages_languageset...\n";
        $arrFields = array();
        $arrFields["languageset_id"] = array("char20", false);
        $arrFields["languageset_language"] = array("char20", true);
        $arrFields["languageset_systemid"] = array("char20", false);

        if(!$this->objDB->createTable("languages_languageset", $arrFields, array("languageset_id", "languageset_systemid")))
            $strReturn .= "An error occurred! ...\n";

        //aspects --------------------------------------------------------------------------------------
        $strReturn .= "Installing table aspects...\n";
        $objManager->createTable(SystemAspect::class);

        //changelog -------------------------------------------------------------------------------------
        $strReturn .= "Installing table changelog...\n";
        $this->installChangeTables();

        //messages
        $strReturn .= "Installing table messages...\n";
        $objManager->createTable(MessagingMessage::class);
        $objManager->createTable(MessagingConfig::class);

        // password change history
        $strReturn .= "Installing password reset history...\n";
        $objManager->createTable(SystemPwchangehistory::class);

        // idgenerator
        $strReturn .= "Installing idgenerator table...\n";
        $objManager->createTable(IdGenerator::class);

        //Now we have to register module by module

        //The Systemkernel
        $this->registerModule("system", _system_modul_id_, "", "SystemAdmin.php", $this->objMetadata->getStrVersion());
        //The Rightsmodule
        $this->registerModule("right", _system_modul_id_, "", "RightAdmin.php", $this->objMetadata->getStrVersion(), false);
        //The Usermodule
        $this->registerModule("user", _user_modul_id_, "", "UserAdmin.php", $this->objMetadata->getStrVersion());
        //languages
        $this->registerModule("languages", _languages_modul_id_, "", "LanguagesAdmin.php", $this->objMetadata->getStrVersion());
        //messaging
        $this->registerModule("messaging", _messaging_module_id_, "MessagingPortal.php", "MessagingAdmin.php", $this->objMetadata->getStrVersion());


        //Registering a few constants
        $strReturn .= "Registering system-constants...\n";

        //And the default skin
        $this->registerConstant("_admin_skin_default_", "kajona_v4", SystemSetting::$int_TYPE_STRING, _user_modul_id_);

        //and a few system-settings
        $this->registerConstant("_system_portal_disable_", "false", SystemSetting::$int_TYPE_BOOL, _system_modul_id_);
        $this->registerConstant("_system_portal_disablepage_", "", SystemSetting::$int_TYPE_PAGE, _system_modul_id_);

        //New in 3.0: Number of db-dumps to hold
        $this->registerConstant("_system_dbdump_amount_", 5, SystemSetting::$int_TYPE_INT, _system_modul_id_);
        //new in 3.0: mod-rewrite on / off
        $this->registerConstant("_system_mod_rewrite_", "false", SystemSetting::$int_TYPE_BOOL, _system_modul_id_);
        $this->registerConstant("_system_mod_rewrite_admin_only_", "false", SystemSetting::$int_TYPE_BOOL, _system_modul_id_);
        
        
        //New Constant: Max time to lock records
        $this->registerConstant("_system_lock_maxtime_", 7200, SystemSetting::$int_TYPE_INT, _system_modul_id_);
        //Email to send error-reports
        $this->registerConstant("_system_admin_email_", $this->objSession->getSession("install_email"), SystemSetting::$int_TYPE_STRING, _system_modul_id_);

        $this->registerConstant("_system_email_defaultsender_", $this->objSession->getSession("install_email"), SystemSetting::$int_TYPE_STRING, _system_modul_id_);
        $this->registerConstant("_system_email_forcesender_", "false", SystemSetting::$int_TYPE_BOOL, _system_modul_id_);

        //3.0.2: user are allowed to change their settings?
        $this->registerConstant("_user_selfedit_", "true", SystemSetting::$int_TYPE_BOOL, _user_modul_id_);

        //3.1: nr of rows in admin
        $this->registerConstant("_admin_nr_of_rows_", 15, SystemSetting::$int_TYPE_INT, _system_modul_id_);
        $this->registerConstant("_admin_only_https_", "false", SystemSetting::$int_TYPE_BOOL, _system_modul_id_);
        $this->registerConstant("_cookies_only_https_", "false", SystemSetting::$int_TYPE_BOOL, _system_modul_id_);

        //3.1: remoteloader max cachtime --> default 60 min
        $this->registerConstant("_remoteloader_max_cachetime_", 60 * 60, SystemSetting::$int_TYPE_INT, _system_modul_id_);

        //3.2: max session duration
        $this->registerConstant("_system_release_time_", 3600, SystemSetting::$int_TYPE_INT, _system_modul_id_);
        //3.4: cache buster to be able to flush the browsers cache (JS and CSS files)
        $this->registerConstant("_system_browser_cachebuster_", 0, SystemSetting::$int_TYPE_INT, _system_modul_id_);
        //3.4: Adding constant _system_graph_type_ indicating the chart-engine to use
        $this->registerConstant("_system_graph_type_", "jqplot", SystemSetting::$int_TYPE_STRING, _system_modul_id_);
        //3.4: Enabling or disabling the internal changehistory
        $this->registerConstant("_system_changehistory_enabled_", "false", SystemSetting::$int_TYPE_BOOL, _system_modul_id_);

        $this->registerConstant("_system_timezone_", "", SystemSetting::$int_TYPE_STRING, _system_modul_id_);


        //Creating the admin & guest groups
        $objAdminGroup = new UserGroup();
        $objAdminGroup->setStrName("Admins");
        $objAdminGroup->updateObjectToDb();
        $strReturn .= "Registered Group Admins...\n";

        $objGuestGroup = new UserGroup();
        $objGuestGroup->setStrName("Guests");
        $objGuestGroup->updateObjectToDb();
        $strReturn .= "Registered Group Guests...\n";

        //Systemid of guest-user & admin group
        $strGuestID = $objGuestGroup->getSystemid();
        $intGuestShortId = $objGuestGroup->getIntShortId();
        $strAdminID = $objAdminGroup->getSystemid();
        $intAdminShortid = $objAdminGroup->getIntShortId();
        $this->registerConstant("_guests_group_id_", $strGuestID, SystemSetting::$int_TYPE_STRING, _user_modul_id_);
        $this->registerConstant("_admins_group_id_", $strAdminID, SystemSetting::$int_TYPE_STRING, _user_modul_id_);

        //BUT: We have to modify the right-record of the root node, too
        $strGroupsAll = ",".$intGuestShortId.",".$intAdminShortid.",";
        $strGroupsAdmin = ",".$intAdminShortid.",";

        //Create an root-record for the tree
        //So, lets generate the record
        $strQuery = "INSERT INTO "._dbprefix_."system
                     ( system_id, system_prev_id, system_module_nr, system_create_date, system_lm_time, system_status, system_sort, system_class,
                        right_inherit, right_view, right_edit, right_delete, right_right, right_right1, right_right2, right_right3, right_right4, right_right5, right_changelog
                     ) VALUES
                     (?, ?, ?, ?, ?, ?, ?, ?,
                     ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        //Send the query to the db
        $this->objDB->_pQuery(
            $strQuery,
            array(0, 0, _system_modul_id_, Date::getCurrentTimestamp(), time(), 1, 1, SystemCommon::class,
                0, $strGroupsAll, $strGroupsAdmin, $strGroupsAdmin, $strGroupsAdmin, $strGroupsAdmin, $strGroupsAdmin, $strGroupsAdmin, $strGroupsAdmin, $strGroupsAdmin, $strGroupsAdmin)
        );

        $this->objDB->flushQueryCache();

        $strReturn .= "Modified root-rights....\n";
        Carrier::getInstance()->getObjRights()->rebuildRightsStructure();
        $strReturn .= "Rebuilt rights structures...\n";

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
        $objLanguage = new LanguagesLanguage();

        if($this->strContentLanguage == "de")
            $objLanguage->setStrName("de");
        else
            $objLanguage->setStrName("en");

        $objLanguage->setBitDefault(true);
        $objLanguage->updateObjectToDb();
        $strReturn .= "ID of new language: ".$objLanguage->getSystemid()."\n";

        //the admin-language
        $strAdminLanguage = $this->strContentLanguage;

        //creating a new default-aspect
        $strReturn .= "Registering new default aspects...\n";
        $objAspect = new SystemAspect();
        $objAspect->setStrName("content");
        $objAspect->setBitDefault(true);
        $objAspect->updateObjectToDb();
        SystemAspect::setCurrentAspectId($objAspect->getSystemid());

        $objAspect = new SystemAspect();
        $objAspect->setStrName("management");
        $objAspect->updateObjectToDb();

        $objUser = new UserUser();
        $objUser->setStrUsername($strUsername);
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
        $objModule = SystemModule::getModuleByName("system");
        $objModule->setStrAspect(SystemAspect::getAspectByName("management")->getSystemid());
        $objModule->updateObjectToDb();

        $objModule = SystemModule::getModuleByName("user");
        $objModule->setStrAspect(SystemAspect::getAspectByName("management")->getSystemid());
        $objModule->updateObjectToDb();

        $objModule = SystemModule::getModuleByName("languages");
        $objModule->setStrAspect(SystemAspect::getAspectByName("management")->getSystemid());
        $objModule->updateObjectToDb();


        $strReturn .= "Trying to copy the *.root files to top-level...\n";
        $arrFiles = array(
            "index.php", "image.php", "xml.php", ".htaccess"
        );
        foreach($arrFiles as $strOneFile) {
            if(!file_exists(_realpath_.$strOneFile) && is_file(Resourceloader::getInstance()->getAbsolutePathForModule("module_system")."/".$strOneFile.".root")) {
                if(!copy(Resourceloader::getInstance()->getAbsolutePathForModule("module_system")."/".$strOneFile.".root", _realpath_.$strOneFile))
                    $strReturn .= "<b>Copying ".$strOneFile.".root to top level failed!!!</b>";
            }
        }



        $strReturn .= "Setting messaging to pos 1 in navigation.../n";
        $objModule = SystemModule::getModuleByName("messaging");
        $objModule->setAbsolutePosition(1);

        //to avoid problems on subsequent installers
        OrmBase::resetBitLogicalDeleteAvailable();


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
        $arrProvider = SystemChangelog::getAdditionalProviders();
        foreach($arrProvider as $objOneProvider) {
            $arrTables[] = $objOneProvider->getTargetTable();
        }

        $arrDbTables = $this->objDB->getTables();
        foreach($arrTables as $strOneTable) {
            if(!in_array(_dbprefix_.$strOneTable, $arrDbTables)) {
                if(!$this->objDB->createTable($strOneTable, $arrFields, array("change_id"), array("change_date", "change_user", "change_systemid", "change_property"), false))
                    $strReturn .= "An error occurred! ...\n";
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
        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        $strReturn .= "Version found:\n\t Module: ".$arrModule["module_name"].", Version: ".$arrModule["module_version"]."\n\n";

       
        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6") {
            $strReturn .= "Updating 4.6 to 4.6.1...\n";
            $this->updateModuleVersion("", "4.6.1");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6.1") {
            $strReturn .= "Updating 4.6.1 to 4.6.2...\n";
            $this->updateModuleVersion("", "4.6.2");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6.2") {
            $strReturn .= "Updating 4.6.2 to 4.6.3...\n";
            $this->updateModuleVersion("", "4.6.3");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6.3") {
            $strReturn .= $this->update_463_464();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6.4") {
            $strReturn .= $this->update_464_465();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6.5") {
            $strReturn .= $this->update_465_47();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.7" || $arrModule["module_version"] == "4.7.1" || $arrModule["module_version"] == "4.7.2") {
            $strReturn .= $this->update_47_475();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.7.5") {
            $strReturn .= $this->update_475_476();
        }


        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.7.6") {
            $strReturn .= $this->update_476_50();
        }
        
        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "5.0" || $arrModule["module_version"] == "5.0.1") {
            $strReturn .= "Updating 5.0 to 5.1...\n";
            $this->updateModuleVersion("", "5.1");
        }


        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "5.1") {
            $strReturn .= $this->update_51_511();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "5.1.1") {
            $strReturn .= $this->update_511_512();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "5.1.2") {
            $strReturn .= $this->update_512_513();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "5.1.3") {
            $strReturn .= $this->update_513_514();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "5.1.4") {
            $strReturn .= $this->update_514_62();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "5.1.5") {
            $strReturn .= "Updating 5.1.5 to 6.2...\n";
            $this->updateModuleVersion($this->objMetadata->getStrTitle(), "6.2");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "6.2") {
            $strReturn .= $this->update_62_621();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "6.2.1") {
            $strReturn .= $this->update_621_622();
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "6.2.2") {
            $strReturn .= $this->update_622_623();
        }

        return $strReturn."\n\n";
    }



    private function update_463_464() {
        $strReturn = "Updating 4.6.3 to 4.6.4...\n";

        $strReturn .= "Adding mail-config settings...\n";

        $this->registerConstant("_system_email_defaultsender_", SystemSetting::getConfigValue("_system_admin_email_"), SystemSetting::$int_TYPE_STRING, _system_modul_id_);
        $this->registerConstant("_system_email_forcesender_", "false", SystemSetting::$int_TYPE_BOOL, _system_modul_id_);


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.6.4");
        return $strReturn;
    }

    private function update_464_465() {
        $strReturn = "Updating 4.6.4 to 4.6.5...\n";

        $strReturn .= "Updating user table...\n";
        $this->objDB->addColumn("user", "user_items_per_page", DbDatatypes::STR_TYPE_INT);

        $strReturn .= "Removing setting _user_log_nrofrecords_...\n";
        SystemSetting::getConfigByName("_user_log_nrofrecords_")->deleteObjectFromDatabase();
        $strReturn .= "Removing setting _system_use_dbcache_...\n";
        SystemSetting::getConfigByName("_system_use_dbcache_")->deleteObjectFromDatabase();

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.6.5");
        return $strReturn;
    }


    private function update_465_47() {

        $strReturn = "Updating 4.6.5 to 4.7...\n";

        $strReturn .= "Patching bootstrap.php < 4.7\n";
        if(is_file(_realpath_."core/bootstrap.php")) {
            $objFileystem = new Filesystem();
            if(!$objFileystem->isWritable("/core/bootstrap.php")) {
                $strReturn .= "Error! /core/bootstrap.php is not writable. Please set up write permissions for the update-procedure.\nAborting update.";
                return $strReturn;
            }
            $objFileystem->fileCopy(Resourceloader::getInstance()->getCorePathForModule("module_system")."/module_system/installer/bootstrap.php_47", "/core/bootstrap.php", true);
        }

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.7");
        return $strReturn;
    }

    private function update_47_475() {

        $strReturn = "Updating 4.7 to 4.7.5...\n";

        $strReturn .= "Updating system table\n";
        $this->objDB->addColumn("system", "system_deleted", DbDatatypes::STR_TYPE_INT);
        $strQuery = "UPDATE "._dbprefix_."system SET system_deleted = 0";
        $this->objDB->_pQuery($strQuery, array());

        $strReturn .= "Updating database indexes\n";
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."system")." ADD INDEX ( ".$this->objDB->encloseColumnName("system_deleted")." ) ", array());
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."system")." ADD INDEX ( ".$this->objDB->encloseColumnName("system_lock_time")." ) ", array());

        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."user")." ADD INDEX ( ".$this->objDB->encloseColumnName("user_username")." ) ", array());
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."user")." ADD INDEX ( ".$this->objDB->encloseColumnName("user_subsystem")." ) ", array());
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."user")." ADD INDEX ( ".$this->objDB->encloseColumnName("user_active")." ) ", array());
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."user")." ADD INDEX ( ".$this->objDB->encloseColumnName("user_deleted")." ) ", array());

        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."user_group")." ADD INDEX ( ".$this->objDB->encloseColumnName("group_name")." ) ", array());
        $this->objDB->_pQuery("ALTER TABLE ".$this->objDB->encloseTableName(_dbprefix_."user_group")." ADD INDEX ( ".$this->objDB->encloseColumnName("group_subsystem")." ) ", array());


        Carrier::getInstance()->flushCache(Carrier::INT_CACHE_TYPE_DBTABLES | Carrier::INT_CACHE_TYPE_DBSTATEMENTS);
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.7.5");
        return $strReturn;
    }

    private function update_475_476() {

        $strReturn = "Updating 4.7.5 to 4.7.6...\n";

        // password change history
        $strReturn .= "Installing password reset history...\n";

        $objManager = new OrmSchemamanager();
        $objManager->createTable("Kajona\\System\\System\\SystemPwchangehistory");

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "4.7.6");
        return $strReturn;
    }


    private function update_476_50() {
        $strReturn = "Updating 4.7.6 to 5.0...\n";
        $strReturn .= "Registering new constant...\n";
        $this->registerConstant("_system_mod_rewrite_admin_only_", "false", SystemSetting::$int_TYPE_BOOL, _system_modul_id_);
        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "5.0");
        return $strReturn;
    }

    private function update_51_511() {
        $strReturn = "Updating 5.1.1 to 5.1.2...\n";



        $strReturn .= "Updating users and groups\n";
        $arrRightsRow = Rights::getInstance()->getArrayRights(SystemModule::getModuleIdByNr(_user_modul_id_));

        foreach($this->objDB->getPArray("SELECT * FROM "._dbprefix_."user", array()) as $arrOneRow) {
            //fire two inserts
            $this->objDB->_pQuery(
                "INSERT INTO "._dbprefix_."system (system_id, system_prev_id, system_module_nr, system_sort, system_status, system_class, system_deleted) VALUES (?, ?, ?, -1, ?, ?, ?)",
                array(
                    $arrOneRow["user_id"],
                    SystemModule::getModuleIdByNr(_user_modul_id_),
                    _user_modul_id_,
                    $arrOneRow["user_active"],
                    UserUser::class,
                    $arrOneRow["user_deleted"]
                )
            );

            $this->objDB->_pQuery(
                "INSERT INTO "._dbprefix_."system_right (right_id, right_inherit, right_view, right_edit, right_delete, right_right, right_right1, right_right2, right_right3, right_right4, right_right5, right_changelog) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                array(
                    $arrOneRow["user_id"],
                    1,
                    implode(",", $arrRightsRow[Rights::$STR_RIGHT_VIEW]),
                    implode(",", $arrRightsRow[Rights::$STR_RIGHT_EDIT]),
                    implode(",", $arrRightsRow[Rights::$STR_RIGHT_DELETE]),
                    implode(",", $arrRightsRow[Rights::$STR_RIGHT_RIGHT]),
                    implode(",", $arrRightsRow[Rights::$STR_RIGHT_RIGHT1]),
                    implode(",", $arrRightsRow[Rights::$STR_RIGHT_RIGHT2]),
                    implode(",", $arrRightsRow[Rights::$STR_RIGHT_RIGHT3]),
                    implode(",", $arrRightsRow[Rights::$STR_RIGHT_RIGHT4]),
                    implode(",", $arrRightsRow[Rights::$STR_RIGHT_RIGHT5]),
                    implode(",", $arrRightsRow[Rights::$STR_RIGHT_CHANGELOG])
                )
            );
        }

        foreach($this->objDB->getPArray("SELECT * FROM "._dbprefix_."user_group", array()) as $arrOneRow) {
            //fire two inserts
            $this->objDB->_pQuery(
                "INSERT INTO "._dbprefix_."system (system_id, system_prev_id, system_module_nr, system_sort, system_status, system_class, system_deleted) VALUES (?, ?, ?, -1, ?, ?, ?)",
                array($arrOneRow["group_id"], SystemModule::getModuleIdByNr(_user_modul_id_), _user_modul_id_, 1, UserGroup::class, 0)
            );

            $this->objDB->_pQuery(
                "INSERT INTO "._dbprefix_."system_right (right_id, right_inherit, right_view, right_edit, right_delete, right_right, right_right1, right_right2, right_right3, right_right4, right_right5, right_changelog) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                array(
                    $arrOneRow["group_id"],
                    1,
                    implode(",", $arrRightsRow[Rights::$STR_RIGHT_VIEW]),
                    implode(",", $arrRightsRow[Rights::$STR_RIGHT_EDIT]),
                    implode(",", $arrRightsRow[Rights::$STR_RIGHT_DELETE]),
                    implode(",", $arrRightsRow[Rights::$STR_RIGHT_RIGHT]),
                    implode(",", $arrRightsRow[Rights::$STR_RIGHT_RIGHT1]),
                    implode(",", $arrRightsRow[Rights::$STR_RIGHT_RIGHT2]),
                    implode(",", $arrRightsRow[Rights::$STR_RIGHT_RIGHT3]),
                    implode(",", $arrRightsRow[Rights::$STR_RIGHT_RIGHT4]),
                    implode(",", $arrRightsRow[Rights::$STR_RIGHT_RIGHT5]),
                    implode(",", $arrRightsRow[Rights::$STR_RIGHT_CHANGELOG])
                )
            );
        }

        $this->objDB->removeColumn("user", "user_active");
        $this->objDB->removeColumn("user", "user_deleted");

        // alter session last url column
        $this->objDB->changeColumn("session", "session_lasturl", "session_lasturl", DbDatatypes::STR_TYPE_TEXT);

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "5.1.1");
        return $strReturn;
    }

    private function update_511_512()
    {
        $strReturn = "Updating 5.1.1 to 5.1.2...\n";

        $strReturn .= "Checking postacomment / guestbook cross-dependencies...\n";
        if(SystemModule::getModuleByName("guestbook") !== null && SystemModule::getModuleByName("postacomment") === null) {
            $strReturn .= "ERROR\n";
            $strReturn .= "Module guestbook is replaced by module postacomment.\n";
            $strReturn .= "Please install module postacomment in order to avoid a loss of guestbook data.\n";
            $strReturn .= "Aborting update sequence.\n";
            return $strReturn;
        }

        $strReturn .= "Removing legacy samplecontent module...\n";

        $objFilesystem = new Filesystem();
        if(is_file(_realpath_."core/module_samplecontent.phar")) {
            $objFilesystem->fileDelete("/core/module_samplecontent.phar");
        }
        elseif(is_dir(_realpath_."core/module_samplecontent")) {
            $objFilesystem->folderDeleteRecursive("/core/module_samplecontent");
        }

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "5.1.2");
        return $strReturn;
    }

    private function update_512_513()
    {
        $strReturn = "Updating 5.1.2 to 5.1.3...\n";

        $strReturn .= "Registering messaging portal controller\n";
        $objModule = SystemModule::getModuleByName("messaging");
        $objModule->setStrNamePortal("MessagingPortal.php");
        $objModule->updateObjectToDb();

        $strReturn .= "Removing xml controller entries...\n";

        $this->objDB->removeColumn("system_module", "module_xmlfilenameportal");
        $this->objDB->removeColumn("system_module", "module_xmlfilenameadmin");

        Carrier::getInstance()->flushCache(Carrier::INT_CACHE_TYPE_DBSTATEMENTS | Carrier::INT_CACHE_TYPE_DBQUERIES | Carrier::INT_CACHE_TYPE_MODULES | Carrier::INT_CACHE_TYPE_DBTABLES);
        Classloader::getInstance()->flushCache();

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "5.1.3");
        return $strReturn;
    }

    private function update_513_514()
    {
        $strReturn = "Updating 5.1.3 to 5.1.4...\n";
        $strReturn .= "Updating session table\n";

        //save some user metadata, if available, for future requests
        $objUser = Session::getInstance()->getUser();
        if ($objUser !== null) {
            $strGroups = implode(",", $objUser->getArrGroupIds());
            Session::getInstance()->setSession(Session::STR_SESSION_USERID, $objUser->getSystemid());
            Session::getInstance()->setSession(Session::STR_SESSION_GROUPIDS, $strGroups);
            Session::getInstance()->setSession(Session::STR_SESSION_ISADMIN, $objUser->getIntAdmin());
        }

        //remove columns
        $this->objDB->removeColumn("session", "session_groupids");

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "5.1.4");
        return $strReturn;
    }

    private function update_514_62()
    {
        $strReturn = "Updating 5.1.4 to 6.2...\n";

        $strReturn .= "Updating user table\n";
        $this->objDB->addColumn("user_kajona", "user_specialconfig", DbDatatypes::STR_TYPE_TEXT, true);

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "6.2");
        return $strReturn;
    }

    private function update_62_621()
    {
        $strReturn = "Updating 6.2 to 6.2.1...\n";

        $strReturn .= "Adding cookie setting\n";
        $this->registerConstant("_cookies_only_https_", "false", SystemSetting::$int_TYPE_BOOL, _system_modul_id_);

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "6.2.1");
        return $strReturn;
    }


    private function update_621_622()
    {
        $strReturn = "Updating 6.2.1 to 6.2.2...\n";


        $strReturn .= "Removing system_comment column...\n";
        $this->objDB->removeColumn("system", "system_comment");

        Carrier::getInstance()->flushCache(Carrier::INT_CACHE_TYPE_DBQUERIES | Carrier::INT_CACHE_TYPE_DBSTATEMENTS);

        $strReturn .= "Registering the id generator\n";
        // install idgenerator table
        $objSchemamanager = new OrmSchemamanager();
        $objSchemamanager->createTable(IdGenerator::class);

        $strReturn .= "Altering group table...\n";
        $this->objDB->addColumn("user_group", "group_short_id", DbDatatypes::STR_TYPE_INT);

        $strReturn .= "Adding ids to each group\n";
        $strQuery = "SELECT group_id FROM "._dbprefix_."user_group WHERE group_short_id < 1 OR group_short_id IS NULL";
        foreach($this->objDB->getPArray($strQuery, array()) as $arrOneRow) {
            $strQuery = "UPDATE "._dbprefix_."user_group set group_short_id = ? WHERE group_id = ?";
            $this->objDB->_pQuery($strQuery, array(IdGenerator::generateNextId(UserGroup::INT_SHORTID_IDENTIFIER), $arrOneRow["group_id"]));
        }

        $strReturn .= $this->migrateUserData(2500);

        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "6.2.2");
        return $strReturn;
    }

    private function update_622_623()
    {
        $strReturn = "Updating 6.2.2 to 6.2.3...\n";

        $strReturn .= "Adding permisson columns to system table";
        $this->objDB->addColumn("system", "right_inherit", DbDatatypes::STR_TYPE_INT);
        $this->objDB->addColumn("system", "right_view", DbDatatypes::STR_TYPE_TEXT);
        $this->objDB->addColumn("system", "right_edit", DbDatatypes::STR_TYPE_TEXT);
        $this->objDB->addColumn("system", "right_delete", DbDatatypes::STR_TYPE_TEXT);
        $this->objDB->addColumn("system", "right_right", DbDatatypes::STR_TYPE_TEXT);
        $this->objDB->addColumn("system", "right_right1", DbDatatypes::STR_TYPE_TEXT);
        $this->objDB->addColumn("system", "right_right2", DbDatatypes::STR_TYPE_TEXT);
        $this->objDB->addColumn("system", "right_right3", DbDatatypes::STR_TYPE_TEXT);
        $this->objDB->addColumn("system", "right_right4", DbDatatypes::STR_TYPE_TEXT);
        $this->objDB->addColumn("system", "right_right5", DbDatatypes::STR_TYPE_TEXT);
        $this->objDB->addColumn("system", "right_changelog", DbDatatypes::STR_TYPE_TEXT);


        $strReturn .= "Moving data...\n";

        $strQuery = "UPDATE "._dbprefix_."system SET 
                right_inherit = r.right_inherit, 
                right_view = r.right_view, 
                right_edit = r.right_edit, 
                right_delete = r.right_delete, 
                right_right = r.right_right, 
                right_right1 = r.right_right1, 
                right_right2 = r.right_right2, 
                right_right3 = r.right_right3,
                right_right4 = r.right_right4, 
                right_right5 = r.right_right5, 
                right_changelog = r.right_changelog
                FROM (
                    SELECT right_id, right_inherit, right_view, right_edit, right_delete, right_right, right_right1, right_right2, right_right3, right_right4, right_right5, right_changelog FROM "._dbprefix_."system_right
                ) AS r WHERE system_id = r.right_id ";
        $this->objDB->_pQuery($strQuery, array());

        Carrier::getInstance()->flushCache(Carrier::INT_CACHE_TYPE_DBQUERIES | Carrier::INT_CACHE_TYPE_DBSTATEMENTS);


        $strReturn .= "Dropping old permissions table...\n";
        $this->objDB->_pQuery("DROP TABLE "._dbprefix_."system_right", array());


        $strReturn .= "Updating module-versions...\n";
        $this->updateModuleVersion($this->objMetadata->getStrTitle(), "6.2.3");
        return $strReturn;
    }

    /**
     * Helper to migrate the system-id based permission table to an int based one
     *
     * @param null|int $intPagesize
     * @param bool $bitEchodata
     * @return string
     */
    public function migrateUserData($intPagesize = null, $bitEchodata = false) {

        $strRun = "Migrating old permissions table to new table data...\n";

        $arrIdToInt = array();
        foreach ($this->objDB->getPArray("SELECT group_id, group_short_id FROM "._dbprefix_."user_group ORDER BY group_id DESC", array()) as $arrOneRow) {
            $arrIdToInt[$arrOneRow["group_id"]] = $arrOneRow["group_short_id"];
        }


        $intStart = 0;
        $intEnd = $intPagesize;

        if($intPagesize !== null) {
            $intStart = 0;
            $intEnd = $intPagesize;
            $arrResultSet = $this->objDB->getPArray("SELECT * FROM "._dbprefix_."system_right ORDER BY right_id DESC", array(), $intStart, $intEnd-1);
        } else {
            $arrResultSet = $this->objDB->getPArray("SELECT * FROM "._dbprefix_."system_right ORDER BY right_id DESC", array());
        }

        while (count($arrResultSet) > 0) {
            $strRun .= "Fetching records ".$intStart." to ".($intEnd-1).PHP_EOL;
            $arrInserts = array();

            foreach ($arrResultSet as $arrSingleRow) {
                $arrParams = array();

                foreach (["right_changelog", "right_delete", "right_edit", "right_right", "right_right1", "right_right2", "right_right3", "right_right4", "right_right5", "right_view"] as $strOneCol) {
                    $strNewString = ",";
                    foreach (explode(",", $arrSingleRow[$strOneCol]) as $strOneGroup) {
                        if (!empty($strOneGroup) && isset($arrIdToInt[$strOneGroup])) {
                            $strNewString .= $arrIdToInt[$strOneGroup].",";
                        } elseif (validateSystemid($strOneGroup)) {
                            //do nothing, seems to be an old id
                        } else {
                            //keep migrated ones
                            $strNewString .= $strOneGroup.",";
                        }
                    }
                    $arrParams[] = $strNewString;
                }

                $strQuery = "UPDATE "._dbprefix_."system_right SET right_changelog = ?,right_delete = ?,right_edit = ?,right_right = ?,right_right1 = ?,right_right2 = ?,right_right3 = ?,right_right4 = ?,right_right5 = ?,right_view =? WHERE right_id = ?";
                $arrParams[] = $arrSingleRow["right_id"];

                $this->objDB->_pQuery($strQuery, $arrParams);
            }


            $strRun .= "Converted ".count($arrResultSet)." source rows ".PHP_EOL;

            if ($bitEchodata) {
               echo $strRun;
                flush();
                ob_flush();
                $strRun = "";
            }

            if($intPagesize !== null) {
                $intStart += $intPagesize;
                $intEnd += $intPagesize;
                $arrResultSet = $this->objDB->getPArray("SELECT * FROM "._dbprefix_."system_right ORDER BY right_id DESC", [], $intStart, $intEnd - 1);
            }
            else {
                $arrResultSet = array();
            }

            $this->objDB->flushQueryCache();
        }

        return $strRun;
    }
}
