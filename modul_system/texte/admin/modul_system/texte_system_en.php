<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                        *
********************************************************************************************************/

// --- Module texts -------------------------------------------------------------------------------------

$text["modul_titel"]				= "System";
$text["modul_rechte"]				= "Module permissions";
$text["modul_rechte_root"]          = "Rights root-record";
$text["module_liste"]				= "Installed modules";
$text["modul_sortup"]               = "Shift up";
$text["modul_sortdown"]             = "Shift down";
$text["modul_status_disabled"]      = "Set module active (is inactive)";
$text["modul_status_enabled"]       = "Set module inactive (is active)";
$text["modul_status_system"]        = "Woops, you want to set the system-kernel inactive? To process, please execute format c: instead! ;-)";
$text["system_info"]				= "System information";
$text["system_settings"]			= "System settings";
$text["systemTasks"]			    = "System tasks";
$text["system_sessions"]            = "Sessions";
$text["systemlog"]                  = "System logfile";
$text["updatecheck"]                = "Update-Check";
$text["about"]                      = "About Kajona";

$text["permissions_default_header"] = array(0 => "View", 1 => "Edit", 2 => "Delete", 3 => "Permissions", 4 => "", 5 => "", 6 => "", 7 => "", 8 => "");
$text["permissions_root_header"]    = array(0 => "View", 1 => "Edit", 2 => "Delete", 3 => "Permissions", 4 => "Universal 1", 5 => "Universal 2", 6 => "Universal 3", 7 => "Universal 4", 8 => "Universal 5");
$text["permissions_header"]         = array(0 => "View", 1 => "Edit", 2 => "Delete", 3 => "Permissions", 4 => "Settings", 5 => "Systemtasks", 6 => "Systemlog", 7 => "Updates", 8 => "");
            							
$text["dateStyleShort"]             = "m/d/Y";            							
$text["dateStyleLong"]              = "M/d/Y H:i:s";      

$text["status_active"]              = "Change status (is active)";
$text["status_inactive"]            = "Change status (is inactive)";

$text["deleteHeader"]               = "Confirm deletion";
$text["deleteButton"]               = "Yes, delete";

$text["fehler_recht"]				= "Not enough permissions to perform this action";

$text["gd"]							= "GD-Lib";
$text["db"]							= "Database";
$text["php"]						= "PHP";
$text["server"]						= "Webserver";
$text["version"] 					= "Version";
$text["geladeneerweiterungen"]	 	= "Extensions loaded";
$text["executiontimeout"] 			= "Execution timeout";
$text["inputtimeout"] 				= "Input timeout";
$text["memorylimit"] 				= "Memory limit";
$text["errorlevel"] 				= "Error level";
$text["postmaxsize"] 				= "Post max size";
$text["uploadmaxsize"] 				= "Upload max size";
$text["uploads"] 					= "Uploads";

$text["system"] 					= "System";
$text["speicherplatz"] 				= "Disk space";
$text["diskspace_free"]             = " (free/total)";


$text["datenbankserver"] 			= "Database server";
$text["datenbankclient"] 			= "Database client";
$text["datenbankverbindung"] 		= "Database connection";
$text["anzahltabellen"] 			= "Number of tables";
$text["groessegesamt"] 				= "Size in total";
$text["groessedaten"] 				= "Size of data";
$text["datenbanktreiber"] 			= "Database driver";

$text["keinegd"]					= "GD-Lib not installed";
$text["gifread"]					= "GIF read-support";
$text["gifwrite"]					= "GIF write-support";
$text["png"]						= "PNG support";
$text["jpg"]						= "JPG support";

$text["browser"]					= "Pages browser";

$text["speichern"]                  = "Save";

$text["warnung_settings"]           = "!! ATTENTION !!<br />Using wrong values for the following settings could make the system become unusable!";
$text["settings_updated"]           = "Settings changed successfully";

$text["settings_true"]				= "Yes";
$text["settings_false"]				= "No";

