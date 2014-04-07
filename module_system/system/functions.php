<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
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
 * Helper to load and scan all module-ids available. Triggered by the bootloader.
 * Change with care
 *
 * @return void
 */
function bootstrapIncludeModuleIds() {
    //Module-Constants
    foreach(scandir(_realpath_) as $strRootFolder) {

        if(uniStrpos($strRootFolder, "core") === false)
            continue;

        foreach(scandir(_realpath_."/".$strRootFolder) as $strDirEntry ) {
            if(is_dir(_realpath_."/".$strRootFolder."/".$strDirEntry) && is_dir(_realpath_."/".$strRootFolder."/".$strDirEntry."/system/") && is_dir(_realpath_."/".$strRootFolder."/".$strDirEntry."/system/config/")) {
                foreach(scandir(_realpath_."/".$strRootFolder."/".$strDirEntry."/system/config/") as $strModuleEntry ) {
                    if(preg_match("/module\_([a-z\_])+\_id\.php/", $strModuleEntry))
                        @include_once _realpath_."/".$strRootFolder."/".$strDirEntry."/system/config/".$strModuleEntry;
                }
            }
        }
    }
}

/**
 * Returns a value from the GET-array
 *
 * @param string $strKey
 * @return string
 * @deprecated use @link{class_carrier::getInstance()->getParam("")} instead
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
 * @deprecated use @link{class_carrier::getInstance()->getParam("")} instead
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
 * Please indicate whether the source is encoded in "multipart/form-data", in this case
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
 * @return string
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
 * @see class_carrier::getAllParams()
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
 * @deprecated
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
 * @deprecated
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
 * @deprecated
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
    return class_link::getLinkAdminManual($strLinkContent, $strText, $strAlt, $strImage, $strImageId, $strLinkId, $bitTooltip, $strCss);
}

/**
 * Generates a link for the admin-area
 *
 * @deprecated
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
    return class_link::getLinkAdmin($strModule, $strAction, $strParams, $strText, $strAlt, $strImage, $bitTooltip, $strCss);
}

/**
 * Generates a link for the admin-area
 *
 * @deprecated
 *
 * @param string $strModule
 * @param string $strAction
 * @param string $strParams
 * @param bool $bitEncodedAmpersand
 * @return string
 */
function getLinkAdminHref($strModule, $strAction = "", $strParams = "", $bitEncodedAmpersand = true) {
    return class_link::getLinkAdminHref($strModule, $strAction, $strParams, $bitEncodedAmpersand);
}

/**
 * Generates an admin-url to trigger xml-requests. Takes care of url-rewriting
 *
 * @deprecated
 *
 * @param $strModule
 * @param string $strAction
 * @param string $strParams
 * @param bool $bitEncodedAmpersand
 *
 * @return mixed|string
 */
function getLinkAdminXml($strModule, $strAction = "", $strParams = "", $bitEncodedAmpersand = false) {
    return class_link::getLinkAdminXml($strModule, $strAction, $strParams, $bitEncodedAmpersand);
}


/**
 * Generates a link opening in a popup in admin-area
 *
 * @deprecated
 *
 * @param string $strModule
 * @param string $strAction
 * @param string $strParams
 * @param string $strText
 * @param string $strAlt
 * @param string $strImage
 * @param int|string $intWidth
 * @param int|string $intHeight
 * @param string $strTitle
 * @param bool $bitTooltip
 * @param bool $bitPortalEditor
 *
 * @return string
 */
function getLinkAdminPopup($strModule, $strAction, $strParams = "", $strText = "", $strAlt = "", $strImage = "", $intWidth = "500", $intHeight = "500", $strTitle = "", $bitTooltip = true, $bitPortalEditor = false) {
    return class_link::getLinkAdminPopup($strModule, $strAction, $strParams, $strText, $strAlt, $strImage, $intWidth, $intHeight, $strTitle, $bitTooltip, $bitPortalEditor);
}

/**
 * Generates a link opening in a dialog in admin-area
 *
 * @deprecated
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
    return class_link::getLinkAdminDialog($strModule, $strAction, $strParams, $strText, $strAlt, $strImage, $strTitle, $bitTooltip, $bitPortalEditor, $strOnClick, $intWidth, $intHeight);
}

/**
 * Returns an image-tag with surrounding tooltip
 *
 * @param string $strImage
 * @param string $strAlt
 * @param bool $bitNoAlt
 *
 * @return string
 * @deprecated replaced by class_adminskin_helper::getAdminImage()
 * @see class_adminskin_helper::getAdminImage()
 */
function getImageAdmin($strImage, $strAlt="", $bitNoAlt = false) {
    return class_adminskin_helper::getAdminImage($strImage, $strAlt, $bitNoAlt);
}

/**
 * Determines the rights-filename of a system-record. Looks up if the record
 * uses its' own rights or inherits the rights from another record.
 *
 * @param string $strSystemid
 * @return string
 * @todo move to toolkit
 */
function getRightsImageAdminName($strSystemid) {
    if(class_carrier::getInstance()->getObjRights()->isInherited($strSystemid))
        return "icon_key_inherited";
    else
        return "icon_key";
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
    }
    else if(strpos($strBytes, "k") !== false) {
        $intReturn = str_replace("m", "", $strBytes);
        $intReturn = $intReturn * 1024;
    }
    else if(strpos($strBytes, "g") !== false) {
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
 *
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

        while($intTemp > 1024) {
            $intTemp = $intTemp / 1024;
            $intCounter++;
        }

        $strReturn = number_format($intTemp, 2)." ".$arrFormats[$intCounter];
        return $strReturn;
    }
    return $strReturn;
}

