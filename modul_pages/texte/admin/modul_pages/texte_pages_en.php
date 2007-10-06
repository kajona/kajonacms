<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	texte_pages_en.php																					*
* 	Admin language file for module_pages																*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                           *
********************************************************************************************************/

// --- Module texts -------------------------------------------------------------------------------------

$text["modul_titel"]				= "Pages";
$text["modul_rechte"]				= "Module permissions";
$text["modul_liste"]				= "List";
$text["modul_liste_alle"]			= "Flat list";
$text["modul_neu"]					= "Create page";
$text["modul_neu_ordner"]			= "Create folder";
$text["modul_elemente"]				= "Page elements";
$text["modul_element_neu"]			= "Create page element";
$text["flushCache"]                 = "Flush page-cache";
$text["updatePlaceholder"]          = "Update placeholder";

$text["permissions_header"]         = array(
            							0 => "View",
            							1 => "Edit",
            							2 => "Delete",
            							3 => "Permissions",
            							4 => "Elements",		//Recht1
            							5 => "Folder",			//Recht2
            							6 => "Placeholders",
            							7 => "",
            							8 => ""
            							);

$text["browser"]					= "Open browser";
$text["klapper"]					= "Show/hide folders";

$text["status_active"]              = "Change status (is active)";
$text["status_inactive"]            = "Change status (is inactive)";

$text["seite_bearbeiten"]			= "Edit page";
$text["liste_seiten_leer"]			= "No pages available";
$text["seite_inhalte"]				= "Edit page content";
$text["seite_loeschen"]				= "Delete page";
$text["seite_loeschen_frage"]		= " : really delete page?";
$text["seite_loeschen_link"]		= "Delete";
$text["seite_loeschen_erfolg"]		= "Page was delete successfully";
$text["seite_rechte"]				= "Edit permissions";
$text["seite_vorschau"]				= "Show preview";
$text["seite_copy"]                 = "Copy page";
$text["lastuserTitle"]				= "Last author:";
$text["lasteditTitle"]				= "Last modification:";
$text["pageNameTitle"]				= "Page name:";

$text["pages_hoch"]					= "One level up";
$text["pages_ordner_oeffnen"]		= "Open folder";
$text["ordner_anlegen_erfolg"]		= "Folder was created successfully";
$text["ordner_loeschen_erfolg"]		= "Folder was deleted successfully";
$text["ordner_loeschen_fehler"]		= "An error occured while deleting folder";
$text["ordner_loschen_leer"]        = "The folder can\'t be deleted, it\'s not empty";
$text["pages_ordner_rechte"]		= "Edit permissions";
$text["pages_ordner_loeschen"]		= "Delete folder";
$text["pages_ordner_loeschen_frage"]= " : really delete folder?";
$text["pages_ordner_loeschen_link"]	= "Delete";
$text["pages_ordner_edit"]			= "Edit folder";

$text["inhalte_titel"]				= "Page management - ";
$text["inhalte_navi2"]				= "Page: ";
$text["inhalte_liste"]				= "List of pages";
$text["inhalte_element"]			= "Manage page elements";

$text["fehler_recht"]				= "Not enough permissions to perform this action";
$text["fehler_name"]				= "No page name provided";

$text["element_bearbeiten"]			= "Edit element";
$text["element_install"]            = "Install element";
$text["element_installer_hint"]     = "Installers found of elements not yet installed:";
$text["element_anlegen"]			= "Create element";
$text["element_anlegen_fehler"]		= "An error occured while creating page element";
$text["element_bearbeiten_fehler"]	= "An error occured while saving page element";
$text["element_loeschen"]			= "Delete page element";
$text["element_loeschen_frage"]		= " : really delete page element?<br />";
$text["element_loeschen_link"]		= "Delete";
$text["element_loeschen_fehler"]	= "An error occured while deleting page element";
$text["element_hoch"]				= "Shift element up";
$text["element_runter"]				= "Shift element down";
$text["element_status_aktiv"]		= "Change status (is active)";
$text["element_status_inaktiv"]		= "Change status (is inactive)";
$text["element_liste_leer"]			= "No page elements provided by page template";
$text["elemente_liste_leer"]		= "No page elements installed";

