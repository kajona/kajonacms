<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                                *
********************************************************************************************************/

/**
 * @package module_system
 */

//For the sake of different loaders - check again :(
//Mbstring loaded? If yes, we could use unicode-safe string-functions
if(!defined("_mbstringloaded_")) {
	if(extension_loaded("mbstring")) {
        define("_mbstringloaded_", true);
        mb_internal_encoding("UTF-8");
	}
    else {
        define("_mbstringloaded_", false);
    }
}

/**
 * Returns a value from the GET-array
 *
 * @param string $strKey
 * @return string
 */
function getGet($strKey) {
	if(issetGet($strKey))
		return $_GET[$strKey];
	else
		return "";
}

/**
 * Returns the complete GET-Array
 *
 * @return mixed
 */
function getArrayGet() {
	return $_GET;
}

/**
 * Returns the complete FILE-Array
 *
 * @return mixed
 */
function getArrayFiles() {
	return $_FILES;
}


/**
 * Checks whether a kay exists in GET-array, or not
 *
 * @param string $strKey
 * @return bool
 */
function issetGet($strKey) {
	if(isset($_GET[$strKey]))
		return true;
	else
		return false;
}

/**
 * Returns a value from the Post-array
 *
 * @param string $strKey
 * @return string
 */
function getPost($strKey) {
	if(issetPost($strKey))
		return $_POST[$strKey];
	else
		return "";
}

/**
 * Returns the complete POST-array
 *
 * @return mixed
 */
function getArrayPost() {
	return $_POST;
}

/**
 * Looks, if a key is in POST-array or not
 *
 * @param string $strKey
 * @return bool
 */
function issetPost($strKey) {
	if(isset($_POST[$strKey]))
		return true;
	else
		return false;
}

/**
 * Returns the complete http-post-body as raw-data.
 * Please indicate wheter the source is encoded in "multipart/form-data", in this case
 * the data is read another way internally.
 *
 * @param bool $bitMultipart
 * @return string
 * @since 3.4.0
 */
function getPostRawData($bitMultipart = false) {
    if($bitMultipart)
        return $HTTP_RAW_POST_DATA;
    else
        return file_get_contents("php://input");
}

/**
 * Returns a value from the SERVER-Array
 *
 * @param mixed $strKey
 * @return unknown
 */
function getServer($strKey) {
	if(issetServer($strKey))
		return $_SERVER[$strKey];
	else
		return "";
}

/**
 * Returns all params passed during startup by get, post or files
 *
 * @return array
 * @deprecated use class_carrier::getAllParams() instead
 * @todo remove
 */
function getAllPassedParams() {
    return class_carrier::getAllParams();
}

/**
 * Key in SERVER-Array?
 *
 * @param string $strKey
 * @return bool
 */
function issetServer($strKey) {
	if(isset($_SERVER[$strKey]))
		return true;
	else
		return false;
}

/**
 * Tests, if the requested cookie exists
 *
 * @param string $strKey
 * @return bool
 */
function issetCookie($strKey) {
    return isset($_COOKIE[$strKey]);
}

/**
 * Provides acces to the $_COOKIE Array.
 * NOTE: Use the cookie-class to get data from cookies!
 *
 * @param string $strKey
 * @return mixed
 */
function getCookie($strKey) {
    if(issetCookie($strKey))
        return $_COOKIE[$strKey];
    else
        return "";
}

/**
 * Generates a link using the content passed.
 * The param $strLinkContent should contain all contents of the a-tag.
 * The system renders <a $strLinkContent title... class...>($strText|$strImage)</a>
 *
 * @param string $strLinkContent
 * @param string $strText
 * @param string $strAlt
 * @param string $strImage
 * @param string $strImageId
 * @param string $strLinkId
 * @param bool $bitTooltip
 * @param string $strCss
 * @return string
 */
function getLinkAdminManual($strLinkContent, $strText , $strAlt="", $strImage="", $strImageId = "", $strLinkId = "", $bitTooltip = true, $strCss = "") {
    $strLink = "";
    if($strImage != "") {
        if(!$bitTooltip)
            $strLink = "<a ".$strLinkContent."  title=\"".$strAlt."\" ".($strLinkId != "" ? "id=\"".$strLinkId."\"" : "")." ><img src=\""._skinwebpath_."/pics/".$strImage."\" alt=\"".$strAlt."\" ".($strImageId != "" ? "id=\"".$strImageId."\"" : "")." /></a>";
        else
            $strLink = "<a ".$strLinkContent."  title=\"".$strAlt."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\" ".($strLinkId != "" ? "id=\"".$strLinkId."\"" : "")." ><img src=\""._skinwebpath_."/pics/".$strImage."\" alt=\"".$strAlt."\" title=\"\" ".($strImageId != "" ? "id=\"".$strImageId."\"" : "")." /></a>";
    }

    if($strImage == "" && $strText != "")   {
        if($strAlt == "")
            $strAlt = $strText;
        $strLink = "<a ".$strLinkContent." title=\"".$strAlt."\" ".($strCss!= "" ? " class=\"".$strCss."\"" : "")." ".($strLinkId != "" ? "id=\"".$strLinkId."\"" : "")." >".$strText."</a>";
    }

    return $strLink;
}

/**
 * Generates a link for the admin-area
 *
 * @param string $strModule
 * @param string $strAction
 * @param string $strParams
 * @param string $strText
 * @param string $strAlt
 * @param string $strImage
 * @param bool $bitTooltip
 * @param string $strCss
 * @return string
 */