/**
 * Changes a timestamp to a readable string
 *
 * @param int $intTime
 * @param bool $bitLong
 *
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

    //if the $objDate is a string, convert it to date object
    if($objDate != null && !$objDate instanceof class_date && uniEreg("([0-9]){14}", $objDate)) {
        $objDate = new class_date($objDate);
    }

    if($objDate instanceof class_date) {

        //convert to a current date
        if($strFormat == "") {
            if($bitLong)
                $strReturn = uniStrtolower(class_carrier::getInstance()->getObjLang()->getLang("dateStyleLong", "system"));
            else
                $strReturn = uniStrtolower(class_carrier::getInstance()->getObjLang()->getLang("dateStyleShort", "system"));
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
    if(strlen($color) != 6) {
        return array(0,0,0);
    }
    $rgb = array();
    for ($x=0;$x<3;$x++){
        $rgb[$x] = hexdec(substr($color, (2*$x), 2));
    }
    return $rgb;
}

/**
 * Converts an array of R,G,B values to its matching hex-pendant
 * @param $arrRGB
 * @return string
 */
function rgb2hex($arrRGB) {
    $strHex = "";
    foreach ($arrRGB as $intColor) {
        if($intColor > 255)
            $intColor = 255;

        $strHexVal = dechex($intColor);
        if(uniStrlen($strHexVal) == 1)
            $strHexVal = '0'.$strHexVal;
        $strHex .= $strHexVal;
    }
    return  "#".$strHex;
}



/**
 * Creates a Link for the portal
 *
 * @deprecated
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
    return class_link::getLinkPortal($strPageI, $strPageE, $strTarget, $strText, $strAction, $strParams, $strSystemid, $strCssClass, $strLanguage, $strSeoAddon);
}

/**
 * Creates a raw Link for the portal (just the href)
 *
 * @deprecated
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
    return class_link::getLinkPortalHref($strPageI, $strPageE, $strAction, $strParams, $strSystemid, $strLanguage, $strSeoAddon);
}

/**
 * Generates a link opening in a popup in portal-area
 *
 * @deprecated
 *
 * @param string $strPageI
 * @param string $strPageE
 * @param string $strAction
 * @param string $strParams
 * @param string $strSystemid
 * @param string $strTitle
 * @param int|string $intWidth
 * @param int|string $intHeight
 *
 * @return string
 */
function getLinkPortalPopup($strPageI, $strPageE, $strAction = "", $strParams = "", $strSystemid = "", $strTitle = "", $intWidth = "500", $intHeight = "500") {
    return class_link::getLinkPortalPopup($strPageI, $strPageE, $strAction, $strParams, $strSystemid, $strTitle, $intWidth, $intHeight);
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
 * Tries to find all links in a given string and creates a-tags around them.
 * @param $strText
 *
 * @return string
 * @since 4.3
 * @todo: white-space handling is still messed up
 */
function replaceTextLinks($strText) {
    return preg_replace('#([^href=("|\')|^>]((http|https|ftp)://)[^ |^<|^>]+)#', '<a href="\1">\1</a>', $strText);
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
function htmlStripTags($strHtml, $strAllowTags = "") {
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
    $arrSearch =  array( " ", ".", ":", "ä", "ö", "ü", "/", "ß", "!");
    $arrReplace = array( "_", "_", "_","ae","oe","ue", "_","ss", "_");

    $strReturn = uniStrReplace($arrSearch, $arrReplace, $strReturn);

    //and the ending
    if(!$bitFolder)
       $strEnding = uniStrReplace($arrSearch, $arrReplace, $strEnding);

    //remove all other special characters
    $strTemp = preg_replace("/[^A-Za-z0-9_-]/", "", $strReturn);

    //do a replacing in the ending, too
    if($strEnding != "") {
        //remove all other special characters
        $strEnding = ".".preg_replace("/[^A-Za-z0-9_-]/", "", $strEnding);

    }

    $strReturn = $strTemp.$strEnding;

    return $strReturn;
}

/**
 * Returns the file extension for a file (including the dot).
 *
 * @param string $strPath
 * @return string
 */
function getFileExtension($strPath) {
    return uniStrtolower(uniSubstr($strPath, uniStrrpos($strPath, ".")));
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
 * @param string $strID
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
 * @param bool|string $bitHtmlEntities escape html-entities?
 *
 * @deprecated use class_db::dbSafeString() instead
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
 * @param int|string $intEnd
 *
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
            $mixedSearch = '!'.preg_quote($mixedSearch, '!').'!u';
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
function setConditionalGetHeaders($strChecksum) {
    class_response_object::getInstance()->addHeader("ETag: ".$strChecksum);
    class_response_object::getInstance()->addHeader("Cache-Control: max-age=86400, must-revalidate");

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
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_NOT_MODIFIED);
            class_response_object::getInstance()->addHeader("ETag: ".$strChecksum);
            class_response_object::getInstance()->addHeader("Cache-Control: max-age=86400, must-revalidate");

            return true;
        }
    }

    return false;
}

