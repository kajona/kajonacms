<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$					    *
********************************************************************************************************/
//Edited with Kajona Language Editor GUI, see www.kajona.de and www.mulchprod.de for more information
//Kajona Language Editor Core Build 101

//non-editable entries
$lang["permissions_header"]              = array(0 => "Anzeigen", 1 => "Bearbeiten", 2 => "Löschen", 3 => "Rechte", 4 => "Worker", 5 => "", 6 => "", 7 => "", 8 => "");

//editable entries
$lang["_stats_duration_online_"]         = "Anzahl Sekunden:";
$lang["_stats_duration_online_hint"]     = "Definiert, wie lange Besucher als online gelten";
$lang["_stats_exclusionlist_"]           = "Auszuschließende Domains:";
$lang["_stats_exclusionlist_hint"]       = "Kommaseparierte Liste an Domains, die aus den Statistiken ausgenommen werden sollen";
$lang["_stats_nrofrecords_"]             = "Anzahl Einträge:";
$lang["_stats_nrofrecords_hint"]         = "Legt die Anzahl an Zeilen in den Statistiken fest";
$lang["allgemein"]                       = "Allgemeines";
$lang["anteil"]                          = "%";
$lang["anzahl_hits"]                     = "Seitenhits gesamt:";
$lang["anzahl_online"]                   = "Besucher online:";
$lang["anzahl_pagespvisit"]              = "Seiten pro Besuch (Schnitt):";
$lang["anzahl_timepvisit"]               = "Dauer in Sek pro Besuch (Schnitt):";
$lang["anzahl_visitor"]                  = "Besucher gesamt:";
$lang["ende"]                            = "Ende:";
$lang["export_end"]                      = "End-Datum:";
$lang["export_failure"]                  = "Der Export wurde nicht erfolgreich abgeschlossen.";
$lang["export_filename"]                 = "Dateiname:";
$lang["export_start"]                    = "Start-Datum:";
$lang["export_success"]                  = "Der Export der Daten war erfolgreich.";
$lang["fehler_recht"]                    = "Keine ausreichenden Rechte um diese Aktion durchzuführen";
$lang["filtern"]                         = "Filtern";
$lang["import_failure"]                  = "Der Import wurde nicht erfolgreich abgeschlossen.";
$lang["import_filename"]                 = "Datei:";
$lang["import_success"]                  = "Der Import der Daten war erfolgreich.";
$lang["interval"]                        = "Darstellungsintervall:";
$lang["interval_15days"]                 = "15 Tage";
$lang["interval_1day"]                   = "Ein Tag";
$lang["interval_2days"]                  = "Zwei Tage";
$lang["interval_30days"]                 = "30 Tage";
$lang["interval_60days"]                 = "60 Tage";
$lang["interval_7days"]                  = "Sieben Tage";
$lang["intro_worker_lookup"]             = "Aktueller Vorgang: IP-Adressen auflösen<br />Anzahl zu verarbeitender Adressen: ";
$lang["intro_worker_lookupip2c"]         = "Aktueller Vorgang: IP-Adressen nach Ländern auflösen.<br />Hierfür muss die PHP-Option 'allow_url_fopen' aktiviert sein.<br />Anzahl zu verarbeitender Adressen: ";
$lang["modul_rechte"]                    = "Modul-Rechte";
$lang["modul_titel"]                     = "Statistiken";
$lang["modul_worker"]                    = "Worker";
$lang["progress_worker_lookup"]          = "Fortschritt:";
$lang["quickhelp_list"]                  = "Die Statistiken geben einen Einblick in die verschiedenen Logfiles des Systems. In diesen Reports werden alle Zugriffe auf das Portal in verschiedenen Darstellungen ausgewertet. Hierzu gehört eine Analyse der Benutzerzahlen, eine Auswertung der auf diese Seite verweisenden Links oder auch eine Auswertung der Browser.";
$lang["quickhelp_statsCommon"]           = "Die Statistiken geben einen Einblick in die verschiedenen Logfiles des Systems. In diesen Reports werden alle Zugriffe auf das Portal in verschiedenen Darstellungen ausgewertet. Hierzu gehört eine Analyse der Benutzerzahlen, eine Auswertung der auf diese Seite verweisenden Links oder auch eine Auswertung der Browser.";
$lang["quickhelp_worker"]                = "Worker werden für regelmäßige Aufgaben verwendet. Dazu gehört unter Anderem der Task 'IP-Adressen auflösen'. Durch Starten dieses Tasks werden alle IP-Adressen in der Datenbank durch ihre entsprechenden Hostnamen ersetzt, sofern dies möglich ist. Hierdurch werden die Statistiken an vielen Stellen besser lesbar.";
$lang["referer_direkt"]                  = "Direktzugriff";
$lang["start"]                           = "Start:";
$lang["submit_export"]                   = "Exportieren";
$lang["submit_import"]                   = "Importieren";
$lang["task_csvExportIntro"]             = "Mit diesem Task werden in der Datenbank vorhandene Daten in eine CSV-Datei exportiert und anschließend aus der Datenbank gelöscht. Dies kann dann sinnvoll werden, wenn sich bereits viele Daten in der Datenbank angesammelt haben und hierdurch die Datenbank bereits recht groß geworden ist. Die exportierten Dateien können jederzeit wieder in die Datenbank reimportiert werden, z.B. um diese wieder in den Reports auszuwerten.";
$lang["task_exportToCsv"]                = "Daten in eine CSV-Datei exportieren";
$lang["task_importFromCsv"]              = "Daten aus einer CSV-Datei importieren";
$lang["task_importFromCsvIntro"]         = "Mit Hilfe des aktuellen Tasks 'importFromCsv' können Datensätze, die in einer CSV-Datei vorliegen, in das System importiert werden. Nach dem Import stehen diese Daten dann wieder in allen Reports zur Verfügung.";
$lang["task_ip2c"]                       = "IP-Adressen nach Ursprungsländern auflösen";
$lang["task_lookup"]                     = "IP-Adressen auflösen (IP -> Hostname)";
$lang["task_lookupReset"]                = "Fehlerhafte Hostnames zurücksetzten";
$lang["top_browser_gewicht"]             = "Häufigkeit / Hits";
$lang["top_browser_titel"]               = "Browser";
$lang["top_country_gewicht"]             = "Zugriffe";
$lang["top_country_titel"]               = "Land";
$lang["top_query_gewicht"]               = "Häufigkeit";
$lang["top_query_titel"]                 = "Keyword";
$lang["top_referer_gewicht"]             = "Häufigkeit";
$lang["top_referer_titel"]               = "Verweise";
$lang["top_seiten_gewicht"]              = "Aufrufe";
$lang["top_seiten_language"]             = "Sprache";
$lang["top_seiten_titel"]                = "Seitenname";
$lang["top_session_anzseiten"]           = "Anzahl Seiten";
$lang["top_session_dauer"]               = "Dauer in Sek";
$lang["top_session_detail"]              = "Detail";
$lang["top_session_detail_end"]          = "Ende: ";
$lang["top_session_detail_hostname"]     = "Hostname: ";
$lang["top_session_detail_ip"]           = "Remote-IP: ";
$lang["top_session_detail_start"]        = "Beginn: ";
$lang["top_session_detail_time"]         = "Dauer in Sek: ";
$lang["top_session_detail_verlauf"]      = "Besuchs-Verlauf: <br />";
$lang["top_session_titel"]               = "Session-ID";
$lang["top_system_gewicht"]              = "Häufigkeit";
$lang["top_system_titel"]                = "System";
$lang["top_visitor_gewicht"]             = "Hits";
$lang["top_visitor_titel"]               = "IP-Adresse";
$lang["topbrowser"]                      = "Top Browser";
$lang["topcountries"]                    = "Top Länder";
$lang["topqueries"]                      = "Top Keywords";
$lang["topreferer"]                      = "Top Verweise";
$lang["topseiten"]                       = "Top Seiten";
$lang["topsessions"]                     = "Top Sessions";
$lang["topsystem"]                       = "Top Systeme";
$lang["topvisitor"]                      = "Top Besucher";
$lang["worker_intro"]                    = "Hier können verschiedene Wartungs-Tasks gestartet werden. Diese können bei der Ausführung längere Zeit in Anspruch nehmen.<br />";
$lang["worker_lookupReset_end"]          = "Vorgang abgeschlossen. Alle fehlerhaften Hostnames wurden zurückgesetzt.";
$lang["worker_lookup_end"]               = "Vorgang abgeschlossen. Alle Adressen wurden ausgewertet.";
?>