function getLinkAdmin($strModule, $strAction, $strParams = "", $strText ="", $strAlt="", $strImage="", $bitTooltip = true, $strCss = "") {
    $strLink = "";
	if($strImage != "") {
		if($strAlt == "")
			$strAlt = $strAction;
		if(!$bitTooltip)
			$strLink = "<a href=\"".getLinkAdminHref($strModule, $strAction, $strParams)."\" title=\"".$strAlt."\"><img src=\""._skinwebpath_."/pics/".$strImage."\" alt=\"".$strAlt."\" /></a>";
		else
			$strLink = "<a href=\"".getLinkAdminHref($strModule, $strAction, $strParams)."\" title=\"".$strAlt."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\"><img src=\""._skinwebpath_."/pics/".$strImage."\" alt=\"".$strAlt."\" title=\"\" /></a>";
	}

	if($strImage == "" && $strText != "") 	{
		if($strAlt == "")
			$strAlt = $strText;


        if($strAlt != $strText)
            $strLink = "<a href=\"".getLinkAdminHref($strModule, $strAction, $strParams)."\" title=\"".$strAlt."\" ".($strCss!= "" ? " class=\"".$strCss."\"" : "")." onmouseover=\"KAJONA.admin.tooltip.add(this);\">".$strText."</a>";
        else
            $strLink = "<a href=\"".getLinkAdminHref($strModule, $strAction, $strParams)."\" ".($strCss!= "" ? " class=\"".$strCss."\"" : "").">".$strText."</a>";
	}

	return $strLink;
}

/**
 * Generates a link for the admin-area
 *
 * @param string $strModule
 * @param string $strAction
 * @param string $strParams
 * @param bool $bitEncodedAmpersand
 * @param bool $bitBlockPrintview
 * @return string
 */
function getLinkAdminHref($strModule, $strAction = "", $strParams = "", $bitEncodedAmpersand = true, $bitBlockPrintview = false) {
    $strLink = "";

    //add print-view param?
    if(!$bitBlockPrintview && (getGet("printView") != "" || getPost("printView") != ""))
        $strParams .= "&printView=1";


    //systemid in params?
    $strSystemid = "";
    $arrParams = explode("&", $strParams);

    foreach($arrParams as $strKey => $strValue) {
    	$arrEntry = explode("=", $strValue);
    	if(count($arrEntry) == 2 && $arrEntry[0] == "systemid") {
    	   $strSystemid = $arrEntry[1];
    	   unset($arrParams[$strKey]);
    	}
    	else if($strValue == "")
           unset($arrParams[$strKey]);
    }

	//urlencoding
    $strModule = urlencode($strModule);
    $strAction = urlencode($strAction);

    //rewriting enabled?
    if(_system_mod_rewrite_ == "true") {

    	//scheme: /admin/module.action.systemid
    	if($strModule != "" && $strAction == "" && $strSystemid == "")
    	   $strLink = _webpath_."/admin/".$strModule.".html";
    	else if($strModule != "" && $strAction != "" && $strSystemid == "")
    	   $strLink = _webpath_."/admin/".$strModule."/".$strAction.".html";
    	else
           $strLink = _webpath_."/admin/".$strModule."/".$strAction."/".$strSystemid.".html";

        if(count($arrParams) > 0)
        $strLink .= "?".implode("&amp;", $arrParams);

    }
    else {
	   $strLink = ""._indexpath_."?admin=1&amp;module=".$strModule.
                                        ($strAction != "" ? "&amp;action=".$strAction : "" ).
                                        ($strSystemid != "" ?  "&amp;systemid=".$strSystemid : "");

       if(count($arrParams) > 0)
           $strLink .= "&amp;".(implode("&amp;", $arrParams));
    }

    if(!$bitEncodedAmpersand)
        $strLink = uniStrReplace("&amp;", "&", $strLink);

	return $strLink;
}





/**
 * Generats a raw link in admin-area.
 * Can be used to create links to external resources. no admin=1 and so on is added.
 *
 * @param string $strParames
 * @param string $strText
 * @param string $strAlt
 * @param string $strImage
 * @param string $strTarget
 * @return string
 */
function getLinkAdminRaw($strParams, $strText = "", $strAlt="", $strImage="", $strTarget = "_self") {
	$strLink = "";
	//Wenn Parameter gegeben sind, diese aufbereiten)
	$strParams = str_replace("&", "&amp;", $strParams);

	//Admin?
	if(_admin_) {
		if($strImage != "") {
			$strLink = "<a href=\"".$strParams."\" target=\"".$strTarget."\" title=\"".$strAlt."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\"><img src=\""._skinwebpath_."/pics/".$strImage."\" alt=\"".$strAlt."\" /></a>";
		}

		if($strImage == "" && $strText != "") {
			$strLink = "<a href=\""._indexpath_."?".$strParams."\" title=\"".$strAlt."\">".$strText."</a>";
		}
	}
	return $strLink;
}

/**
 * Generates a link opening in a popup in admin-area
 *
 * @param string $strModule
 * @param string $strAction
 * @param string $strParams
 * @param string $strText
 * @param string $strAlt
 * @param string $strImage
 * @param int $intWidth
 * @param int $intHeight
 * @param string $strTitle
 * @param bool $bitTooltip
 * @param bool $bitPortalEditor
 * @return string
 */