$text["session_loggedin"]           = "Loggedin";
$text["session_loggedout"]          = "Guest";
$text["session_admin"]              = "Administration, module: ";
$text["session_portal"]             = "Portal, page: ";
$text["session_username"]           = "Username";
$text["session_valid"]              = "Valid until";
$text["session_status"]             = "State";
$text["session_activity"]           = "Activity";
$text["session_logout"]             = "Invalidate session";

$text["_system_portal_disable_"]            = "Deactivate portal:";
$text["_system_portal_disable_hint"]        = "Activates / deactivates the whole portal.";
$text["_system_portal_disablepage_"]        = "Temporary page:";
$text["_system_portal_disablepage_hint"]    = "This page is shown, if the portal is deactivated.";
$text["_bildergalerie_cachepfad_"]          = "Images cache path:";
$text["_bildergalerie_cachepfad_hint"]      = "Temporary created images are stored in this folder.";
$text["_system_dbdump_amount_"]             = "Number of DB-dumps:";
$text["_system_dbdump_amount_hint"]         = "Defines how many DB-dumps should be kept.";
$text["_system_mod_rewrite_hint"]           = "Activates / deactivates URL-rewriting for nice-URLs. The apache-module \"mod_rewrite\" has to be installed to use this option!";
$text["_system_mod_rewrite_"]               = "URL-rewriting:";
$text["_system_admin_email_hint"]           = "If an address is given, an email is sent to in case of critical errors.";
$text["_system_admin_email_"]               = "Admin Email:";
$text["_system_lock_maxtime_"]              = "Max locktime:";
$text["_system_lock_maxtime_hint"]          = "After the given duration in seconds, locked records will be unlocked automatically.";
$text["_system_output_gzip_"]               = "GZIP-compression of the output:";
$text["_system_output_gzip_hint"]           = "Activates GZIP-compression of outputs before sending them to the client.";
$text["_admin_nr_of_rows_"]                 = "Number of records per page:";
$text["_admin_nr_of_rows_hint"]             = "Number of records in the admin-lists, if supported by the module. Can be redefined by a module!";
$text["_admin_only_https_"]                 = "Admin only via HTTPS:";
$text["_admin_only_https_hint"]             = "Forces the use of HTTPS when loading the administration. The webserver has to support HTTPS to use this option.";
$text["_system_use_dbcache_"]               = "Database cache:";
$text["_system_use_dbcache_hint"]           = "Enables/Disables the internal database query cache.";
$text["_remoteloader_max_cachetime_"]       = "Cache time of external sources:";
$text["_remoteloader_max_cachetime_hint"]   = "Time in seconds to cache externally loaded contents (e.g. RSS-Feeds).";
$text["_system_release_time_"]              = "Duration of a session:";
$text["_system_release_time_hint"]          = "After this amount of seconds a session gets invalid.";


$text["errorintro"]                 = "Please provide all needed values!";
$text["pageview_forward"]           = "Forward";
$text["pageview_backward"]          = "Back";
$text["pageview_total"]             = "Total: ";

$text["systemtask_run"]             = "Execute";

$text["log_empty"]                  = "No entries in the logfile";

$text["update_nodom"]               = "This PHP-installation does not suppport XML-DOM. This is required for the update-check to work.";
$text["update_nourlfopen"]          = "To make this function work, the value &apos;allow_url_fopen&apos; must be set to &apos;on&apos; in the php-config file!";
$text["update_module_name"]         = "Module";
$text["update_module_localversion"] = "This installation";
$text["update_module_remoteversion"]= "Available";
$text["update_available"]           = "Please update!";
$text["update_nofilefound"]         = "The list of updates failed to load.<br />Possible reasons can be having the php-config value 'allow_url_fopen' set to 'off' or using a system without support for sockets.";
$text["update_invalidXML"]          = "The servers response was erroneous. Please try again.";

