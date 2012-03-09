<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: lang_gallery_en.php 3952 2011-06-26 12:13:25Z sidler $					    *
********************************************************************************************************/
//Edited with Kajona Language Editor GUI, see www.kajona.de and www.mulchprod.de for more information
//Kajona Language Editor Core Build 337


$lang["_mediamanager_default_filesrepoid_"] = "Default file-repository:";
$lang["_mediamanager_default_imagesrepoid_"] = "Default images-respository:";
$lang["_gallery_search_resultpage_"]     = "Result page:";
$lang["_gallery_search_resultpage_hint"] = "This page shows the list of pictures found by the search";

$lang["modul_titel"]                     = "Mediamananger";
$lang["actionNew"]                       = "New repository";
$lang["module_action_new"]                       = "New repository";
$lang["actionMasssync"]                = "Synchronize all";
$lang["actionOpenFolder"]                    = "Show folder";

$lang["sync_add"]                        = "Added: ";
$lang["sync_del"]                        = " Deleted: ";
$lang["sync_end"]                     = "Synchronization finished successfully<br />";

$lang["folder_new_dialogHeader"]     = "Create new folder";
$lang["file_delete_error"]           = "Error deleting the file";
$lang["file_delete_success"]           = "File deleted succesfully";
$lang["folder_create_success"]           = "Folder created successfully";
$lang["folder_create_error"]           = "Error creating the folder";
$lang["delete_question"]          = "Do you really want to delete the file &quot;<b>%%element_name%%</b>&quot;?<br />All stored details, even in other repositories, will be deleted, too!";

$lang["form_file_name"] = "Title:";
$lang["form_file_description"] = "Description:";
$lang["form_file_subtitle"] = "Subtitle:";


$lang["form_repo_uploadFilter"]       = "Upload-filter:";
$lang["filemanager_upload_filter_h"]     = "A comma-separated list of file types allowed to be uploaded (e.g. &quot;.jpg,.gif,.png&quot;) ";
$lang["form_repo_viewFilter"]         = "View-Filter:";
$lang["filemanager_view_filter_h"]       = "A comma-separated list of file types allowed to be shown (e.g. &quot;.jpg,.gif,.png&quot;)";


$lang["upload_erfolg"]                   = "File was uploaded successfully<br />";
$lang["upload_fehler"]                   = "An error occured while uploading file<br />";
$lang["upload_fehler_filter"]            = "The uploaded file type is not allowed<br />";
$lang["upload_multiple_cancel"]          = "Cancel";
$lang["upload_multiple_dialogHeader"]    = "Upload files";
$lang["upload_multiple_errorFilesize"]   = "The marked files can't be uploaded due to their file size.<br />The maximum file size is:";
$lang["upload_multiple_errorFlash"]      = "Please install <a href=\"http://get.adobe.com/en/flashplayer/\" target=\"_blank\">Adobe Flash Player</a> >= Version 9 to use the comfortable file upload.<br />Alternatively you can use \"Filemanager\" in the top navigation.";
$lang["upload_multiple_pleaseWait"]      = "Please wait...";
$lang["upload_multiple_totalFilesAndSize"] = "file(s) with a total of";
$lang["upload_multiple_uploadFiles"]     = "Upload file(s)";
$lang["upload_multiple_warningNotComplete"] = "The file upload is still running!\\nDo you really want to abort it?";
$lang["upload_submit"]                   = "Upload";

$lang["xml_cropping_success"]            = "Cropping successful";
$lang["xml_rotate_success"]              = "Rotating successfull";
$lang["xmlupload_error_copyUpload"]      = "Error while copying the file on the server";
$lang["xmlupload_error_filter"]          = "Filetyp not allowed in current filter";
$lang["xmlupload_error_notWritable"]     = "Folder not writable";
$lang["xmlupload_success"]               = "Upload successfull";

$lang["mediamanager_upload"] = "Select file:";
$lang["actionEditImage"] = "Edit image";

$lang["image_dimensions"] = "Image dimensions:";
$lang["file_size"] = "Filesize:";
$lang["file_editdate"] = "Last modified:";