function getLinkAdminPopup($strModule, $strAction, $strParams = "", $strText = "", $strAlt="", $strImage="", $intWidth = "500", $intHeight = "500", $strTitle = "", $bitTooltip = true, $bitPortalEditor = false) {
    $strLink = "";
	//if($strParams != "")
	//	$strParams = str_replace("&", "&amp;", $strParams);

    if($bitPortalEditor && $intHeight == "500")
        $intHeight = 690;

	//urlencoding
    $strModule = urlencode($strModule);
    $strAction = urlencode($strAction);

	if($bitPortalEditor)
        $strParams .= "&pe=1";

	if($strImage != "") {
		if($strAlt == "")
			$strAlt = $strAction;

		if(!$bitTooltip)
			$strLink = "<a href=\"#\" onclick=\"window.open('".getLinkAdminHref($strModule, $strAction, $strParams)."','".$strTitle."','scrollbars=yes,resizable=yes,width=".$intWidth.",height=".$intHeight."'); return false;\" title=\"".$strAlt."\"><img src=\""._skinwebpath_."/pics/".$strImage."\" alt=\"".$strAlt."\" align=\"absbottom\" /></a>";
		else
			$strLink = "<a href=\"#\" onclick=\"window.open('".getLinkAdminHref($strModule, $strAction, $strParams)."','".$strTitle."','scrollbars=yes,resizable=yes,width=".$intWidth.",height=".$intHeight."'); return false;\" title=\"".$strAlt."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\"><img src=\""._skinwebpath_."/pics/".$strImage."\" alt=\"".$strAlt."\" align=\"absbottom\" /></a>";
	}

	if($strImage == "" && $strText != "") {
		if($strAlt == "")
			$strAlt = $strText;
		$strLink = "<a href=\"#\" ".($bitPortalEditor ? "class=\"pe_link\"" : "")." ".($bitTooltip ? "onmouseover=\"KAJONA.admin.tooltip.add(this, '".$strAlt."');\" " : "" )." onclick=\"window.open('".getLinkAdminHref($strModule, $strAction, $strParams)."','".$strTitle."','scrollbars=yes,resizable=yes,width=".$intWidth.",height=".$intHeight."'); return false;\">".$strText."</a>";
	}
	return $strLink;
}

/**
 * Generates a link opening in a dialog in admin-area
 *
 * @param string $strModule
 * @param string $strAction
 * @param string $strParams
 * @param string $strText
 * @param string $strAlt
 * @param string $strImage
 * @param string $strTitle
 * @param bool $bitTooltip
 * @param bool $bitPortalEditor
 * @param bool|string $strOnClick
 * @param null|int $intWidth
 * @param null|int $intHeight
 * @return string
 */
function getLinkAdminDialog($strModule, $strAction, $strParams = "", $strText = "", $strAlt="", $strImage="", $strTitle = "", $bitTooltip = true, $bitPortalEditor = false, $strOnClick = "", $intWidth = null, $intHeight = null) {
    $strLink = "";

    //urlencoding
    $strModule = urlencode($strModule);
    $strAction = urlencode($strAction);

    if($strOnClick == "") {
        if($intWidth !== null && $intHeight !== null)
            $strOnClick = "KAJONA.admin.folderview.dialog.setContentIFrame('".getLinkAdminHref($strModule, $strAction, $strParams)."'); KAJONA.admin.folderview.dialog.setTitle('".$strTitle."'); KAJONA.admin.folderview.dialog.init('".$intWidth."px', '".$intHeight."px'); return false;";
        else
            $strOnClick = "KAJONA.admin.folderview.dialog.setContentIFrame('".getLinkAdminHref($strModule, $strAction, $strParams)."'); KAJONA.admin.folderview.dialog.setTitle('".$strTitle."'); KAJONA.admin.folderview.dialog.init(); return false;";
    }

    if($bitPortalEditor)
        $strParams .= "&pe=1";

    if($strImage != "") {
        if($strAlt == "")
            $strAlt = $strAction;

        if(!$bitTooltip)
            $strLink = "<a href=\"#\" onclick=\"".$strOnClick."\" title=\"".$strAlt."\"><img src=\""._skinwebpath_."/pics/".$strImage."\" alt=\"".$strAlt."\" align=\"absbottom\" /></a>";
        else
            $strLink = "<a href=\"#\" onclick=\"".$strOnClick."\" title=\"".$strAlt."\" onmouseover=\"KAJONA.admin.tooltip.add(this);\"><img src=\""._skinwebpath_."/pics/".$strImage."\" alt=\"".$strAlt."\" align=\"absbottom\" /></a>";
    }

    if($strImage == "" && $strText != "") {
        if($strAlt == "")
            $strAlt = $strText;
        $strLink = "<a href=\"#\" ".($bitPortalEditor ? "class=\"pe_link\"" : "")." ".($bitTooltip ? "onmouseover=\"KAJONA.admin.tooltip.add(this, '".$strAlt."');\" " : "" )." onclick=\"".$strOnClick."\">".$strText."</a>";
    }
    return $strLink;
}

/**
 * Returns an image-tag with surrounding tooltip
 *
 * @param string $strImage
 * @param string $strAlt
 * @param bool $bitNoAlt
 * @param string $strId
 * @param string $strStyle
 * @return string
 * @todo move to toolkit
 */
function getImageAdmin($strImage, $strAlt="", $bitNoAlt = false, $strId="", $strStyle="") {
	return "<img src=\""._skinwebpath_."/pics/".$strImage."\" alt=\"".($bitNoAlt ? "" : $strAlt)."\" title=\"".($bitNoAlt ? "" : $strAlt)."\" onmouseover=\"KAJONA.admin.tooltip.add(this, '".$strAlt."', false);\" ".($strId == "" ? "" : "id=\"".$strId."\"" )."  ".($strStyle == "" ? "" : "style=\"".$strStyle."\"" )."  />";
}