$text["about_part1"]                = "<h2>Kajona V3 - Open Source Content Management System</h2>Kajona V 3.1.1, Codename \"taskforce\"<br /><br /><a href=\"http://www.kajona.de\" target=\"_blank\">www.kajona.de</a><br /><a href=\"mailto:info@kajona.de\" target=\"_blank\">info@kajona.de</a><br /><br />For further information, support or proposals, please visit our website.<br />Additional support is given using our <a href=\"http://board.kajona.de/\" target=\"_blank\">board</a>.";

$text["about_part2"]                = "<h2>Head developers</h2><ul><li><a href=\"mailto:sidler@kajona.de\" target=\"_blank\">Stefan Idler</a> (project management, technical administration, development)</li><li><a href=\"mailto:jschroeter@kajona.de\" target=\"_blank\">Jakob Schröter</a> (frontend administration, development)</li></ul><h2>Contributors / Developers</h2><ul><li>Thomas Hertwig</li><li><a href=\"mailto:tim.kiefer@kojikui.de\" target=\"_blank\">Tim Kiefer</a></li></ul>";

$text["about_part3"]                = "<h2>Credits</h2><ul><li>Icons:<br />Everaldo Coelho (Crystal Clear, Crystal SVG), <a href=\"http://everaldo.com/\" target=\"_blank\">http://everaldo.com/</a><br />Steven Robson (Krystaline), <a href=\"http://www.kde-look.org/content/show.php?content=17509\" target=\"_blank\">http://www.kde-look.org/content/show.php?content=17509</a><br />David Patrizi, <a href=\"mailto:david@patrizi.de\">david@patrizi.de</a></li><li>browscap.ini:<br />Gary Keith, <a href=\"http://browsers.garykeith.com/downloads.asp\" target=\"_blank\">http://browsers.garykeith.com/downloads.asp</a></li><li>FCKeditor:<br />Frederico Caldeira Knabben, <a href=\"http://www.fckeditor.net/\" target=\"_blank\">http://www.fckeditor.net/</a></li><li>JpGraph:<br />Aditus, <a href=\"http://www.aditus.nu/jpgraph/\" target=\"_blank\">http://www.aditus.nu/jpgraph/</a></li><li>DejaVu Fonts:<br />DejaVu Team, <a href=\"http://dejavu.sourceforge.net\" target=\"_blank\">http://dejavu.sourceforge.net</a></li><li>Yahoo! User Interface Library:<br />Yahoo!, <a href=\"http://developer.yahoo.com/yui/\" target=\"_blank\">http://developer.yahoo.com/yui/</a></li></ul>";

$text["setAbsolutePosOk"]           = "Saving position succeeded";
$text["setStatusOk"]                = "Changing the status succeeded";
$text["setStatusError"]             = "Error changing the status";

$text["toolsetCalendarMonth"]       = "\"January\", \"February\", \"March\", \"April\", \"May\", \"June\", \"July\", \"August\", \"September\", \"Oktober\", \"November\", \"December\"";
$text["toolsetCalendarWeekday"]     = "\"Su\", \"Mu\", \"Tu\", \"We\", \"Th\", \"Fr\", \"Sa\"";

// --- Quickhelp texts ----------------------------------------------------------------------------------

$text["quickhelp_title"]            = "Quickhelp";

