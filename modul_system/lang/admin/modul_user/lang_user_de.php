<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

// --- Module texts -------------------------------------------------------------------------------------

$lang["modul_titel"]				= "Benutzer";
$lang["modul_rechte"]				= "Modul-Rechte";

$lang["user"]						= "Benutzer: ";
$lang["user_liste"]					= "Alle Benutzer";
$lang["user_anlegen"]				= "Neuer Benutzer";
$lang["user_bearbeiten"]			= "Benutzer bearbeiten";
$lang["user_loeschen_frage"]		= "Möchten Sie den Benutzer &quot;<b>%%element_name%%</b>&quot; wirklich löschen?";
$lang["user_loeschen_erfolg"]		= "Benutzer erfolgreich gelöscht";
$lang["user_loeschen_fehler"]		= "Fehler beim Löschen des Benutzers";
$lang["user_zugehoerigkeit"]		= "Gruppenzugehörigkeiten";
$lang["user_erfolg"]				= "Benutzer erfolgreich gespeichert";
$lang["user_fehler"]				= "Fehler beim Speichern des Benutzers";
$lang["user_fehler_mail"]			= "E-Mail Adresse angeben <a href=\"javascript:history.back(-1)\">zurück</a>";
$lang["user_fehler_pass"]			= "Passwörter sind unterschiedlich <a href=\"javascript:history.back(-1)\">zurück</a>";
$lang["user_fehler_name"]			= "Bitte Benutzername angeben <a href=\"javascript:history.back(-1)\">zurück</a>";
$lang["user_fehler_namedoppelt"]	= "Benutzername schon vorhanden, bitte wählen Sie einen anderen Benutzernamen <a href=\"javascript:history.back(-1)\">zurück</a>";
$lang["fehler_speichern"]			= "Beim Speichern des Benutzers ist ein Fehler aufgetreten!";

$lang["user_logins"]                = "Logins: ";
$lang["user_lastlogin"]             = " Letzer Login: ";


$lang["permissions_header"]         = array(0 => "Anzeigen", 1 => "Bearbeiten", 2 => "Löschen", 3 => "Rechte", 4 => "Logs", 5 => "", 6 => "", 7 => "", 8 => "");

$lang["gruppen"]					= "Gruppen: ";
$lang["gruppen_liste"]				= "Alle Gruppen";
$lang["gruppen_anlegen"]			= "Neue Gruppe";
$lang["gruppe_anlegen_erfolg"]		= "Gruppe erfolgreich gespeichert";
$lang["gruppe_anlegen_fehler"]		= "Fehler beim Speichern der Gruppe";
$lang["gruppe_anlegen_fehler_name"]	= "Bitte Gruppenname angeben";
$lang["gruppe_bearbeiten"]			= "Gruppe bearbeiten";
$lang["gruppe_bearbeiten_x"]		= "Diese Gruppe kann nicht bearbeitet werden";
$lang["gruppe_loeschen_frage"]		= "Möchten Sie die Gruppe &quot;<b>%%element_name%%</b>&quot; wirklich löschen?";
$lang["gruppe_loeschen_erfolg"]		= "Löschen erfolgreich";
$lang["gruppe_loeschen_fehler"]		= "Löschen fehlerhaft";
$lang["gruppe_loeschen_x"]			= "Diese Gruppe kann nicht gelöscht werden";
$lang["gruppe_mitglieder"]			= "Mitglieder der Gruppe anzeigen";

$lang["group_memberlist"]  			= "Mitglieder der Gruppe ";
$lang["mitglied_loeschen"]			= "Benutzer aus der Gruppe entfernen";
$lang["mitglied_loeschen_frage_1"]	= "Möchten Sie den Benutzer &quot;<b>%%element_name%%</b>&quot; wirklich aus der Gruppe";
$lang["mitglied_loeschen_frage_2"]	= " entfernen?";
$lang["mitglied_loeschen_erfolg"]	= "Benutzer erfolgreich aus der Gruppe entfernt";
$lang["mitglied_loeschen_fehler"]	= "Fehler beim Entfernen";
$lang["mitglied_speichern_erfolg"] 	= "Zugehörigkeit erfolgreich gespeichert";
$lang["mitglied_speichern_fehler"] 	= "Fehler beim Speichern der Zugehörigkeit";

$lang["log"]						= "Log: ";
$lang["loginlog"]					= "Login-Protokoll";
$lang["login_nr"]					= "#";
$lang["login_user"]					= "Benutzer";
$lang["login_datum"]				= "Datum";
$lang["login_status"]				= "Status";
$lang["login_status_0"]				= "Login Fehler";
$lang["login_status_1"]				= "Login OK";
$lang["login_ip"]					= "IP-Adresse";

//Form-Texts
$lang["user_personaldata"]			= "Persönliche Daten";
$lang["username"]					= "Benutzername:";
$lang["passwort"]					= "Passwort:";
$lang["passwort2"]					= "Passwort:";
$lang["email"]						= "E-Mail:";
$lang["vorname"]					= "Vorname:";
$lang["nachname"]					= "Nachname:";
$lang["strasse"]					= "Straße:";
$lang["plz"]						= "PLZ:";
$lang["ort"]						= "Ort:";
$lang["tel"]						= "Telefon:";
$lang["handy"]						= "Handy:";
$lang["gebdatum"]					= "Geburtsdatum:";
$lang["user_system"]				= "Systemeinstellungen";
$lang["aktiv"]						= "Aktiv:";
$lang["admin"]						= "Admin-Login:";
$lang["portal"]						= "Portal-Login:";
$lang["submit"]						= "Speichern";
$lang["skin"]						= "Admin-Skin:";
$lang["gruppe"]						= "Gruppen-Name:";
$lang["language"]                   = "Admin-Sprache:";

