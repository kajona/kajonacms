<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
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

    At the time of generation, this placeholder will be replaced by the specified content:

    Value of placeholder
*/

	$arrGlobal["copyright"] 			= "powered by <a href=\"http://www.kajona.de\" target=\"_blank\" title=\"Kajona³ CMS - empowering your content\">Kajona³</a>";

//---Kajona head parts, please leave them as the are----------------------------------------------------------

    $arrGlobal["kajona_head"]           = "<script type=\"text/javascript\" src=\""._webpath_."/portal/scripts/yui/yuiloader-dom-event/yuiloader-dom-event.js\"></script>\n";
    $arrGlobal["kajona_head"]          .= "    <script type=\"text/javascript\">KAJONA_WEBPATH = '"._webpath_."';</script>\n";
    $arrGlobal["kajona_head"]		   .= "    <script type=\"text/javascript\" src=\""._webpath_."/portal/scripts/kajona.js\"></script>\n";
    $arrGlobal["kajona_head"]          .= "    <meta http-equiv=\"content-language\" content=\"".$this->getPortalLanguage()."\" />\n";
    $arrGlobal["kajona_head"]          .= "    <meta name=\"generator\" content=\"Kajona³, www.kajona.de\" />";


/*
    The next placeholder is used as an extra separator for the page-title. In some cases, module may add additional
    texts to the current title, e.g. the name of a news. In this case, you can define a separator. This may lead to s.th. like
    "New Kajona version released | Welcome | Kajona³" instead of "Welcome | Kajona³". Feel free to modify the following line.
 */

	$arrGlobal["kajonaTitleSeparator"] = " | ";

?>