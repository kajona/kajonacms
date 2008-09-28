<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	texte_system_de.php																					*
* 	Admin language file for module_system    															*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                        *
********************************************************************************************************/

// --- Module texts -------------------------------------------------------------------------------------

$text["modul_titel"]				= "System";
$text["modul_rechte"]				= "Modul-Rechte";
$text["modul_rechte_root"]          = "Root-Rechte";
$text["module_liste"]				= "Installierte Module";
$text["modul_sortup"]               = "Nach oben verschieben";
$text["modul_sortdown"]             = "Nach unten verschieben";
$text["modul_status_disabled"]      = "Modul aktiv schalten (ist inaktiv)";
$text["modul_status_enabled"]       = "Modul inaktiv schalten (ist aktiv)";
$text["modul_status_system"]        = "Hmmm. Den System-Kernel deaktivieren? Zuvor bitte format c: ausführen!";
$text["system_info"]				= "Systeminformationen";
$text["system_settings"]			= "Systemeinstellungen";
$text["systemTasks"]			    = "System-Tasks";
$text["system_sessions"]            = "Sessions";
$text["systemlog"]                  = "System-Log";
$text["updatecheck"]                = "Update-Check";
$text["about"]                      = "Über Kajona";

$text["permissions_default_header"] = array(
            							0 => "Anzeigen",
            							1 => "Bearbeiten",
            							2 => "Löschen",
            							3 => "Rechte",
            							4 => "",
            							5 => "",
            							6 => "",
            							7 => "",
            							8 => ""
            							);

$text["permissions_root_header"]    = array(
                                    	0 => "Anzeigen",
            							1 => "Bearbeiten",
            							2 => "Löschen",
            							3 => "Rechte",
            							4 => "Universal 1",      //Recht1
            							5 => "Universal 2",
            							6 => "Universal 3",
            							7 => "Universal 4",
            							8 => "Universal 5"
            							);

$text["permissions_header"]         = array(
            							0 => "Anzeigen",
            							1 => "Bearbeiten",
            							2 => "Löschen",
            							3 => "Rechte",
            							4 => "Einstellungen",       //recht1
            							5 => "Systemtasks    ",           //recht2
            							6 => "Systemlog",           //recht3
            							7 => "Updates",
            							8 => ""
            							);
            							
$text["dateStyleShort"]             = "d.m.Y";            							
$text["dateStyleLong"]              = "d M Y H:i:s";            							


$text["fehler_recht"]				= "Keine ausreichenden Rechte um diese Aktion durchzuführen";

$text["gd"]							= "GD-Lib";
$text["db"]							= "Datenbank";
$text["php"]						= "PHP";
$text["server"]						= "Webserver";
$text["version"] 					= "Version";
$text["geladeneerweiterungen"]	 	= "Geladene Erweiterungen";
$text["executiontimeout"] 			= "Execution Timeout";
$text["inputtimeout"] 				= "Input Timeout";
$text["memorylimit"] 				= "Memory Limit";
$text["errorlevel"] 				= "Error Level";
$text["postmaxsize"] 				= "Post Max Size";
$text["uploadmaxsize"] 				= "Upload Max Size";
$text["uploads"] 					= "Uploads";

$text["system"] 					= "System";
$text["speicherplatz"] 				= "Speicherplatz";
$text["diskspace_free"]             = " (frei/gesamt)";


$text["datenbankserver"] 			= "Datenbankserver";
$text["datenbankclient"] 			= "Datenbankclient";
$text["datenbankverbindung"] 		= "Datenbankverbindung";
$text["anzahltabellen"] 			= "Anzahl Tabellen";
$text["groessegesamt"] 				= "Größe Gesamt";
$text["groessedaten"] 				= "Größe Daten";
$text["datenbanktreiber"] 			= "Datenbanktreiber";

$text["keinegd"]					= "Keine GD-Lib installiert";
$text["gifread"]					= "GIF Read Support";
$text["gifwrite"]					= "GIF Write Support";
$text["png"]						= "PNG Support";
$text["jpg"]						= "JPG Support";

