<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                  *
********************************************************************************************************/


//Tables & rows of page-elements
$arrSearch["pages_elements"][_dbprefix_."element_paragraph"][] = "paragraph_title LIKE ?";
$arrSearch["pages_elements"][_dbprefix_."element_paragraph"][] = "paragraph_content LIKE ?";
$arrSearch["pages_elements"][_dbprefix_."element_paragraph"][] = "paragraph_link LIKE ?";
$arrSearch["pages_elements"][_dbprefix_."element_paragraph"][] = "paragraph_image LIKE ?";
$arrSearch["pages_elements"][_dbprefix_."element_image"][] = "image_title LIKE ?";
$arrSearch["pages_elements"][_dbprefix_."element_image"][] = "image_image LIKE ?";
$arrSearch["pages_elements"][_dbprefix_."element_image"][] = "image_link LIKE ?";
$arrSearch["pages_elements"][_dbprefix_."element_universal"][] = "char1 LIKE ?";
$arrSearch["pages_elements"][_dbprefix_."element_universal"][] = "char2 LIKE ?";
$arrSearch["pages_elements"][_dbprefix_."element_universal"][] = "char3 LIKE ?";
$arrSearch["pages_elements"][_dbprefix_."element_universal"][] = "text LIKE ?";

//Pagedata
$arrSearch["page"][_dbprefix_."page"][] = "page_name LIKE ?";
$arrSearch["page"][_dbprefix_."page"][] = "pageproperties_description LIKE ?";
$arrSearch["page"][_dbprefix_."page"][] = "pageproperties_keywords LIKE ?";
$arrSearch["page"][_dbprefix_."page"][] = "pageproperties_browsername LIKE ?";


