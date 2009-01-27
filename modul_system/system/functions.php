<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                                *
********************************************************************************************************/

/**
 * @package modul_system
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
 */
function getAllPassedParams() {
    return array_merge(getArrayGet(), getArrayPost(), getArrayFiles());
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
            $strLink = "<a ".$strLinkContent." \" title=\"".$strAlt."\" ".($strLinkId != "" ? "id=\"".$strLinkId."\"" : "")." ><img src=\""._skinwebpath_."/pics/".$strImage."\" alt=\"".$strAlt."\" ".($strImageId != "" ? "id=\"".$strImageId."\"" : "")." /></a>";
        else
            $strLink = "<a ".$strLinkContent." \" title=\"".$strAlt."\" class=\"showTooltip\" ".($strLinkId != "" ? "id=\"".$strLinkId."\"" : "")." ><img src=\""._skinwebpath_."/pics/".$strImage."\" alt=\"".$strAlt."\" title=\"\" ".($strImageId != "" ? "id=\"".$strImageId."\"" : "")." /></a>";
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
			$strLink = "<a href=\"".getLinkAdminHref($strModule, $strAction, $strParams)."\" title=\"".$strAlt."\" class=\"showTooltip\"><img src=\""._skinwebpath_."/pics/".$strImage."\" alt=\"".$strAlt."\" title=\"\" /></a>";
	}

	if($strImage == "" && $strText != "") 	{
		if($strAlt == "")
			$strAlt = $strText;
		$strLink = "<a href=\"".getLinkAdminHref($strModule, $strAction, $strParams)."\" title=\"".$strAlt."\" ".($strCss!= "" ? " class=\"".$strCss."\"" : "").">".$strText."</a>";
	}

	return $strLink;
}

/**
 * Generates a link for the admin-area
 *
 * @param string $strModule
 * @param string $strAction
 * @param string $strParams
 * @return string
 */
