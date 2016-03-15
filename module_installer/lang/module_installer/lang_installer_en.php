<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$					    *
********************************************************************************************************/
//Edited with Kajona Language Editor GUI, see www.kajona.de and www.mulchprod.de for more information
//Kajona Language Editor Core Build 398


//editable entries
$lang["installer_config_dbdriver"]       = "Database driver";
$lang["installer_config_dbhostname"]     = "Database server";
$lang["installer_config_dbname"]         = "Database name";
$lang["installer_config_dbpassword"]     = "Database password";
$lang["installer_config_dbport"]         = "Database port";
$lang["installer_config_dbportinfo"]     = "To use a standard-port, leave empty.";
$lang["installer_config_dbprefix"]       = "Table prefix";
$lang["installer_config_dbusername"]     = "Database user";
$lang["installer_config_intro"]          = "<b>Set up database-access</b><br />";
$lang["installer_config_write"]          = "Save to config.php";
$lang["installer_dbcx_error"]            = "Connection to the database could not be established. Please verify the connection credentials.";
$lang["installer_dbdriver_na"]           = "We are sorry, but the selected database-driver is not available on the system. Please install the following PHP-extension in order to use the driver";
$lang["installer_dbdriver_oci8"]         = "Attention: The Oracle-driver is still under development.";
$lang["installer_dbdriver_sqlite3"]      = "The SQLite-driver creates a database stored at /project/dbdumps. Therefore the database name is used as the filename, all other values are not taken into account.";
$lang["installer_elements_found"]        = "<b>Installation of the page elements</b><br /><br />Select which of the found page elements you want to install:<br /><br />";
$lang["installer_finish_closer"]         = "<br />Have fun using Kajona!";
$lang["installer_finish_hints"]          = "You should set back the write permission on /project/system/config/config.php to read-only permission.<br />Additionally, you should remove the file /installer.php completely out of security reasons.<br /><br /><br />The administration in now available under:<br />&nbsp;&nbsp;&nbsp;&nbsp;<a href=\""._webpath_."/admin\">"._webpath_."/admin</a><br /><br />The portal is available under:<br />&nbsp;&nbsp;&nbsp;&nbsp;<a href=\""._webpath_."/\">"._webpath_."</a><br /><br />";
$lang["installer_finish_hints_update"]   = "<b>Attention: If you updated from a v3 system, please make sure to run the post-update scripts as soon as all modules have been upgraded.</b><br /><a href=\"_webpath_/v3_v4_postupdate.php\">Run post-update</a><br />Refer to the <a href=\"http://www.kajona.de/update_342_to_40.html\" target=\"_blank\">update instructions for 3.4.2 to 4.0</a> for more details.<br/><br />";
$lang["installer_finish_intro"]          = "<b>Installation finshed</b><br /><br />";
$lang["installer_given"]                 = "given";
$lang["installer_install"]               = "Install";
$lang["installer_installpe"]             = "Install page elements";
$lang["installer_loaded"]                = "loaded";
$lang["installer_login_email"]           = "Email";
$lang["installer_login_installed"]       = "The system is already installed and an admin-account already exists.";
$lang["installer_login_intro"]           = "<b>Set up admin-user</b><br /><br />Please provide a username and a password.<br />Those will be used later to log in to the administration.<br />Because of security reasons, usernames like \"admin\" or \"administrator\" should be avoided.<br /><br />";
$lang["installer_login_password"]        = "Password";
$lang["installer_login_save"]            = "Create account";
$lang["installer_login_username"]        = "Username";
$lang["installer_missing"]               = "missing";
$lang["installer_mode_auto"]             = "Automatic installation";
$lang["installer_mode_auto_hint"]        = "All modules available and their samplecontents are installed.";
$lang["installer_mode_manual"]           = "Manual installation";
$lang["installer_mode_manual_hint"]      = "Manual selection of the modules to install. The installation of the samplecontent may be skipped.";
$lang["installer_module_notinstalled"]   = "Module not installed";
$lang["installer_modules_found"]         = "<b>Install/update the modules</b><br /><br />Select which of the found modules you want to install:<br /><br />";
$lang["installer_modules_needed"]        = "Modules needed to install: ";
$lang["installer_next"]                  = "Next step >";
$lang["installer_nloaded"]               = "missing";
$lang["installer_phpcheck_folder"]       = "Write-permissions on ";
$lang["installer_phpcheck_intro"]        = "<b>Welcome</b><br /><br />";
$lang["installer_phpcheck_intro2"]       = "<br />The installation of the system is spilt up into serveral steps: <br />Check of permissions, DB-configuration, credentials to access the administration, module-installation, element-installation and installation of the samplecontents.<br />Dependant on the modules choosen, the number of steps can vary.<br /><br />The permissions on some files and the availability <br />of needed php-modules are being checked:<br />";
$lang["installer_phpcheck_lang"]         = "To load the installer using a different language, use one of the following links:<br /><br />";
$lang["installer_phpcheck_module"]       = "PHP-module ";
$lang["installer_prev"]                  = "< Previous step";
$lang["installer_samplecontent"]         = "<b>Installation of the samplecontent</b><br /><br />The module 'samplecontent' creates a few standard pages and navigation entries.<br />According to the modules installed, additional contents will be created.<br /><br /><br />";
$lang["installer_step_adminsettings"]    = "Admin access";
$lang["installer_step_dbsettings"]       = "Database settings";
$lang["installer_step_finish"]           = "Finalize";
$lang["installer_step_modeselect"]       = "Choose installation mode";
$lang["installer_step_modules"]          = "Modules";
$lang["installer_step_phpsettings"]      = "PHP configuration";
$lang["installer_step_samplecontent"]    = "Demo content";
$lang["installer_systemlog"]             = "System log";
$lang["installer_systemversion_needed"]  = "Minimal version required: ";
$lang["installer_update"]                = "Update to ";
$lang["installer_versioninstalled"]      = "Version installed: ";
