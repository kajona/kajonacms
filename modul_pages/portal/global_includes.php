<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	global_includes.php																					*
* 	Defines Elements to include on the pages                                                            *
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                          *
********************************************************************************************************/

/*
    Usage: Define as many elements as wanted using the

    $arrGlobal["nameOfPlaceholder"]     = "Value of placeholder";

    schema.
    To have the elements included in the pages, define the placeholders in the templates!
    In the example above, the templates should provide placeholder like

    %%nameOfPlaceholder%%

    At the time of generation, this placeholder will be replaced by the speicified content:

    Value of placeholder
*/

	$arrGlobal["copyright"] 			= "powered by <a href=\"http://www.kajona.de\" target=\"_blank\" title=\"Kajona³ CMS - empowering your content\">Kajona³</a>";

//---Kajona Parts, PLEASE leave them as is--------------------------------------------------------------

	$arrGlobal["kajona_head"]			= "<meta name=\"generator\" content=\"Kajona³, www.kajona.de\" />\n";
	$arrGlobal["kajona_head"]		    .= "<script language=\"Javascript\" type=\"text/javascript\" src=\""._webpath_."/portal/scripts/kajona.js\"></script>\n";

?>