$text["browser"]					= "Seitenbrowser";

$text["speichern"]                  = "Speichern";

$text["warnung_settings"]           = "!! ACHTUNG !!<br />Bei folgenden Einstellungen können falsche Werte das System unbrauchbar machen!";
$text["settings_updated"]           = "Einstellungen wurden geändert.";

$text["settings_true"]				= "Ja";
$text["settings_false"]				= "Nein";

$text["session_loggedin"]           = "Angemeldet";
$text["session_loggedout"]          = "Gast";
$text["session_admin"]              = "Administration, Modul: ";
$text["session_portal"]             = "Portal, Seite: ";
$text["session_username"]           = "Benutzer";
$text["session_valid"]              = "Gültig bis";
$text["session_status"]             = "Status";
$text["session_activity"]           = "Aktivität";
$text["session_logout"]             = "Session beenden";

$text["_system_portal_disable_"]            = "Portal deaktiviert:";
$text["_system_portal_disable_hint"]        = "Diese Einstellung aktiviert/deaktiviert das gesamte Portal.";
$text["_system_portal_disablepage_"]        = "Zwischenseite:";
$text["_system_portal_disablepage_hint"]    = "Diese Seite wird angezeigt, wenn das Portal deaktiviert wurde.";
$text["_bildergalerie_cachepfad_"]          = "Bildercache-Pfad:";
$text["_bildergalerie_cachepfad_hint"]      = "Hier werden temporär erzeugte Bilder abgelegt.";
$text["_system_dbdump_amount_"]             = "Anzahl DB-Dumps:";
$text["_system_dbdump_amount_hint"]         = "Definiert, wie viele Datenbank-Sicherungen vorgehalten werden sollen.";
$text["_system_mod_rewrite_hint"]           = "Schaltet URL-Rewriting für Nice-URLs ein oder aus. Das Apache-Modul \"mod_rewrite\" muss dazu installiert sein!";
$text["_system_mod_rewrite_"]               = "URL-Rewriting:";
$text["_system_admin_email_hint"]           = "Falls ausgefüllt, wird im Fall eines schweren Fehlers eine E-Mail an diese Adresse gesendet.";
$text["_system_admin_email_"]               = "Admin E-Mail:";
$text["_system_lock_maxtime_"]              = "Maximale Sperrdauer:";
$text["_system_lock_maxtime_hint"]          = "Nach der angegebenen Dauer in Sekunden werden gesperrte Datensätze automatisch wieder freigegeben.";
$text["_system_output_gzip_"]               = "GZIP-Kompression der Ausgaben:";
$text["_system_output_gzip_hint"]           = "Aktiviert das Komprimieren der Ausgaben per GZIP, bevor diese an den Browser gesendet werden.";
$text["_admin_nr_of_rows_"]                 = "Anzahl Datensätze pro Seite:";
$text["_admin_nr_of_rows_hint"]             = "Anzahl an Datensätzen in den Admin-Listen, sofern das Modul dies unterstützt. Kann von einem Modul überschrieben werden!";
$text["_admin_only_https_"]                 = "Admin nur per HTTPS:";
$text["_admin_only_https_hint"]             = "Bevorzugt die Verwendung von HTTPS im Adminbereich. Der Webserver muss hierfür HTTPS unterstützen.";
$text["_system_use_dbcache_"]               = "Datenbankcache aktiv:";
$text["_system_use_dbcache_hint"]           = "Aktiviert/Deaktiviert den systeminternen Cache für Datenbankabfragen.";
$text["_remoteloader_max_cachetime_"]       = "Cachedauer externer Quellen:";
$text["_remoteloader_max_cachetime_hint"]   = "Cachedauer in Sekunden für extern nachgeladene Inhalte (z.B. RSS-Feeds).";
$text["_system_release_time_"]              = "Dauer einer Session:";
$text["_system_release_time_hint"]          = "Nach dieser Dauer in Sekunden wird eine Session automatisch ungültig.";


