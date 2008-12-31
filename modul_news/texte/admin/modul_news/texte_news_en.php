<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

// --- Module texts -------------------------------------------------------------------------------------
$text["modul_rechte"]				= "Module permissions";
$text["modul_liste"]				= "List";
$text["modul_anlegen"]				= "Create news";
$text["modul_kat_anlegen"]			= "Create category";
$text["modul_titel"]				= "News";
$text["modul_titel2"]				= "News management - category ";
$text["fehler_recht"]				= "Not enough permissions to perform this action";
$text["liste_leer"]					= "No news available";
$text["modul_list_feed"]            = "RSS-feeds";
$text["modul_new_feed"]             = "New RSS-feed";

$text["permissions_header"]         = array(0 => "View", 1 => "Edit", 2 => "Delete", 3 => "Permissions", 4 => "Edit news", 5 => "Feeds", 6 => "", 7 => "", 8 => "");

$text["klapper"]					= "Show/hide category";

$text["kat_anzeigen"]				= "Show category";
$text["kat_bearbeiten"]				= "Edit category";
$text["kat_loeschen_frage"]			= " : really delete category?";
$text["kat_loeschen_link"]			= "Delete";
$text["kat_rechte"]					= "Change permissions";
$text["kat_ausblenden"]				= "Show/hide categories";

$text["news_inhalt"]				= "Edit news";
$text["news_grunddaten"]			= "Edit news basic data";
$text["news_rechte"]				= "Change permissions";
$text["news_loeschen_frage"]		= " : really delete news?";
$text["news_loeschen_link"]			= "Delete";
$text["news_basicdata"]             = "News basic data";
$text["news_title"]                 = "Title:";
$text["start"]                      = "Start date:";
$text["end"]                        = "End date:";
$text["archive"]                    = "Archive date:";
$text["news_categories"]            = "Categories";
$text["browser"]                    = "Open browser";

$text["news_intro"]                 = "Teaser:";
$text["news_text"]                  = "Long text:";
$text["news_image"]                 = "Image:";

$text["news_cat_title"]             = "Category title:";
$text["speichern"]                  = "Save";

$text["feed_title"]                 = "Feed title:";
$text["feed_urltitle"]              = "Feed URL title:";
$text["feed_link"]                  = "Link for further information:";
$text["feed_desc"]                  = "Feed description:";
$text["feed_page"]                  = "Page for detail view:";
$text["feed_cat"]                   = "Feed category:";
$text["feed_cat_all"]               = "All categories";
$text["feed_liste_leer"]            = "No feeds available";
$text["editNewsFeed"]               = "Edit feed";
$text["feed_loeschen_frage"]        = " : really delete feed?";

$text["_news_suche_seite_"]         = "Result page:";
$text["_news_suche_seite_hint"]     = "This page shows the details of the news which where found by the search.";

$text["required_news_cat_title"]    = "Category title";
$text["required_news_title"]        = "News title";
$text["required_feed_title"]        = "Feed title";
$text["required_feed_urltitle"]     = "URL feed title";
$text["required_feed_page"]         = "Details page";


// --- Quickhelp texts ----------------------------------------------------------------------------------

$text["quickhelp_list"]             = "All news and categories are listed here.<br />The first part contains the categories, the second one the news.<br />To show all news for a specific category you can choose it by clicking on '".$text["kat_anzeigen"]."'.<br />The news list contains the news title, the number of hits, the start date and the end date.";
$text["quickhelp_newNews"]          = "You can edit the basic data of a news by creating or editing it. The basic data contains the news title and several dates which can be defined:<ul><li>Start date: from this date on the news is shown on the portal</li><li>End date: from this date on the news is shown neither on the portal nor in the archive</li><li>Archive date: from this date on the news is shown in the archive and no longer on the portal</li></ul>Furthermore the belongings to the categories can be changed here.";
$text["quickhelp_editNews"]         = "You can edit the basic data of a news by creating or editing it. The basic data contains the news title and several dates which can be defined:<ul><li>Start date: from this date on the news is shown on the portal</li><li>End date: from this date on the news is shown neither on the portal nor in the archive</li><li>Archive date: from this date on the news is shown in the archive and no longer on the portal</li></ul>Furthermore the belongings to the categories can be changed here.";
$text["quickhelp_newCat"]           = "At the moment you just can define the title of a category.";
$text["quickhelp_editCat"]          = "At the moment you just can define the title of a category.";
$text["quickhelp_editNewscontent"]  = "You can edit the news content here.";
$text["quickhelp_newsFeed"]         = "In this section you can manage the rss feeds. The list contains all rss feeds which exist in the system.";
$text["quickhelp_newNewsFeed"]      = "You can change the properties of new and existing news feeds here. <br />The details view is used when a subscriber requests a news message. You can delimit the number of news by setting the feed category.<br />You can set the URL of the feed in the field 'Feed URL title' e.g. /newsnfacts.rss. The title should only contain letters and figures (a-z, A-Z, 0-9).";
$text["quickhelp_editNewsFeed"]     = "You can change the properties of new and existing news feeds here. <br />The details view is used when a subscriber requests a news message. You can delimit the number of news by setting the feed category.<br />You can set the URL of the feed in the field 'Feed URL title' e.g. /newsnfacts.rss. The title should only contain letters and figures (a-z, A-Z, 0-9).";
?>