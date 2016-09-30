<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                          *
********************************************************************************************************/

/*
    Usage: Define as many elements as wanted using the

    $arrGlobal["nameOfPlaceholder"]     = "Value of placeholder";

    schema.
    To have the elements included in the pages, define the placeholders in the templates!
    In the example above, the templates should provide a placeholder like

    %%nameOfPlaceholder%%

    At the time of generation, this placeholder will be replaced by the specified content:

    Value of placeholder
*/

    $arrGlobal["copyright"] 			= "powered by <a href=\"http://www.kajona.de\" target=\"_blank\" title=\"Kajona CMS - empowering your content\">Kajona</a>";

//---Kajona head parts, please leave them as they are----------------------------------------------------

    $arrGlobal["kajona_head"]           = "    <script type=\"text/javascript\" src=\""._webpath_."/templates/default/js/jquery/jquery.min.js?".\Kajona\System\System\SystemSetting::getConfigValue("_system_browser_cachebuster_")."\"></script>\n";
    $arrGlobal["kajona_head"]          .= "    <script type=\"text/javascript\">KAJONA_LANGUAGE = '".\Kajona\System\System\Carrier::getInstance()->getObjLang()->getStrTextLanguage()."'; KAJONA_WEBPATH = '"._webpath_."'; KAJONA_BROWSER_CACHEBUSTER = '".\Kajona\System\System\SystemSetting::getConfigValue("_system_browser_cachebuster_")."';</script>\n";
    $arrGlobal["kajona_head"]          .= "    <script type=\"text/javascript\">KAJONA_PHARMAP = ".json_encode(array_values(\Kajona\System\System\Classloader::getInstance()->getArrPharModules())).";</script>";
    $arrGlobal["kajona_head"]		   .= "    <script type=\"text/javascript\" src=\""._webpath_.\Kajona\System\System\Resourceloader::getInstance()->getWebPathForModule("module_system")."/scripts/loader.js?".\Kajona\System\System\SystemSetting::getConfigValue("_system_browser_cachebuster_")."\"></script>\n";
    $arrGlobal["kajona_head"]		   .= "    <script type=\"text/javascript\" src=\""._webpath_."/templates/default/js/kajona.js?".\Kajona\System\System\SystemSetting::getConfigValue("_system_browser_cachebuster_")."\"></script>\n";
    $arrGlobal["kajona_head"]          .= "    <meta http-equiv=\"content-language\" content=\"".$this->getStrPortalLanguage()."\" />\n";
    $arrGlobal["kajona_head"]          .= "    <meta name=\"generator\" content=\"Kajona, www.kajona.de\" />";


/*
    The next placeholder is used as an extra separator for the page-title. In some scenarios, modules may add additional
    texts to the current title, e.g. the name of a news. In this case, you can define a separator. This may lead to s.th. like
    "New Kajona version released | Welcome | Kajona" instead of "Welcome | Kajona". Feel free to modify the following line.
 */

    $arrGlobal["kajonaTitleSeparator"] = " | ";