$text["errorintro"]                 = "Bitte alle benötigten Felder ausfüllen!";
$text["pageview_forward"]           = "Weiter";
$text["pageview_backward"]          = "Zurück";
$text["pageview_total"]             = "Gesamt: ";

$text["systemtask_run"]             = "Ausführen";

$text["log_empty"]                  = "Keine Einträge im Logfile vorhanden";

$text["update_nodom"]               = "Diese PHP-Installation unterstützt kein XML-DOM. Dies ist für den Update-Check erforderlich.";
$text["update_nourlfopen"]          = "Für diese Funktion muss der Wert &apos;allow_url_fopen&apos; in der PHP-Konfiguration auf &apos;on&apos; gesetzt sein!";
$text["update_module_name"]         = "Modul";
$text["update_module_localversion"] = "Diese Installation";
$text["update_module_remoteversion"]= "Verfügbar";
$text["update_available"]           = "Bitte updaten!";
$text["update_nofilefound"]         = "Die Liste der Updates konnte nicht geladen werden.<br />Gründe hierfür können sein, dass auf diesem System der PHP-Config-Wert
                                       'allow_url_fopen' auf 'off' gesetzt wurde, oder das System keine Unterstützung für Sockets bietet.";
$text["update_invalidXML"]          = "Die Antwort vom Server war leider nicht korrekt. Bitte versuchen Sie die letzte Aktion erneut.";

$text["about_part1"]                = "<h2>Kajona V3 - Open Source Content Management System</h2>
                                       Kajona V 3.1.1, Codename \"taskforce\"<br /><br />
                                       <a href=\"http://www.kajona.de\" target=\"_blank\">www.kajona.de</a><br />
                                       <a href=\"mailto:info@kajona.de\" target=\"_blank\">info@kajona.de</a><br /><br />
                                       Für weitere Infomationen, Support oder bei Anregungen besuchen Sie einfach unsere Webseite.<br />
									   Support erhalten Sie auch in unserem <a href=\"http://board.kajona.de/\" target=\"_blank\">Forum</a>.
                                       ";

$text["about_part2"]                = "<h2>Entwicklungsleitung</h2>
                                       <ul>
                                       <li><a href=\"mailto:sidler@kajona.de\" target=\"_blank\">Stefan Idler</a> (Projektleitung, Technische Leitung, Entwicklung)</li>
                                       <li><a href=\"mailto:jschroeter@kajona.de\" target=\"_blank\">Jakob Schröter</a> (Leitung Frontend, Entwicklung)</li>
                                       </ul>
                                       <h2>Contributors / Entwickler</h2>
                                       <ul>
                                       <li>Thomas Hertwig</li>
                                       </ul>
                                       ";

$text["about_part3"]                = "<h2>Credits</h2>
                                       <ul>
                                       <li>Icons:<br />Everaldo Coelho (Crystal Clear, Crystal SVG), <a href=\"http://everaldo.com/\" target=\"_blank\">http://everaldo.com/</a><br />Steven Robson (Krystaline), <a href=\"http://www.kde-look.org/content/show.php?content=17509\" target=\"_blank\">http://www.kde-look.org/content/show.php?content=17509</a><br />David Patrizi, <a href=\"mailto:david@patrizi.de\">david@patrizi.de</a></li>
                                       <li>browscap.ini:<br />Gary Keith, <a href=\"http://browsers.garykeith.com/downloads.asp\" target=\"_blank\">http://browsers.garykeith.com/downloads.asp</a></li>
                                       <li>FCKeditor:<br />Frederico Caldeira Knabben, <a href=\"http://www.fckeditor.net/\" target=\"_blank\">http://www.fckeditor.net/</a></li>
                                       <li>JpGraph:<br />Aditus, <a href=\"http://www.aditus.nu/jpgraph/\" target=\"_blank\">http://www.aditus.nu/jpgraph/</a></li>
                                       <li>DejaVu Fonts:<br />DejaVu Team, <a href=\"http://dejavu.sourceforge.net\" target=\"_blank\">http://dejavu.sourceforge.net</a></li>
                                       <li>Yahoo! User Interface Library:<br />Yahoo!, <a href=\"http://developer.yahoo.com/yui/\" target=\"_blank\">http://developer.yahoo.com/yui/</a></li>
                                       </ul>
                                       ";