$lang["overview"]                      = "Overview";

////editable entries
//$lang["_gallery_imagetypes_"]            = "Picture-types:";
//$lang["_gallery_imagetypes_hint"]        = "Comma-separated list of picture-types to be processed by galleries";
//$lang["_gallery_search_resultpage_"]     = "Result page:";
//$lang["_gallery_search_resultpage_hint"] = "This page shows the list of pictures found by the search";
//$lang["bild_bearbeiten"]                 = "Edit picture";
//$lang["bild_speichern_fehler"]           = "An error occured while saving picture";
//$lang["datei_loeschen_frage"]            = "Do you really want to delete the picture &quot;<b>%%element_name%%</b>&quot;?";
//$lang["fehler_gal"]                      = "An error occured while creating gallery";
//$lang["fehler_gal_bearbeiten"]           = "An error occured while saving gallery";
//$lang["galerie_anzeigen"]                = "Show gallery";
//$lang["galerie_bearbeiten"]              = "Edit gallery";
//$lang["galerie_liste_leer"]              = "No galleries available";
//$lang["galerie_loeschen_erfolg"]         = "The gallery was deleted successfully";
//$lang["galerie_loeschen_fehler"]         = "An error occured while deleting gallery";
//$lang["galerie_loeschen_frage"]          = "Do you really want to delete the gallery &quot;<b>%%element_name%%</b>&quot;?<br />All stored details will be deleted, too!";
//$lang["galerie_neu"]                     = "Create gallery";
//$lang["galerie_syncro"]                  = "Synchronize gallery";
//$lang["hideSyncDialog"]                  = "Close";
//$lang["image_properties"]                = "Edit basic properties";
//$lang["liste_bilder_leer"]               = "No pictures available";
//$lang["ordner_bearbeiten"]               = "Edit folder";
//$lang["ordner_oeffnen"]                  = "Show folder";
//$lang["pic_filename"]                    = "Filename: ";
//$lang["pic_folder"]                      = "Folder: ";
//$lang["pic_size"]                        = "Picture size: ";
//$lang["pic_size_pixel"]                  = " pixel";
//$lang["pic_subtitle"]                    = "Subtitle:";
//$lang["quickhelp_editGallery"]           = "The basic data of a gallery is set up using this form.<br />This includes the title of the gallery an the corresponding start-folder on the filesystem.";
//$lang["quickhelp_editImage"]             = "An existing image or folder can be extended by a set of additional informations.";
//$lang["quickhelp_list"]                  = "All existing galleries are included in this list.<br />By using the action 'Synchronize gallery', the filesystem will be synchronized with the database. New files will be added to the database, deleted files will be removed and modified files will be updated to the database.";
//$lang["quickhelp_newGallery"]            = "The basic data of a gallery is set up using this form.<br />This includes the title of the gallery an the corresponding start-folder on the filesystem.";
//$lang["quickhelp_showGallery"]           = "Files and folders contained by the gallery selected before are listed in this view.";
//$lang["sortierung_hoch"]                 = "Shift one position up";
//$lang["sortierung_runter"]               = "Shift one position down";
//$lang["syncDialogHeader"]                = "Synchronization";
//$lang["sync_add"]                        = "Added: ";
//$lang["sync_del"]                        = " Deleted: ";
//$lang["sync_upd"]                        = " Updated: ";
//$lang["galerie_ordner_link"]             = "Show";
//$lang["gallery_rating_rate1"]            = "Rate image with ";
//$lang["gallery_rating_rate2"]            = " point(s)!";
//$lang["gallery_rating_voted"]            = "You already voted for this image.";
//$lang["liste_leer"]                      = "No pictures available";
//$lang["uebersicht"]                      = "Overview";
//
////non-editable entries
//$lang["permissions_header"]              = array(0 => "View", 1 => "Edit", 2 => "Delete", 3 => "Permissions", 4 => "Sync",  5 => "Rating", 6 => "Gallery properties", 7 => "", 8 => "");