//Form-Texts Memberships
$lang["user_memberships"]			= "Gruppenzugehörigkeit des Benutzers ";

$lang["fehler_recht"]				= "Keine ausreichenden Rechte um diese Aktion durchzuführen";

$lang["_guests_group_id_"]         = "ID der Gäste-Gruppe:";
$lang["_admin_gruppe_id_"]          = "ID der Admin-Gruppe:";
$lang["_user_log_nrofrecords_"]          = "Anzahl Zeilen:";
$lang["_user_log_nrofrecords_hint"]      = "Definiert die Anzahl an Zeilen, die im Login-Protokoll ausgegeben werden sollen.";
$lang["_admin_skin_default_"]       = "Standard-Admin-Skin:";
$lang["_user_selfedit_"]            = "Eigene Daten:";
$lang["_user_selfedit_hint"]        = "Legt fest, ob ein Benutzer seine eigenen Daten berbeiten darf";

$lang["user_active"]                = "Status ändern (ist aktiv)";
$lang["user_inactive"]              = "Status ändern (ist inaktiv)";

$lang["required_username"]          = "Benutzername";
$lang["required_email"]             = "E-Mail-Adresse";
$lang["required_passwort"]          = "Passwort";
$lang["required_passwort2"]         = "Passwort bestätigen";
$lang["required_gruppename"]        = "Gruppenname";
$lang["required_user_existing"]     = "Benutername bereits vergeben";
$lang["required_password_equal"]    = "Passwörter sind nicht identisch";

$lang["lang_de"]                    = "Deutsch";
$lang["lang_en"]                    = "Englisch";

$lang["login_statusTitle"]          = "Angemeldet als:";
$lang["login_profileTitle"]         = "Benutzer bearbeiten";
$lang["login_logoutTitle"]          = "Abmelden";
$lang["login_dashboard"]            = "Startseite";

$lang["login_loginTitle"]           = "Anmelden";
$lang["login_loginUser"]            = "Benutzer";
$lang["login_loginPass"]            = "Passwort";
$lang["login_loginButton"]          = "Anmelden";
$lang["login_loginError"]           = "Leider waren Ihre Anmeldedaten nicht korrekt. Bitte prüfen Sie Ihren Benutzernamen und Ihr Passwort auf Korrektheit.<br /><br />Sollte dies keine Abhilfe schaffen, wenden Sie sich bitte an Ihren Systemadministrator.";
$lang["login_loginJsInfo"]          = "Bitte aktivieren Sie JavaScript in Ihrem Browser und laden Sie die Seite neu, um alle Funktionen nutzen zu können.";
$lang["login_loginCookiesInfo"]     = "Bitte aktivieren Sie Cookies in Ihrem Browser und laden Sie die Seite neu, um alle Funktionen nutzen zu können.";


// --- Quickhelp texts ----------------------------------------------------------------------------------

$lang["quickhelp_list"]				= "Alle im System angelegten Benutzer werden in dieser Ansicht aufgelistet.<br />Benutzer können hier bearbeitet werden, ebenfalls können die Gruppenzugehörigkeiten verändert werden.";
$lang["quickhelp_edit"]				= "Dieses Formular dient zum Bearbeiten oder Anlegen eines neuen Benutzers. <br />Wenn der Benutzer aktiv geschaltet wurde, dann darf er sich grunsätzlich am System anmelden.<br />Zusätzlich muss aber der Bereich definiert sein, in dem sich ein Benutzer anmelden darf.";
$lang["quickhelp_new"]				= "Dieses Formular dient zum Bearbeiten oder Anlegen eines neuen Benutzers. <br />Wenn der Benutzer aktiv geschaltet wurde, dann darf er sich grunsätzlich am System anmelden.<br />Zusätzlich muss aber der Bereich definiert sein, in dem sich ein Benutzer anmelden darf.";
$lang["quickhelp_membership"]		= "Mit Hilfe dieser Liste können die Gruppenzugehörigkeiten eines Benutzers angepasst werden.<br />So kann ein Benutzer einer Gruppe hinzugefügt werden, oder wieder aus einer Gruppe entfernt werden.<br />ACHTUNG: Ein Benutzer ohne Gruppenzugehörigkeit kann sich nicht am System anmelden!";
$lang["quickhelp_grouplist"]		= "Gruppen, die momentan im System angelegt sind, werden hier aufgelistet. <br />Die Grupper der Gäste sowie die Gruppe der Administratoren kann nicht bearbeitet werden, da diese vom System vorrausgesetzt werden";
$lang["quickhelp_groupnew"]			= "Zum Anlegen einer Gruppe wird lediglich der Gruppenname benötigt.";
$lang["quickhelp_groupsave"]		= "Zum Anlegen einer Gruppe wird lediglich der Gruppenname benötigt.";
$lang["quickhelp_groupmember"]		= "Listet alle Mitglieder einer Gruppe auf. Um die Mitgliedschaft eines Benutzers in dieser Gruppe zu beenden, kann die Zugehörigkeit einfach gelöscht werden. Soll ein Benutzer der Gruppe hinzugefügt werden, so muss dies über den Gruppendialog des Benutzers direkt erfolgen.";
$lang["quickhelp_loginlog"]			= "Das Login-Protokoll gibt eine Liste der Loginversuche und erfolgreichen Logins am System aus. So kann nachvollzogen werden, wer wann von wo aus mit dem System gearbeitet hat.";
?>