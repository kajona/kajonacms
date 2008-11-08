<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                           *
********************************************************************************************************/

// --- Module texts -------------------------------------------------------------------------------------

$text["modul_titel"]				= "Stats";
$text["modul_rechte"]				= "Module permissions";
$text["modul_worker"]               = "Worker";
$text["allgemein"]					= "General";
$text["topseiten"]					= "Top pages";
$text["topreferer"]					= "Top references";
$text["topbrowser"]					= "Top browser";
$text["topvisitor"]					= "Top visitors";
$text["topsystem"]					= "Top systems";
$text["topsessions"]	       		= "Top sessions";
$text["topqueries"]	         		= "Top keywords";
$text["topcountries"]	       		= "Top countries";

$text["fehler_recht"]				= "Not enough permissions to perform this action";

$text["permissions_header"]         = array(
            							0 => "View",
            							1 => "Edit",
            							2 => "Delete",
            							3 => "Permissions",
            							4 => "Worker",      //Recht1
            							5 => "",
            							6 => "",
            							7 => "",
            							8 => ""
            							);

$text["anzahl_hits"]				= "Total page hits:";
$text["anzahl_visitor"]				= "Total visitors:";
$text["anzahl_pagespvisit"]			= "Pages per visit (average):";
$text["anzahl_timepvisit"]			= "Duration per visit in seconds (average):";
$text["anzahl_online"]				= "Visitors online:";

$text["top_seiten_titel"]			= "Page name";
$text["top_seiten_gewicht"]			= "Hits";
$text["top_seiten_language"]		= "Language";
$text["top_visitor_titel"]			= "IP-address";
$text["top_visitor_gewicht"]		= "Hits";
$text["referer_direkt"]				= "Direct access";
$text["top_referer_titel"]			= "Links";
$text["top_referer_gewicht"]		= "Frequency";
$text["top_browser_titel"]			= "Browser";
$text["top_browser_gewicht"]		= "Frequency / Hits";
$text["top_system_titel"]			= "System";
$text["top_system_gewicht"]			= "Frequency";
$text["anteil"]					    = "%";

$text["top_session_titel"]          = "Session-ID";
$text["top_session_dauer"]          = "Duration in sec";
$text["top_session_anzseiten"]      = "Number of pages";
$text["top_session_detail"]         = "Details";
$text["top_session_detail_start"]   = "Start: ";
$text["top_session_detail_end"]     = "End: ";
$text["top_session_detail_time"]    = "Duration in sec: ";
$text["top_session_detail_ip"]      = "Remote-IP: ";
$text["top_session_detail_hostname"]= "Hostname: ";
$text["top_session_detail_verlauf"] = "Visitors trace: <br />";
$text["top_query_titel"]            = "Keyword";
$text["top_query_gewicht"]          = "Frequency";

$text["top_country_titel"]          = "Country";
$text["top_country_gewicht"]        = "Hits";

$text["filtern"]                    = "Filter";
$text["start"]                      = "Start:";
$text["ende"]                       = "End:";
$text["submit_export"]               = "Export";
$text["submit_import"]               = "Import";

$text["_stats_ausschluss_"]          = "Domains to exclude:";
$text["_stats_ausschluss_hint"]      = "Comma-separated list of domains to exclude from stats";
$text["_stats_zeitraum_online_"]     = "Number of seconds:";
$text["_stats_zeitraum_online_hint"] = "Defines, how long a user applies to be online";
$text["_stats_anzahl_liste_"]        = "Number of rows:";
$text["_stats_anzahl_liste_hint"]    = "The number of rows to be shown in lists";

$text["worker_intro"]                = "Here you can start different maintenance tasks. Those can take up a long time during execution.<br />";

$text["task_lookup"]                 = "Resolve IP-addresses (IP -> Hostname)";
$text["task_lookupReset"]            = "Reset erroneous hostnames";
$text["task_ip2c"]                   = "Resolve origin countries of ip-addresses";
$text["task_exportToCsv"]            = "Export data to CSV-file";

$text["task_csvExportIntro"]         = "With this task, existing records from the database can be exported to a CSV-file. Those records are being deleted
                                        from the database at the same time! This task can be usefull, if the databse contains many old records. The exported
                                        and deleted records can be reimported anytime.";
$text["export_start"]                = "Start date:";
$text["export_end"]                  = "End date:";
$text["export_filename"]             = "File name:";
$text["export_success"]              = "The export was successfull.";
$text["export_failure"]              = "An error occured during the export.";
$text["task_importFromCsv"]          = "Import data from a CSV-file";
$text["task_importFromCsvIntro"]     = "By using the task 'Import data from a CSV-file', records existing in a CSV-file can be imported into the system. After
                                        the import, those records can be used in all reports.";
$text["import_filename"]             = "File:";
$text["import_success"]              = "The import was successfull.";
$text["import_failure"]              = "An error occured during the import.";

$text["interval"]                    = "Illustration interval:";
$text["interval_1day"]               = "One day";
$text["interval_2days"]              = "Two days";
$text["interval_7days"]              = "Seven days";
$text["interval_15days"]             = "15 days";
$text["interval_30days"]             = "30 days";
$text["interval_60days"]             = "60 days";


$text["intro_worker_lookup"]         = "Current task: Resolve IP-addresses. <br />Number of addresses to process: ";
$text["intro_worker_lookupip2c"]     = "Current tals: Resolve IP-addresses by country.<br />
                                        Therefore the PHP-option 'allow_url_fopen' has to be enabled.<br />
                                        Number of addresses to process: "; 
$text["progress_worker_lookup"]      = "Progress:";
$text["worker_lookup_end"]           = "Execution finished. All addresses have been processed.";
$text["worker_lookupReset_end"]      = "Execution finished. All erroneous hostnames have been resetted.";


// --- Quickhelp texts ----------------------------------------------------------------------------------

$text["quickhelp_worker"]            = "Worker are used for periodical tasks. This can be tasks like 'Resolve IP-addresses' or other ones. Using this tasks,
                                        the stats often become more significant.";

$text["quickhelp_statsCommon"]       = "The stats are providing an insight into the systems different logfiles. In those reports, all logfiles are analyzed and processed
                                        to grant different views to the data.";
$text["quickhelp_list"]              = $text["quickhelp_statsCommon"];
?>