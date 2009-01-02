<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

// --- Module texts -------------------------------------------------------------------------------------

$text["modul_titel"]				= "Benutzer";
$text["modul_rechte"]				= "Modul-Rechte";

$text["user"]						= "Benutzer: ";
$text["user_liste"]					= "Alle Benutzer";
$text["user_anlegen"]				= "Neuer Benutzer";
$text["user_bearbeiten"]			= "Benutzer bearbeiten";
$text["user_loeschen_frage"]		= "Möchten Sie den Benutzer &quot;<b>%%element_name%%</b>&quot; wirklich löschen?";
$text["user_loeschen_erfolg"]		= "Benutzer erfolgreich gelöscht";
$text["user_loeschen_fehler"]		= "Fehler beim Löschen des Benutzers";
$text["user_zugehoerigkeit"]		= "Gruppenzugehörigkeiten";
$text["user_erfolg"]				= "Benutzer erfolgreich gespeichert";
$text["user_fehler"]				= "Fehler beim Speichern des Benutzers";
$text["user_fehler_mail"]			= "E-Mail Adresse angeben <a href=\"javascript:history.back(-1)\">zurück</a>";
$text["user_fehler_pass"]			= "Passwörter sind unterschiedlich <a href=\"javascript:history.back(-1)\">zurück</a>";
$text["user_fehler_name"]			= "Bitte Benutzername angeben <a href=\"javascript:history.back(-1)\">zurück</a>";
$text["user_fehler_namedoppelt"]	= "Benutzername schon vorhanden, bitte wählen Sie einen anderen Benutzernamen <a href=\"javascript:history.back(-1)\">zurück</a>";
$text["fehler_speichern"]			= "Beim Speichern des Benutzers ist ein Fehler aufgetreten!";

$text["user_logins"]                = "Logins: ";
$text["user_lastlogin"]             = " Letzer Login: ";


$text["permissions_header"]         = array(0 => "Anzeigen", 1 => "Bearbeiten", 2 => "Löschen", 3 => "Rechte", 4 => "Logs", 5 => "", 6 => "", 7 => "", 8 => "");

$text["gruppen"]					= "Gruppen: ";
$text["gruppen_liste"]				= "Alle Gruppen";
$text["gruppen_anlegen"]			= "Neue Gruppe";
$text["gruppe_anlegen_erfolg"]		= "Gruppe erfolgreich gespeichert";
$text["gruppe_anlegen_fehler"]		= "Fehler beim Speichern der Gruppe";
$text["gruppe_anlegen_fehler_name"]	= "Bitte Gruppenname angeben";
$text["gruppe_bearbeiten"]			= "Gruppe bearbeiten";
$text["gruppe_bearbeiten_x"]		= "Diese Gruppe kann nicht bearbeitet werden";
$text["gruppe_loeschen_frage"]		= "Möchten Sie die Gruppe &quot;<b>%%element_name%%</b>&quot; wirklich löschen?";
$text["gruppe_loeschen_erfolg"]		= "Löschen erfolgreich";
$text["gruppe_loeschen_fehler"]		= "Löschen fehlerhaft";
$text["gruppe_loeschen_x"]			= "Diese Gruppe kann nicht gelöscht werden";
$text["gruppe_mitglieder"]			= "Mitglieder der Gruppe anzeigen";

$text["group_memberlist"]  			= "Mitglieder der Gruppe ";
$text["mitglied_loeschen"]			= "Benutzer aus der Gruppe entfernen";
$text["mitglied_loeschen_frage_1"]	= "Möchten Sie den Benutzer &quot;<b>%%element_name%%</b>&quot; wirklich aus der Gruppe";
$text["mitglied_loeschen_frage_2"]	= " entfernen?";
$text["mitglied_loeschen_erfolg"]	= "Benutzer erfolgreich aus der Gruppe entfernt";
$text["mitglied_loeschen_fehler"]	= "Fehler beim Entfernen";
$text["mitglied_speichern_erfolg"] 	= "Zugehörigkeit erfolgreich gespeichert";
$text["mitglied_speichern_fehler"] 	= "Fehler beim Speichern der Zugehörigkeit";

$text["log"]						= "Log: ";
$text["loginlog"]					= "Login-Protokoll";
$text["login_nr"]					= "#";
$text["login_user"]					= "Benutzer";
$text["login_datum"]				= "Datum";
$text["login_status"]				= "Status";
$text["login_status_0"]				= "Login Fehler";
$text["login_status_1"]				= "Login OK";
$text["login_ip"]					= "IP-Adresse";

//Form-Texts
$text["user_personaldata"]			= "Persönliche Daten";
$text["username"]					= "Benutzername:";
$text["passwort"]					= "Passwort:";
$text["passwort2"]					= "Passwort:";
$text["email"]						= "E-Mail:";
$text["vorname"]					= "Vorname:";
$text["nachname"]					= "Nachname:";
$text["strasse"]					= "Straße:";
$text["plz"]						= "PLZ:";
$text["ort"]						= "Ort:";
$text["tel"]						= "Telefon:";
$text["handy"]						= "Handy:";
$text["gebdatum"]					= "Geburtsdatum:";
$text["user_system"]				= "Systemeinstellungen";
$text["aktiv"]						= "Aktiv:";
$text["admin"]						= "Admin-Login:";
$text["portal"]						= "Portal-Login:";
$text["submit"]						= "Speichern";
$text["skin"]						= "Admin-Skin:";
$text["gruppe"]						= "Gruppen-Name:";
$text["language"]                   = "Admin-Sprache:";

