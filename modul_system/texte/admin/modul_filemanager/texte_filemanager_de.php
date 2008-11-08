<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                    *
********************************************************************************************************/

// --- Module texts -------------------------------------------------------------------------------------

$text["modul_titel"]				= "Filemanager";
$text["modul_rechte"]				= "Modul-Rechte";
$text["modul_liste"]				= "Liste";
$text["modul_neu"]					= "Neues Repository";

$text["permissions_header"]         = array(
            							0 => "Anzeigen",
            							1 => "Bearbeiten",
            							2 => "Löschen",
            							3 => "Rechte",
            							4 => "Upload",		//Recht1
            							5 => "Verwalten",	//Recht2
            							6 => "",
            							7 => "",
            							8 => ""
            							);

$text["repo_oeffnen"]				= "Ordner öffnen";
$text["repo_bearbeiten"]			= "Eigenschaften festlegen";
$text["repo_bearbeiten_fehler"]		= "Beim Bearbeiten ist ein Fehler aufgetreten";
$text["repo_rechte"]				= "Rechte bearbeiten";
$text["repo_loeschen_frage"]			= " : Repository wirklich löschen?<br />";
$text["repo_loeschen_link"]			= "Löschen";

$text["ordner_anlegen"]				= "Neuer Ordner";
$text["ordner_anlegen_erfolg"]		= "Ordner erfolgreich angelegt";
$text["ordner_anlegen_fehler"]		= "Fehler beim Anlegen des Ordners";
$text["ordner_anlegen_fehler_l"]	= "Ordner bereits vorhanden";
$text["ordner_loeschen_frage"]		= ": Den Ordner wirklich löschen?";
$text["ordner_loeschen_link"]		= "Löschen";
$text["ordner_loeschen_fehler_l"]	= "Der Ordner ist nicht leer!";
$text["ordner_loeschen_fehler"]		= "Fehler beim Löschen des Ordners!";
$text["ordner_loeschen_erfolg"]		= "Ordner erfolgreich gelöscht";
$text["ordner_hoch"]				= "Eine Ebene nach oben";

$text["datei_loeschen_frage"]		= " : Datei wirklich löschen?<br />Bitte Wechselwirkungen mit anderen Modulen bedenken!";
$text["datei_loeschen_link"]		= "Löschen";
$text["datei_loeschen_erfolg"]		= "Datei erfolgreich gelöscht";
$text["datei_loeschen_fehler"]		= "Fehler beim Löschen der Datei";
$text["datei_umbenennen"]			= "Datei umbenennen";
$text["datei_umbenennen_hinweis"]	= "Bitte beim Umbennen bedenken, dass dies zu Wechselwirkungen mit vorhandenen Modulen wie bsp. der Bildergalerie führen kann";
$text["datei_umbenennen_erfolg"]	= "Datei erfolgreich umbenannt";
$text["datei_umbenennen_fehler_z"]	= "Der Dateiname ist bereits vergeben!";
$text["datei_umbenennen_fehler"]	= "Fehler beim Umbenennen!";
$text["datei_upload"]				= "Datei hochladen";
$text["datei_oeffnen"]				= "Datei öffnen";
$text["datei_erstell"]				= "Erstellungsdatum";
$text["datei_bearbeit"]				= "Letzte Änderung";
$text["datei_zugriff"]				= "Letzter Zugriff";
$text["datei_pfad"]					= "Dateipfad:";
$text["datei_typ"]					= "Dateityp:";
$text["datei_groesse"]				= "Dateigröße:";
$text["bild_groesse"]				= "Bildgröße:";
$text["bild_vorschau"]				= "Vorschau:";

$text["fehler_recht"]				= "Keine ausreichende Rechte";
$text["liste_leer"]					= "Keine Repositories angelegt";

$text["browser"]					= "Ordnerbrowser";

$text["submit"]                     = "Speichern";
$text["filemanager_name"]           = "Name:";
$text["filemanager_path"]           = "Pfad:";
$text["filemanager_upload_filter"]  = "Upload-Filter:";
$text["filemanager_upload_filter_h"]= "Eine kommaseparierte Liste an Dateiendungen, die hochgeladen werden dürfen (z.B. &quot;.jpg,.gif,.png&quot;) ";
$text["filemanager_view_filter"]    = "Ansicht-Filter:";
$text["filemanager_view_filter_h"]  = "Eine kommaseparierte Liste an Dateiendungen, die angezeigt werden (z.B. &quot;.jpg,.gif,.png&quot;)";

$text["fehler_repo"]                = "Fehler beim Anlegen des Repositorys. Existiert der Ordner?";

$text["foldertitle"]                = "Pfad: ";
$text["nrfoldertitle"]              = "Anzahl Ordner: ";
$text["nrfilestitle"]               = "Anzahl Dateien: ";