function getLinkAdminHref($strModule, $strAction, $strParams = "") {

	//optimizing params
	if($strParams != "")
		$strParams = str_replace("&", "&amp;", $strParams);
				
    //systemid in params?
    $strSystemid = "";
    $arrParams = explode("&amp;", $strParams);
    
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
    if(_system_mod_rewrite_ == "true" && count($arrParams) == 0) {
    	
    	//scheme: /admin/module.action.systemid
    	$strLink = _webpath_."/admin/".$strModule.".".$strAction.".".$strSystemid.".html";
    	
    
    }
    else
	   $strLink = ""._indexpath_."?admin=1&amp;module=".$strModule."&amp;action=".$strAction.$strParams."";

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
			$strLink = "<a href=\"".$strParams."\" target=\"".$strTarget."\" title=\"".$strAlt."\" class=\"showTooltip\"><img src=\""._skinwebpath_."/pics/".$strImage."\" alt=\"".$strAlt."\" /></a>";
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
 * @param int $intHeight
 * @param int $intWidth
 * @param string $strTitle
 * @param bool $bitTooltip
 * @param bool $bitPortalEditor
 * @return string
 */
function getLinkAdminPopup($strModule, $strAction, $strParams = "", $strText = "", $strAlt="", $strImage="", $intHeight = "500", $intWidth = "500", $strTitle = "", $bitTooltip = true, $bitPortalEditor = false) {
    $strLink = "";
	//if($strParams != "")
	//	$strParams = str_replace("&", "&amp;", $strParams);
		
	//urlencoding
    $strModule = urlencode($strModule);
    $strAction = urlencode($strAction);

	if($bitPortalEditor)
        $strParams .= "&amp;pe=1";
        
	if($strImage != "") {
		if($strAlt == "")
			$strAlt = $strAction;

		if(!$bitTooltip)
			$strLink = "<a href=\"#\" onclick=\"javascript:window.open('".getLinkAdminHref($strModule, $strAction, $strParams)."','".$strTitle."','scrollbars=yes,resizable=yes,width=".$intWidth.",height=".$intHeight."'); return false;\" title=\"".$strAlt."\"><img src=\""._skinwebpath_."/pics/".$strImage."\" alt=\"".$strAlt."\" align=\"absbottom\" /></a>";
		else
			$strLink = "<a href=\"#\" onclick=\"javascript:window.open('".getLinkAdminHref($strModule, $strAction, $strParams)."','".$strTitle."','scrollbars=yes,resizable=yes,width=".$intWidth.",height=".$intHeight."'); return false;\" title=\"".$strAlt."\" class=\"showTooltip\"><img src=\""._skinwebpath_."/pics/".$strImage."\" alt=\"".$strAlt."\" align=\"absbottom\" /></a>";
	}

	if($strImage == "" && $strText != "") {
		if($strAlt == "")
			$strAlt = $strText;
		$strLink = "<a href=\"#\" ".($bitPortalEditor ? "class=\"pe_link\"" : "")." onclick=\"javascript:window.open('"._indexpath_."?admin=1&amp;module=".$strModule."&amp;action=".$strAction.$strParams."','".$strTitle."','scrollbars=yes,resizable=yes,width=".$intWidth.",height=".$intHeight."'); return false;\">".$strText."</a>";
	}
	return $strLink;
}

/**
 * Generates a Mouse-Overn for an image, but doesn't generate a a-tag sourrounding the image
 *
 * @param string $strText
 * @param string $strImage
 * @return string
 */
function getNoticeAdminWithoutAhref($strText, $strImage) {
	return "<img src=\""._skinwebpath_."/pics/".$strImage."\" onmouseover=\"htmlTooltip(this, '".$strText."');\" title=\"\" />";
}

/**
 * Returns a image-tag with sourrounding mouse-overtag
 *
 * @param string $strImage
 * @param string $strAlt
 * @param bool $bitNoAlt
 * @return string
 */
function getImageAdmin($strImage, $strAlt="", $bitNoAlt = false, $strId="") {
	return "<img src=\""._skinwebpath_."/pics/".$strImage."\" alt=\"".($bitNoAlt ? "" : $strAlt)."\" title=\"".($bitNoAlt ? "" : $strAlt)."\" onmouseover=\"htmlTooltip(this, '".$strAlt."');\" ".($strId == "" ? "" : "id=\"".$strId."\"" )." />";
}

/**
 * Determins the rights-filename of a system-record. Looks up if the record
 * uses its' own rights or inherits the rights from another record.
 *
 * @param string $strSystemid
 * @return string
 */
function getRightsImageAdminName($strSystemid) {
	if(class_carrier::getInstance()->getObjRights()->isInherited($strSystemid))
	   return "icon_key_inherited.gif";
	else
	   return "icon_key.gif";   
}


/**
 * Makes out of a bytenumber a human readable string
 *
 * @param int $intBytes
 * @param bool $bitPhpIni (Value ends with M/K/B)
 * @return string
 */
function bytesToString($intBytes, $bitPhpIni = false) {
	$strReturn = "";
	if($intBytes > 0) {
		$arrFormats = array("B", "KB", "MB", "GB", "TB");

		if($bitPhpIni) {
			$intBytes = strtolower($intBytes);

			if(strpos($intBytes, "m") !== false) {
				$intBytes = str_replace("m", "", $intBytes);
				$intBytes = $intBytes * 1024 * 1024;
			}
			if(strpos($intBytes, "k") !== false) {
				$intBytes = str_replace("m", "", $intBytes);
				$intBytes = $intBytes * 1024;
			}
			if(strpos($intBytes, "g") !== false) {
				$intBytes = str_replace("m", "", $intBytes);
				$intBytes = $intBytes * 1024 * 1024 * 1024;
			}

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
			$strReturn = date(class_carrier::getInstance()->getObjText()->getText("dateStyleLong", "system", "admin"), $intTime);
		else
			$strReturn = date(class_carrier::getInstance()->getObjText()->getText("dateStyleShort", "system", "admin"), $intTime);
	}
	return $strReturn;
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

	$strHref = getLinkPortalRaw($strPageI, $strPageE, $strAction, $strParams, $strSystemid, $strLanguage, $strSeoAddon);
	
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
function getLinkPortalRaw($strPageI, $strPageE, $strAction = "", $strParams = "", $strSystemid = "", $strLanguage = "", $strSeoAddon = "") {
	$strReturn = "";
	$bitInternal = true;
	//Internal links are more important than external links!
	if($strPageI == "" && $strPageE != "")
		$bitInternal = false;
	$strParams = str_replace("&", "&amp;", $strParams);
	
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
	include_once(_systempath_."/class_modul_languages_language.php");
	$intNumberOfLanguages = class_modul_languages_language::getNumberOfLanguagesAvailable(true);
	
	if($strLanguage == "" && $intNumberOfLanguages > 1) {
		include_once(_systempath_."/class_modul_system_common.php");
		$objCommon = new class_modul_system_common();
		$strLanguage = $objCommon->getStrPortalLanguage();
	}
	else if($strLanguage != "" && $intNumberOfLanguages <=1)
	    $strLanguage = "";
		
    $strHref = "";
	if($bitInternal) {
	    //chek, if we could use mod_rewrite
	    $bitRegularLink = true;
	    if(_system_mod_rewrite_ == "true") {
            if($strParams == "") {
                //used later to add seo-relevant keywords
                include_once(_systempath_."/class_modul_pages_page.php");
                $objPage = class_modul_pages_page::getPageByName($strPageI);
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
                    
                // add anchor if given
                if($strAnchor != "")
                    $strHref .= "#".$strAnchor;

                //plus the domain as a prefix
                $strHref = _webpath_."/".$strHref;     
                    
                    
                $bitRegularLink = false;
            }
	    }

        if($bitRegularLink)
		    $strHref = "_indexpath_?".($strPageI != "" ? "page=".$strPageI : "" )."".
		                              ($strSystemid != "" ? "&amp;systemid=".$strSystemid : "" ). 
		                              ($strAction != "" ? "&amp;action=".$strAction : "").
		                              ($strLanguage != "" ? "&amp;language=".$strLanguage : "").
		                              ($strParams != "" ? $strParams : "" ).
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
 * @param int $intHeight
 * @param int $intWidth
 * @return string
 */
function getLinkPortalPopup($strPageI, $strPageE, $strAction = "", $strParams = "", $strSystemid = "", $strTitle = "", $intHeight = "500", $intWidth = "500") {
	
    $strLink = getLinkPortalRaw($strPageI, $strPageE, $strAction, $strParams, $strSystemid);

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
    preg_match("/<a href=\"([^\"]+)\"\s+(.*)>(.*)<\/a>/i", $strLink, $arrHits);
    $arrReturn = array();
    $arrReturn["link"] = $strLink;
    $arrReturn["name"] = isset($arrHits[3]) ? $arrHits[3] : "" ;
    $arrReturn["href"] = isset($arrHits[1]) ? $arrHits[1] : "";
    return $arrReturn;
}


/**
 * Changes HTML to databasable ;) strings
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
 * @deprecated Doesn't make that much sense?!
 * @todo please check if needed, maybe remove method
 * Encodes an url to be more safe but being less strict than urlencode()
 *
 * @param string $strText
 * @return string
 */
function saveUrlEncode($strText) {
	$arraySearch = array( 	" ");
	$arrayReplace = array(	"%20");
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
    
	$arrSearch  = array(" ", "/", "&", "+", ".", ":", ",", ";", "=", "ä",  "Ä",  "ö",  "Ö",  "ü",  "Ü",  "ß");
	$arrReplace = array("-", "-", "-", "-", "-", "-", "-", "-", "-", "ae", "Ae", "oe", "Oe", "ue", "Ue", "ss");
	
	$strReturn = str_replace($arrSearch, $arrReplace, $strText);
	
	//remove all other special characters
	$strReturn = ereg_replace("[^A-Za-z0-9_-]", "", $strReturn);
	
	return $strReturn;
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

	$strName = strtolower($strName);

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
 */
function checkEmailaddress($strAddress) {
	$intTest = uniEreg("([A-Za-z0-9])([A-Za-z0-9]|_|-|\.)*@([A-Za-z0-9]|_|-|\.)+\.([A-Za-z])([A-Za-z])+", $strAddress);
	if($intTest === false)
		return false;
	else
		return true;
}

/**
 * Validates, if the passed value is numeric
 *
 * @param int $intNumber
 * @return bool
 */
function checkNumber($intNumber) {
    $bitReturn = is_numeric($intNumber);
    return $bitReturn;
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
 */
function checkText($strText, $intMin = 1, $intMax = 0) {
	$bitReturn = false;
	$intLen = strlen($strText);
	if($intMax == 0) {
		if($intLen >= $intMin)
			$bitReturn = true;
	}
	else {
		if($intLen >= $intMin && $intLen <= $intMax)
			$bitReturn = true;
	}
	return $bitReturn;
}

/**
 * Returns a value for IE or for others
 *
 * @param string $strIe
 * @param string $strOther
 * @return string
 */
function IEOther($strIe, $strOther) {
	$strBrowser = getServer("HTTP_USER_AGENT");
	if(strpos($strBrowser, "IE") !== false)
		return $strIe;
	else
		return $strOther;
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
    if(strlen($strID) != 20)
		return false;

	//Check against wrong characters
	if(ereg("([a-z|A-a|0-9])*", $strID)) {
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
 * Wrapper to phps strtolower
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
        return ereg($strPattern, $strString);
}

/**
 * Wrapper to phps eregi
 *
 * @param string $strPattern
 * @param string $strString
 * @return int
 */
function uniEregi($strPattern, $strString) {
    if(_mbstringloaded_)
        return mb_eregi($strPattern, $strString);
    else
        return eregi($strPattern, $strString);
}

/**
 * Wrapper to phps ereg_replace
 *
 * @param string $strPattern
 * @param string $strReplacement
 * @param string $strString
 * @return int
 */
function uniEregReplace($strPattern, $strReplacement, $strString) {
    if(_mbstringloaded_)
        return mb_ereg_replace($strPattern, $strReplacement, $strString);
    else
        return ereg_replace($strPattern, $strReplacement, $strString);
}

/**
 * Wrapper to phps eregi_replace
 *
 * @param string $strPattern
 * @param string $strReplacement
 * @param string $strString
 * @return int
 */
function uniEregiReplace($strPattern, $strReplacement, $strString) {
    if(_mbstringloaded_)
        return mb_eregi_replace($strPattern, $strReplacement, $strString);
    else
        return eregi_replace($strPattern, $strReplacement, $strString);
}

/**
 * Unicode-safe wrapper to strReplace
 *
 * @param mixed $mixedSearch array or string
 * @param mixed $mixedReplace array or string
 * @param string $strSubject
 * @deprecated str_replace is utf8-aware!
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
function uniStrTrim($strString, $intLength, $strAdd = "...") {
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
            include_once(_systempath_."/class_http_statuscodes.php");
            header(class_http_status_codes::$strSC_NOT_MODIFIED);
            header("ETag: ".$strChecksum);
            header("Cache-Control: max-age=86400, must-revalidate");
            
            return true;
        }
    }

    return false;
}

?>