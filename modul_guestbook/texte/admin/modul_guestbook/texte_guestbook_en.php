<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	texte_guestbook_en.php																				*
* 	Admin language file for module_guestbook															*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                       *
********************************************************************************************************/

// --- Module texts -------------------------------------------------------------------------------------
$text["modul_rechte"]				= "Module permissions";
$text["modul_liste"]				= "List";
$text["modul_anlegen"]				= "New guestbook";
$text["modul_titel"]				= "Guestbooks";

$text["permissions_header"]         = array(
            							0 => "View",
            							1 => "Edit",
            							2 => "Delete",
            							3 => "Permissions",
            							4 => "Sign",         //recht1
            							5 => "",
            							6 => "",
            							7 => "",
            							8 => ""
            							);

$text["gaestebuch_anzeigen"]		= "View guestbook";
$text["gaestebuch_bearbeiten"]		= "Edit guestbook";
$text["gaestebuch_loeschen"]		= "Delete guestbook";
$text["gaestebuch_rechte"]			= "Change permissions";
$text["gaestebuch_listeleer"]		= "No guestbooks available";

$text["status_active"]              = "Change status (is active)";
$text["status_inactive"]            = "Change status (is inactive)";

$text["gaestebuch_modus_0"]			= "New posts are disabled";
$text["gaestebuch_modus_1"]			= "New posts are enabled";

$text["fehler_recht"]				= "Not enough permissions to perform this action";
$text["loeschen_frage"]				= " : Delete guestbook with all entries?<br />";
$text["loeschen_link"]				= "Delete";

$text["loeschen_post"]				= "Delete";
$text["post_liste_leer"]			= "No posts available";
$text["post_loeschen_frage"]		= " : Really delete post?<br />";
$text["post_loeschen_link"]			= "Delete";

$text["guestbook_title"]            = "Title:";
$text["guestbook_moderated"]        = "Control-mode:";
$text["speichern"]                  = "Save";

$text["required_guestbook_title"]   = "Title";

$text["_guestbook_suche_seite_"]    = "Result page:";
$text["_guestbook_suche_seite_hint"]= "On this page, the posts found by the search, are linked to.";


// --- Quickhelp texts ----------------------------------------------------------------------------------

$text["quickhelp_list"]             = "All guestbooks created can be found in this list.";
$text["quickhelp_newGuestbook"]     = "While creating or edtiting a guestbook, a title can be assigned.
                                       In addition, you can decide, whether new posts are published instantly. In the other case, new posts will
                                       remain inactive until an admin decides to activate them.<br /><br />
                                       Hint: If guests should be allowed to sign the guestbook, they need the right 'Sign'!";
$text["quickhelp_editGuestbook"]    = $text["quickhelp_newGuestbook"];
$text["quickhelp_viewGuestbook"]    = "In this list, all posts belonging to the current guestbook are listed. Posts can be deleted, activated or inactivated.";
$text["quickhelp_deletePost"]       = "If you want to delete the current post, confirm this action now.";
?>