$text["option_ja"]					= "Yes";
$text["option_nein"]				= "No";

$text["ds_gesperrt"]				= "The record is currently locked";
$text["ds_seite_gesperrt"]			= "The page can\'t be deleted, it contains locked records";
$text["ds_entsperren"]				= "Unlock record";

$text["warning_elementsremaining"]  = "ATTENTION<br>There are pageelements in the system which can't be assigned to any placeholder provided
                                       by the template. This can happen, if a placeholder was renamed or removed from the template. To rename a placeholder
                                       in the system, you can use the function \"Update placeholder\". A list of the elements follows after this warning.";

$text["placeholder"]                = "Placeholder: ";

$text["name"]						= "Name (language unindependant):";
$text["beschreibung"]				= "Description:";
$text["keywords"]					= "Keywords:";
$text["ordner_name"]				= "Folder:";
$text["ordner_name_parent"]			= "Parent folder:";
$text["template"]					= "Template:";
$text["browsername"]                = "Browser title:";
$text["seostring"]                  = "SEO-URL-Keywords:";

$text["element_name"]				= "Name:";
$text["element_admin"]				= "Admin-class:";
$text["element_portal"]				= "Portal-class:";
$text["element_repeat"]				= "Repeatable:";
$text["submit"]						= "Save";
$text["element_cachetime"]          = "Max. cache duration in sec (-1 = no caching) :";

$text["_pages_templatewechsel_"]        = "Allow change of templates:";
$text["_pages_templatewechsel_hint"]    = "Defines, whether the template of pages with contents is allowed to be changed or not. If set to yes, this may
                                           come with unexpected sideeffects!";

$text["_pages_maxcachetime_"]       = "Maximum cache duration:";
$text["_pages_maxcachetime_hint"]   = "Defines, how many seconds a page remains valid in the cache";

$text["_pages_portaleditor_"]       = "Portaleditor enabled:";

$text["_pages_newdisabled_"]        = "New pages inactive:";
$text["_pages_newdisabled_hint"]    = "If set to yes, new pages remain inactive";

$text["_pages_cacheenabled_"]       = "Page-cache enabled:";

$text["_pages_startseite_"]         = "Start page:";
$text["_pages_fehlerseite_"]        = "Error page:";
$text["_pages_defaulttemplate_"]    = "Default template:";

$text["page_element_placeholder_title"] = "Internal title:";
$text["page_element_system_folder"] = "Show/hide optinal fields";
$text["page_element_start"]         = "Display period start:";
$text["page_element_end"]           = "Display period end:";
$text["element_pos"]                = "Position at placeholder:";
$text["element_first"]              = "At the beginning";
$text["element_last"]               = "At the end";
$text["page_element_placeholder_language"] = "Language:";

$text["flushCacheSuccess"]          = "Page-cache was flushed successfully";

$text["required_ordner_name"]       = "Name of the folder";
$text["required_element_name"]      = "Name of the element";
$text["required_element_cachetime"] = "Cache duration of the element";
$text["required_name"]              = "Name of the page";
$text["required_elementid"]         = "There already exists an element with this name.";

$text["plUpdateHelp"]               = "Here you are able to update placeholders saved in the database.<br />
                                       This can be necessary if a placeholder was extended by another possible page element.
                                       In this case the new element will appear in the admin, but remains invisible in the portal. To change this,
                                       placeholders saved in the database have to be updated to the new placeholders.<br />
                                       To do so, you need the name of the changed template, the title of the old placeholder (name_element) and the
                                       name of the new placeholder (e.g. name_element|element2). The placeholder should be provided without percent signs.";
