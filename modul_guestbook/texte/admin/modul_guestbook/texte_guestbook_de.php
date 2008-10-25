<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	texte_guestbook_de.php																				*
* 	Admin language file for module_guestbook															*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

// --- Module texts -------------------------------------------------------------------------------------
$text["modul_rechte"]				= "Modul-Rechte";
$text["modul_liste"]				= "Liste";
$text["modul_anlegen"]				= "Gästebuch anlegen";
$text["modul_titel"]				= "Gästebücher";

$text["permissions_header"]         = array(
            							0 => "Anzeigen",
            							1 => "Bearbeiten",
            							2 => "Löschen",
            							3 => "Rechte",
            							4 => "Eintragen",         //recht1
            							5 => "",
            							6 => "",
            							7 => "",
            							8 => ""
            							);

$text["gaestebuch_anzeigen"]		= "Gästebuch anzeigen";
$text["gaestebuch_bearbeiten"]		= "Gästebuch bearbeiten";
$text["gaestebuch_loeschen"]		= "Gästebuch löschen";
$text["gaestebuch_rechte"]			= "Rechte bearbeiten";
$text["gaestebuch_listeleer"]		= "Kein Gästebuch angelegt";

$text["gaestebuch_modus_0"]			= "Freischaltung erforderlich";
$text["gaestebuch_modus_1"]			= "Keine Freischaltung erforderlich";

$text["fehler_recht"]				= "Keine ausreichenden Rechte um diese Aktion durchzuführen";
$text["loeschen_frage"]				= " : Gästebuch mit allen Einträgen wirklich löschen?<br />";
$text["loeschen_link"]				= "Löschen";

$text["loeschen_post"]				= "Löschen";
$text["post_liste_leer"]			= "Keine Einträge vorhanden";
$text["post_loeschen_frage"]		= " : Eintrag wirklich löschen?<br />";
$text["post_loeschen_link"]			= "Löschen";
$text["edit_post"]                  = "Bearbeiten";
$text["post_text"]                  = "Nachricht:";

$text["guestbook_title"]            = "Titel:";
$text["guestbook_moderated"]        = "Freischaltungsmodus:";
$text["speichern"]                  = "Speichern";

$text["required_guestbook_title"]   = "Titel";

$text["_guestbook_suche_seite_"]    = "Treffer-Seite:";
$text["_guestbook_suche_seite_hint"]= "Auf diese Seite verlinken die Treffer der Suche, die in Gästebuch-Posts gefunden wurden.";


// --- Quickhelp texts ----------------------------------------------------------------------------------

$text["quickhelp_list"]             = "Alle angelegten Gästebücher finde Sie in dieser Liste.";
$text["quickhelp_newGuestbook"]     = "Beim Anlegen oder Bearbeiten eines Gästebuchs kann für dieses ein Titel vergeben werden.
                                       Ebenso kann der Freischaltmodus aktiviert oder deaktivert. Ist dieser aktiviert, werden neue
                                       Einträge per default deaktiviert abgespeischert. Damit diese im Portal erscheinen, müssen diese dann
                                       durch einen Admin oder Redakteur freigegeben werden.<br /><br />
                                       Hinweis: Sollen sich Gäste in das Gästebuch eintragen dürfen, so benötigen diese das Recht 'Eintragen'!";
$text["quickhelp_editGuestbook"]    = $text["quickhelp_newGuestbook"];
$text["quickhelp_viewGuestbook"]    = "In dieser Liste werden alle Einträge eines Gästebuchs angezeigt. Diese können dann gelöscht,
                                       aktiviert oder deaktiviert werden.";
$text["quickhelp_deletePost"]       = "Soll ein Post gelöscht werden, so muss dies hier bestätigt werden.";
?>