<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                         *
********************************************************************************************************/

// --- Module texts -------------------------------------------------------------------------------------

$lang["modul_titel"]				= "Bildergalerien";
$lang["modul_rechte"]				= "Modul-Rechte";
$lang["modul_liste"]				= "Liste";
$lang["galerie_neu"]				= "Neue Galerie";
$lang["browser"]					= "Ordnerbrowser";
$lang["gallery_masssync"]           = "Alle synchronisieren";

$lang["galerie_liste_leer"]			= "Keine Galerien angelegt";
$lang["fehler_gal"]					= "Fehler beim Anlegen der Galerie";
$lang["fehler_gal_bearbeiten"]		= "Fehler beim Bearbeiten der Gallerie";
$lang["galerie_anzeigen"]			= "Galerie anzeigen";
$lang["galerie_syncro"]				= "Galerie synchronisieren";
$lang["galerie_bearbeiten"]			= "Galerie bearbeiten";
$lang["galerie_loeschen_frage"]		= "Möchten Sie die Galerie &quot;<b>%%element_name%%</b>&quot; wirklich löschen?<br />Dabei werden alle hinterlegten Daten gelöscht!";
$lang["galerie_loeschen_erfolg"]	= "Löschen der Galerie erfolgreich";
$lang["galerie_loeschen_fehler"]	= "Fehler beim Loeschen der Galerie";
$lang["galerie_rechte"]				= "Rechte bearbeiten";

$lang["permissions_header"]         = array(0 => "Anzeigen", 1 => "Bearbeiten", 2 => "Löschen", 3 => "Rechte", 4 => "Syncro",  5 => "Rating", 6 => "", 7 => "", 8 => "");

$lang["gallery_title"]              = "Titel:";
$lang["gallery_path"]               = "Pfad:";
$lang["pic_name"]                   = "Name:";
$lang["pic_description"]            = "Beschreibung:";
$lang["pic_size"]                   = "Bildgröße: ";
$lang["pic_size_pixel"]             = " Pixel";
$lang["pic_filename"]               = "Dateiname: ";
$lang["pic_folder"]                 = "Ordner: ";
$lang["pic_subtitle"]               = "Untertitel:";

$lang["speichern"]                  = "Speichern";

$lang["liste_bilder_leer"]			= "Keine Bilder vorhanden";

$lang["syncro_ende"]				= "Synchronisierung erfolgreich abgeschlossen<br />";

$lang["sortierung_hoch"]			= "Eine Position nach oben";
$lang["sortierung_runter"]			= "Eine Position nach unten";
$lang["ordner_oeffnen"]				= "Ordner anzeigen";
$lang["ordner_bearbeiten"]			= "Ordner bearbeiten";
$lang["ordner_hoch"]				= "Eine Ebene nach oben springen";
$lang["bild_bearbeiten"]			= "Bild bearbeiten";
$lang["bild_rechte"]				= "Rechte bearbeiten";
$lang["bild_speichern_fehler"]		= "Fehler beim Speichern des Bildes";
$lang["image_properties"]           = "Eigenschaften des Bildes bearbeiten";



$lang["fehler_recht"]				= "Keine ausreichende Rechte";

$lang["_bildergalerie_suche_seite_"]         = "Treffer-Seite:";
$lang["_bildergalerie_suche_seite_hint"]     = "Auf dieser Seite erfolgt die Detailansicht der Bilder, die in der Suche gefunden wurden.";

$lang["_bildergalerie_bildtypen_"]      = "Bildtypen:";
$lang["_bildergalerie_bildtypen_hint"]  = "Kommaseparierte Liste an Bildtypen, die die Bildergalerie verarbeiten soll.";

$lang["required_gallery_title"]     = "Titel";
$lang["required_gallery_path"]      = "Pfad";
$lang["required_pic_name"]          = "Titel";

$lang["sync_add"]                   = "Hinzugefügt: ";
$lang["sync_del"]                   = " Gelöscht: ";
$lang["sync_upd"]                   = " Aktualisiert: ";


// --- Quickhelp texts ----------------------------------------------------------------------------------

$lang["quickhelp_newGallery"]       = "Die Grunddaten einer Galerie werden mit Hilfe dieses Formulars festgelegt.<br />Hierzu gehören der Titel der Galerie, sowie der entsprechende Start-Pfad der Galerie im Dateisystem.";
$lang["quickhelp_editGallery"]      = "Die Grunddaten einer Galerie werden mit Hilfe dieses Formulars festgelegt.<br />Hierzu gehören der Titel der Galerie, sowie der entsprechende Start-Pfad der Galerie im Dateisystem.";
$lang["quickhelp_list"]             = "Alle bereits angelegten Galerien werden in dieser Liste angezeigt.<br />Mit Hilfe der Aktion 'Synchronisieren' kann der Datenbestand des Dateisystems mit dem der Datenbank synchronisiert werden. Neue Dateien werden in die Datenbank aufgenommen, gelöschte aus dieser entfernt, ebenso werden im Dateisystem veränderte Dateien in der Datenbank aktualisiert.";
$lang["quickhelp_showGallery"]      = "Dateien und Ordner, die sich in der zuvor gewählten Galerie befinden werden hier aufgelistet.";
$lang["quickhelp_editImage"]        = "Ein vorhandenes Bild oder ein vorhandener Ordner können hier um weitere Informationen ergänzt werden.";

?>