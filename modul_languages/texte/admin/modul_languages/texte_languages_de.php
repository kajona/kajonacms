<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	texte_languages_de.php																				*
* 	Admin language file for module_languages															*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

// --- Module texts -------------------------------------------------------------------------------------
$text["modul_titel"]				= "Sprachen";
$text["modul_rechte"]               = "Modul-Rechte";
$text["modul_liste"]                = "Liste";
$text["modul_anlegen"]              = "Sprache anlegen";
$text["modul_assign"]               = "Inhalte zuweisen";
$text["assign_hint"]                = "Mit dieser Aktion können alle Seiteneigenschaften sowie alle Seitenelemente, die bisher keiner Sprache
                                       zugeordnet wurde (und somit in der Administration nicht sichtbar sind) der aktuellen Default-Sprache zugeordnet
                                       werden. Gibt es bereits Seiteneigenschaften in dieser Sprache, so wird der betreffende, nicht zugeordnete
                                       Datensatz übersprungen.";

$text["assign_work"]                = "Jetzt zuweisen";
$text["assign_pagesprop_true"]      = "Zuweisen der Seiteneigenschaten war erfolgreich";
$text["assign_pagesprop_false"]     = "Beim Zuweisen der Seiteneigenschaften ist ein Fehler aufgetreten!";
$text["assign_pagesel_true"]        = "Zuweisen der Seitenelemente war erfolgreich";
$text["assign_pagesel_false"]       = "Beim Zuweisen der Seitenelemente ist ein Fehler aufgetreten!";
$text["assign_no_default"]          = "Bitte zuerst eine Default-Sprache anlegen!";

$text["liste_leer"]                 = "Keine Sprachen angelegt";

$text["fehler_recht"]               = "Keine ausreichenden Rechte um diese Aktion durchzuführen";

$text["lang_aktiv"]                 = "Aktiv";
$text["lang_inaktiv"]               = "Inaktiv";

$text["language_bearbeiten"]        = "Sprache bearbeiten";
$text["language_loeschen"]          = "Sprache löschen";
$text["language_status"]            = "Status ändern (ist ";
$text["language_rechte"]            = "Rechte";
$text["language_isDefault"]         = "Standardsprache";

$text["lang_save"]                  = "Speichern";
$text["language_name"]              = "Sprache:";

$text["default"]                    = "Ja";
$text["nondefault"]                 = "Nein";
$text["language_default"]           = "Standardsprache:";

$text["lang_de"]                    = "Deutsch";
$text["lang_en"]                    = "Englisch";
$text["lang_fr"]                    = "Französisch";
$text["lang_ru"]                    = "Russisch";

$text["language_existing"]          = "Die Sprache wurde bereits angelegt";

$text["delete_question"]            = ": Diese Sprache wirklich löschen?<br />";
$text["delete_link"]                = "Löschen";


// --- Quickhelp texts ----------------------------------------------------------------------------------

$text["quickhelp_list"]             = "Sämtliche Sprachen die im System angelegt wurden finden sich in dieser Liste wieder.
                                       Um die default-Sprache des Systems zu verändern, müssen die Grunddaten der zukünftigen
                                       Standardsprache bearbeitet werden.";
$text["quickhelp_newLanguage"]      = "Um eine neue Sprache anzulegen oder eine bereits vorhanden Sprache zu bearbeiten, können in diesem Formular
                                       die Daten der Sprache erfasst werden. <br />Die Liste der Sprachen lässt sich hier nicht erweitern, dies muss
                                       durch einen Administrator erfolgen.";
$text["quickhelp_editLanguage"]     = $text["quickhelp_newLanguage"];

?>