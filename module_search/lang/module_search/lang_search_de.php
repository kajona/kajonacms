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


//editable entries
$lang["_search_deferred_indexer_"]       = "Verzögerter Indexaufbau";
$lang["_search_deferred_indexer_hint"]       = "Durch eine Verzögerung beim Indexieren von Objekten wird das Anlegen und Aktualisieren von Objekten schneller. Dies setzt jedoch das Modul Workflows als installiert und korrekt konfiguriert voraus.";
$lang["action_execute_search"]           = "Suche ausführen";
$lang["action_new"]                      = "Suche anlegen";
$lang["workflow_deferredindexer_title"]                 = "Such-Index Update";
$lang["delete_question"]                 = "Möchten Sie die Suche &quot;<b>%%element_name%%</b>&quot; wirklich löschen?";
$lang["form_additionalheader"]           = "Zusätzliche Filter";
$lang["form_search_changeenddate"]    = "Änderung bis";
$lang["form_search_changestartdate"]  = "Änderung von";
$lang["form_search_query"]               = "Suchbegriff";
$lang["header_amount"]                   = "Anzahl";
$lang["header_query"]                    = "Suchbegriff";
$lang["hitlist_text1"]                   = "Die Suche nach";
$lang["hitlist_text2"]                   = "ergab";
$lang["hitlist_text3"]                   = "Treffer";
$lang["modul_titel"]                     = "Suche";
$lang["search_details"]                  = "Ausführliches Ergebnis anzeigen";
$lang["search_modules"]                  = "Suchmodul";
$lang["search_users"]                    = "Benutzer";
$lang["search_search"]                   = "Suche";
$lang["searchterm_label"]                = "Suchbegriff";
$lang["select_all"]                      = "Alle Module";
$lang["stats_title"]                     = "Suchanfragen";
$lang["submit_label"]                    = "Suchen";
$lang["systemtask_search_indexrebuild"]              = "Suchindex neu aufbauen";
$lang["worker_indexrebuild_end"]              = "Index wurde neu aufgebaut.<br /><br />Anzahl Dokumente im Index: {0}<br />Anzahl Einträge im Index: {1}";
$lang["worker_indexrebuild"]              = "Index wird neu aufgebaut...<br /><br />Anzahl Dokumente im Index: {0}<br />Anzahl Einträge im Index: {1}";
$lang["quickhelp_search"]                  = "Die Suchfunktion bietet die Möglichkeit das gesamte System nach jedem beliebigen Suchbegriff zu durchsuchen. Hierzu wird der gewünschte Suchbegriff eingegeben und durch Anklicken des entsprechenden Buttons die Suche ausgelöst. Die jeweiligen Ergebnisse werden listenförmig angezeigt und können entsprechend den jeweiligen Rechten des Benutzers weiter bearbeitet werden. ";
$lang["quickhelp_list"]                  = "In dieser Liste werden bereits vom Benutzer angelegte Suchen (z.B. aufgrund von regelmäßigen Abfragen) angezeigt. Diese können von dieser Stelle aus weiter parametrisiert bzw. gestartet werden.";
$lang["quickhelp_new"]                  = "Über diese Funktion können routinemäßig bzw. regelmäßig zu überprüfende Suchbegriffe angelegt werden. Durch Anklicken des Buttons werden diese in die Liste der angelegten Suchen übernommen und die Abfrage kann weiter spezifiziert, gestartet bzw. geändert werden.";

$lang["workflow_deferredindexer_cfg_val1"] = "Sekunden zwischen zwei Ausführungen";
$lang["workflow_deferredindexer_cfg_val2"] = "Zu indexierende Objekte je Durchlauf";

$lang["search_reduce_hits_link"] = "Die Suchanfrage liefert sehr viele Ergebnisse. Bitte verfeinern Sie die Suchanfrage durch zusätzliche Filter oder Bedingungen:<br /><ul><li>Mehrere Suchbegriffe werden durch Leerzeichen voneinander getrennt: wort1 wort2</li><li>Ist ein Wort zwingend erforderlich, kann dieses mit einem + Symbol gekennzeichnet werden: wort1 +wort2</li><li>Soll ein Wort ausgeschlossen werden, kann dieses mit einem - Symbol gekennzeichnet werden: wort1 -wort2</li></ul>";