$text["setAbsolutePosOk"]           = "Speichern der Position erfolgreich";

$text["toolsetCalendarMonth"]       = "\"Januar\", \"Februar\", \"M\\u00E4rz\", \"April\", \"Mai\", \"Juni\", \"Juli\", \"August\", \"September\", \"Oktober\", \"November\", \"Dezember\"";
$text["toolsetCalendarWeekday"]     = "\"So\", \"Mo\", \"Di\", \"Mi\", \"Do\", \"Fr\", \"Sa\"";

// --- Quickhelp texts ----------------------------------------------------------------------------------

$text["quickhelp_title"]            = "Schnellhilfe";

$text["quickhelp_list"]				= "Die Liste der Module gibt eine schnelle Übersicht über die aktuell im System installierten Module.<br />
									   Zusätzlich werden die aktuell installierten Versionen der installierten Module genannt, ebenso das
									   ursprüngliche Installationsdatum des Moduls.<br />
									   Über die Rechte des Moduls kann der Modul-Rechte-Knoten bearbeitet werden, von welchem die Inhalte bei
									   aktivierter Rechtevererbung ihre Einstellungen erben.<br />
									   Durch Verschieben der Module in der Liste lässt sich die Reihenfolge in der Modulnavigation anpassen.";
$text["quickhelp_moduleList"]       =  $text["quickhelp_list"];
$text["quickhelp_systemInfo"]		= "Kajona versucht an dieser Stelle, ein paar Informationen über das System heraus zu finden, auf welchem sich die
									   Installation befindet.";
$text["quickhelp_systemSettings"]	= "Hier können grundlegende Einstellungen des Systems vorgenommen werden. Hierfür kann jedes Modul beliebige
                                       Einstellungsmöglichkeiten anbieten. Die hier vorgenommenen Einstellungen sollten mit Vorsicht verändert werden,
                                       falsche Einstellungen können das System im schlimmsten Fall unbrauchbar machen.<br /><br />
                                       Hinweis: Werden Werte an einem Modul geändert, so muss für JEDES Modul der Speichern-Button gedrückt werden. Ein Abändern
                                       der Einstellungen verschiedener Module wird beim Speichern nicht übernommen. Es werden nur die Werte der zum Speichern-Button
                                       zugehörigen Felder übernommen.";
$text["quickhelp_systemTasks"]		= "Systemtasks sind kleine Programme, die alltägliche Aufaben wie Wartungsarbeiten im System übernehmen.<br />
									   Hierzu gehört das Sichern der Datenbank und ggf. das Rückspielen einer Sicherung in das System.";
$text["quickhelp_systemlog"]		= "Das Systemlogbuch gibt die Einträge des Logfiles aus, in welche die Module Nachrichten schreiben können.<br />
									   Die Feinheit des Loggings kann in der config-Datei (/system/config.php) eingestellt werden.";
$text["quickhelp_updateCheck"]		= "Mit der Aktion Updatecheck werden die Versionsnummern der im System installierten Module mit den Versionsnummern
									   der aktuell verfügbaren Module verglichen. Sollte ein Modul nicht mehr in der neusten Verion installiert sein,
									   so gibt Kajona in der Zeile dieses Moduls einen Hinweis hierzu aus.";


//--- systemtasks ---------------------------------------------------------------------------------------

