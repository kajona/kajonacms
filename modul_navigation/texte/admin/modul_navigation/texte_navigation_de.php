<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	texte_navigation_de.php																				*
* 	Admin language file for module_navigation            												*
*																										*
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                      *
********************************************************************************************************/

// --- Module texts -------------------------------------------------------------------------------------
$text["modul_rechte"]				= "Modul-Rechte";
$text["modul_liste"]				= "Liste";
$text["modul_anlegen"]				= "Neue Navigation";
$text["modul_anlegenpunkt"]			= "Neuer Unterpunkt";
$text["modul_titel"]				= "Navigationen";
$text["fehler_recht"]				= "Keine ausreichenden Rechte um diese Aktion durchzuführen";
$text["browser"]					= "Browser öffnen";
$text["liste_leer"]					= "Keine Navigation angelegt";


$text["navigation_bearbeiten"]		= "Navigation bearbeiten";
$text["navigation_anzeigen"]		= "Unterpunkte anzeigen";
$text["navigation_loeschen"]		= "Navigation löschen";
$text["navigation_loeschen_frage"]	= " : Navigation mit allen Unterpunkten löschen?";
$text["navigation_loeschen_link"]	= "Löschen";
$text["navigation_rechte"]			= "Rechte bearbeiten";
$text["navigation_erfolg"]			= "Navigation erfolgreich angelegt";
$text["navigation_erf_loeschen"]	= "Navigation erfolgreich gelöscht";

$text["navigation_ebene"]			= "Eine Ebene nach oben";

$text["navigationp_bearbeiten"]		= "Navigationspunkt bearbeiten";
$text["navigationp_anzeigen"]		= "Unterpunkte anzeigen";
$text["navigationp_hoch"]			= "Punkt nach oben verschieben";
$text["navigationp_runter"]			= "Punkt nach unten verschieben";
$text["navigationp_loeschen"]		= "Navigationspunkt löschen";
$text["navigationp_recht"]			= "Rechte bearbeiten";

$text["status_aktiv"]               = "Status ändern (ist aktiv)";
$text["status_inaktiv"]             = "Status ändern (ist inaktiv)";

$text["navigation_name"]            = "Name:";
$text["speichern"]                  = "Speichern";
$text["navigation_page_i"]          = "Interne Seite:";
$text["navigation_page_e"]          = "Externer Verweis:";
$text["navigation_target"]          = "Ziel:";
$text["navigation_image"]           = "Bild:";

$text["navigation_tagetblank"]      = "_blank (Neues Browserfenster)";
$text["navigation_tagetself"]       = "_self (Selbes Browserfenster)";

$text["required_navigation_name"]   = "Name";


// --- Quickhelp texts ----------------------------------------------------------------------------------

$text["quickhelp_list"]             = "Alle im System angelegten Navigationsbäume und dessen Unterpunkte werden in dieser Liste angezeigt.
                                       Die Sortierung der Navigationspunkte einer Ebene zueinander kann über Anklicken der Pfeil-Grafiken verändert werden.";
$text["quickhelp_newNavi"]          = "Für eine neue Navigation muss lediglich der Titel des Navigationsbaumes vergeben werden.
                                       Über die Aktion 'bearbeiten' kann dieser später wieder verändert werden.";
$text["quickhelp_editNavi"]         = $text["quickhelp_newNavi"];
$text["quickhelp_deleteNavi"]       = "Mit dieser Aktion wird eine Navigation oder ein Navigationspunkt mit allen Unterpunkten aus dem System gelöscht.
                                       Dieser Schritt kann nicht rückgängig gemacht werden.";
$text["quickhelp_newNaviPoint"]     = "Beim Anlegen oder Bearbeiten eines Navigationspunktes können dessen Eigenschaften verändert werden.
                                       Hierzu gehören der Name der Punktes, sowie die Seite, auf welche der Punkt zeigen soll. Für externe Seiten kann
                                       das Feld 'externer Verweis' verwendet werden. Soll statt des Namens ein Bild geladen werden, so kann dies unter
                                       'Bild' angegeben werden. Das Ziel gibt das spätere Link-Target an.";
$text["quickhelp_editNaviPoint"]    = $text["quickhelp_newNaviPoint"];
?>