$text["datei_name"]                 = "Dateiname:";
$text["rename"]                     = "Umbenennen";
$text["ordner_name"]                = "Ordnername:";
$text["anlegen"]                    = "Ordner anlegen";

$text["filemanager_upload"]         = "Datei hochladen:";
$text["max_size"]                   = "Maximale Dateigröße: ";
$text["upload_submit"]              = "Hochladen";
$text["add_upload_field"]           = "Zusätzliches Uploadfeld hinzufügen";
$text["upload_erfolg"]				= "Datei erfolgreich hochgeladen<br />";
$text["upload_fehler"]				= "Dateiupload fehlerhaft<br />";
$text["upload_fehler_filter"]		= "Der hochgeladene Dateityp ist nicht erlaubt<br />";
$text["upload_multiple_uploadFiles"]	= "Datei(en) hochladen";
$text["upload_multiple_cancel"]		= "Abbrechen";

$text["_filemanager_ordner_groesse_"] = "Größe anzeigen:";
$text["_filemanager_ordner_groesse_hint"] = "Aktiviert oder deaktiviert das rekursive Bestimmen der Ordnergrößen im Filemanager.
                                            Bei tiefen Verzeichnishierarchien kann dies zu Performanceeinschränkungen führen.";

$text["required_filemanager_name"]  = "Name";
$text["required_filemanager_path"]  = "Pfad";

$text["useFile"]                    = "Übernehmen";

$text["xmlupload_success"]          = "Upload erfolgreich";
$text["xmlupload_error_copyUpload"] = "Fehler beim Kopieren der Datei auf dem Server";
$text["xmlupload_error_filter"]     = "Dateityp im Filter nicht erlaubt";
$text["xmlupload_error_notWritable"]= "Zielordner nicht beschreibbar";
$text["xmlupload_error_permissions"]= "Keine ausreichenden Rechte";


// --- Quickhelp texts ----------------------------------------------------------------------------------

$text["quickhelp_list"]             = "Der Filemanager dient als Dateimananger. Mit Hilfe dessen können Dateien in das System hochgeladen werden, umbenannt oder gelöscht werden.
                                       In dieser Ansicht finden Sie eine Liste der angelegten Repositories. Jedes Respository kann nach speziellen
                                       Anforderungen konfiguriert werden.";
$text["quickhelp_newRepo"]          = "Beim Anlegen oder Bearbeiten eines Repositories können verschiedene Eigenschaften definiert werden.
                                       Hierzu gehört der Name des Repositorys, der entsprechende Pfad im Dateisystem, ein Upload-Filter zum expliziten zulassen von
                                       für den Upload erlaubten Dateitypen sowie ein Ansichts-Filter zum Ausblenden von bestimmten Dateien.";
$text["quickhelp_editRepo"]         = $text["quickhelp_newRepo"];
$text["quickhelp_openFolder"]       = "Alle sich in diesem Ordner befindlichen Dateien und Ordner werden in dieser Liste angezeigt (Die Liste kann auf Grund
                                       von Filtern eingeschränkt sein. In dieser Ansicht können außerdem Dateien hochgeladen, umbenannt oder gelöscht werden. Das Anlegen
                                       von neuen Ordnern oder das Löschen von leeren Ordnern ist ebenfalls möglich.";
$text["quickhelp_newFolder"]        = $text["quickhelp_openFolder"];
$text["quickhelp_imageDetail"]      = $text["quickhelp_openFolder"];
$text["quickhelp_deleteFile"]       = $text["quickhelp_openFolder"];
$text["quickhelp_deleteFolder"]     = $text["quickhelp_openFolder"];

// --- MODULE FOLDERVIEW --------------------------------------------------------------------------------
$text["moduleFolderviewTitle"]      = "Ordneransicht";

$text["ordner_hoch"]                = "Eine Ebene nach oben";
$text["ordner_oeffnen"]             = "Ordner öffnen";
$text["ordner_uebernehmen"]         = "Ordner übernehmen";

$text["seite_uebernehmen"]          = "Seite übernehmen";
$text["seite_oeffnen"]              = "Seitenelemente anzeigen";

$text["datei_detail"]               = "Detailansicht";
$text["datei_name"]                 = "Dateiname:";
$text["datei_pfad"]                 = "Dateipfad:";
$text["datei_typ"]                  = "Dateityp:";
$text["datei_groesse"]              = "Dateigröße:";
$text["datei_erstell"]              = "Erstelldatum:";
$text["datei_bearbeit"]             = "Letzte Änderung:";
$text["datei_zugriff"]              = "Letzter Zugriff:";
$text["bild_groesse"]               = "Bildgröße:";
$text["bild_vorschau"]              = "Vorschau:";
$text["pfad"]                       = "Pfad: ";
$text["ordner_anz"]                 = "Anzahl Ordner: ";
$text["dateien_anz"]                = "Anzahl Dateien: ";
?>