$text["systemtask_dbconsistency_name"]               = "Datenbankkonsistenz überprüfen";
$text["systemtask_dbconsistency_curprev_ok"]         = "Alle Eltern-Kind Beziehungen sind korrekt";
$text["systemtask_dbconsistency_curprev_error"]      = "Folgende Eltern-Kind Beziehungen sind fehlerhaft (fehlender Elternteil):";
$text["systemtask_dbconsistency_right_ok"]           = "Alle Rechte-Records haben einen zugehörigen System-Record";
$text["systemtask_dbconsistency_right_error"]        = "Folgende Rechte-Records sind fehlerhaft (fehlender System-Record):";
$text["systemtask_dbconsistency_date_ok"]            = "Alle Datum-Records haben einen zugehörigen System-Record";
$text["systemtask_dbconsistency_date_error"]         = "Folgende Datum-Records sind fehlerhaft (fehlender System-Record):";

$text["systemtask_dbexport_name"]   = "Datenbank sichern";
$text["systemtask_dbexport_success"]= "Sicherung erfolgreich angelegt";
$text["systemtask_dbexport_error"]  = "Fehler beim Sichern der Datenbank";

$text["systemtask_dbimport_name"]   = "Datenbank importieren";
$text["systemtask_dbimport_success"]= "Sicherung erfolgreich eingespielt";
$text["systemtask_dbimport_error"]  = "Fehler beim Einspielen der Sicherung";
$text["systemtask_dbimport_file"]   = "Sicherung:";

$text["systemtask_flushpiccache_name"]               = "Bildercache leeren";
$text["systemtask_flushpiccache_done"]               = "Leeren abgeschlossen.";
$text["systemtask_flushpiccache_deleted"]            = "<br />Anzahl gelöschter Bilder: ";
$text["systemtask_flushpiccache_skipped"]            = "<br />Anzahl übersprungener Bilder: ";

$text["systemtask_flushremoteloadercache_name"]      = "Remoteloadercache leeren";
$text["systemtask_flushremoteloadercache_done"]      = "Leeren abgeschlossen.";

//--- installer -----------------------------------------------------------------------------------------

$text["installer_given"]            = "vorhanden";
$text["installer_missing"]          = "fehlen";
$text["installer_nloaded"]          = "fehlt";
$text["installer_loaded"]           = "geladen";
$text["installer_next"]             = "Nächster Schritt >";
$text["installer_prev"]             = "< Vorheriger Schritt";
$text["installer_install"]          = "Installieren";
$text["installer_installpe"]        = "Seitenelemente installieren";
$text["installer_update"]           = "Update auf ";
$text["installer_systemlog"]        = "System Log";

$text["installer_phpcheck_intro"]   = "<b>Herzlich Willkommen</b><br /><br />";
$text["installer_phpcheck_lang"]    = "Um den Installer in einer anderen Sprache zu laden, bitte einen der folgenden Links verwenden:<br /><br />";
$text["installer_phpcheck_intro2"]  = "<br />Die Installation des Systems erfolgt in mehreren Schritten: ";
$text["installer_phpcheck_intro2"]  .= "<br />Rechtepüfung, DB-Konfiguration, Zugangsdaten zur Administration, Modulinstallation, Elementinstallation und Installation der Beispielinhalte.<br />";
$text["installer_phpcheck_intro2"]  .= "<br />Je nach Modulauswahl kann die Anzahl dieser Schritte abweichen.";
$text["installer_phpcheck_intro2"]  .= "<br /><br /><b>Hinweis:</b> Das Modul \"Sprachen/Languages\" sollte nur installiert werden, wenn dieses benötigt wird. Es lässt sich auch nachträglich jederzeit installieren.";
$text["installer_phpcheck_intro2"]  .= "<br /><br />Es werden die Schreibrechte einzelner Dateien und Verzeichnisse sowie<br />die Verfügbarkeit benötigter PHP-Module überprüft:<br />";
$text["installer_phpcheck_folder"]  = "<br />Schreibrechte auf ";
$text["installer_phpcheck_module"]  = "<br />PHP-Modul ";

