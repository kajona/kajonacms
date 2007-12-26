<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	texte_downloads_de.php																				*
* 	Admin language file for module_downloads     														*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

// --- Module texts -------------------------------------------------------------------------------------

$text["modul_titel"]				= "Downloads";
$text["modul_rechte"]				= "Modul-Rechte";
$text["modul_liste"]				= "Liste";
$text["archiv_neu"]					= "Neues Archiv";
$text["logbuch"]					= "Logbuch";
$text["browser"]					= "Ordnerbrowser";
$text["archive_masssync"]           = "Alle synchronisieren";

$text["permissions_header"]         = array(
            							0 => "Anzeigen",
            							1 => "Bearbeiten",
            							2 => "Löschen",
            							3 => "Rechte",
            							4 => "Syncro",		//Recht1
            							5 => "Download",	//Recht2
            							6 => "Logbuch",		//Recht3
            							7 => "",
            							8 => ""
            							);

$text["logbuch_loeschen_link"]		= "Logbuch leeren";

$text["status_active"]              = "Status ändern (ist aktiv)";
$text["status_inactive"]            = "Status ändern (ist inaktiv)";

$text["archiv_anzeigen"]			= "Archiv öffnen";
$text["archiv_bearbeiten"]			= "Archiv bearbeiten";
$text["archiv_loeschen"]			= "Archiv löschen";
$text["archiv_loeschen_frage"]		= " : Archiv wirklich löschen? <br /> Dabei werden alle hinterlegten Details gelöscht!<br />";
$text["archiv_loeschen_link"]		= "Löschen";
$text["archiv_loeschen_erfolg"]		= "Löschen erfolgreich";
$text["archiv_loeschen_fehler"]		= "Auf Grund nicht ausreichender Rechte konnte das Löschen nicht erfolgreich abgeschlossen werden";
$text["archiv_rechte"]				= "Rechte bearbeiten";
$text["archiv_syncro"]				= "Archiv synchronisieren";
$text["syncro_ende"]				= "Synchronisierung erfolgreich abgeschlossen<br />";

$text["archive_title"]              = "Titel:";
$text["archive_path"]               = "Pfad:";

$text["speichern"]                  = "Speichern";

$text["downloads_name"]             = "Name:";
$text["downloads_description"]      = "Beschreibung:";
$text["downloads_max_kb"]           = "Maximale Downloadrate in kb/s (0=unbegrenzt):";

$text["sortierung_hoch"]			= "Eine Position nach oben";
$text["sortierung_runter"]			= "Eine Position nach unten";

$text["ordner_oeffnen"]				= "Ordner anzeigen";
$text["ordner_hoch"]				= "Eine Ebene nach oben springen";

$text["datei_bearbeiten"]			= "Details bearbeiten";
$text["datei_speichern_fehler"]		= "Fehler beim Speichern der Details";

$text["fehler_recht"]				= "Keine ausreichende Rechte";

$text["liste_leer_archive"]			= "Keine Archive angelegt";
$text["liste_leer_dl"]				= "Keine Downloads vorhanden";

$text["header_id"]                  = "Download-ID";
$text["header_date"]                = "Datum";
$text["header_file"]                = "Datei";
$text["header_user"]                = "Benutzer";
$text["header_ip"]                  = "IP/Hostname";
$text["header_amount"]              = "Anzahl";

$text["stats_title"]                = "Downloads";
$text["stats_toptitle"]             = "Top Downloads";

$text["sync_add"]                   = "Hinzugefügt: ";
$text["sync_del"]                   = " Gelöscht: ";
$text["sync_upd"]                   = " Aktualisiert: ";



$text["datum"]                      = "Datum:";
$text["hint_datum"]                 = "Löscht alle Eintraege des Logbuchs, die älter als das angegeben Datum sind.";

$text["_downloads_suche_seite_"]         = "Treffer-Seite:";
$text["_downloads_suche_seite_hint"]     = "Auf dieser Seite erfolgt die Listenansicht der Downloads, die in der Suche gefunden wurden.";

$text["required_archive_title"]     = "Titel des Archivs";
$text["required_archive_path"]      = "Pfad des Archivs";
$text["required_downloads_name"]    = "Name";
$text["required_downloads_max_kb"]  = "Geschwindigkeit";


// --- Quickhelp texts ----------------------------------------------------------------------------------

$text["quickhelp_newArchive"]       = "Die Grunddaten eines Archivs werden mit Hilfe dieses Formulars festgelegt.<br />
                                       Hierzu gehören der Titel des Archivs, sowie der entsprechende Start-Pfad des Archivs
                                       im Dateisystem.";
$text["quickhelp_editArchive"]      = $text["quickhelp_newArchive"];
$text["quickhelp_list"]             = "Alle bereits angelegten Archive werden in dieser Liste angezeigt.<br />
                                       Mit Hilfe der Aktion 'Synchronisieren' kann der Datenbestand des Dateisystems mit dem der
                                       Datenbank synchronisiert werden. Neue Dateien werden in die Datenbank aufgenommen,
                                       gelöschte aus dieser entfernt, ebenso werden im Dateisystem veränderte Dateien in der
                                       Datenbank aktualisiert.";
$text["quickhelp_showArchive"]      = "Dateien und Ordner, die sich im zuvor gewählten Archiv befinden werden hier aufgelistet.";
$text["quickhelp_editFile"]         = "Eine vorhandene Datei oder ein vorhandener Ordner können hier um weitere Informationen ergänzt werden.<br />
                                       Bei Dateien kann zusätzlich eine maximale Downloadrate in KB festgelegt werden. Wird die Datei über das Portal
                                       angefordert, so kann die Geschwindigkeit des Downloads hiermit limitiert werden.";
?>