/**
 * Determins the rights-filename of a system-record. Looks up if the record
 * uses its' own rights or inherits the rights from another record.
 *
 * @param string $strSystemid
 * @return string
 * @todo move to toolkit
 */
function getRightsImageAdminName($strSystemid) {
	if(class_carrier::getInstance()->getObjRights()->isInherited($strSystemid))
	   return "icon_key_inherited.gif";
	else
	   return "icon_key.gif";
}


/**
 * Converts a php size string (e.g. "4M") into bytes
 *
 * @param int $strBytes
 * @return int
 */
function phpSizeToBytes($strBytes) {
	$intReturn = 0;

	$strBytes = uniStrtolower($strBytes);

	if(strpos($strBytes, "m") !== false) {
		$intReturn = str_replace("m", "", $strBytes);
		$intReturn = $intReturn * 1024 * 1024;
	} else if(strpos($strBytes, "k") !== false) {
		$intReturn = str_replace("m", "", $strBytes);
		$intReturn = $intReturn * 1024;
	} else if(strpos($strBytes, "g") !== false) {
		$intReturn = str_replace("m", "", $strBytes);
		$intReturn = $intReturn * 1024 * 1024 * 1024;
	}

	return $intReturn;
}

/**
 * Makes out of a byte number a human readable string
 *
 * @param int $intBytes
 * @param bool $bitPhpIni (Value ends with M/K/B)
 * @return string
 */
function bytesToString($intBytes, $bitPhpIni = false) {
	$strReturn = "";
	if($intBytes >= 0) {
		$arrFormats = array("B", "KB", "MB", "GB", "TB");

		if($bitPhpIni) {
			$intBytes = phpSizeToBytes($intBytes);
		}

		$intTemp = $intBytes;
		$intCounter = 0;

		while($intTemp > 1024) 	{
			$intTemp = $intTemp / 1024;
			$intCounter++;
		}

		$strReturn = number_format($intTemp, 2) . " " . $arrFormats[$intCounter];
		return $strReturn;
	}
	return $strReturn;
}

/**
 * Changes a timestamp to a readable string
 *
 * @param int $intTime
 * @param bool $bitLong
 * @return string
 */
function timeToString($intTime, $bitLong = true) {
	$strReturn = "";
	if($intTime > 0) {
		if($bitLong)
			$strReturn = date(class_carrier::getInstance()->getObjLang()->getLang("dateStyleLong", "system"), $intTime);
		else
			$strReturn = date(class_carrier::getInstance()->getObjLang()->getLang("dateStyleShort", "system"), $intTime);
	}
	return $strReturn;
}

/**
 * Converts a dateobject to a readable string
 *
 * @param class_date $objDate
 * @param bool $bitLong
 * @param string $strFormat if given, the passed format will be used, otherwise the format defined in the i18n files
 *                          usable placeholders are: d, m, y, h, i, s
 * @return string
 */
function dateToString($objDate, $bitLong = true, $strFormat = "") {
    $strReturn = "";
    if($objDate != null) {
        //convert to a current date
        if($strFormat == "") {
            if($bitLong)
                $strReturn = uniStrtolower(class_carrier::getInstance()->getObjText()->getText("dateStyleLong", "system", "admin"));
            else
                $strReturn = uniStrtolower(class_carrier::getInstance()->getObjText()->getText("dateStyleShort", "system", "admin"));
        }
        else
            $strReturn = $strFormat;

        //"d.m.Y H:i:s";
        $strReturn = uniStrReplace("d", $objDate->getIntDay(), $strReturn);
        $strReturn = uniStrReplace("m", $objDate->getIntMonth(), $strReturn);
        $strReturn = uniStrReplace("y", $objDate->getIntYear(), $strReturn);
        $strReturn = uniStrReplace("h", $objDate->getIntHour(), $strReturn);
        $strReturn = uniStrReplace("i", $objDate->getIntMin(), $strReturn);
        $strReturn = uniStrReplace("s", $objDate->getIntSec(), $strReturn);

    }
    return $strReturn;
}

/**
 * Formats a number according to the localized separators.
 * Those are defined in the lang-files, different entries for
 * decimal- and thousands separator.
 *
 * @param float $floatNumber
 * @param int $intNrOfDecimals the number of decimals
 * @return string
 */
function numberFormat($floatNumber, $intNrOfDecimals = 2) {
    $strDecChar = class_carrier::getInstance()->getObjLang()->getLang("numberStyleDecimal", "system");
    $strThousandsChar = class_carrier::getInstance()->getObjLang()->getLang("numberStyleThousands", "system");
    return number_format((float)$floatNumber, $intNrOfDecimals, $strDecChar, $strThousandsChar);
}

/**
 * Converts a hex-string to its rgb-values
 *
 * @see http://www.jonasjohn.de/snippets/php/hex2rgb.htm
 * @param string $color
 * @return array
 */
function hex2rgb($color){
    $color = str_replace('#', '', $color);
    if (strlen($color) != 6){ return array(0,0,0); }
    $rgb = array();
    for ($x=0;$x<3;$x++){
        $rgb[$x] = hexdec(substr($color,(2*$x),2));
    }
    return $rgb;
}


/**
 * Creates a Link for the portal
 *
 * @param string $strPageI
 * @param string $strPageE
 * @param string $strTarget
 * @param string $strText
 * @param string $strAction
 * @param string $strParams
 * @param string $strSystemid
 * @param string $strCssClass
 * @param string $strLanguage
 * @param string $strSeoAddon
 * @return string
 */
