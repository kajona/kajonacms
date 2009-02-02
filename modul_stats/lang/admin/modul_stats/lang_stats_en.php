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
$lang["permissions_header"]              = array(0 => "View", 1 => "Edit", 2 => "Delete", 3 => "Permissions", 4 => "Worker", 5 => "", 6 => "", 7 => "", 8 => "");

//editable entries
$lang["_stats_duration_online_"]         = "Number of seconds:";
$lang["_stats_duration_online_hint"]     = "Defines, how long a user applies to be online";
$lang["_stats_exclusionlist_"]           = "Domains to exclude:";
$lang["_stats_exclusionlist_hint"]       = "Comma-separated list of domains to exclude from stats";
$lang["_stats_nrofrecords_"]             = "Number of rows:";
$lang["_stats_nrofrecords_hint"]         = "The number of rows to be shown in lists";
$lang["allgemein"]                       = "General";
$lang["anteil"]                          = "%";
$lang["anzahl_hits"]                     = "Total page hits:";
$lang["anzahl_online"]                   = "Visitors online:";
$lang["anzahl_pagespvisit"]              = "Pages per visit (average):";
$lang["anzahl_timepvisit"]               = "Duration per visit in seconds (average):";
$lang["anzahl_visitor"]                  = "Total visitors:";
$lang["ende"]                            = "End:";
$lang["export_end"]                      = "End date:";
$lang["export_failure"]                  = "An error occured during the export.";
$lang["export_filename"]                 = "File name:";
$lang["export_start"]                    = "Start date:";
$lang["export_success"]                  = "The export was successfull.";
$lang["fehler_recht"]                    = "Not enough permissions to perform this action";
$lang["filtern"]                         = "Filter";
$lang["import_failure"]                  = "An error occured during the import.";
$lang["import_filename"]                 = "File:";
$lang["import_success"]                  = "The import was successfull.";
$lang["interval"]                        = "Illustration interval:";
$lang["interval_15days"]                 = "15 days";
$lang["interval_1day"]                   = "One day";
$lang["interval_2days"]                  = "Two days";
$lang["interval_30days"]                 = "30 days";
$lang["interval_60days"]                 = "60 days";
$lang["interval_7days"]                  = "Seven days";
$lang["intro_worker_lookup"]             = "Current task: Resolve IP-addresses. <br />Number of addresses to process: ";
$lang["intro_worker_lookupip2c"]         = "Current tals: Resolve IP-addresses by country.<br />Therefore the PHP-option 'allow_url_fopen' has to be enabled.<br />Number of addresses to process: ";
$lang["modul_rechte"]                    = "Module permissions";
$lang["modul_titel"]                     = "Stats";
$lang["modul_worker"]                    = "Worker";
$lang["progress_worker_lookup"]          = "Progress:";
$lang["quickhelp_list"]                  = "The stats are providing an insight into the systems different logfiles. In those reports, all logfiles are analyzed and processed to grant different views to the data.";
$lang["quickhelp_statsCommon"]           = "The stats are providing an insight into the systems different logfiles. In those reports, all logfiles are analyzed and processed to grant different views to the data.";
$lang["quickhelp_worker"]                = "Worker are used for periodical tasks. This can be tasks like 'Resolve IP-addresses' or other ones. Using this tasks, the stats often become more significant.";
$lang["referer_direkt"]                  = "Direct access";
$lang["start"]                           = "Start:";
$lang["submit_export"]                   = "Export";
$lang["submit_import"]                   = "Import";
$lang["task_csvExportIntro"]             = "With this task, existing records from the database can be exported to a CSV-file. Those records are being deleted from the database at the same time! This task can be usefull, if the databse contains many old records. The exported and deleted records can be reimported anytime.";
$lang["task_exportToCsv"]                = "Export data to CSV-file";
$lang["task_importFromCsv"]              = "Import data from a CSV-file";
$lang["task_importFromCsvIntro"]         = "By using the task 'Import data from a CSV-file', records existing in a CSV-file can be imported into the system. After the import, those records can be used in all reports.";
$lang["task_ip2c"]                       = "Resolve origin countries of ip-addresses";
$lang["task_lookup"]                     = "Resolve IP-addresses (IP -> Hostname)";
$lang["task_lookupReset"]                = "Reset erroneous hostnames";
$lang["top_browser_gewicht"]             = "Frequency / Hits";
$lang["top_browser_titel"]               = "Browser";
$lang["top_country_gewicht"]             = "Hits";
$lang["top_country_titel"]               = "Country";
$lang["top_query_gewicht"]               = "Frequency";
$lang["top_query_titel"]                 = "Keyword";
$lang["top_referer_gewicht"]             = "Frequency";
$lang["top_referer_titel"]               = "Links";
$lang["top_seiten_gewicht"]              = "Hits";
$lang["top_seiten_language"]             = "Language";
$lang["top_seiten_titel"]                = "Page name";
$lang["top_session_anzseiten"]           = "Number of pages";
$lang["top_session_dauer"]               = "Duration in sec";
$lang["top_session_detail"]              = "Details";
$lang["top_session_detail_end"]          = "End: ";
$lang["top_session_detail_hostname"]     = "Hostname: ";
$lang["top_session_detail_ip"]           = "Remote-IP: ";
$lang["top_session_detail_start"]        = "Start: ";
$lang["top_session_detail_time"]         = "Duration in sec: ";
$lang["top_session_detail_verlauf"]      = "Visitors trace: <br />";
$lang["top_session_titel"]               = "Session-ID";
$lang["top_system_gewicht"]              = "Frequency";
$lang["top_system_titel"]                = "System";
$lang["top_visitor_gewicht"]             = "Hits";
$lang["top_visitor_titel"]               = "IP-address";
$lang["topbrowser"]                      = "Top browser";
$lang["topcountries"]                    = "Top countries";
$lang["topqueries"]                      = "Top keywords";
$lang["topreferer"]                      = "Top references";
$lang["topseiten"]                       = "Top pages";
$lang["topsessions"]                     = "Top sessions";
$lang["topsystem"]                       = "Top systems";
$lang["topvisitor"]                      = "Top visitors";
$lang["worker_intro"]                    = "Here you can start different maintenance tasks. Those can take up a long time during execution.<br />";
$lang["worker_lookupReset_end"]          = "Execution finished. All erroneous hostnames have been resetted.";
$lang["worker_lookup_end"]               = "Execution finished. All addresses have been processed.";
?>