$text["quickhelp_list"]				= "The list of modules provides an overview of the modules currently installed.<br />Additionally, the modules versions and the installation dates are displayed.<br />You are able to modify the permissons of the module-rights-record, the base for all contents to inherit their permissions from (if activated).<br />It's also possible to reorder the modules in the module navigation by changing the position of a module in this list.";
$text["quickhelp_moduleList"]       = "The list of modules provides an overview of the modules currently installed.<br />Additionally, the modules versions and the installation dates are displayed.<br />You are able to modify the permissons of the module-rights-record, the base for all contents to inherit their permissions from (if activated).<br />It's also possible to reorder the modules in the module navigation by changing the position of a module in this list.";
$text["quickhelp_systemInfo"]		= "Kajona tries to find out a few informations about the environment in which Kajona is running.";
$text["quickhelp_systemSettings"]	= "You can define basic settings of the system. Therefore, every module is allowed to provide any number of settings. The changes made should be made with care, wrong values can make the system become unusuable.<br /><br />Note: If there are changes made to a given module, you have to save the new values for every module! Changes on other modules will be ignored! When clicking a save-button, just the corresponding values are saved.";
$text["quickhelp_systemTasks"]      = "Systemtasks are small programms handling everyday work.<br />This includes tasks to backup the database or to restore backups created before.";
$text["quickhelp_systemlog"]		= "The system-log shows the entries of the global logfile.<br />The granularity of the logging-engine could be set in the config-file (/system/config/config.php).";
$text["quickhelp_updateCheck"]		= "By using the update-check, the version of the modules installed locally and the versions of the modules available online are compared. If there's a new version available, Kajona displays a hint at the concerning module.";

//--- systemtasks ---------------------------------------------------------------------------------------

$text["systemtask_dbconsistency_name"]               = "Check database consistency";
$text["systemtask_dbconsistency_curprev_ok"]         = "All parent-child relations are correct";
$text["systemtask_dbconsistency_curprev_error"]      = "The following parent-child relations are erroneous (missing parent-link):";
$text["systemtask_dbconsistency_right_ok"]           = "All right-records have a corresponding system-record";
$text["systemtask_dbconsistency_right_error"]        = "The following right-records are erroneous (missing system-record):";
$text["systemtask_dbconsistency_date_ok"]            = "All date-records have a corresponding system-record";
$text["systemtask_dbconsistency_date_error"]         = "The following date-records are erroneous (missing system-record):";

$text["systemtask_dbexport_name"]   = "Backup database";
$text["systemtask_dbexport_success"]= "Backup created succesfully";
$text["systemtask_dbexport_error"]  = "Error dumping the database";

$text["systemtask_dbimport_name"]   = "Import database backup";
$text["systemtask_dbimport_success"]= "Backup restored successfully";
$text["systemtask_dbimport_error"]  = "Error restoring the backup";
$text["systemtask_dbimport_file"]   = "Backup:";

$text["systemtask_flushpiccache_name"]               = "Flush images cache";
$text["systemtask_flushpiccache_done"]               = "Flushing completed.";
$text["systemtask_flushpiccache_deleted"]            = "<br />Number of files deleted: ";
$text["systemtask_flushpiccache_skipped"]            = "<br />Number of files skipped: ";

$text["systemtask_flushremoteloadercache_name"]      = "Flush remoteloadercache";
$text["systemtask_flushremoteloadercache_done"]      = "Flushing completed.";

//--- MODULE RIGHTS -------------------------------------------------------------------------------------

// --- Module texts -------------------------------------------------------------------------------------
$text["moduleRightsTitle"]          = "Permissions";

$text["titel_root"]                 = "Rights root-record";
$text["titel_leer"]                 = "<em>No title defined</em>";
$text["titel_erben"]                = "Inherit rights:";

$text["fehler_setzen"]              = "Error saving permissions";
$text["setzen_erfolg"]              = "Permissions saved successfully";

$text["backlink"]                   = "Back";
$text["desc"]                       = "Edit permissions of:";
$text["submit"]                     = "Save";


// --- Quickhelp texts ----------------------------------------------------------------------------------
$text["quickhelp_change"]           = "Using this form, you are able to adjust the permissions of a record.<br />Depending on the module the record belongs to, the different types of permissions may vary.";

//--- installer -----------------------------------------------------------------------------------------

$text["installer_given"]            = "given";
$text["installer_missing"]          = "missing";
$text["installer_nloaded"]          = "missing";
$text["installer_loaded"]           = "loaded";
$text["installer_next"]             = "Next step >";
$text["installer_prev"]             = "< Previous step";
$text["installer_install"]          = "Install";
$text["installer_installpe"]        = "Install page elements";
$text["installer_update"]           = "Update to ";
$text["installer_systemlog"]        = "System log";
$text["installer_versioninstalled"] = "Version installed: ";