function getLinkPortal($strPageI, $strPageE, $strTarget = "_self", $strText = "", $strAction = "", $strParams = "", $strSystemid = "", $strCssClass = "", $strLanguage = "", $strSeoAddon = "") {
	$strReturn = "";

	$strHref = getLinkPortalHref($strPageI, $strPageE, $strAction, $strParams, $strSystemid, $strLanguage, $strSeoAddon);

	if($strTarget == "")
		$strTarget = "_self";

	$strReturn .="<a href=\"".$strHref."\" target=\"".$strTarget."\" ".($strCssClass != "" ? " class=\"".$strCssClass."\" ": "").">".$strText."</a>";

	return $strReturn;
}

/**
 * Creates a raw Link for the portal (just the href)
 *
 * @param string $strPageI
 * @param string $strPageE
 * @param string $strAction
 * @param string $strParams
 * @param string $strSystemid
 * @param string $strLanguage
 * @param string $strSeoAddon Only used if using mod_rewrite
 * @return string
 */
function getLinkPortalHref($strPageI, $strPageE = "", $strAction = "", $strParams = "", $strSystemid = "", $strLanguage = "", $strSeoAddon = "") {
	$strReturn = "";
	$bitInternal = true;

	//return "#" if neither an internal nor an external page is set
    if($strPageI == "" && $strPageE == "")
        return "#";

	//Internal links are more important than external links!
	if($strPageI == "" && $strPageE != "")
		$bitInternal = false;

    //if given, remove first & from params
    if(substr($strParams, 0, 1) == "&")
        $strParams = substr($strParams, 1);

	$strParams = str_replace("&", "&amp;", $strParams);
    //create an array out of the params

    // any anchors set to the page?
    $strAnchor = "";
    if(uniStrpos($strPageI, "#") !== false) {
        //get anchor, remove anchor from link
        $strAnchor = urlencode(uniSubstr($strPageI, uniStrpos($strPageI, "#")+1));
        $strPageI = uniSubstr($strPageI, 0, uniStrpos($strPageI, "#"));
    }

	//urlencoding
    $strPageI = urlencode($strPageI);
    $strAction = urlencode($strAction);
    //$strParams = urlencode($strParams);

	//more than one language installed?
	$intNumberOfLanguages = class_module_languages_language::getNumberOfLanguagesAvailable(true);

	if($strLanguage == "" && $intNumberOfLanguages > 1) {
		$objCommon = new class_module_system_common();
		$strLanguage = $objCommon->getStrPortalLanguage();
	}
	else if($strLanguage != "" && $intNumberOfLanguages <=1)
	    $strLanguage = "";

    $strHref = "";
	if($bitInternal) {
	    //check, if we could use mod_rewrite
	    $bitRegularLink = true;
	    if(_system_mod_rewrite_ == "true") {

            //used later to add seo-relevant keywords
            $objPage = class_module_pages_page::getPageByName($strPageI);
            if($strLanguage != "") {
                $objPage->setStrLanguage($strLanguage);
                $objPage->initObject();
            }

            $strAddKeys = $objPage->getStrSeostring().($strSeoAddon != "" && $objPage->getStrSeostring() != "" ? "-" : "").urlSafeString($strSeoAddon);
            if(uniStrlen($strAddKeys) > 0 && uniStrlen($strAddKeys) <=2 )
                $strAddKeys .= "__";

            //trim string
            $strAddKeys = uniStrTrim($strAddKeys, 100, "");

            //ok, here we go. scheme for rewrite_links: pagename.addKeywords.action.systemid.language.html
            //but: special case: just pagename & language
            if($strAction == ""&& $strSystemid == "" && $strAddKeys == "" && $strLanguage != "")
                $strHref .= $strPageI.".".$strLanguage.".html";
            elseif($strAction == "" && $strSystemid == "" && $strLanguage == "")
                $strHref .= $strPageI.($strAddKeys == "" ? "" : ".".$strAddKeys).".html";
            elseif($strAction != "" && $strSystemid == "" && $strLanguage == "")
                $strHref .= $strPageI.".".$strAddKeys.".".$strAction .".html";
            elseif($strSystemid != "" && $strLanguage == "")
                $strHref .= $strPageI.".".$strAddKeys.".".$strAction .".".$strSystemid.".html";
            else
                $strHref .= $strPageI.".".$strAddKeys.".".$strAction .".".$strSystemid.".".$strLanguage.".html";

            //params?
            if($strParams != "")
                $strHref .= "?".$strParams;

            // add anchor if given
            if($strAnchor != "")
                $strHref .= "#".$strAnchor;

            //plus the domain as a prefix
            $strHref = "_webpath_"."/".$strHref;


            $bitRegularLink = false;

	    }

        if($bitRegularLink)
		    $strHref = "_indexpath_"."?".($strPageI != "" ? "page=".$strPageI : "" )."".
		                              ($strSystemid != "" ? "&amp;systemid=".$strSystemid : "" ).
		                              ($strAction != "" ? "&amp;action=".$strAction : "").
		                              ($strLanguage != "" ? "&amp;language=".$strLanguage : "").
		                              ($strParams != "" ? "&amp;".$strParams : "" ).
		                              ($strAnchor != "" ? "#".$strAnchor : "")."";
	}
	else {
		$strHref = $strPageE;
	}

	$strReturn .= $strHref;

	return $strReturn;
}

/**
 * Generates a link opening in a popup in portal-area
 *
 * @param string $strPageI
 * @param string $strPageE
 * @param string $strAction
 * @param string $strParams
 * @param string $strSystemid
 * @param string $strTitle
 * @param int $intWidth
 * @param int $intHeight
 * @return string
 */
