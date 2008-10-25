<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	texte_gallery_en.php     																			*
* 	Admin language file for module_gallery																*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                         *
********************************************************************************************************/

// --- Module texts -------------------------------------------------------------------------------------

$text["modul_titel"]				= "Galleries";
$text["modul_rechte"]				= "Module permissions";
$text["modul_liste"]				= "List";
$text["galerie_neu"]				= "Create gallery";
$text["browser"]					= "Browse folders";
$text["gallery_masssync"]           = "Synchronize all";

$text["galerie_liste_leer"]			= "No galleries available";
$text["fehler_gal"]					= "An error occured while creating gallery";
$text["fehler_gal_bearbeiten"]		= "An error occured while saving gallery";
$text["galerie_anzeigen"]			= "Show gallery";
$text["galerie_syncro"]				= "Synchronize gallery";
$text["galerie_bearbeiten"]			= "Edit gallery";
$text["galerie_loeschen"]			= "Delete gallery";
$text["galerie_loeschen_frage"]		= " : really delete gallery? <br />All stored details will be deleted, too!<br />";
$text["galerie_loeschen_link"]		= "Delete";
$text["galerie_loeschen_erfolg"]	= "The gallery was deleted successfully";
$text["galerie_loeschen_fehler"]	= "An error occured while deleting gallery";
$text["galerie_rechte"]				= "Edit permissions";

$text["permissions_header"]         =  array(
            							0 => "View",
            							1 => "Edit",
            							2 => "Delete",
            							3 => "Permissions",
            							4 => "Sync",		//Recht1
            							5 => "Rating",     //right2
            							6 => "",
            							7 => "",
            							8 => ""
            							);

$text["gallery_title"]              = "Title:";
$text["gallery_path"]               = "Path:";
$text["pic_name"]                   = "Name:";
$text["pic_description"]            = "Description:";
$text["pic_size"]                   = "Picture size: ";
$text["pic_size_pixel"]             = " pixel";
$text["pic_filename"]               = "Filename: ";
$text["pic_folder"]                 = "Folder: ";
$text["pic_subtitle"]               = "Subtitle:";

$text["speichern"]                  = "Save";

$text["liste_bilder_leer"]			= "No pictures available";

$text["syncro_ende"]				= "Synchronization finished successfully<br />";

$text["sortierung_hoch"]			= "Shift one position up";
$text["sortierung_runter"]			= "Shift one position down";
$text["ordner_oeffnen"]				= "Show folder";
$text["ordner_bearbeiten"]			= "Edit folder";
$text["ordner_hoch"]				= "One level up";
$text["bild_bearbeiten"]			= "Edit picture";
$text["bild_rechte"]				= "Edit permissions";
$text["bils_speichern_fehler"]		= "An error occured while saving picture";


$text["fehler_recht"]				= "Not enough permissions to perform this action";

$text["_bildergalerie_suche_seite_"]         = "Result page:";
$text["_bildergalerie_suche_seite_hint"]     = "This page shows the list of pictures found by the search";

$text["_bildergalerie_bildtypen_"]      = "Picture-types:";
$text["_bildergalerie_bildtypen_hint"]  = "Comma-separated list of picture-types to be processed by galleries";

$text["required_gallery_title"]     = "Title";
$text["required_gallery_path"]      = "Path";
$text["required_pic_name"]          = "Title";

$text["sync_add"]                   = "Added: ";
$text["sync_del"]                   = " Deleted: ";
$text["sync_upd"]                   = " Updated: ";

// --- Quickhelp texts ----------------------------------------------------------------------------------

$text["quickhelp_newGallery"]       = "The basic data of a gallery is set up using this form.<br />
                                       This includes the title of the gallery an the corresponding start-folder on the filesystem.";
$text["quickhelp_editGallery"]      = $text["quickhelp_newGallery"];
$text["quickhelp_list"]             = "All existing galleries are included in this list.<br />
                                       By using the action 'Synchronize gallery', the filesystem will be synchronized with the database.
                                       New files will be added to the database, deleted files will be removed and modified files will be updated
                                       to the database.";
$text["quickhelp_showGallery"]      = "Files and folders contained by the gallery selected before are listed in this view.";
$text["quickhelp_editImage"]        = "An existing image or folder can be extended by a set of additional informations.";

?>