<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                         *
********************************************************************************************************/

// --- Module texts -------------------------------------------------------------------------------------

$text["modul_titel"]				= "Bildergalerien";
$text["modul_rechte"]				= "Modul-Rechte";
$text["modul_liste"]				= "Liste";
$text["galerie_neu"]				= "Neue Galerie";
$text["browser"]					= "Ordnerbrowser";
$text["gallery_masssync"]           = "Alle synchronisieren";

$text["galerie_liste_leer"]			= "Keine Galerien angelegt";
$text["fehler_gal"]					= "Fehler beim Anlegen der Galerie";
$text["fehler_gal_bearbeiten"]		= "Fehler beim Bearbeiten der Gallerie";
$text["galerie_anzeigen"]			= "Galerie anzeigen";
$text["galerie_syncro"]				= "Galerie synchronisieren";
$text["galerie_bearbeiten"]			= "Galerie bearbeiten";
$text["galerie_loeschen_frage"]		= " : Galerie wirklich löschen? <br />Dabei werden alle hinterlegten Daten gelöscht!<br />";
$text["galerie_loeschen_link"]		= "Löschen";
$text["galerie_loeschen_erfolg"]	= "Löschen der Galerie erfolgreich";
$text["galerie_loeschen_fehler"]	= "Fehler beim Loeschen der Galerie";
$text["galerie_rechte"]				= "Rechte bearbeiten";

$text["permissions_header"]         = array(
            							0 => "Anzeigen",
            							1 => "Bearbeiten",
            							2 => "Löschen",
            							3 => "Rechte",
            							4 => "Syncro",		//Recht1
            							5 => "Rating",       //Recht2
            							6 => "",
            							7 => "",
            							8 => ""
            							);

$text["gallery_title"]              = "Titel:";
$text["gallery_path"]               = "Pfad:";
$text["pic_name"]                   = "Name:";
$text["pic_description"]            = "Beschreibung:";
$text["pic_size"]                   = "Bildgröße: ";
$text["pic_size_pixel"]             = " Pixel";
$text["pic_filename"]               = "Dateiname: ";
$text["pic_folder"]                 = "Ordner: ";
$text["pic_subtitle"]               = "Untertitel:";

$text["speichern"]                  = "Speichern";

$text["liste_bilder_leer"]			= "Keine Bilder vorhanden";

$text["syncro_ende"]				= "Synchronisierung erfolgreich abgeschlossen<br />";

$text["sortierung_hoch"]			= "Eine Position nach oben";
$text["sortierung_runter"]			= "Eine Position nach unten";
$text["ordner_oeffnen"]				= "Ordner anzeigen";
$text["ordner_bearbeiten"]			= "Ordner bearbeiten";
$text["ordner_hoch"]				= "Eine Ebene nach oben springen";
$text["bild_bearbeiten"]			= "Bild bearbeiten";
$text["bild_rechte"]				= "Rechte bearbeiten";
$text["bils_speichern_fehler"]		= "Fehler beim Speichern des Bildes";


$text["fehler_recht"]				= "Keine ausreichende Rechte";

$text["_bildergalerie_suche_seite_"]         = "Treffer-Seite:";
$text["_bildergalerie_suche_seite_hint"]     = "Auf dieser Seite erfolgt die Detailansicht der Bilder, die in der Suche gefunden wurden.";

$text["_bildergalerie_bildtypen_"]      = "Bildtypen:";
$text["_bildergalerie_bildtypen_hint"]  = "Kommaseparierte Liste an Bildtypen, die die Bildergalerie verarbeiten soll.";

$text["required_gallery_title"]     = "Titel";
$text["required_gallery_path"]      = "Pfad";
$text["required_pic_name"]          = "Titel";

$text["sync_add"]                   = "Hinzugefügt: ";
$text["sync_del"]                   = " Gelöscht: ";
$text["sync_upd"]                   = " Aktualisiert: ";


// --- Quickhelp texts ----------------------------------------------------------------------------------

$text["quickhelp_newGallery"]       = "Die Grunddaten einer Galerie werden mit Hilfe dieses Formulars festgelegt.<br />
                                       Hierzu gehören der Titel der Galerie, sowie der entsprechende Start-Pfad der Galerie
                                       im Dateisystem.";
$text["quickhelp_editGallery"]      = $text["quickhelp_newGallery"];
$text["quickhelp_list"]             = "Alle bereits angelegten Galerien werden in dieser Liste angezeigt.<br />
                                       Mit Hilfe der Aktion 'Synchronisieren' kann der Datenbestand des Dateisystems mit dem der
                                       Datenbank synchronisiert werden. Neue Dateien werden in die Datenbank aufgenommen,
                                       gelöschte aus dieser entfernt, ebenso werden im Dateisystem veränderte Dateien in der
                                       Datenbank aktualisiert.";
$text["quickhelp_showGallery"]      = "Dateien und Ordner, die sich in der zuvor gewählten Galerie befinden werden hier aufgelistet.";
$text["quickhelp_editImage"]        = "Ein vorhandenes Bild oder ein vorhandener Ordner können hier um weitere Informationen ergänzt werden.";

?>