function getLinkPortalPopup($strPageI, $strPageE, $strAction = "", $strParams = "", $strSystemid = "", $strTitle = "", $intWidth = "500", $intHeight = "500") {

    $strLink = getLinkPortalHref($strPageI, $strPageE, $strAction, $strParams, $strSystemid);

	$strLink = "<a href=\"$strLink\" onclick=\"return !window.open('".$strLink."','".$strTitle."','scrollbars=yes,resizable=yes,width=".$intWidth.",height=".$intHeight."')\" title=\"".$strTitle."\">".$strTitle."</a>";

	return $strLink;
}

/**
 * Splits up a html-link into its parts, such as
 * link, name, href
 *
 * @param string $strLink
 * @return array
 */
function splitUpLink($strLink) {
    //use regex to get href and name
    $arrHits = array();
    preg_match("/<a href=\"([^\"]+)\"\s+([^>]*)>(.*)<\/a>/i", $strLink, $arrHits);
    $arrReturn = array();
    $arrReturn["link"] = $strLink;
    $arrReturn["name"] = isset($arrHits[3]) ? $arrHits[3] : "" ;
    $arrReturn["href"] = isset($arrHits[1]) ? $arrHits[1] : "";
    return $arrReturn;
}


/**
 * Changes HTML to simple printable strings
 *
 * @param string $strHtml
 * @param bool $bitEntities
 * @param bool $bitEscapeCrlf
 * @return string
 */
function htmlToString($strHtml, $bitEntities = false, $bitEscapeCrlf = true) {
	$strReturn = $strHtml;

	if($bitEntities) {
		$strReturn = htmlentities($strHtml, ENT_COMPAT, "UTF-8");
	}
	else {
	    if(get_magic_quotes_gpc() == 0)
		   $strReturn = str_replace("'", "\'", $strHtml);
	}
	$arrSearch = array();
	if($bitEscapeCrlf) {
    	$arrSearch[] = "\r\n";
    	$arrSearch[] = "\n\r";
    	$arrSearch[] = "\n";
    	$arrSearch[] = "\r";
	}
    $arrSearch[] = "%%";

	$arrReplace = array();
	if($bitEscapeCrlf) {
    	$arrReplace[] = "<br />";
    	$arrReplace[] = "<br />";
    	$arrReplace[] = "<br />";
    	$arrReplace[] = "<br />";
	}
	$arrReplace[] = "\%\%";


	$strReturn = str_replace($arrSearch, $arrReplace, $strReturn);
	return $strReturn;
}

/**
 * Wrapper to phps strip_tags
 * Removes all html and php tags in a string
 *
 * @param string $strHtml
 * @param string $strAllowTags
 * @return string
 */
function htmlStripTags ($strHtml, $strAllowTags = "") {
	$strReturn = strip_tags($strHtml, $strAllowTags);
	return $strReturn;
}

/**
 * This function does a few cleanups and optimizations on HTML content generated via the WYSIWYG editor.
 * Should be used everytime before HTML content generated via the WYSIWYG editor is going to be saved.
 * E.g. it replaces absolute URLs with dynamic _webpath_ and synchronizes the width/height style-values set
 * by WYSIWYG editor for on-the-fly images (starting with image.php?image=...)
 *
 * For example:
 *      <img src="http://www.mydomain.com/image.php?image=/portal/pics/myimage.jpg&maxHeight=200" style="width: 100px; height: 100px" />
 * becomes
 *      <img src="_webpath_/image.php?image=/portal/pics/myimage.jpg&maxHeight=100" style="width: 100px; height: 100px" />
 *
 * @param string $strHtmlContent
 * @return string
 */
function processWysiwygHtmlContent($strHtmlContent) {
    //replace the webpath to remain flexible
    $strHtmlContent = uniStrReplace(_webpath_, "_webpath_", $strHtmlContent);

    $strHtmlContent = uniStrReplace("%%", "\%\%", $strHtmlContent);

    //synchronize the width/height style-values set via WYSIWYG editor for on-the-fly images
    $arrImages = "";
    preg_match_all('!image\.php\?image=([/\-\._a-zA-Z0-9]*)([&;=a-zA-Z0-9]*)\" ([\"\'&;:\ =a-zA-Z0-9]*)width: ([0-9]*)px; height: ([0-9]*)px;!', $strHtmlContent, $arrImages);
    for($i = 0; $i < sizeof($arrImages[0]); ++$i) {
        $strSearch = $arrImages[0][$i];
        $strNewWidth = $arrImages[4][$i];
        $strNewHeight = $arrImages[5][$i];

        //only add one parameter to optimize unproportional scaling
        $strScalingParams = $strNewWidth >= $strNewHeight ? "&amp;maxWidth=".$strNewWidth : "&amp;maxHeight=".$strNewHeight;

        $strReplace = "image.php?image=".$arrImages[1][$i].$strScalingParams."\" ".$arrImages[3][$i]."width: ".$strNewWidth."px; height: ".$strNewHeight."px;";
        $strHtmlContent = uniStrReplace($strSearch, $strReplace, $strHtmlContent);
    }

    return $strHtmlContent;
}

/**
 * @deprecated Doesn't make that much sense?!
 * @todo please check if needed, maybe remove method
 * Encodes an url to be more safe but being less strict than urlencode()
 *
 * @param string $strText
 * @return string
 */
function saveUrlEncode($strText) {
	$arraySearch = array(" ");
	$arrayReplace = array("%20");
	return str_replace($arraySearch, $arrayReplace, $strText);
}

