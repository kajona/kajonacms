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
$lang["installer_config_dbdriver"]       = "Datenbanktreiber";
$lang["installer_config_dbhostname"]     = "Datenbankserver";
$lang["installer_config_dbname"]         = "Datenbankname";
$lang["installer_config_dbpassword"]     = "Datenbankpasswort";
$lang["installer_config_dbport"]         = "Datenbankport";
$lang["installer_config_dbportinfo"]     = "Für den Standardport bitte leer lassen";
$lang["installer_config_dbprefix"]       = "Tabellenpräfix";
$lang["installer_config_dbusername"]     = "Datenbankbenutzer";
$lang["installer_config_intro"]          = "<b>Datenbankeinstellungen erfassen</b><br />";
$lang["installer_config_write"]          = "In config.php speichern";
$lang["installer_dbcx_error"]            = "Verbindung zur Datenbank konnte nicht aufgebaut werden. Bitte prüfen Sie die angegebenen Zugangsdaten.";
$lang["installer_dbdriver_na"]           = "Es tut uns leid, aber der gewählte Datenbanktreiber ist auf dem System nicht verfügbar. Bitte installieren Sie die nachstehende PHP-Erweiterung um den Treiber zu verwenden";
$lang["installer_dbdriver_oci8"]         = "Achtung: Der Oracle-Treiber befindet sich noch im Teststadium.";
$lang["installer_dbdriver_sqlite3"]      = "Der SQLite-Treiber legt die Datenbank im Verzeichnis /project/dbdumps ab. Hierbei gilt der Datenbankname als Dateiname, alle anderen Werte sind nicht weiter von Belang.";
$lang["installer_elements_found"]        = "<b>Installation der Seitenelemente</b><br /><br />Bitte wählen Sie die Seitenelemente aus, die Sie installieren möchten:<br /><br />";
$lang["installer_finish_hints"]          = "<div class='alert alert-danger'><b>Achtung!</b> Sie sollten nun die Schreibrechte auf die Datei /project/module_system/system/config/config.php auf Leserechte zurücksetzen.<br />Zusätzlich sollte aus Sicherheitsgründen die Datei /installer.php unbedingt komplett gelöscht werden.</div><br /><br />";
$lang["installer_finish_intro"]          = "<b>Installation abgeschlossen</b><br /><br /><div class='alert alert-success'><b>Herzlichen Glückwunsch!</b> Die Installation wurde erfolgreich abgeschlossen, das System steht Ihnen nun zur Verfügung!</div>Die Administrationsoberfläche erreichen Sie nun unter:<br />&nbsp;&nbsp;&nbsp;&nbsp;<a href=\""._webpath_."/admin\">"._webpath_."/admin</a><br /><br />Das Portal erreichen Sie unter:<br />&nbsp;&nbsp;&nbsp;&nbsp;<a href=\""._webpath_."/\">"._webpath_."</a><br /><br />";
$lang["installer_given"]                 = "vorhanden";
$lang["installer_install"]               = "Installieren";
$lang["installer_installpe"]             = "Seitenelemente installieren";
$lang["installer_loaded"]                = "geladen";
$lang["installer_login_email"]           = "E-Mail";
$lang["installer_login_installed"]       = "Das System wurde bereits mit einem Admin-Benutzer installiert.";
$lang["installer_login_intro"]           = "<b>Admin-Benutzer einrichten</b><br /><br />Bitte geben Sie hier einen Benutzernamen und ein Passwort an.<br />Diese Daten werden später als Zugang zur Administration verwendet.<br />Aus Sicherheitsgründen sollten Sie Benutzernamen wie \"admin\" oder \"administrator\" vermeiden.<br /><br />";
$lang["installer_login_password"]        = "Passwort";
$lang["installer_login_save"]            = "Benutzer anlegen";
$lang["installer_login_username"]        = "Benutzername";
$lang["installer_missing"]               = "fehlen";
$lang["installer_mode_auto"]             = "Automatische Installation";
$lang["installer_mode_auto_hint"]        = "Alle verfügbaren Module und Beispielinhalte werden installiert.";
$lang["installer_mode_manual"]           = "Manuelle Installation";
$lang["installer_mode_manual_hint"]      = "Die zu installierende Module können manuell ausgewählt werden, die Installation der Beispielinhalte kann übersprungen werden.";
$lang["installer_module_notinstalled"]   = "Modul ist nicht installiert";
$lang["installer_modules_found"]         = "<b>Installation/Update der Module</b><br /><br />Bitte wählen Sie die Module aus, die Sie installieren möchten:<br /><br />";
$lang["installer_modules_needed"]        = "Zur Installation benötigte Module: ";
$lang["installer_next"]                  = "Nächster Schritt >";
$lang["installer_nloaded"]               = "fehlt";
$lang["installer_phpcheck_folder"]       = "Schreibrechte auf ";
$lang["installer_phpcheck_intro"]        = "<b>Herzlich Willkommen</b><br /><br />";
$lang["installer_phpcheck_intro2"]       = "<br />Die Installation des Systems erfolgt in mehreren Schritten: <br />Rechtepüfung, DB-Konfiguration, Zugangsdaten zur Administration sowie Modulinstallation.<br />Je nach Modulauswahl kann die Anzahl dieser Schritte abweichen.<br /><br />Es werden die Schreibrechte einzelner Dateien und Verzeichnisse sowie<br />die Verfügbarkeit benötigter PHP-Module überprüft:<br />";
$lang["installer_phpcheck_lang"]         = "Um den Installer in einer anderen Sprache zu laden, bitte einen der folgenden Links verwenden:<br /><br />";
$lang["installer_phpcheck_module"]       = "PHP-Modul ";
$lang["installer_phpcheck_version"]       = "PHP-Version ";
$lang["installer_prev"]                  = "< Vorheriger Schritt";
$lang["installer_samplecontent"]         = "<b>Installation der Beispielinhalte</b><br /><br />Das Modul Samplecontent erstellt einige Standard-Seiten und Navigationen.<br />Je nach installierten Modulen werden verschiedene Beispielinhalte installiert.<br /><br /><br />";
$lang["installer_step_adminsettings"]    = "Administrationszugang";
$lang["installer_step_dbsettings"]       = "Datenbankeinstellungen";
$lang["installer_step_finish"]           = "Abschluss";
$lang["installer_step_autoinstall"]       = "Installation";
$lang["installer_step_modules"]          = "Module";
$lang["installer_step_phpsettings"]      = "PHP-Konfiguration";
$lang["installer_step_samplecontent"]    = "Beispielinhalte";
$lang["installer_systemlog"]             = "System Log";
$lang["installer_systemversion_needed"]  = "Minimal benötigte Version: ";
$lang["installer_update"]                = "Update auf ";
$lang["installer_versioninstalled"]      = "Installierte Version: ";

$lang["installer_package_title"] = "Paket";
$lang["installer_package_version"] = "Version";
$lang["installer_package_installation"] = "Installation";
$lang["installer_package_samplecontent"] = "Beispielinhalte";
$lang["installer_package_hint"] = "Hinweise";
$lang["installer_package_hint_noinstaller"] = "Das Paket benötigt keine zusätzliche Installation";
$lang["installer_start_installation"] = "Installation starten";
$lang["installer_start_installation_hint"] = "Alle Daten sind vorhanden, die Installation kann beginnen";
$lang["installer_start_statusinfo_intro"] = "Aktuelle Installation: ";