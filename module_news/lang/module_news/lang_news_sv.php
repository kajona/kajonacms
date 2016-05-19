<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$					    *
********************************************************************************************************/
//Edited with Kajona Language Editor GUI, see www.kajona.de and www.mulchprod.de for more information
//Kajona Language Editor Core Build 385

//editable entries
$lang["action_new_feed"]                 = "Ny RSS-feed";
$lang["action_new_news"]                 = "Skapa nyhet";
$lang["backward"]                        = "Tillbaka";
$lang["browser"]                         = "Öppna filhanterare";
$lang["delete_category_question"]        = "Vill du verkligen rader denna kategori &quot;<b>%%element_name%%</b>&quot;?";
$lang["delete_feed_question"]            = "Vill du verkligen radera feed &quot;<b>%%element_name%%</b>&quot;?";
$lang["delete_news_question"]            = "Vil du verkligen radera nyheten &quot;<b>%%element_name%%</b>&quot;?";
$lang["feed_amount"]                     = "Antal nyheter;";
$lang["feed_amount_hint"]                = "Antal nyheter per feed (0=obegränsat)";
$lang["feed_liste_leer"]                 = "Inga feeds tillgängliga";
$lang["fehler_recht"]                    = "Inte tillräckliga rättigheter för denna handling";
$lang["form_feed_cat"]                   = "Feedkategori";
$lang["form_feed_desc"]                  = "Beskrivning av feeds";
$lang["form_feed_link"]                  = "Länk för ytterligare information";
$lang["form_feed_page"]                  = "Sida för detaljvy";
$lang["form_feed_title"]                 = "Feedtitel";
$lang["form_feed_urltitle"]              = "Feed URL-titel";
$lang["form_news_dateend"]               = "Slutdatum";
$lang["form_news_datespecial"]           = "Arkivdatum";
$lang["form_news_datestart"]             = "Startdatum";
$lang["form_news_image"]                 = "Bild";
$lang["form_news_intro"]                 = "Sammandrag";
$lang["form_news_text"]                  = "Nyhetstext";
$lang["form_news_title"]                 = "Titel";
$lang["forward"]                         = "Framåt";
$lang["kat_anzeigen"]                    = "Visa kategori";
$lang["kat_ausblenden"]                  = "Visa/göm kategori";
$lang["kat_bearbeiten"]                  = "Redigera kategori";
$lang["kat_loeschen_frage"]              = "Vill du verkligen radera kategori &quot;<b>%%element_name%%</b>&quot;?";
$lang["kat_rechte"]                      = "Redigera rättigheter";
$lang["klapper"]                         = "Visa/göm kategorier";
$lang["languageset_addnewstolanguage"]   = "Knyt nyheten till ett språk";
$lang["languageset_addtolanguage"]       = "Knyt till ett språk";
$lang["languageset_assign"]              = "Redigera anknytning";
$lang["languageset_currentlanguage"]     = "Denna nyhet har för närvarande knutits till följande språk: {0}";
$lang["languageset_language"]            = "Språk";
$lang["languageset_maintainlanguages"]   = "Knyt an nyhet";
$lang["languageset_news"]                = "Nyhet";
$lang["languageset_news_na"]             = "Inte underhållen";
$lang["languageset_notmaintained"]       = "Fär närvarande är denna nyhet inte tilldelad något språk. En språkomställning för detta är inte automatiskt tillgänlig.";
$lang["languageset_remove"]              = "Radera anknytning";
$lang["liste_leer"]                      = "Inga nyheter tillgängliga";
$lang["modul_liste"]                     = "Lista";
$lang["modul_rechte"]                    = "Modulrättigheter";
$lang["modul_titel"]                     = "Nyheter";
$lang["modul_titel_category"]            = "Nyhetsförvaltning - kategori";
$lang["modul_titel_feed"]                = "RSS-Feeds";
$lang["modul_titel_news"]                = "Nyheter";
$lang["news_basicdata"]                  = "Nyheter grunddata";
$lang["news_cat_title"]                  = "Kategorititel";
$lang["news_categories"]                 = "Kategorier";
$lang["news_edit"]                       = "Redigera nyheter";
$lang["news_languageset"]                = "Redigera språkanknytning";
$lang["news_list_empty"]                 = "Inga nyheter tillgängliga";
$lang["news_locked"]                     = "Nyhetsdatapost är låst";
$lang["news_mehr"]                       = "[läs vidare]";
$lang["news_rechte"]                     = "Redigera rättigheter";
$lang["news_unlock"]                     = "Lås upp nyhetsdatapost";
$lang["news_zurueck"]                    = "Tillbaka";
$lang["quickhelp_edit_category"]         = "För närvarande kan endast en titel anges för ny eller föreliggande kategori.";
$lang["quickhelp_edit_feed"]             = "Med hjälp av detta formulär kan egenskaperna för aktuell eller ny feed ändras.<br />Sidan 'detaljvy' anropas därmed, när en prenumerant på nyhetsfeeden kallar på en feeds detaljpresentation. Med hjälp av inställningen 'kategori av feed' kan man begränsa visade nyhetsposter. <br />Feedens URL kan anges i fältet 'URL-titel till feed' t.ex.  /newsnfacts.rss. Titeln skall endast betstå av bokstäver och siffror (a-z, A-Z, 0-9).";
$lang["quickhelp_edit_languageset"]      = "För att möjliggöra språkomställning av nyheter i portalen måste dessa grupperas i s.k. lanquagesets. Med hjälp av denna information kan Kajona möjliggöra omställning av språk.";
$lang["quickhelp_edit_news"]             = "Vid redigering eller skapande av en nyhet kan dess grunddata anges. Till dessa hör bl.a. nyhetens titel. Vidare kan olika datum definieras:<ul><li>Startdatum: Från detta datum visas nyheten på portalen</li><li>Slutdatum: Från detta datum försvinner nyheten komplett från portalen, också från arkivet.</li><li>Arkivdatum: från detta datum flyttas nyheten till arkivvyn.</li></ul><p>I tillägg kan tillhörigheten till olika nyhetskategorier anges.</p>";
$lang["quickhelp_list"]                  = "Alla nyheter och kategorier listas i denna vy.<br />I första delen listas kategorierna, i andra de enskilda nyheterna.<br />För att visa alla nyheter i en kategori klickar man på  '".$lang["kat_anzeigen"]."'.<br />Nyhetslistan innehåller titeln på nyheten, antal träffar, start-, slut och arkivdatum.";
$lang["quickhelp_list_feed"]             = "I denna del kan RSS-feederna hanteras. Listan innehåller alla RSS-feeds som finns i systemet.";
$lang["quickhelp_new_category"]          = "För närvarande kan endast en titel anges för en kategori.";
$lang["quickhelp_new_feed"]              = "Med hjälp av detta formulär kan egenskaperna för aktuell eller ny feed ändras.<br />Sidan 'detaljvy' anropas därmed, när en prenumerant på nyhetsfeeden kallar på en feeds detaljpresentation. Med hjälp av inställningen 'kategori av feed' kan man begränsa visade nyhetsposter. <br />Feedens URL kan anges i fältet 'URL-titel till feed' t.ex.  /newsnfacts.rss. Titeln skall endast betstå av bokstäver och siffror (a-z, A-Z, 0-9).";
$lang["quickhelp_new_news"]              = "Vid redigering eller skapande av en nyhet kan dess grunddata anges. Till dessa hör bl.a. nyhetens titel. Vidare kan olika datum definieras:<ul><li>Startdatum: Från detta datum visas nyheten på portalen</li><li>Slutdatum: Från detta datum försvinner nyheten komplett från portalen, också från arkivet.</li><li>Arkivdatum: från detta datum flyttas nyheten till arkivvyn.</li></ul><p>I tillägg kan tillhörigheten till olika nyhetskategorier anges.</p>";
$lang["required_feed_page"]              = "Detaljsida";
$lang["required_feed_title"]             = "Feedtitel";
$lang["required_feed_urltitle"]          = "URL-feedtitel";
$lang["required_news_cat_title"]         = "Kategorititel";
$lang["required_news_title"]             = "Nyhetstitel";
$lang["speichern"]                       = "Spara";

$lang["permissions_header"]              = array(0 => "View", 1 => "Edit", 2 => "Delete", 3 => "Permissions", 4 => "", 5 => "Feeds", 6 => "Rating", 7 => "", 8 => "");