/**
 * Replaces some special characters with url-safe characters and removes any other special characters.
 * Should be used whenever a string is placed into an URL
 *
 * @param string $strText
 * @return string
 */
function urlSafeString($strText) {
    $strReturn = "";
    if($strText == "")
        return "";

	$strText = html_entity_decode($strText, ENT_COMPAT, "UTF-8");

	$arrSearch  = array(" ", "/", "&", "+", ".", ":", ",", ";", "=", "ä",  "Ä",  "ö",  "Ö",  "ü",  "Ü",  "ß");
	$arrReplace = array("-", "-", "-", "-", "-", "-", "-", "-", "-", "ae", "Ae", "oe", "Oe", "ue", "Ue", "ss");

	$strReturn = str_replace($arrSearch, $arrReplace, $strText);

	//remove all other special characters
	$strReturn = preg_replace("/[^A-Za-z0-9_-]/", "", $strReturn);

	return $strReturn;
}

/**
 * Removes traversals like ../ from the passed string
 * @param string $strFilename
 * @return string
 */
function removeDirectoryTraversals($strFilename) {
    $strFilename = urldecode($strFilename);
    return uniStrReplace("..", "", $strFilename);
}

/**
 * Creates a filename valid for filesystems
 *
 * @param string $strName
 * @param bool $bitFolder
 * @return string
 */
function createFilename($strName, $bitFolder = false) {
	$strReturn = "";

	$strName = uniStrtolower($strName);

	if(!$bitFolder)
		$strEnding = uniSubstr($strName, (uniStrrpos($strName, ".")+1));
	else
		$strEnding = "";

	if(!$bitFolder)
		$strReturn = uniSubstr($strName, 0, (uniStrrpos($strName, ".") ));
	else
		$strReturn = $strName;

	//Filter non allowed chars
	$arrSearch = 		array( " ", ".", ":", "ä", "ö", "ü", "/", "ß", "!");
	$arrReplace = 		array( "_", "_", "_","ae","oe","ue", "_","ss", "_");

	$strReturn = uniStrReplace($arrSearch, $arrReplace, $strReturn);

	//and the ending
	if(!$bitFolder)
	   $strEnding = uniStrReplace($arrSearch, $arrReplace, $strEnding);

	$strTemp = "";
	//search for other, unknown chars and replace them
	for($intI = 0; $intI < uniStrlen($strReturn); $intI++) {
	    //no match right up till here -> doin the hard way
	    if(!preg_match("/[a-z0-9-_]/", $strReturn[$intI]))
	        $strTemp .= "-";
	    else
	        $strTemp .= $strReturn[$intI];

	}

	//do a replacing in the ending, too
	if($strEnding != "") {
    	$strTempEnding = "";
        //search for other, unknown chars and replace them
        for($intI = 0; $intI < uniStrlen($strEnding); $intI++) {
            //no match right up till here -> doin the hard way
            if(!preg_match("/[a-z0-9-_]/", $strEnding[$intI]))
                $strTempEnding .= "-";
            else
                $strTempEnding .= $strEnding[$intI];
        }

        $strEnding = ".".$strTempEnding;

	}

	$strReturn = $strTemp.$strEnding;

	return $strReturn;
}

/**
 * Validates if the passed string is a valid mail-address
 *
 * @param string $strAddress
 * @return bool
 * @deprecated use class_email_validator instead
 */
function checkEmailaddress($strAddress) {
    $objValidator = new class_email_validator();
    return $objValidator->validate($strAddress);
}

/**
 * Validates, if the passed value is numeric
 *
 * @param int $intNumber
 * @return bool
 * @deprecated use class_numeric_validator instead
 */
function checkNumber($intNumber) {
    $objValidator = new class_numeric_validator();
    return $objValidator->validate($intNumber);
}

/**
 * Validates, if the passed Param represents a valid folder in the filesystem
 *
 * @param string $strPath
 * @return bool
 */
function checkFolder($strPath) {
	$bitTest = is_dir(_realpath_.$strPath) && strlen($strPath) > 0;
	if($bitTest === false)
		return false;
	else
		return true;
}

/**
 * Checks the length of a passed string
 *
 * @param string $strText
 * @param int $intMin
 * @param int $intMax
 * @return bool
 *
 * @deprecated replaced by @link{class_text_validator}
 * @see interface_validator
 */
function checkText($strText, $intMin = 1, $intMax = 0) {
    $objValidator = new class_text_validator();
    return $objValidator->validate($strText);
}


/**
 * Generates a new SystemID
 *
 * @return string The new SystemID
 */
function generateSystemid() {
	//generate md5 key
	$strKey = md5(_realpath_);

	$strTemp = "";

	//Do the magic: take out 6 characters randomly...
	for($intI = 0; $intI < 7; $intI++) {
		$intTemp = rand(0, 31);
		$strTemp .= $strKey[$intTemp];
	}

	$intId = uniqid($strTemp);

	return $intId;
}


/**
 * Checks a systemid for the correct syntax
 *
 * @param string $strtID
 * @return bool
 */
function validateSystemid($strID) {

	//Check against wrong characters
	if(preg_match("/([a-z|A-a|0-9]){20}/", $strID)) {
		return true;
	}
	else
		return false;
}

/**
 * Wrapper to dbSafeString of class_db
 *
 * @param string $strString
 * @param string $bitHtmlEntities escape html-entities?
 * @see class_db::dbSafeString($strString, $bitHtmlEntities = true)
 * @return string
 */
function dbsafeString($strString, $bitHtmlEntities = true) {
    return class_carrier::getInstance()->getObjDB()->dbsafeString($strString, $bitHtmlEntities);
}

