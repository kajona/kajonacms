<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	texte_filemanager_en.php																			*
* 	Admin language file for module_filemanager															*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                                    *
********************************************************************************************************/

// --- Module texts -------------------------------------------------------------------------------------

$text["modul_titel"]				= "Filemanager";
$text["modul_rechte"]				= "Module permissions";
$text["modul_liste"]				= "List";
$text["modul_neu"]					= "Create repository";

$text["permissions_header"]         = array(
            							0 => "View",
            							1 => "Edit",
            							2 => "Delete",
            							3 => "Permissions",
            							4 => "Upload",		//Recht1
            							5 => "Manage",	//Recht2
            							6 => "",
            							7 => "",
            							8 => ""
            							);

$text["repo_oeffnen"]				= "Show folder";
$text["repo_bearbeiten"]			= "Define properties";
$text["repo_bearbeiten_fehler"]		= "An error occured while saving the repository";
$text["repo_rechte"]				= "Edit permissions";
$text["repo_loeschen"]				= "Delete repository";
$text["repo_loeschen_frage"]		= " : really delete repository?<br />";
$text["repo_loeschen_link"]			= "Delete";

$text["ordner_anlegen"]				= "Create folder";
$text["ordner_anlegen_erfolg"]		= "Folder was created successfully";
$text["ordner_anlegen_fehler"]		= "An error occured while saving the folder";
$text["ordner_anlegen_fehler_l"]	= "The folder already exists";
$text["ordner_loeschen"]			= "Delete folder";
$text["ordner_loeschen_frage"]		= ": really delete folder?";
$text["ordner_loeschen_link"]		= "Delete";
$text["ordner_loeschen_fehler_l"]	= "The folder is not empty!";
$text["ordner_loeschen_fehler"]		= "An error occured while deleting folder!";
$text["ordner_loeschen_erfolg"]		= "The folder was deleted successfully";
$text["ordner_hoch"]				= "One level up";

$text["datei_loeschen"]				= "Delete file";
$text["datei_loeschen_frage"]		= " : really delete file?<br />Please keep in mind about interdependencies with other modules!";
$text["datei_loeschen_link"]		= "Delete";
$text["datei_loeschen_erfolg"]		= "File was deleted successfully";
$text["datei_loeschen_fehler"]		= "An error occured while deleting the file";
$text["datei_umbenennen"]			= "Rename file";
$text["datei_umbenennen_hinweis"]	= "Please keep in mind, that a renaming could interact with modules using this file e.g. the galleries";
$text["datei_umbenennen_erfolg"]	= "The file was renamed successfully";
$text["datei_umbenennen_fehler_z"]	= "A file with the given file name already exisits!";
$text["datei_umbenennen_fehler"]	= "An error occured while renaming file!";
$text["datei_upload"]				= "Upload file";
$text["datei_oeffnen"]				= "Show file";
$text["datei_erstell"]				= "Date of creation:";
$text["datei_bearbeit"]				= "Date of last modification:";
$text["datei_zugriff"]				= "Date of last access:";
$text["datei_pfad"]					= "Path:";
$text["datei_typ"]					= "File type:";
$text["datei_groesse"]				= "File size:";
$text["bild_groesse"]				= "Image size:";
$text["bild_vorschau"]				= "Preview:";

$text["upload_erfolg"]				= "File was uploaded successfully<br />";
$text["upload_fehler"]				= "An error occured while uploading file<br />";
$text["upload_fehler_filter"]		= "The uploaded file type is not allowed<br />";

$text["fehler_recht"]				= "Not enough permissions to perform this action";
$text["liste_leer"]					= "No repositories available";

$text["browser"]					= "Open folder browser";

$text["submit"]                     = "Save";
$text["filemanager_name"]           = "Name:";
$text["filemanager_path"]           = "Path:";
$text["filemanager_upload_filter"]  = "Upload-filter:";
$text["filemanager_upload_filter_h"]= "A comma-separated list of file types allowed to be uploaded (e.g. &quot;.jpg,.gif,.png&quot;) ";
$text["filemanager_view_filter"]    = "View-Filter:";
$text["filemanager_view_filter_h"]  = "A comma-separated list of file types allowed to be shown (e.g. &quot;.jpg,.gif,.png&quot;)";

$text["fehler_repo"]                = "An error occured while saving the repository. Does the folder really exists?";

$text["foldertitle"]                = "Path: ";
$text["nrfoldertitle"]              = "Number of folders: ";
$text["nrfilestitle"]               = "Number of files: ";

$text["datei_name"]                 = "File name:";
$text["rename"]                     = "Rename";
$text["ordner_name"]                = "Folder name:";
$text["anlegen"]                    = "Create folder";
$text["filemanager_upload"]         = "Upload file:";
$text["max_size"]                   = "Maximal file size: ";
$text["upload"]                     = "Upload";
$text["add_upload_field"]           = "Add additional upload field";

$text["_filemanager_ordner_groesse_"] = "Calculate size:";
$text["_filemanager_ordner_groesse_hint"] = "Activates or deactivates the recursive calculation of the size of a folder in the filemanager.
                                            Deep folder structures can lead to performance problems.";

$text["required_filemanager_name"]  = "Name";
$text["required_filemanager_path"]  = "Path";

$text["useFile"]                    = "Apply";


// --- Quickhelp texts ----------------------------------------------------------------------------------

$text["quickhelp_list"]             = "The filemanager is, as the name says, the integrated filemanager of Kajona. You can use it to upload files to the system
                                       or to rename or delete exisiting files.<br />
                                       In this view, you find a list of created repositoryies. Every repository can be configured to meet different requirements.";
$text["quickhelp_newRepo"]          = "When creating or editing a repository, you can set up the different properties.<br />
                                       This includes the name, the path on the filesystem, an upload filter to provide a white-list of filetypes allowed for uploads
                                       and a view-filter to define a white-list of file types to be shown in the lists.";
$text["quickhelp_editRepo"]         = $text["quickhelp_newRepo"];
$text["quickhelp_openFolder"]       = "All files and folders contained by the current folder are listed in this view (the list can be limited due to filters set up
                                       for the current repository). Additionaly, files can be uploaded, edited or deleted. It is also possible to create folders.";
$text["quickhelp_newFolder"]        = $text["quickhelp_openFolder"];
$text["quickhelp_imageDetail"]      = $text["quickhelp_openFolder"];
$text["quickhelp_deleteFile"]       = $text["quickhelp_openFolder"];
$text["quickhelp_deleteFolder"]     = $text["quickhelp_openFolder"];
?>