$text["installer_phpcheck_intro"]   = "<b>Welcome</b><br /><br />";
$text["installer_phpcheck_lang"]    = "To load the installer using a different language, use one of the following links:<br /><br />";
$text["installer_phpcheck_intro2"]  = "<br />The installation of the system is spilt up into serveral steps: <br />Check of permissions, DB-configuration, credentials to access the administration, module-installation, element-installation and installation of the samplecontents.<br /><br />Dependant on the modules choosen, the number of steps can vary.<br /><br />The permissions on some files and the availability <br />of needed php-modules are being checked:<br />";
$text["installer_phpcheck_folder"]  = "<br />Write-permissions on ";
$text["installer_phpcheck_module"]  = "<br />PHP-module ";

$text["installer_login_intro"]      = "<b>Set up admin-user</b><br /><br />Please provide a username and a password.<br />Those will be used later to log in to the administration.<br />Because of security reasons, usernames like \"admin\" or \"administrator\" should be avoided.<br /><br />";
$text["installer_login_installed"]  = "<br />The system is already installed and an admin-account already exists.<br />";
$text["installer_login_username"]   = "Username:";
$text["installer_login_password"]   = "Password:";
$text["installer_login_save"]       = "Create account";

$text["installer_config_intro"]      = "<b>Set up database-access</b><br /><br />Note: The webserver needs write-permissions on /system/config/config.php.<br />Empty values for the database server, -user, -password and -name are not allowed.<br /><br />In the case you want to use emtpy values, edit the config-file /system/config/config.php manually using a text-editor. For further informations, refer to the manual.<br /><br /><b>ATTENTION:</b> The PostgreSQL driver is still in an alpha stadium and should be used in test environments only.<br /><br />";
$text["installer_config_dbhostname"] = "Database server:";
$text["installer_config_dbusername"] = "Database user:";
$text["installer_config_dbpassword"] = "Database password:";
$text["installer_config_dbport"]     = "Database port:";
$text["installer_config_dbportinfo"] = "To use a standard-port, leave empty.";
$text["installer_config_dbdriver"]   = "Database driver:";
$text["installer_config_dbname"]     = "Database name:";
$text["installer_config_dbprefix"]   = "Table prefix:";
$text["installer_config_write"]      = "Save to config.php";

$text["installer_modules_found"]     = "<b>Install/update the modules</b><br /><br />Select which of the found modules you want to install:<br /><br />";
$text["installer_modules_needed"]    = "Modules needed to install: ";
$text["installer_module_notinstalled"] = "Module not installed";
$text["installer_systemversion_needed"] = "Minimal version of system needed: ";

$text["installer_elements_found"]    = "<b>Installation of the page elements</b><br /><br />Select which of the found page elements you want to install:<br /><br />";

$text["installer_samplecontent"]     = "<b>Installation of the samplecontent</b><br /><br />The module 'samplecontent' creates a few standard pages and navigation entries.<br />According to the modules installed, additional contents will be created.<br /><br /><br />";

$text["installer_finish_intro"]      = "<b>Installation finshed</b><br /><br />";
$text["installer_finish_hints"]      = "You should set back the write permission on /system/config/config.php to read-only permission.<br />Additionally, you should remove the folder /installer/ completely out of security reasons.<br /><br /><br />The administation in now available under:<br />&nbsp;&nbsp;&nbsp;&nbsp;<a href=\""._webpath_."/admin\">"._webpath_."/admin</a><br /><br />The portal is available under:<br />&nbsp;&nbsp;&nbsp;&nbsp;<a href=\""._webpath_."/\">"._webpath_."</a><br /><br />";
$text["installer_finish_closer"]     = "<br />Have fun using Kajona!";
?>