//Form-Texts Memberships
$text["user_memberships"]			= "Gruppenzugehörigkeit des Benutzers ";

$text["fehler_recht"]				= "Keine ausreichenden Rechte um diese Aktion durchzuführen";

$text["_gaeste_gruppe_id_"]         = "ID der Gäste-Gruppe:";
$text["_admin_gruppe_id_"]          = "ID der Admin-Gruppe:";
$text["_user_log_anzahl_"]          = "Anzahl Zeilen:";
$text["_user_log_anzahl_hint"]      = "Definiert die Anzahl an Zeilen, die im Login-Protokoll ausgegeben werden sollen.";
$text["_admin_skin_default_"]       = "Default-Skin im Admin-Bereich:";
$text["_user_selfedit_"]            = "Eigene Daten:";
$text["_user_selfedit_hint_"]       = "Legt fest, ob ein Benutzer seine eigenen Daten berbeiten darf";

$text["user_active"]                = "Status ändern (ist aktiv)";
$text["user_inactive"]              = "Status ändern (ist inaktiv)";

$text["required_username"]          = "Benutzername";
$text["required_email"]             = "E-Mail-Adresse";
$text["required_passwort"]          = "Passwort";
$text["required_passwort2"]         = "Passwort bestätigen";
$text["required_gruppename"]        = "Gruppenname";
$text["required_user_existing"]     = "Benutername bereits vergeben";
$text["required_password_equal"]    = "Passwörter sind nicht identisch";

$text["lang_de"]                    = "Deutsch";
$text["lang_en"]                    = "Englisch";

$text["login_statusTitle"]          = "Angemeldet als:";
$text["login_profileTitle"]         = "Benutzer bearbeiten";
$text["login_logoutTitle"]          = "Abmelden";
$text["login_dashboard"]            = "Startseite";

$text["login_loginTitle"]           = "Anmelden";
$text["login_loginUser"]            = "Benutzer";
$text["login_loginPass"]            = "Passwort";
$text["login_loginButton"]          = "Anmelden";
$text["login_loginError"]           = "Leider waren Ihre Anmeldedaten nicht korrekt. Bitte prüfen Sie Ihren Benutzernamen und Ihr Passwort auf Korrektheit.<br /><br />Sollte dies keine Abhilfe schaffen, wenden Sie sich bitte an Ihren Systemadministrator.";
$text["login_loginJsInfo"]          = "Bitte aktivieren Sie JavaScript in Ihrem Browser und laden Sie die Seite neu, um alle Funktionen nutzen zu können.";
$text["login_loginCookiesInfo"]     = "Bitte aktivieren Sie Cookies in Ihrem Browser und laden Sie die Seite neu, um alle Funktionen nutzen zu können.";


// --- Quickhelp texts ----------------------------------------------------------------------------------

$text["quickhelp_list"]				= "Alle im System angelegten Benutzer werden in dieser Ansicht aufgelistet.<br />Benutzer können hier bearbeitet werden, ebenfalls können die Gruppenzugehörigkeiten verändert werden.";
$text["quickhelp_edit"]				= "Dieses Formular dient zum Bearbeiten oder Anlegen eines neuen Benutzers. <br />Wenn der Benutzer aktiv geschaltet wurde, dann darf er sich grunsätzlich am System anmelden.<br />Zusätzlich muss aber der Bereich definiert sein, in dem sich ein Benutzer anmelden darf.";
$text["quickhelp_new"]				= "Dieses Formular dient zum Bearbeiten oder Anlegen eines neuen Benutzers. <br />Wenn der Benutzer aktiv geschaltet wurde, dann darf er sich grunsätzlich am System anmelden.<br />Zusätzlich muss aber der Bereich definiert sein, in dem sich ein Benutzer anmelden darf.";
$text["quickhelp_membership"]		= "Mit Hilfe dieser Liste können die Gruppenzugehörigkeiten eines Benutzers angepasst werden.<br />So kann ein Benutzer einer Gruppe hinzugefügt werden, oder wieder aus einer Gruppe entfernt werden.<br />ACHTUNG: Ein Benutzer ohne Gruppenzugehörigkeit kann sich nicht am System anmelden!";
$text["quickhelp_grouplist"]		= "Gruppen, die momentan im System angelegt sind, werden hier aufgelistet. <br />Die Grupper der Gäste sowie die Gruppe der Administratoren kann nicht bearbeitet werden, da diese vom System vorrausgesetzt werden";
$text["quickhelp_groupnew"]			= "Zum Anlegen einer Gruppe wird lediglich der Gruppenname benötigt.";
$text["quickhelp_groupsave"]		= "Zum Anlegen einer Gruppe wird lediglich der Gruppenname benötigt.";
$text["quickhelp_groupmember"]		= "Listet alle Mitglieder einer Gruppe auf. Um die Mitgliedschaft eines Benutzers in dieser Gruppe zu beenden, kann die Zugehörigkeit einfach gelöscht werden. Soll ein Benutzer der Gruppe hinzugefügt werden, so muss dies über den Gruppendialog des Benutzers direkt erfolgen.";
$text["quickhelp_loginlog"]			= "Das Login-Protokoll gibt eine Liste der Loginversuche und erfolgreichen Logins am System aus. So kann nachvollzogen werden, wer wann von wo aus mit dem System gearbeitet hat.";
?>