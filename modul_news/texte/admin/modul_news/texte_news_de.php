<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

// --- Module texts -------------------------------------------------------------------------------------
$text["modul_rechte"]				= "Modul-Rechte";
$text["modul_liste"]				= "Liste";
$text["modul_anlegen"]				= "News anlegen";
$text["modul_kat_anlegen"]			= "Kategorie anlegen";
$text["modul_titel"]				= "News";
$text["modul_titel2"]				= "Newsverwaltung - Kategorie ";
$text["fehler_recht"]				= "Keine ausreichenden Rechte um diese Aktion durchzuführen";
$text["liste_leer"]					= "Keine News angelegt";
$text["modul_list_feed"]            = "RSS-Feeds";
$text["modul_new_feed"]             = "Neuer RSS-Feed";

$text["permissions_header"]         = array(0 => "Anzeigen", 1 => "Bearbeiten", 2 => "Löschen", 3 => "Rechte", 4 => "News bearbeiten", 5 => "Feeds", 6 => "", 7 => "", 8 => "");

$text["klapper"]					= "Kategorien ein-/ausblenden";

$text["kat_anzeigen"]				= "Kategorie anzeigen";
$text["kat_bearbeiten"]				= "Kategorie bearbeiten";
$text["kat_loeschen_frage"]			= "Möchten Sie die Kategorie &quot;<b>%%element_name%%</b>&quot; wirklich löschen?";
$text["kat_rechte"]					= "Rechte bearbeiten";
$text["kat_ausblenden"]				= "Kategorien ein-/ausblenden";

$text["news_inhalt"]				= "Newsinhalte bearbeiten";
$text["news_grunddaten"]			= "Newsgrunddaten bearbeiten";
$text["news_rechte"]				= "Rechte bearbeiten";
$text["news_loeschen_frage"]		= "Möchten Sie die News &quot;<b>%%element_name%%</b>&quot; wirklich löschen?";
$text["news_basicdata"]             = "News-Grunddaten";
$text["news_title"]                 = "Titel:";
$text["start"]                      = "Start-Datum:";
$text["end"]                        = "Ende-Datum:";
$text["archive"]                    = "Archiv-Datum:";
$text["news_categories"]            = "Kategorien";
$text["browser"]                    = "Browser öffnen";

$text["news_intro"]                 = "Aufmacher:";
$text["news_text"]                  = "Langtext:";
$text["news_image"]                 = "Bild:";

$text["news_cat_title"]             = "Kategorie-Titel:";
$text["speichern"]                  = "Speichern";

$text["feed_title"]                 = "Titel des Feeds:";
$text["feed_urltitle"]              = "URL-Titel des Feeds:";
$text["feed_link"]                  = "Link für weitere Infos:";
$text["feed_desc"]                  = "Beschreibung des Feeds:";
$text["feed_page"]                  = "Seite der Detailansicht:";
$text["feed_cat"]                   = "Kategorie des Feeds:";
$text["feed_cat_all"]               = "Alle Kategorien";
$text["feed_liste_leer"]            = "Keine Feeds angelegt";
$text["editNewsFeed"]               = "Feed bearbeiten";
$text["feed_loeschen_frage"]        = "Möchten Sie den Feed &quot;<b>%%element_name%%</b>&quot; wirklich löschen?";

$text["_news_suche_seite_"]         = "Treffer-Seite:";
$text["_news_suche_seite_hint"]     = "Auf dieser Seite erfolgt die Detailansicht der News, die in der Suche gefunden wurden.";

$text["required_news_cat_title"]    = "Kategorie-Titel";
$text["required_news_title"]        = "Newstitel";
$text["required_feed_title"]        = "Feedtitel";
$text["required_feed_urltitle"]     = "URL-Feedtitel";
$text["required_feed_page"]         = "Detailseite";


// --- Quickhelp texts ----------------------------------------------------------------------------------

