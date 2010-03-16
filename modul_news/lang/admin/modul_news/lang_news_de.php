<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$					    *
********************************************************************************************************/
//Edited with Kajona Language Editor GUI, see www.kajona.de and www.mulchprod.de for more information
//Kajona Language Editor Core Build 233

//non-editable entries
$lang["permissions_header"]              = array(0 => "Anzeigen", 1 => "Bearbeiten", 2 => "Löschen", 3 => "Rechte", 4 => "News bearbeiten", 5 => "Feeds", 6 => "", 7 => "", 8 => "");

//editable entries
$lang["_news_search_resultpage_"]        = "Treffer-Seite:";
$lang["_news_search_resultpage_hint"]    = "Auf dieser Seite erfolgt die Detailansicht der News, die in der Suche gefunden wurden.";
$lang["archive"]                         = "Archiv-Datum:";
$lang["browser"]                         = "Browser öffnen";
$lang["contentToolbar_content"]          = "Inhalte bearbeiten";
$lang["contentToolbar_properties"]       = "Grunddaten bearbeiten";
$lang["editNewsFeed"]                    = "Feed bearbeiten";
$lang["end"]                             = "Ende-Datum:";
$lang["feed_amount"]                     = "Anzahl News:";
$lang["feed_amount_hint"]                = "Anzahl der News pro Feed (0=unbeschränkt)";
$lang["feed_cat"]                        = "Kategorie des Feeds:";
$lang["feed_cat_all"]                    = "Alle Kategorien";
$lang["feed_desc"]                       = "Beschreibung des Feeds:";
$lang["feed_link"]                       = "Link für weitere Infos:";
$lang["feed_liste_leer"]                 = "Keine Feeds angelegt";
$lang["feed_loeschen_frage"]             = "Möchten Sie den Feed &quot;<b>%%element_name%%</b>&quot; wirklich löschen?";
$lang["feed_page"]                       = "Seite der Detailansicht:";
$lang["feed_title"]                      = "Titel des Feeds:";
$lang["feed_urltitle"]                   = "URL-Titel des Feeds:";
$lang["fehler_recht"]                    = "Keine ausreichenden Rechte um diese Aktion durchzuführen";
$lang["kat_anzeigen"]                    = "Kategorie anzeigen";
$lang["kat_ausblenden"]                  = "Kategorien ein-/ausblenden";
$lang["kat_bearbeiten"]                  = "Kategorie bearbeiten";
$lang["kat_loeschen_frage"]              = "Möchten Sie die Kategorie &quot;<b>%%element_name%%</b>&quot; wirklich löschen?";
$lang["kat_rechte"]                      = "Rechte bearbeiten";
$lang["klapper"]                         = "Kategorien ein-/ausblenden";
$lang["languageset_addtolanguage"]       = "Einer Sprache zuordnen";
$lang["languageset_currentlanuage"]      = "Der Newseintrag ist aktuell folgender Sprache zugeordnet:";
$lang["languageset_language"]            = "Sprache:";
$lang["languageset_notmaintained"]       = "Aktuell ist der Newsbeitrag keiner Sprache zugeordnet. Eine Sprachumschaltung ist von daher nicht automatisch möglich.";
$lang["liste_leer"]                      = "Keine News angelegt";
$lang["modul_anlegen"]                   = "News anlegen";
$lang["modul_kat_anlegen"]               = "Kategorie anlegen";
$lang["modul_list_feed"]                 = "RSS-Feeds";
$lang["modul_liste"]                     = "Liste";
$lang["modul_new_feed"]                  = "Neuer RSS-Feed";
$lang["modul_rechte"]                    = "Modul-Rechte";
$lang["modul_titel"]                     = "News";
$lang["modul_titel2"]                    = "Newsverwaltung - Kategorie ";
$lang["news_basicdata"]                  = "News-Grunddaten";
$lang["news_cat_title"]                  = "Kategorie-Titel:";
$lang["news_categories"]                 = "Kategorien";
$lang["news_grunddaten"]                 = "Newsgrunddaten bearbeiten";
$lang["news_image"]                      = "Bild:";
$lang["news_inhalt"]                     = "Newsinhalte bearbeiten";
$lang["news_intro"]                      = "Aufmacher:";
$lang["news_languageset"]                = "Sprachzuordnung bearbeiten";
$lang["news_locked"]                     = "Newsdatensatz ist gesperrt";
$lang["news_loeschen_frage"]             = "Möchten Sie die News &quot;<b>%%element_name%%</b>&quot; wirklich löschen?";
$lang["news_rechte"]                     = "Rechte bearbeiten";
$lang["news_text"]                       = "Langtext:";
$lang["news_title"]                      = "Titel:";
$lang["news_unlock"]                     = "Newsdatensatz entsperren";
$lang["quickhelp_editCat"]               = "Für eine neue oder bereits vorhanden Kategorie kann momentan lediglich ein Titel vergeben werden.";
$lang["quickhelp_editLanguageset"]       = "Um eine Sprachumschaltung zwischen News im Portal zu ermöglichen, müssen diese in  sogenannten Languagesets gruppiert werden. Anhand dieser Sets kann Kajona erkennen, welche News zueinander gehören und hierdurch die Umschaltung ermöglichen.";
$lang["quickhelp_editNews"]              = "Beim Bearbeiten oder Anlegen einer News werden deren Grunddaten erfasst. Hierzu gehört unter Anderem der Titel der News. Des Weiteren können verschiedene Datumswerte definiert werden: <ul><li>Start-Datum: Ab diesem Datum erscheint die Newsmeldung im Portal</li><li>Ende-Datum: Ab diesem Datum verschwindet die Newsmeldung komplett aus dem Portal, auch aus dem Archiv</li><li>Archiv-Datum: Ab diesem Datum wandert die Newsmeldung in die Archivansicht</li></ul>Zusätzlich können die Zugehörigkeitein zu verschiedenen News-Kategorien angegeben werden.";
$lang["quickhelp_editNewsFeed"]          = "Mit Hilfe des aktuellen Formulars können die Eigenschaften eines vorhandenen, oder eines anzulegenden Newsfeeds verändert werden.<br />Die Seite 'Detailansicht' wird dann aufgerufen, wenn ein Abonnent des Newsfeeds die Detaildarstellung der Newsmeldung anfordert. Mit der Einstellung 'Kategorie des Feeds' können die im Feed anzuzeigenden Newsmeldungen eingeschränkt werden. <br />Über das Feld URL-Titel wird ein Titel des Feeds festgelegt, anhand dessen der Feed im Internet erreicht werden kann, z.B. /newsnfacts.rss. Dieser Titel sollte nur aus Buchstaben und Ziffern bestehen (a-z, A-Z, 0-9).";
$lang["quickhelp_editNewscontent"]       = "Die eigentlichen Inhalte einer News werden in dieser Ansicht erfasst und bearbeitet.";
$lang["quickhelp_list"]                  = "Alle News und Kategorien werden in dieser Ansicht aufgelistet.<br />Im ersten Teil werden die Kategorien aufgelistet, im zweiten die einzelnen Newsmeldungen. <br />Um alle News einer Kategorie anzuzeigen, kann per Klick auf 'Kategorie anzeigen' bei der entsprechenden Kategorie die Liste der News gefiltert werden.<br />In der Liste der News werden der Titel der News, die Anzahl Zugriffe sowie das Start- End und Archivdatum der News angezeigt.";
$lang["quickhelp_newCat"]                = "Für eine neue oder bereits vorhanden Kategorie kann momentan lediglich ein Titel vergeben werden.";
$lang["quickhelp_newNews"]               = "Beim Bearbeiten oder Anlegen einer News werden deren Grunddaten erfasst. Hierzu gehört unter Anderem der Titel der News. Des Weiteren können verschiedene Datumswerte definiert werden: <ul><li>Start-Datum: Ab diesem Datum erscheint die Newsmeldung im Portal</li><li>Ende-Datum: Ab diesem Datum verschwindet die Newsmeldung komplett aus dem Portal, auch aus dem Archiv</li><li>Archiv-Datum: Ab diesem Datum wandert die Newsmeldung in die Archivansicht</li></ul>Zusätzlich können die Zugehörigkeitein zu verschiedenen News-Kategorien angegeben werden.";
$lang["quickhelp_newNewsFeed"]           = "Mit Hilfe des aktuellen Formulars können die Eigenschaften eines vorhandenen, oder eines anzulegenden Newsfeeds verändert werden.<br />Die Seite 'Detailansicht' wird dann aufgerufen, wenn ein Abonnent des Newsfeeds die Detaildarstellung der Newsmeldung anfordert. Mit der Einstellung 'Kategorie des Feeds' können die im Feed anzuzeigenden Newsmeldungen eingeschränkt werden. <br />Über das Feld URL-Titel wird ein Titel des Feeds festgelegt, anhand dessen der Feed im Internet erreicht werden kann, z.B. /newsnfacts.rss. Dieser Titel sollte nur aus Buchstaben und Ziffern bestehen (a-z, A-Z, 0-9).";
$lang["quickhelp_newsFeed"]              = "Die Verwaltung der RSS-Feeds erfolgt in diesem Teil der Newsverwaltung. In dieser Liste finden Sie alle RSS-Feeds, die im System konfiguriert wurden.";
$lang["required_feed_page"]              = "Detailseite";
$lang["required_feed_title"]             = "Feedtitel";
$lang["required_feed_urltitle"]          = "URL-Feedtitel";
$lang["required_news_cat_title"]         = "Kategorie-Titel";
$lang["required_news_title"]             = "Newstitel";
$lang["speichern"]                       = "Speichern";
$lang["start"]                           = "Start-Datum:";
?>