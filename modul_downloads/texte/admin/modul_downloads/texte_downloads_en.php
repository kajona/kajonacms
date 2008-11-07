<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

// --- Module texts -------------------------------------------------------------------------------------

$text["modul_titel"]				= "Downloads";
$text["modul_rechte"]				= "Module permissions";
$text["modul_liste"]				= "List";
$text["archiv_neu"]					= "Create archive";
$text["logbuch"]					= "Logfile";
$text["browser"]					= "Browse folders";
$text["archive_masssync"]           = "Synchronize all";

$text["permissions_header"]         = array(
                    					0 => "View",
                    					1 => "Edit",
                    					2 => "Delete",
                    					3 => "Permissions",
                    					4 => "Sync",		//Recht1
                    					5 => "Download",	//Recht2
                    					6 => "Logs",		//Recht3
                    					7 => "Rating",      //Recht4
                    					8 => ""
                    					);


$text["logbuch_loeschen_link"]		= "Flush logfile";

$text["archiv_anzeigen"]			= "Open archive";
$text["archiv_bearbeiten"]			= "Edit archive";
$text["archiv_loeschen_frage"]		= " : really delete archive? <br /> All stored details will be deleted!<br />";
$text["archiv_loeschen_link"]		= "Delete";
$text["archiv_loeschen_erfolg"]		= "The archive was delete successfully";
$text["archiv_loeschen_fehler"]		= "Because of missing permissions the archive couldn't be deleted";
$text["archiv_rechte"]				= "Edit permissions";
$text["archiv_syncro"]				= "Synchronize archive";
$text["syncro_ende"]				= "Synchronization finished successfully<br />";

$text["archive_title"]              = "Title:";
$text["archive_path"]               = "Path:";

$text["speichern"]                  = "Save";

$text["downloads_name"]             = "Name:";
$text["downloads_description"]      = "Description:";
$text["downloads_max_kb"]           = "Max downloadspeed in kb/s (0=unlimited):";

$text["sortierung_hoch"]			= "Shift one position up";
$text["sortierung_runter"]			= "Shift one position down";

$text["ordner_oeffnen"]				= "Show folder";
$text["ordner_hoch"]				= "One level up";

$text["datei_bearbeiten"]			= "Edit details";
$text["datei_speichern_fehler"]		= "An error occured while saving details";

$text["fehler_recht"]				= "Not enough permissions to perform this action";

$text["liste_leer_archive"]			= "No archives available";
$text["liste_leer_dl"]				= "No downloads available";

$text["header_id"]                  = "Download-ID";
$text["header_date"]                = "Date";
$text["header_file"]                = "File";
$text["header_user"]                = "User";
$text["header_ip"]                  = "IP/Hostname";
$text["header_amount"]              = "Amount";

$text["stats_title"]                = "Downloads";
$text["stats_toptitle"]             = "Top downloads";

$text["sync_add"]                   = "Added: ";
$text["sync_del"]                   = " Deleted: ";
$text["sync_upd"]                   = " Updated: ";



$text["datum"]                      = "Date:";
$text["hint_datum"]                 = "Deletes all logbook entries recorded before the given date.";

$text["_downloads_suche_seite_"]         = "Result page:";
$text["_downloads_suche_seite_hint"]     = "This page shows the list of downloads found by the search";

$text["required_archive_title"]     = "Title of the archive";
$text["required_archive_path"]      = "Path of the archive";
$text["required_downloads_name"]    = "Name";
$text["required_downloads_max_kb"]  = "Downloadspeed";


// --- Quickhelp texts ----------------------------------------------------------------------------------

$text["quickhelp_newArchive"]       = "The basic data of a archive is captured by this form.<br />
                                       This includes the title and the corresponding start-path on the filesystem.";
$text["quickhelp_editArchive"]      = $text["quickhelp_newArchive"];
$text["quickhelp_list"]             = "All set up archives are included in this list.<br />
                                       Using the action 'synchronize' the files on the filesystem will be synchronized with the database records.
                                       New files on the filesystem will be added to the database, deleted files will be removed from the database. Modified files
                                       will be updated to the database.";
$text["quickhelp_showArchive"]      = "Files and folders contained by the before selected archive are listed in this view.";
$text["quickhelp_editFile"]         = "A file or folder could be extended by a set of additional informations.<br />
                                       When editing a file, a maximal download speed can be defined. This limits the download speed when users are
                                       downloading this file in the portal.";
?>