$text["plRename"]                   = "Update";
$text["plToUpdate"]                 = "Old placeholder:";
$text["plNew"]                      = "New placeholder:";
$text["plUpdateTrue"]               = "Update was successfull.";
$text["plUpdateFalse"]              = "An error occured while updating placeholders.";



// portaleditor

$text["pe_edit"]                            = "Edit";
$text["pe_new"]                             = "New element";
$text["pe_delete"]                          = "Delete";
$text["pe_shiftUp"]                         = "Shift up";
$text["pe_shiftDown"]                       = "Shift down";

$text["pe_status_page"]                     = "Page:";
$text["pe_status_status"]                   = "Status:";
$text["pe_status_autor"]                    = "Last author:";
$text["pe_status_time"]                     = "Last modification:";

$text["pe_icon_edit"]                       = "Open page in administration";
$text["pe_icon_page"]                       = "Edit page details in administration";
$text["pe_disable"]                         = "Set portaleditor temporary inactive";
$text["pe_enable"]                          = "Set portaleditor active";


// --- Quickhelp texts ----------------------------------------------------------------------------------

$text["quickhelp_list"]             = "In this view, you can browse all available pages.<br />
                                       Therefore, pages can be sorted by the use of virtual folders.<br />
                                       While editing the contents of a page, elements can be created, edited or deleted according to the placeholders provided
                                       by the template.";
$text["quickhelp_listAll"]			= "In the flat list, all pages created are shown and the folderstructure is ignored and hidden.<br />
									   This view can be used to find a page quickly.";
$text["quickhelp_newPage"]			= "This form is used to create or edit a page.<br />
									   Therefore, you can provide the following values:<br />
									   <ul>
									   <li>Name: The name of the page. This name is used to open the page in the portal.</li>
									   <li>Browser title: The browser window shows this title.</li>
									   <li>SEO-URL-Keywords: Search-Engine-Optimization, provide a few keywords about the current site. Those will be added to the url.</li>
									   <li>Description: A short description of the page. This text is shown in the search results and other summaries.</li>
									   <li>Keywords: The given, comma-separated list of keywords is placed in the meta-tags of the page. Those keywords
									     are relevant for search-engines.</li>
									   <li>Folder: The internal virtual folder, to save the page into.</li>
									   <li>Template: The template used as a base for the page. In most cases, this field is disabled, as soon as there are contents created
									     on the page.</li>
									   	</ul>";
$text["quickhelp_newFolder"]		= "To create or rename a folder, a folder name can be set here.";
$text["quickhelp_editFolder"]		= $text["quickhelp_newFolder"];
$text["quickhelp_listElements"]		= "This list contains all page elements currently available.<br />
									   The name of the element matches the last part of a placeholder in a template.<br />
									   If the system finds installers of elements not yet installed, those elements are provided at the end
									   of the list to be installed.";
$text["quickhelp_newElement"]		= "This form is used to create or modify the basic data of page elements. Therefore, you are able to set the following fields:<br />
									   <ul>
									    <li>Name: Title of the element</li>
									    <li>Max. cache duration: Duration in seconds the element is valid in the cache.<br />
									         After this duration, the page will be regenerated with the next request.</li>
									    <li>Admin-class: class containing the admin-forms.</li>
									    <li>Portal-class: class responsible to generate the portal-output.</li>
									    <li>Repeatable: Defines, whether an element is allowed more than once at a placeholder or not.</li>
									   </ul>";
$text["quickhelp_editElement"]		= $text["quickhelp_newElement"];
$text["quickhelp_flushCache"]		= "Congratulations - the page cache was flushed a few seconds ago ;-)";
$text["quickhelp_updatePlaceholder"] = "ATTENTION! This action is just needed, if a templates placeholder was extended.<br />
                                        If a templates placeholder is extended, the contents assigned to it won't be diplayed in the portal because
                                        the database still contains the old placeholder. To replace this old placeholder with the new ones, use this
                                        form.";

?>