$text["installer_login_intro"]     = "<b>Admin-Benutzer einrichten</b><br /><br />";
$text["installer_login_intro"]      .= "Bitte geben Sie hier einen Benutzernamen und ein Passwort an.<br />Diese Daten werden später als Zugang zur Administration verwendet.<br />Aus Sicherheitsgründen sollten Sie Benutzernamen wie \"admin\" oder \"administrator\" vermeiden.<br /><br />";
$text["installer_login_installed"]  = "<br />Das System wurde bereits mit einem Admin-Benutzer installiert.<br />";
$text["installer_login_username"]   = "Benutzername:";
$text["installer_login_password"]   = "Passwort:";
$text["installer_login_save"]       = "Benutzer anlegen";

$text["installer_config_intro"]     = "<b>Datenbankeinstellungen erfassen</b><br /><br />";
$text["installer_config_intro"]     .= "Anmerkung: Der Webserver benötigt Schreibrechte auf die Datei /system/config.php.<br />";
$text["installer_config_intro"]     .= "Leere Werte für den Datenbankserver, -benutzer, -passwort und -name sind nicht zugelassen.<br />";
$text["installer_config_intro"]     .= "<br />Für den Fall, dass Sie einen dieser Werte leer lassen möchten, bearbeiten Sie bitte die Datei /system/config.php manuell mit einem Texteditor, Näheres hierzu im Handbuch.<br />";
$text["installer_config_intro"]     .= "<br /><b>ACHTUNG:</b> Der PostgreSQL Treiber befindet sich noch im Alpha-Stadium und sollte nur in Test-Umgebungen verwendet werden.<br /><br />";
$text["installer_config_dbhostname"] = "Datenbankserver:";
$text["installer_config_dbusername"] = "Datenbankbenutzer:";
$text["installer_config_dbpassword"] = "Datenbankpasswort:";
$text["installer_config_dbport"]     = "Datenbankport:";
$text["installer_config_dbportinfo"] = "Für den Standardport bitte leer lassen";
$text["installer_config_dbdriver"]   = "Datenbanktreiber:";
$text["installer_config_dbname"]     = "Datenbankname:";
$text["installer_config_dbprefix"]   = "Tabellenpräfix:";
$text["installer_config_write"]      = "In config.php speichern";

$text["installer_modules_found"]     = "<b>Installation/Update der Module</b><br /><br />Gefundene Module:<br /><br />";
$text["installer_modules_needed"]    = "Zur Installation benötigte Module: ";
$text["installer_module_notinstalled"] = "Modul ist nicht installiert";
$text["installer_systemversion_needed"] = "Minimal benötigte Systemversion: ";

$text["installer_elements_found"]    = "<b>Installation der Seitenelemente</b><br /><br />Gefundene Seitenelemente:<br /><br />";

$text["installer_samplecontent"]     = "<b>Installation der Beispielinhalte</b><br /><br />";
$text["installer_samplecontent"]     .= "Das Modul Samplecontent erstellt einige Standard-Seiten und Navigationen.<br />Je nach installierten Modulen werden verschiedene Beispielinhalte installiert.<br /><br /><br />";

$text["installer_finish_intro"]      = "<b>Installation abgeschlossen</b><br /><br />";
$text["installer_finish_hints"]      = "Sie sollten nun die Schreibrechte auf die Datei /system/config.php auf Leserechte zurücksetzen.<br />";
$text["installer_finish_hints"]      .= "Zusätzlich sollte aus Sicherheitsgründen der Ordner /installer/ unbedingt komplett gelöscht werden.<br /><br />";
$text["installer_finish_hints"]      .= "<br />Die Administrationsoberfläche erreichen Sie nun unter:<br />&nbsp;&nbsp;&nbsp;&nbsp;<a href=\""._webpath_."/admin\">"._webpath_."/admin</a><br /><br />";
$text["installer_finish_hints"]      .= "Das Portal erreichen Sie unter:<br />&nbsp;&nbsp;&nbsp;&nbsp;<a href=\""._webpath_."/\">"._webpath_."</a><br /><br />";
$text["installer_finish_closer"]     = "<br />Wir wünschen viel Spaß mit Kajona!";
?>