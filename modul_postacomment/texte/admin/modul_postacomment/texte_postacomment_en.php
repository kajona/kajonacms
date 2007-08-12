<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	texte_news_en.php																					*
* 	Admin language file for module_news 																*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id: texte_news_en.php 1524 2007-05-03 13:22:46Z sidler $                                            *
********************************************************************************************************/

// --- Module texts -------------------------------------------------------------------------------------
$text["modul_titel"]                = "Comments";
$text["modul_rechte"]				= "Module permissions";
$text["module_list"]				= "List";

$text["liste_leer"]                 = "No posts available";

$text["permissions_header"]         = array(
            							0 => "View",
            							1 => "Edit",
            							2 => "Delete",
            							3 => "Permissions",
            							4 => "Post",	     //Recht1
            							5 => "",            //Recht2
            							6 => "",
            							7 => "",
            							8 => ""
            							);


$text["status_active"]              = "Change status (is active)";
$text["status_inactive"]            = "Change status (is inactive)";

$text["postacomment_edit"]          = "Edit comment";
$text["postacomment_delete"]        = "Delete comment";
$text["postacomment_rights"]        = "Change permissions";

$text["postacomment_delete_question"] = " | Really delete comment?";
$text["postacomment_delete_link"]   = "Delete";

$text["postacomment_username"]      = "Name:";
$text["postacomment_title"]         = "Subject:";
$text["postacomment_comment"]       = "Comment:";

$text["required_postacomment_username"] = "Name";
$text["required_postacomment_comment"]  = "Comment";

$text["submit"]                     = "Speichern";

$text["postacomment_filter"]        = "Page-filter:";
$text["postacomment_dofilter"]      = "filter";


// --- Quickhelp texts ----------------------------------------------------------------------------------

$text["quickhelp_list"]             = "All comments provided by users using the portal are listed in this view.
									   <br />A row has the following structure:<br/><br/>
									   Pagename  (Language) | Date <br/>
								       Username | Subject <br />
								       Message <br/><br />
									   By using the page-filter at top of the list, the posts can be filtered by a single page.
								       Long comments are cut, so use the action edit to read the complete message.";

$text["quickhelp_editPost"]        = "In this view, you can change the values of a comment.";

?>