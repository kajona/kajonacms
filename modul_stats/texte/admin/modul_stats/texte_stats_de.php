<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                           *
********************************************************************************************************/

// --- Module texts -------------------------------------------------------------------------------------

$text["modul_titel"]				= "Statistiken";
$text["modul_rechte"]				= "Modul-Rechte";
$text["modul_worker"]               = "Worker";
$text["allgemein"]					= "Allgemeines";
$text["topseiten"]					= "Top Seiten";
$text["topreferer"]					= "Top Verweise";
$text["topbrowser"]					= "Top Browser";
$text["topvisitor"]					= "Top Besucher";
$text["topsystem"]					= "Top Systeme";
$text["topsessions"]	       		= "Top Sessions";
$text["topqueries"]	         		= "Top Keywords";
$text["topcountries"]	       		= "Top Länder";

$text["fehler_recht"]				= "Keine ausreichenden Rechte um diese Aktion durchzuführen";

$text["permissions_header"]         = array(0 => "Anzeigen", 1 => "Bearbeiten", 2 => "Löschen", 3 => "Rechte", 4 => "Worker", 5 => "", 6 => "", 7 => "", 8 => "");

$text["anzahl_hits"]				= "Seitenhits gesamt:";
$text["anzahl_visitor"]				= "Besucher gesamt:";
$text["anzahl_pagespvisit"]			= "Seiten pro Besuch (Schnitt):";
$text["anzahl_timepvisit"]			= "Dauer in Sek pro Besuch (Schnitt):";
$text["anzahl_online"]				= "Besucher online:";

$text["top_seiten_titel"]			= "Seitenname";
$text["top_seiten_gewicht"]			= "Aufrufe";
$text["top_seiten_language"]		= "Sprache";
$text["top_visitor_titel"]			= "IP-Adresse";
$text["top_visitor_gewicht"]		= "Hits";
$text["referer_direkt"]				= "Direktzugriff";
$text["top_referer_titel"]			= "Verweise";
$text["top_referer_gewicht"]		= "Häufigkeit";
$text["top_browser_titel"]			= "Browser";
$text["top_browser_gewicht"]		= "Häufigkeit / Hits";
$text["top_system_titel"]			= "System";
$text["top_system_gewicht"]			= "Häufigkeit";
$text["anteil"]					    = "%";

$text["top_session_titel"]          = "Session-ID";
$text["top_session_dauer"]          = "Dauer in Sek";
$text["top_session_anzseiten"]      = "Anzahl Seiten";
$text["top_session_detail"]         = "Detail";
$text["top_session_detail_start"]   = "Beginn: ";
$text["top_session_detail_end"]     = "Ende: ";
$text["top_session_detail_time"]    = "Dauer in Sek: ";
$text["top_session_detail_ip"]      = "Remote-IP: ";
$text["top_session_detail_hostname"]= "Hostname: ";
$text["top_session_detail_verlauf"] = "Besuchs-Verlauf: <br />";
$text["top_query_titel"]            = "Keyword";
$text["top_query_gewicht"]          = "Häufigkeit";

$text["top_country_titel"]          = "Land";
$text["top_country_gewicht"]        = "Zugriffe";

$text["filtern"]                    = "Filtern";
$text["start"]                      = "Start:";
$text["ende"]                       = "Ende:";
$text["submit_export"]               = "Exportieren";
$text["submit_import"]               = "Importieren";

$text["_stats_ausschluss_"]          = "Auszuschließende Domains:";
$text["_stats_ausschluss_hint"]      = "Kommaseparierte Liste an Domains, die aus den Statistiken ausgenommen werden sollen";
$text["_stats_zeitraum_online_"]     = "Anzahl Sekunden:";
$text["_stats_zeitraum_online_hint"] = "Definiert, wie lange Besucher als online gelten";
$text["_stats_anzahl_liste_"]        = "Anzahl Einträge:";
$text["_stats_anzahl_liste_hint"]    = "Legt die Anzahl an Zeilen in den Statistiken fest";

$text["worker_intro"]                = "Hier können verschiedene Wartungs-Tasks gestartet werden. Diese können bei der Ausführung längere Zeit in Anspruch nehmen.<br />";