/**
 * Makes a string safe for xml-outputs
 *
 * @param string $strString
 * @return string
 */
function xmlSafeString($strString) {

    $strString = html_entity_decode($strString, ENT_COMPAT, "UTF-8");
    //but: encode &, <, >
    $strString = str_replace(array("&", "<", ">"), array("&amp;", "&lt;", "&gt;"), $strString);

    return $strString;
}

// --- String-Functions ---------------------------------------------------------------------------------


/**
 * Wrapper to phps strpos
 *
 * @param string $strHaystack
 * @param string $strNeedle
 * @return int
 */
function uniStrpos($strHaystack, $strNeedle) {
    if(_mbstringloaded_)
        return mb_strpos($strHaystack, $strNeedle);
    else
        return strpos($strHaystack, $strNeedle);
}



/**
 * Wrapper to phps strrpos
 *
 * @param string $strHaystack
 * @param string $strNeedle
 * @return int
 */
function uniStrrpos($strHaystack, $strNeedle) {
    if(_mbstringloaded_)
        return mb_strrpos($strHaystack, $strNeedle);
    else
        return strrpos($strHaystack, $strNeedle);
}

/**
 * Wrapper to phps stripos
 *
 * @param string $strHaystack
 * @param string $strNeedle
 * @return int
 */
function uniStripos($strHaystack, $strNeedle) {
    if(_mbstringloaded_ && function_exists("mb_stripos"))
        return mb_stripos($strHaystack, $strNeedle);
    else
        return stripos($strHaystack, $strNeedle);
}

/**
 * Wrapper to phps strlen
 *
 * @param string $strString
 * @return int
 */
function uniStrlen($strString) {
    if(_mbstringloaded_)
        return mb_strlen($strString);
    else
        return strlen($strString);
}

/**
 * Wrapper to phps strtolower, due to problems with UTF-8 on some configurations
 *
 * @param string $strString
 * @return string
 */
function uniStrtolower($strString) {
    if(_mbstringloaded_)
        return mb_strtolower($strString);
    else
        return strtolower($strString);
}

/**
 * Wrapper to phps strtoupper, due to problems with UTF-8 on some configurations
 *
 * @param string $strString
 * @return string
 */
function uniStrtoupper($strString) {
    if(_mbstringloaded_)
        return mb_strtoupper($strString);
    else
        return strtoupper($strString);
}

/**
 * Wrapper to phps substr
 *
 * @param string $strString
 * @param int $intStart
 * @param int $intEnd
 * @return string
 */
function uniSubstr($strString, $intStart, $intEnd = "") {
    if(_mbstringloaded_) {
        if($intEnd == "")
            return mb_substr($strString, $intStart);
        else
            return mb_substr($strString, $intStart, $intEnd);
    }
    else {
        if($intEnd == "")
            return substr($strString, $intStart);
        else
            return substr($strString, $intStart, $intEnd);
    }
}

/**
 * Wrapper to phps ereg
 *
 * @param string $strPattern
 * @param string $strString
 * @return int
 */
function uniEreg($strPattern, $strString) {
    if(_mbstringloaded_)
        return mb_ereg($strPattern, $strString);
    else
        return preg_match("/".$strPattern."/", $strString);
}

/**
 * Unicode-safe wrapper to strReplace
 *
 * @param mixed $mixedSearch array or string
 * @param mixed $mixedReplace array or string
 * @param string $strSubject
 * @param bool $bitUnicodesafe
 * @return mixed
 */
function uniStrReplace($mixedSearch, $mixedReplace, $strSubject, $bitUnicodesafe = false) {
    if($bitUnicodesafe) {
        if(!is_array($mixedSearch)) {
            $mixedSearch = '!'.preg_quote($mixedSearch,'!').'!u';
        }
        else{
            foreach ($mixedSearch as $strKey => $strValue) {
                $mixedSearch[$strKey] = '!'.preg_quote($strValue).'!u';
            }
        }
        return preg_replace($mixedSearch, $mixedReplace, $strSubject);
    }
    else {
        return str_replace($mixedSearch, $mixedReplace, $strSubject);
    }
}

/**
 * Unicode-safe string trimmer
 *
 * @param string $strString string to wrap
 * @param int $intLength
 * @param string $strAdd string to add after wrapped string
 * @return string
 */
function uniStrTrim($strString, $intLength, $strAdd = "…") {
    if($intLength > 0 && uniStrlen($strString) > $intLength) {
		return trim(uniSubstr($strString, 0, $intLength)).$strAdd;
    }
    else {
        return $strString;
    }
}

/**
 * Sends headers to the client, to allow conditionalGets
 *
 * @param string $strChecksum Checksum of the content. Must be unique for one state.
 */
function sendConditionalGetHeaders($strChecksum) {
    header("ETag: ".$strChecksum);
    header("Cache-Control: max-age=86400, must-revalidate");

}


/**
 * Checks, if the browser sent the same checksum as provided. If so,
 * a http 304 is sent to the browser
 *
 * @param string $strChecksum
 * @return bool
 */
function checkConditionalGetHeaders($strChecksum) {
    if(issetServer("HTTP_IF_NONE_MATCH")) {
        if(getServer("HTTP_IF_NONE_MATCH") == $strChecksum) {
            //strike. no further actions needed.
            header(class_http_statuscodes::$strSC_NOT_MODIFIED);
            header("ETag: ".$strChecksum);
            header("Cache-Control: max-age=86400, must-revalidate");

            return true;
        }
    }

    return false;
}

