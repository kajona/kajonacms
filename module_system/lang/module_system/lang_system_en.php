<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$					    *
********************************************************************************************************/
//Edited with Kajona Language Editor GUI, see www.kajona.de and www.mulchprod.de for more information
//Kajona Language Editor Core Build 385

//editable entries
$lang["_admin_nr_of_rows_"]              = "Number of records per page:";
$lang["_admin_nr_of_rows_hint"]          = "Number of records in the admin-lists, if supported by the module. Can be redefined by a module!";
$lang["_admin_only_https_"]              = "Admin only via HTTPS:";
$lang["_admin_only_https_hint"]          = "Forces the use of HTTPS when loading the administration. The webserver has to support HTTPS to use this option.";
$lang["_remoteloader_max_cachetime_"]    = "Cache time of external sources:";
$lang["_remoteloader_max_cachetime_hint"] = "Time in seconds to cache externally loaded contents (e.g. RSS-Feeds).";
$lang["_system_admin_email_"]            = "Admin Email:";
$lang["_system_admin_email_hint"]        = "If an address is given, an email is sent to in case of critical errors.";
$lang["_system_browser_cachebuster_"]    = "Browser-Cachebuster";
$lang["_system_browser_cachebuster_hint"] = "This value is appended as GET parameter to all references to JS/CSS files. By incrementing this value the browser will be forced to reload the files from the server, regardless of the browsers caching settings and the sent HTTP headers. The value will be incremented automatically by the system task 'Flush cache'.";
$lang["_system_changehistory_enabled_"]  = "Änderungshistory enabled:";
$lang["_system_dbdump_amount_"]          = "Number of DB-dumps:";
$lang["_system_dbdump_amount_hint"]      = "Defines how many DB-dumps should be kept.";
$lang["_system_graph_type_"]             = "Chart-library used:";
$lang["_system_graph_type_hint"]         = "Valid values: pchart, ezc, flot. pChat has to be downloaded manually, for optimal images ezc makes use of the php-module 'cairo'.<br />See also <a href=\"http://www.kajona.de/nicecharts.html\" taget=\"_blank\">http://www.kajona.de/nicecharts.html</a>";
$lang["_system_lock_maxtime_"]           = "Maximum locktime:";
$lang["_system_lock_maxtime_hint"]       = "After the given duration in seconds, locked records will be unlocked automatically.";
$lang["_system_mod_rewrite_"]            = "URL-rewriting:";
$lang["_system_mod_rewrite_hint"]        = "Activates/deactivates URL-rewriting for nice-URLs. The apache-module \"mod_rewrite\" has to be installed and activated in the .htaccess file to use this option!";
$lang["_system_portal_disable_"]         = "Deactivate portal:";
$lang["_system_portal_disable_hint"]     = "Activates/deactivates the whole portal.";
$lang["_system_portal_disablepage_"]     = "Temporary page:";
$lang["_system_portal_disablepage_hint"] = "This page is shown, if the portal is deactivated.";
$lang["_system_release_time_"]           = "Duration of a session:";
$lang["_system_release_time_hint"]       = "After this amount of seconds a session gets invalid.";
$lang["_system_use_dbcache_"]            = "Database cache:";
$lang["_system_use_dbcache_hint"]        = "Enables/disables the internal database query cache.";
$lang["about_part1"]                     = "<h2>Kajona V3 - Open Source Content Management System</h2>Kajona V 3.4.1, Codename \"connectivity\"<br /><br /><a href=\"http://www.kajona.de\" target=\"_blank\">www.kajona.de</a><br /><a href=\"mailto:info@kajona.de\" target=\"_blank\">info@kajona.de</a><br /><br />For further information, support or proposals, please visit our website.<br />Additional support is given using our <a href=\"http://board.kajona.de/\" target=\"_blank\">board</a>.";
$lang["about_part2"]                     = "<h2>Head developers</h2><ul><li><a href=\"https://www.xing.com/profile/Stefan_Idler\" target=\"_blank\">Stefan Idler</a>, <a href=\"mailto:sidler@kajona.de\">sidler@kajona.de</a> (project management, technical administration, development)</li></ul><h2>Contributors / Developers</h2><ul><li>Stefan Bongartz</li><li><a href=\"https://www.xing.com/profile/Florian_Feigenbutz\" target=\"_blank\">Florian Feigenbutz</a></li><li>Thomas Hertwig</li><li><a href=\"mailto:tim.kiefer@kojikui.de\" target=\"_blank\">Tim Kiefer</a></li><li>Mario Lange</li><li>Stefan Meyer</li><li><a href=\"https://www.xing.com/profile/Jakob_Schroeter\" target=\"_blank\">Jakob Schröter</a>, <a href=\"mailto:jschroeter@kajona.de\">jschroeter@kajona.de</a></li><li><a href=\"mailto:ph.wolfer@googlemail.com\" target=\"_blank\">Philipp Wolfer</a></li></ul><h2>Translations</h2><ul><li>Bulgarian: <a href=\"mailto:contact@rudee.info\">Rumen Emilov</a></li><li>Portuguese: <a href=\"http://www.nunocruz.com\" target=\"_blank\">Nuno Cruz</a></li><li>Russian: <a href=\"https://www.xing.com/profile/Ksenia_KramVinogradova\" target=\"_blank\">Ksenia Kram</a>, <a href=\"https://www.xing.com/profile/Michael_Kram\" target=\"_blank\">Michael Kram</a></li><li>Swedish: <a href=\"mailto:villa.carlberg@telia.com\">Per Gunnarsson</a></li></ul>";
$lang["about_part3"]                     = "<h2>Credits</h2><ul><li>Icons:<br />Everaldo Coelho (Crystal Clear, Crystal SVG), <a href=\"http://everaldo.com/\" target=\"_blank\">http://everaldo.com/</a><br />Oxygen Icons, <a href=\"http://www.oxygen-icons.org/\" target=\"blank\">http://www.oxygen-icons.org/</a></li><li>browscap.ini: Gary Keith, <a href=\"http://browsers.garykeith.com/downloads.asp\" target=\"_blank\">http://browsers.garykeith.com/downloads.asp</a></li><li>CKEditor: Frederico Caldeira Knabben, <a href=\"http://www.ckeditor.com/\" target=\"_blank\">http://www.ckeditor.com/</a></li><li>ez components (charts): <a href=\"http://ezcomponents.org\" target=\"_blank\">http://ezcomponents.org</a></li><li>DejaVu Fonts:<br />DejaVu Team, <a href=\"http://dejavu.sourceforge.net\" target=\"_blank\">http://dejavu.sourceforge.net</a></li><li>Bootstrap, <a href=\"http://twitter.github.com/bootstrap/\" target=\"_blank\">http://twitter.github.com/bootstrap/</a></li><li>Hallo Editor, <a href=\"http://hallojs.org/\" target=\"_blank\">http://hallojs.org/</a></li><li>Fine Uploader, <a href=\"http://fineuploader.com/\" target=\"_blank\">http://fineuploader.com/</a></li></ul>";
$lang["about_part4"]                     = "<h2>Donate</h2><p>If you like to work with Kajona and want to support the project, feel free to donate: </p> <form method=\"post\" action=\"https://www.paypal.com/cgi-bin/webscr\" target=\"_blank\"><input type=\"hidden\" value=\"_donations\" name=\"cmd\" /> <input type=\"hidden\" value=\"donate@kajona.de\" name=\"business\" /> <input type=\"hidden\" value=\"Kajona Development\" name=\"item_name\" /> <input type=\"hidden\" value=\"0\" name=\"no_shipping\" /> <input type=\"hidden\" value=\"1\" name=\"no_note\" /> <input type=\"hidden\" value=\"EUR\" name=\"currency_code\" /> <input type=\"hidden\" value=\"0\" name=\"tax\" /> <input type=\"hidden\" value=\"PP-DonationsBF\" name=\"bn\" /> <input type=\"submit\" name=\"submit\" value=\"Donate via PayPal\" class=\"inputSubmit\" /></form>";
$lang["actionAbout"]                     = "About Kajona";
$lang["actionAspects"]                   = "Aspects";
$lang["actionChangelog"]                 = "Change history";
$lang["actionList"]                      = "Installed modules";
$lang["actionSystemInfo"]                = "System information";
$lang["actionSystemSessions"]            = "Sessions";
$lang["actionSystemSettings"]            = "System settings";
$lang["actionSystemTasks"]               = "System tasks";
$lang["actionSystemlog"]                 = "System logfile";
$lang["anzahltabellen"]                  = "Number of tables";
$lang["aspect_create"]                   = "New aspect";
$lang["aspect_delete_question"]          = "Do you really want to delete the aspect &quot;<b>%%element_name%%</b>&quot;?";
$lang["aspect_edit"]                     = "Edit aspect";
$lang["aspect_isDefault"]                = "default aspect";
$lang["aspect_list_empty"]               = "No aspects created";
$lang["cache_entry_size"]                = "Size";
$lang["cache_hash1"]                     = "Hash 1";
$lang["cache_hash2"]                     = "Hash 2";
$lang["cache_leasetime"]                 = "Valid until";
$lang["cache_source"]                    = "Source";
$lang["change_action"]                   = "Action";
$lang["change_module"]                   = "Module";
$lang["change_newvalue"]                 = "New value";
$lang["change_oldvalue"]                 = "Old value";
$lang["change_property"]                 = "Property";
$lang["change_record"]                   = "Object";
$lang["change_type_setting"]             = "Setting";
$lang["change_user"]                     = "User";
$lang["dateStyleLong"]                   = "m/d/Y H:i:s";
$lang["dateStyleShort"]                  = "m/d/Y";
$lang["datenbankclient"]                 = "Database client";
$lang["datenbankserver"]                 = "Database server";
$lang["datenbanktreiber"]                = "Database driver";
$lang["datenbankverbindung"]             = "Database connection";
$lang["db"]                              = "Database";
$lang["desc"]                            = "Edit permissions of:";
$lang["dialog_cancelButton"]             = "cancel";
$lang["dialog_deleteButton"]             = "Yes, delete";
$lang["dialog_deleteHeader"]             = "Confirm deletion";
$lang["dialog_loadingHeader"]            = "Please wait";
$lang["diskspace_free"]                  = " (free/total)";
$lang["errorintro"]                      = "Please provide all needed values!";
$lang["errorlevel"]                      = "Error level";
$lang["executiontimeout"]                = "Execution timeout";
$lang["fehler_setzen"]                   = "Error saving permissions";
$lang["filebrowser"]                     = "Select a file";
$lang["form_aspect_default"]             = "Default aspect:";
$lang["form_aspect_name"]                = "Name:";
$lang["form_aspect_name_hint"]           = "The name is used as an internal identifier. To localize an aspects' title, create a lang-entry named lang_NAME.";
$lang["gd"]                              = "GD-Lib";
$lang["geladeneerweiterungen"]           = "Extensions loaded";
$lang["gifread"]                         = "GIF read-support";
$lang["gifwrite"]                        = "GIF write-support";
$lang["groessedaten"]                    = "Size of data";
$lang["groessegesamt"]                   = "Size in total";
$lang["inputtimeout"]                    = "Input timeout";
$lang["installer_config_dbdriver"]       = "Database driver:";
$lang["installer_config_dbhostname"]     = "Database server:";
$lang["installer_config_dbname"]         = "Database name:";
$lang["installer_config_dbpassword"]     = "Database password:";
$lang["installer_config_dbport"]         = "Database port:";
$lang["installer_config_dbportinfo"]     = "To use a standard-port, leave empty.";
$lang["installer_config_dbprefix"]       = "Table prefix:";
$lang["installer_config_dbusername"]     = "Database user:";
$lang["installer_config_intro"]          = "<b>Set up database-access</b><br /><br />Note: The webserver needs write-permissions on /system/config/config.php.<br />In the case you want to use emtpy values, edit the config-file /system/config/config.php manually using a text-editor. For further informations, refer to the manual.<br />";
$lang["installer_config_write"]          = "Save to config.php";
$lang["installer_dbdriver_na"]           = "We are sorry, but the selected database-driver is not available on the system. Please install the following PHP-extension in order to use the driver:";
$lang["installer_dbdriver_oci8"]         = "Attention: The Oracle-driver is still under development.";
$lang["installer_dbdriver_sqlite3"]      = "The SQLite-driver creates a database stored at /system/db. Therefore the database name is used as the filename, all other values are not taken into account.";
$lang["installer_elements_found"]        = "<b>Installation of the page elements</b><br /><br />Select which of the found page elements you want to install:<br /><br />";
$lang["installer_finish_closer"]         = "<br />Have fun using Kajona!";
$lang["installer_finish_hints"]          = "You should set back the write permission on /project/system/config/config.php to read-only permission.<br />Additionally, you should remove the file /installer.php completely out of security reasons.<br /><br /><br />The administation in now available under:<br />&nbsp;&nbsp;&nbsp;&nbsp;<a href=\""._webpath_."/admin\">"._webpath_."/admin</a><br /><br />The portal is available under:<br />&nbsp;&nbsp;&nbsp;&nbsp;<a href=\""._webpath_."/\">"._webpath_."</a><br /><br />";
$lang["installer_finish_hints_update"]   = "<b>Attention: If you updated from a v3 system, please make sure to run the post-update scripts as soon as all modules have been upgraded.</b><br /><a href=\"_webpath_/v3_v4_postupdate.php\">Run post-update</a><<br /><br />";
$lang["installer_finish_intro"]          = "<b>Installation finshed</b><br /><br />";
$lang["installer_given"]                 = "given";
$lang["installer_install"]               = "Install";
$lang["installer_installpe"]             = "Install page elements";
$lang["installer_loaded"]                = "loaded";
$lang["installer_login_email"]           = "Email:";
$lang["installer_login_installed"]       = "<br />The system is already installed and an admin-account already exists.<br />";
$lang["installer_login_intro"]           = "<b>Set up admin-user</b><br /><br />Please provide a username and a password.<br />Those will be used later to log in to the administration.<br />Because of security reasons, usernames like \"admin\" or \"administrator\" should be avoided.<br /><br />";
$lang["installer_login_password"]        = "Password:";
$lang["installer_login_save"]            = "Create account";
$lang["installer_login_username"]        = "Username:";
$lang["installer_missing"]               = "missing";
$lang["installer_mode_auto"]             = "Automatic installtion";
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
$lang["installer_phpcheck_intro2"]       = "<br />The installation of the system is spilt up into serveral steps: <br />Check of permissions, DB-configuration, credentials to access the administration, module-installation, element-installation and installation of the samplecontents.<br /><br />Dependant on the modules choosen, the number of steps can vary.<br /><br /> <b>Before running a system-update please read the <br /><a href=\"http://www.kajona.de/update_311_to_320.html\" target=\"_blank\">update instructions from 3.1.x to 3.2.0</a><br /><a href=\"http://www.kajona.de/update_32x_to_330.html\" target=\"_blank\">update instructions from 3.2.x to 3.3.0</a><br /><a href=\"http://www.kajona.de/update_33x_to_340.html\" target=\"_blank\">update instructions from 3.3.x to 3.4.0</a>.</b><br /><br /><br />The permissions on some files and the availability <br />of needed php-modules are being checked:<br />";
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
$lang["installer_systemversion_needed"]  = "Minimal version of system needed: ";
$lang["installer_update"]                = "Update to ";
$lang["installer_versioninstalled"]      = "Version installed: ";
$lang["jpg"]                             = "JPG support";
$lang["keinegd"]                         = "GD-Lib not installed";
$lang["log_empty"]                       = "No entries in the system-logfile";
$lang["login_xml_error"]                 = "Login failed";
$lang["login_xml_succeess"]              = "Login succeeded";
$lang["logout_xml"]                      = "Logout succeeded";
$lang["mail_body"]                       = "Content:";
$lang["mail_cc"]                         = "Recipient in CC:";
$lang["mail_recipient"]                  = "Recipient:";
$lang["mail_send_error"]                 = "Error sending the email. Please retry the last action.";
$lang["mail_send_success"]               = "Email sent successfully.";
$lang["mail_subject"]                    = "Subject:";
$lang["memorylimit"]                     = "Memory limit";
$lang["messageprovider_exceptions_name"] = "System-Exceptions";
$lang["modul_aspectedit"]                = "Edit aspects";
$lang["modul_rechte_root"]               = "Rights root-record";
$lang["modul_sortdown"]                  = "Shift down";
$lang["modul_sortup"]                    = "Shift up";
$lang["modul_status_disabled"]           = "Set module active (is inactive)";
$lang["modul_status_enabled"]            = "Set module inactive (is active)";
$lang["modul_status_system"]             = "Woops, you want to set the system-kernel inactive? To process, please execute format c: instead! ;-)";
$lang["modul_titel"]                     = "System";
$lang["moduleRightsTitle"]               = "Permissions";
$lang["numberStyleDecimal"]              = ".";
$lang["numberStyleThousands"]            = ",";
$lang["operatingsystem"]                 = "Operating system";
$lang["pageview_forward"]                = "Forward";
$lang["pageview_total"]                  = "Total: ";
$lang["php"]                             = "PHP";
$lang["png"]                             = "PNG support";
$lang["postmaxsize"]                     = "Post max size";
$lang["quickhelp_change"]                = "Using this form, you are able to adjust the permissions of a record.<br />Depending on the module the record belongs to, the different types of permissions may vary.";
$lang["quickhelp_list"]                  = "The list of modules provides an overview of the modules currently installed.<br />Additionally, the modules versions and the installation dates are displayed.<br />You are able to modify the permissons of the module-rights-record, the base for all contents to inherit their permissions from (if activated).<br />It's also possible to reorder the modules in the module navigation by changing the position of a module in this list.";
$lang["quickhelp_moduleList"]            = "The list of modules provides an overview of the modules currently installed.<br />Additionally, the modules versions and the installation dates are displayed.<br />You are able to modify the permissons of the module-rights-record, the base for all contents to inherit their permissions from (if activated).<br />It's also possible to reorder the modules in the module navigation by changing the position of a module in this list.";
$lang["quickhelp_systemInfo"]            = "Kajona tries to find out a few informations about the environment in which Kajona is running.";
$lang["quickhelp_systemSettings"]        = "You can define basic settings of the system. Therefore, every module is allowed to provide any number of settings. The changes made should be made with care, wrong values can make the system become unusuable.<br /><br />Note: If there are changes made to a given module, you have to save the new values for every module! Changes on other modules will be ignored! When clicking a save-button, just the corresponding values are saved.";
$lang["quickhelp_systemTasks"]           = "Systemtasks are small programms handling everyday work.<br />This includes tasks to backup the database or to restore backups created before.";
$lang["quickhelp_systemlog"]             = "The system-log shows the entries of the global logfile.<br />The granularity of the logging-engine could be set in the config-file (/system/config/config.php).";
$lang["quickhelp_title"]                 = "Quickhelp";
$lang["quickhelp_updateCheck"]           = "By using the update-check, the version of the modules installed locally and the versions of the modules available online are compared. If there's a new version available, Kajona displays a hint at the concerning module.";
$lang["send"]                            = "Send";
$lang["server"]                          = "Webserver";
$lang["session_activity"]                = "Activity";
$lang["session_admin"]                   = "Administration, module: ";
$lang["session_loggedin"]                = "logged in";
$lang["session_loggedout"]               = "Guest";
$lang["session_logout"]                  = "Invalidate session";
$lang["session_portal"]                  = "Portal, page: ";
$lang["session_portal_imagegeneration"]  = "Image generation";
$lang["session_status"]                  = "State";
$lang["session_username"]                = "Username";
$lang["session_valid"]                   = "Valid until";
$lang["setAbsolutePosOk"]                = "Saving position succeeded";
$lang["setPrevIdOk"]                     = "Saving new parent succeeded";
$lang["setStatusError"]                  = "Error changing the status";
$lang["setStatusOk"]                     = "Changing the status succeeded";
$lang["settings_updated"]                = "Settings changed successfully";
$lang["setzen_erfolg"]                   = "Permissions saved successfully";
$lang["speicherplatz"]                   = "Disk space";
$lang["status_active"]                   = "Change status (is active)";
$lang["status_inactive"]                 = "Change status (is inactive)";
$lang["system_cache"]                    = "Cache";
$lang["systeminfo_php_regglobal"]        = "Register globals";
$lang["systeminfo_php_safemode"]         = "Safe mode";
$lang["systeminfo_php_urlfopen"]         = "Allow url fopen";
$lang["systeminfo_webserver_modules"]    = "Modules loaded";
$lang["systeminfo_webserver_version"]    = "Webserver";
$lang["systemtask_cacheSource_source"]   = "Cache-Types:";
$lang["systemtask_cancel_execution"]     = "Cancel execution";
$lang["systemtask_close_dialog"]         = "OK";
$lang["systemtask_compresspicuploads_done"] = "The resizing and compressing is done.";
$lang["systemtask_compresspicuploads_found"] = "Found images";
$lang["systemtask_compresspicuploads_height"] = "Max. height (pixel)";
$lang["systemtask_compresspicuploads_hint"] = "To save disk space, you can resize and recompress all uploaded pictures in the folder \"/portal/pics/upload\" to the given maximal dimensions.<br />Be aware, that this action can't be reverted and that it may causes loss of picture quality.<br />The process may take a while.";
$lang["systemtask_compresspicuploads_name"] = "Compress uploaded pictures";
$lang["systemtask_compresspicuploads_processed"] = "Processed images";
$lang["systemtask_compresspicuploads_width"] = "Max. width (pixel)";
$lang["systemtask_dbconsistency_curprev_error"] = "The following parent-child relations are erroneous (missing parent-link):";
$lang["systemtask_dbconsistency_curprev_ok"] = "All parent-child relations are correct";
$lang["systemtask_dbconsistency_date_error"] = "The following date-records are erroneous (missing system-record):";
$lang["systemtask_dbconsistency_date_ok"] = "All date-records have a corresponding system-record";
$lang["systemtask_dbconsistency_firstlevel_error"] = "Not all first-level-nodes belong to a module";
$lang["systemtask_dbconsistency_firstlevel_ok"] = "All first-level-nodes belong to a module";
$lang["systemtask_dbconsistency_name"]   = "Check database consistency";
$lang["systemtask_dbconsistency_right_error"] = "The following right-records are erroneous (missing system-record):";
$lang["systemtask_dbconsistency_right_ok"] = "All right-records have a corresponding system-record";
$lang["systemtask_dbexport_error"]       = "Error dumping the database";
$lang["systemtask_dbexport_exclude_intro"] = "If activated both tables, the stats and the cache will be excluded from the dump.";
$lang["systemtask_dbexport_excludetitle"] = "Exclude tables:";
$lang["systemtask_dbexport_name"]        = "Backup database";
$lang["systemtask_dbexport_success"]     = "Backup created succesfully";
$lang["systemtask_dbimport_error"]       = "Error restoring the backup";
$lang["systemtask_dbimport_file"]        = "Backup:";
$lang["systemtask_dbimport_name"]        = "Import database backup";
$lang["systemtask_dbimport_success"]     = "Backup restored successfully";
$lang["systemtask_dialog_title"]         = "Systemtask running";
$lang["systemtask_dialog_title_done"]    = "Systemtask completed";
$lang["systemtask_filedump_error"]       = "An error occured during the backup process.";
$lang["systemtask_filedump_name"]        = "Create backup of filesystem";
$lang["systemtask_filedump_success"]     = "The backup was created successfully. <br/>Out of security reasons, the backup should be removed from the server as soon as possible.<br />Name of the backup-file:&nbsp;";
$lang["systemtask_flushcache_all"]       = "All entries";
$lang["systemtask_flushcache_error"]     = "An error occured.";
$lang["systemtask_flushcache_name"]      = "Flush global cache";
$lang["systemtask_flushcache_success"]   = "The cache was flushed.";
$lang["systemtask_flushpiccache_deleted"] = "<br />Number of files deleted: ";
$lang["systemtask_flushpiccache_done"]   = "Flushing completed.";
$lang["systemtask_flushpiccache_name"]   = "Flush images cache";
$lang["systemtask_flushpiccache_skipped"] = "<br />Number of files skipped: ";
$lang["systemtask_group_cache"]          = "Cache";
$lang["systemtask_group_database"]       = "Database";
$lang["systemtask_group_default"]        = "Miscellaneous";
$lang["systemtask_group_ldap"]           = "Ldap";
$lang["systemtask_group_pages"]          = "Pages";
$lang["systemtask_group_stats"]          = "Stats";
$lang["systemtask_progress"]             = "Progress:";
$lang["systemtask_run"]                  = "Execute";
$lang["systemtask_runningtask"]          = "Task:";
$lang["systemtask_status_error"]         = "Error while setting the status.";
$lang["systemtask_status_success"]       = "The status was updated successfully.";
$lang["systemtask_systemstatus_active"]  = "active";
$lang["systemtask_systemstatus_inactive"] = "inactive";
$lang["systemtask_systemstatus_name"]    = "Update the state of a system-record";
$lang["systemtask_systemstatus_status"]  = "Status:";
$lang["systemtask_systemstatus_systemid"] = "Systemid:";
$lang["titel_erben"]                     = "Inherit rights:";
$lang["titel_leer"]                      = "<em>No title defined</em>";
$lang["titel_root"]                      = "Rights root-record";
$lang["titleTime"]                       = "Time of day";
$lang["toolsetCalendarMonth"]            = "\"January\", \"February\", \"March\", \"April\", \"May\", \"June\", \"July\", \"August\", \"September\", \"October\", \"November\", \"December\"";
$lang["toolsetCalendarWeekday"]          = "\"Su\", \"Mu\", \"Tu\", \"We\", \"Th\", \"Fr\", \"Sa\"";
$lang["update_available"]                = "Please update!";
$lang["update_invalidXML"]               = "The servers response was erroneous. Please try again.";
$lang["update_module_localversion"]      = "This installation";
$lang["update_module_name"]              = "Module";
$lang["update_module_remoteversion"]     = "Available";
$lang["update_nodom"]                    = "This PHP-installation does not suppport XML-DOM. This is required for the update-check to work.";
$lang["update_nofilefound"]              = "The list of updates failed to load.<br />Possible reasons can be having the php-config value 'allow_url_fopen' set to 'off' or using a system without support for sockets.";
$lang["update_nourlfopen"]               = "To make this function work, the value &apos;allow_url_fopen&apos; must be set to &apos;on&apos; in the php-config file!";
$lang["uploadmaxsize"]                   = "Upload max size";
$lang["uploads"]                         = "Uploads";
$lang["version"]                         = "Version";
$lang["warnung_settings"]                = "!! ATTENTION !!<br />Using wrong values for the following settings could make the system become unusable!";

//non-editable entries
$lang["permissions_default_header"]      = array(0 => "View", 1 => "Edit", 2 => "Delete", 3 => "Permissions", 4 => "", 5 => "", 6 => "", 7 => "", 8 => "");
$lang["permissions_header"]              = array(0 => "View", 1 => "Edit", 2 => "Delete", 3 => "Permissions", 4 => "Settings", 5 => "Systemtasks", 6 => "Systemlog", 7 => "", 8 => "Aspects");
$lang["permissions_root_header"]         = array(0 => "View", 1 => "Edit", 2 => "Delete", 3 => "Permissions", 4 => "Universal 1", 5 => "Universal 2", 6 => "Universal 3", 7 => "Universal 4", 8 => "Universal 5");
