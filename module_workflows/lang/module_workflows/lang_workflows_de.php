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

//non-editable entries
$lang["permissions_header"]              = array(0 => "Anzeigen", 1 => "Bearbeiten", 2 => "Löschen", 3 => "Rechte", 4 => "Handler", 5 => "", 6 => "", 7 => "", 8 => "");


//editable entries
$lang["_workflows_trigger_authkey_"]     = "Auth-Key";
$lang["_workflows_trigger_authkey_hint"] = "Der Auth-Key wird als Geheimnis beim Aufruf der Workflow-Engine verwendet. Nur wenn der übergebene Auth-Key dem gespeicherten entspricht werden die ausstehenden Workflows ausgeführt. Dies verhindert, dass Dritte durch einfache Aufrufe die Workflows starten können (DOS-Attacke).<br />Nachstehende URL kann zum Aufruf der Workflows-Egine, z.B. in einem Cron-Job, verwendet werden: <br />"._xmlpath_."?module=workflows&action=trigger&authkey=".\Kajona\System\System\SystemSetting::getConfigValue("_workflows_trigger_authkey_")."";
$lang["action_edit_handler"]             = "Default-Werte bearbeiten";
$lang["action_instantiate_handler"]      = "Neue Instanz des Workflows erstellen";
$lang["action_list_handlers"]            = "Workflow-Handlers";
$lang["action_show_details"]             = "Details anzeigen";
$lang["delete_question"]                 = "Möchten Sie den Workflow &quot;<b>%%element_name%%</b>&quot; wirklich löschen?";
$lang["handler_instances"]               = "{0} Instanzen";
$lang["handler_instatiate"]              = "Möchten Sie wirklich eine neue Instanz des Handlers <b>{0}</b> anlegen?";
$lang["header_list_all"]                 = "Alle Workflows";
$lang["header_list_my"]                  = "Meine Workflows";
$lang["list_empty"]                      = "Keine Workflows angelegt";
$lang["message_messagesummary_body_indicator"] = "Nachricht {0} von {1}";
$lang["message_messagesummary_intro"]    = "Sie haben {0} neue Nachrichten. Nachfolgend finden Sie eine Zusammenstellung der neuesten, ungelesenen Nachrichten.";
$lang["message_messagesummary_subject"]  = "Sie haben {0} neue Nachrichten";
$lang["messageprovider_workflows_summary"] = "Zusammenfassung neuer Nachrichten";
$lang["modul_titel"]                     = "Workflows";
$lang["module_list_handlers"]            = "Workflow-Handler";
$lang["module_mylist"]                   = "Meine Workflows";
$lang["module_trigger"]                  = "Workflows triggern";
$lang["myList_empty"]                    = "Keine zu bearbeitenden Workflows vorhanden.";
$lang["quickhelp_list"]                  = "Auf dieser Seite werden alle im System angelegten Workflows mit dem Hinweis der Fälligkeit dargestellt. Workflows können hier bearbeitet werden und werden absteigend der Fälligkeit dargestellt.<br />Werden Workflows nicht bis zu Ihrer Fälligkeit bearbeitet, so erfolgt i.d.R. ein E-Mail-Benachrichtigungen bzw. Erinnerung.";
$lang["quickhelp_list_handlers"]         = "Workflow-Handler stellen die technische Einheit eines Workflows dar. Handler werden zur Parametrisierung der laufenden Instanzen benötigt. In der Regel stehen Handler nur System-Administratoren zur Verfügung.";
$lang["quickhelp_my_list"]               = "Auf dieser Seite werden die eigenen Workflows mit dem Hinweis der Fälligkeit dargestellt. Workflows können hier bearbeitet werden und werden absteigend der Fälligkeit dargestellt.<br />Werden Workflows nicht bis zu Ihrer Fälligkeit bearbeitet, so erfolgt i.d.R. ein E-Mail-Benachrichtigungen bzw. Erinnerung.";
$lang["systemtask_runworkflows_name"]    = "Workflows starten";
$lang["workflow_char1"]                  = "Char 1";
$lang["workflow_char2"]                  = "Char 2";
$lang["workflow_class"]                  = "Handler";
$lang["workflow_date1"]                  = "Datum 1";
$lang["workflow_date2"]                  = "Datum 2";
$lang["workflow_dbdump_val1"]            = "Interval in Stunden";
$lang["workflow_dbdumps_title"]          = "Regelmäßige Datenbanksicherung";
$lang["workflow_general"]                = "Allgemeine Werte";
$lang["workflow_handler_val1"]           = "Wert 1";
$lang["workflow_handler_val2"]           = "Wert 2";
$lang["workflow_handler_val3"]           = "Wert 3";
$lang["workflow_int1"]                   = "Zahl 1";
$lang["workflow_int2"]                   = "Zahl 2";
$lang["workflow_messagesummary_title"]   = "Zusammenfassung neuer Nachrichten";
$lang["workflow_messagesummary_val1"]    = "Neue Zusammenfassung nach X Tagen";
$lang["workflow_messagesummary_val2"]    = "Uhrzeit des Versands";
$lang["workflow_messagesummary_val3"]    = "Anzahl voll ausgegebener Nachrichten";
$lang["workflow_owner"]                  = "Ersteller";
$lang["workflow_params"]                 = "Technische Parameter";
$lang["workflow_responsible"]            = "Verantwortlicher";
$lang["workflow_runs"]                   = "Ausführungen";
$lang["workflow_status"]                 = "Status";
$lang["workflow_status_1"]               = "Neu";
$lang["workflow_status_2"]               = "Geplant";
$lang["workflow_status_3"]               = "Beendet";
$lang["workflow_systemid"]               = "Verwandte Systemid";
$lang["workflow_text"]                   = "Text";
$lang["workflow_text2"]                  = "Text 2";
$lang["workflow_text3"]                  = "Text 3";
$lang["workflow_trigger"]                = "Nächste Ausführung";
$lang["workflow_ui"]                     = "Bearbeitungsmaske für den aktuellen Schritt anzeigen";