$text["task_lookup"]                 = "IP-Adressen auflösen (IP -> Hostname)";
$text["task_lookupReset"]            = "Fehlerhafte Hostnames zurücksetzten";
$text["task_ip2c"]                   = "IP-Adressen nach Ursprungsländern auflösen";
$text["task_exportToCsv"]            = "Daten in eine CSV-Datei exportieren";

$text["task_csvExportIntro"]         = "Mit diesem Task werden in der Datenbank vorhandene Daten in eine CSV-Datei exportiert und anschließend aus der Datenbank gelöscht. Dies kann dann sinnvoll werden, wenn sich bereits viele Daten in der Datenbank angesammelt haben und hierdurch die Datenbank bereits recht groß geworden ist. Die exportierten Dateien können jederzeit wieder in die Datenbank reimportiert werden, z.B. um diese wieder in den Reports auszuwerten.";
$text["export_start"]                = "Start-Datum:";
$text["export_end"]                  = "End-Datum:";
$text["export_filename"]             = "Dateiname:";
$text["export_success"]              = "Der Export der Daten war erfolgreich.";
$text["export_failure"]              = "Der Export wurde nicht erfolgreich abgeschlossen.";
$text["task_importFromCsv"]          = "Daten aus einer CSV-Datei importieren";
$text["task_importFromCsvIntro"]     = "Mit Hilfe des aktuellen Tasks 'importFromCsv' können Datensätze, die in einer CSV-Datei vorliegen, in das System importiert werden. Nach dem Import stehen diese Daten dann wieder in allen Reports zur Verfügung.";
$text["import_filename"]             = "Datei:";
$text["import_success"]              = "Der Import der Daten war erfolgreich.";
$text["import_failure"]              = "Der Import wurde nicht erfolgreich abgeschlossen.";

$text["interval"]                    = "Darstellungsintervall:";
$text["interval_1day"]               = "Ein Tag";
$text["interval_2days"]              = "Zwei Tage";
$text["interval_7days"]              = "Sieben Tage";
$text["interval_15days"]             = "15 Tage";
$text["interval_30days"]             = "30 Tage";
$text["interval_60days"]             = "60 Tage";


$text["intro_worker_lookup"]         = "Aktueller Vorgang: IP-Adressen auflösen<br />Anzahl zu verarbeitender Adressen: ";
$text["intro_worker_lookupip2c"]     = "Aktueller Vorgang: IP-Adressen nach Ländern auflösen.<br />Hierfür muss die PHP-Option 'allow_url_fopen' aktiviert sein.<br />Anzahl zu verarbeitender Adressen: "; 
$text["progress_worker_lookup"]      = "Fortschritt:";
$text["worker_lookup_end"]           = "Vorgang abgeschlossen. Alle Adressen wurden ausgewertet.";
$text["worker_lookupReset_end"]      = "Vorgang abgeschlossen. Alle fehlerhaften Hostnames wurden zurückgesetzt.";


// --- Quickhelp texts ----------------------------------------------------------------------------------

$text["quickhelp_worker"]            = "Worker werden für regelmäßige Aufgaben verwendet. Dazu gehört unter Anderem der Task 'IP-Adressen auflösen'. Durch Starten dieses Tasks werden alle IP-Adressen in der Datenbank durch ihre entsprechenden Hostnamen ersetzt, sofern dies möglich ist. Hierdurch werden die Statistiken an vielen Stellen besser lesbar.";

$text["quickhelp_statsCommon"]       = "Die Statistiken geben einen Einblick in die verschiedenen Logfiles des Systems. In diesen Reports werden alle Zugriffe auf das Portal in verschiedenen Darstellungen ausgewertet. Hierzu gehört eine Analyse der Benutzerzahlen, eine Auswertung der auf diese Seite verweisenden Links oder auch eine Auswertung der Browser.";
$text["quickhelp_list"]              = "Die Statistiken geben einen Einblick in die verschiedenen Logfiles des Systems. In diesen Reports werden alle Zugriffe auf das Portal in verschiedenen Darstellungen ausgewertet. Hierzu gehört eine Analyse der Benutzerzahlen, eine Auswertung der auf diese Seite verweisenden Links oder auch eine Auswertung der Browser.";
?>