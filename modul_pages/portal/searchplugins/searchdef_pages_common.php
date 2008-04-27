<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*                                                                                                       *
*   searchdef_pages_common.php                                                                          *
*   Table and row defintions of general pagelements to be used by the search-module.                    *
*                                                                                                       *
*-------------------------------------------------------------------------------------------------------*
*   $Id: searchdef_pages_common.php 1927 2008-01-23 21:08:07Z sidler $                                  *
********************************************************************************************************/ 


//Tables & rows of page-elements
$arrSearch["pages_elements"][_dbprefix_."element_absatz"][] = "absatz_titel";
$arrSearch["pages_elements"][_dbprefix_."element_absatz"][] = "absatz_inhalt";
$arrSearch["pages_elements"][_dbprefix_."element_absatz"][] = "absatz_link";
$arrSearch["pages_elements"][_dbprefix_."element_absatz"][] = "absatz_bild";
$arrSearch["pages_elements"][_dbprefix_."element_bild"][] = "bild_titel";
$arrSearch["pages_elements"][_dbprefix_."element_bild"][] = "bild_bild";
$arrSearch["pages_elements"][_dbprefix_."element_bild"][] = "bild_link";
$arrSearch["pages_elements"][_dbprefix_."element_universal"][] = "char1";
$arrSearch["pages_elements"][_dbprefix_."element_universal"][] = "char2";
$arrSearch["pages_elements"][_dbprefix_."element_universal"][] = "char3";
$arrSearch["pages_elements"][_dbprefix_."element_universal"][] = "text";

//Pagedata
$arrSearch["page"][_dbprefix_."page"][] = "page_name";
$arrSearch["page"][_dbprefix_."page"][] = "pageproperties_description";
$arrSearch["page"][_dbprefix_."page"][] = "pageproperties_keywords";
$arrSearch["page"][_dbprefix_."page"][] = "pageproperties_browsername";


?>