$text["quickhelp_list"]             = "Alle News und Kategorien werden in dieser Ansicht aufgelistet.<br />Im ersten Teil werden die Kategorien aufgelistet, im zweiten die einzelnen Newsmeldungen. <br />Um alle News einer Kategorie anzuzeigen, kann per Klick auf 'Kategorie anzeigen' bei der entsprechenden Kategorie die Liste der News gefiltert werden.<br />In der Liste der News werden der Titel der News, die Anzahl Zugriffe sowie das Start- End und Archivdatum der News angezeigt.";
$text["quickhelp_newNews"]          = "Beim Bearbeiten oder Anlegen einer News werden deren Grunddaten erfasst. Hierzu gehört unter Anderem der Titel der News. Des Weiteren können verschiedene Datumswerte definiert werden: <ul><li>Start-Datum: Ab diesem Datum erscheint die Newsmeldung im Portal</li><li>Ende-Datum: Ab diesem Datum verschwindet die Newsmeldung komplett aus dem Portal, auch aus dem Archiv</li><li>Archiv-Datum: Ab diesem Datum wandert die Newsmeldung in die Archivansicht</li></ul>Zusätzlich können die Zugehörigkeitein zu verschiedenen News-Kategorien angegeben werden.";
$text["quickhelp_editNews"]         = "Beim Bearbeiten oder Anlegen einer News werden deren Grunddaten erfasst. Hierzu gehört unter Anderem der Titel der News. Des Weiteren können verschiedene Datumswerte definiert werden: <ul><li>Start-Datum: Ab diesem Datum erscheint die Newsmeldung im Portal</li><li>Ende-Datum: Ab diesem Datum verschwindet die Newsmeldung komplett aus dem Portal, auch aus dem Archiv</li><li>Archiv-Datum: Ab diesem Datum wandert die Newsmeldung in die Archivansicht</li></ul>Zusätzlich können die Zugehörigkeitein zu verschiedenen News-Kategorien angegeben werden.";
$text["quickhelp_newCat"]           = "Für eine neue oder bereits vorhanden Kategorie kann momentan lediglich ein Titel vergeben werden.";
$text["quickhelp_editCat"]          = "Für eine neue oder bereits vorhanden Kategorie kann momentan lediglich ein Titel vergeben werden.";
$text["quickhelp_editNewscontent"]  = "Die eigentlichen Inhalte einer News werden in dieser Ansicht erfasst und bearbeitet.";
$text["quickhelp_newsFeed"]         = "Die Verwaltung der RSS-Feeds erfolgt in diesem Teil der Newsverwaltung. In dieser Liste finden Sie alle RSS-Feeds, die im System konfiguriert wurden.";
$text["quickhelp_newNewsFeed"]      = "Mit Hilfe des aktuellen Formulars können die Eigenschaften eines vorhandenen, oder eines anzulegenden Newsfeeds verändert werden.<br />Die Seite 'Detailansicht' wird dann aufgerufen, wenn ein Abonnent des Newsfeeds die Detaildarstellung der Newsmeldung anfordert. Mit der Einstellung 'Kategorie des Feeds' können die im Feed anzuzeigenden Newsmeldungen eingeschränkt werden. <br />Über das Feld URL-Titel wird ein Titel des Feeds festgelegt, anhand dessen der Feed im Internet erreicht werden kann, z.B. /newsnfacts.rss. Dieser Titel sollte nur aus Buchstaben und Ziffern bestehen (a-z, A-Z, 0-9).";
$text["quickhelp_editNewsFeed"]     = "Mit Hilfe des aktuellen Formulars können die Eigenschaften eines vorhandenen, oder eines anzulegenden Newsfeeds verändert werden.<br />Die Seite 'Detailansicht' wird dann aufgerufen, wenn ein Abonnent des Newsfeeds die Detaildarstellung der Newsmeldung anfordert. Mit der Einstellung 'Kategorie des Feeds' können die im Feed anzuzeigenden Newsmeldungen eingeschränkt werden. <br />Über das Feld URL-Titel wird ein Titel des Feeds festgelegt, anhand dessen der Feed im Internet erreicht werden kann, z.B. /newsnfacts.rss. Dieser Titel sollte nur aus Buchstaben und Ziffern bestehen (a-z, A-Z, 0-9).";
?>