<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                                *
********************************************************************************************************/

require_once (__DIR__."/StringUtil.php");

use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\Carrier;
use Kajona\System\System\Date;
use Kajona\System\System\HttpStatuscodes;
use Kajona\System\System\Link;
use Kajona\System\System\ResponseObject;
use Kajona\System\System\StringUtil;
use Kajona\System\System\Validators\EmailValidator;
use Kajona\System\System\Validators\NumericValidator;
use Kajona\System\System\Validators\TextValidator;

/**
 * @package module_system
 */

//For the sake of different loaders - check again :(
//Mbstring loaded? If yes, we could use unicode-safe string-functions
if (!defined("_mbstringloaded_")) {
    if (extension_loaded("mbstring")) {
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
 *
 * @return string
 * @deprecated use @link{Carrier::getInstance()->getParam("")} instead
 */
function getGet($strKey)
{
    if (issetGet($strKey)) {
        return $_GET[$strKey];
    }
    else {
        return "";
    }
}

/**
 * Returns the complete GET-Array
 *
 * @return mixed
 */
function getArrayGet()
{
    return $_GET;
}

/**
 * Returns the complete FILE-Array
 *
 * @return mixed
 */
function getArrayFiles()
{
    return $_FILES;
}


/**
 * Checks whether a kay exists in GET-array, or not
 *
 * @param string $strKey
 *
 * @return bool
 * @deprecated use Carrier::issetParam
 */
function issetGet($strKey)
{
    if (isset($_GET[$strKey])) {
        return true;
    }
    else {
        return false;
    }
}

/**
 * Returns a value from the Post-array
 *
 * @param string $strKey
 *
 * @return string
 * @deprecated use @link{Carrier::getInstance()->getParam("")} instead
 */
function getPost($strKey)
{
    if (issetPost($strKey)) {
        return $_POST[$strKey];
    }
    else {
        return "";
    }
}

/**
 * Returns the complete POST-array
 *
 * @return mixed
 */
function getArrayPost()
{
    return $_POST;
}

/**
 * Looks, if a key is in POST-array or not
 *
 * @param string $strKey
 *
 * @return bool
 * @deprecated use Carrier::issetParam
 */
function issetPost($strKey)
{
    if (isset($_POST[$strKey])) {
        return true;
    }
    else {
        return false;
    }
}

/**
 * Returns the complete http-post-body as raw-data.
 * Please indicate whether the source is encoded in "multipart/form-data", in this case
 * the data is read another way internally.
 *
 * @param bool $bitMultipart
 *
 * @return string
 * @since 3.4.0
 */
function getPostRawData($bitMultipart = false)
{
    /*
      sidler, 06/2014: removed, since no longer supported and deprecated up from php 5.6

    if($bitMultipart)
        return $HTTP_RAW_POST_DATA;
    else
    */
    return file_get_contents("php://input");
}

/**
 * Returns a value from the SERVER-Array
 *
 * @param mixed $strKey
 *
 * @return string
 */
function getServer($strKey)
{
    if (issetServer($strKey)) {
        return $_SERVER[$strKey];
    }
    else {
        return "";
    }
}

/**
 * Returns all params passed during startup by get, post or files
 *
 * @return array
 * @deprecated use Carrier::getAllParams() instead
 * @see Carrier::getAllParams()
 * @todo remove
 */
function getAllPassedParams()
{
    return Carrier::getAllParams();
}

/**
 * Key in SERVER-Array?
 *
 * @param string $strKey
 *
 * @return bool
 * @deprecated use Carrier::issetParam
 */
function issetServer($strKey)
{
    if (isset($_SERVER[$strKey])) {
        return true;
    }
    else {
        return false;
    }
}

/**
 * Tests, if the requested cookie exists
 *
 * @param string $strKey
 *
 * @return bool
 * @deprecated
 */
function issetCookie($strKey)
{
    return isset($_COOKIE[$strKey]);
}

/**
 * Provides access to the $_COOKIE Array.
 * NOTE: Use the cookie-class to get data from cookies!
 *
 * @param string $strKey
 *
 * @return mixed
 * @deprecated
 */
function getCookie($strKey)
{
    if (issetCookie($strKey)) {
        return $_COOKIE[$strKey];
    }
    else {
        return "";
    }
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
 *
 * @return string
 */
function getLinkAdminManual($strLinkContent, $strText, $strAlt = "", $strImage = "", $strImageId = "", $strLinkId = "", $bitTooltip = true, $strCss = "")
{
    return Link::getLinkAdminManual($strLinkContent, $strText, $strAlt, $strImage, $strImageId, $strLinkId, $bitTooltip, $strCss);
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
 *
 * @return string
 */
function getLinkAdmin($strModule, $strAction, $strParams = "", $strText = "", $strAlt = "", $strImage = "", $bitTooltip = true, $strCss = "")
{
    return Link::getLinkAdmin($strModule, $strAction, $strParams, $strText, $strAlt, $strImage, $bitTooltip, $strCss);
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
 *
 * @return string
 */
function getLinkAdminHref($strModule, $strAction = "", $strParams = "", $bitEncodedAmpersand = true)
{
    return Link::getLinkAdminHref($strModule, $strAction, $strParams, $bitEncodedAmpersand);
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
function getLinkAdminXml($strModule, $strAction = "", $strParams = "", $bitEncodedAmpersand = false)
{
    return Link::getLinkAdminXml($strModule, $strAction, $strParams, $bitEncodedAmpersand);
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
function getLinkAdminPopup($strModule, $strAction, $strParams = "", $strText = "", $strAlt = "", $strImage = "", $intWidth = "500", $intHeight = "500", $strTitle = "", $bitTooltip = true, $bitPortalEditor = false)
{
    return Link::getLinkAdminPopup($strModule, $strAction, $strParams, $strText, $strAlt, $strImage, $intWidth, $intHeight, $strTitle, $bitTooltip, $bitPortalEditor);
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
 *
 * @return string
 */
function getLinkAdminDialog($strModule, $strAction, $strParams = "", $strText = "", $strAlt = "", $strImage = "", $strTitle = "", $bitTooltip = true, $bitPortalEditor = false, $strOnClick = "", $intWidth = null, $intHeight = null)
{
    return Link::getLinkAdminDialog($strModule, $strAction, $strParams, $strText, $strAlt, $strImage, $strTitle, $bitTooltip, $bitPortalEditor, $strOnClick, $intWidth, $intHeight);
}

/**
 * Returns an image-tag with surrounding tooltip
 *
 * @param string $strImage
 * @param string $strAlt
 * @param bool $bitNoAlt
 *
 * @return string
 * @deprecated replaced by AdminskinHelper::getAdminImage()
 * @see AdminskinHelper::getAdminImage()
 */
function getImageAdmin($strImage, $strAlt = "", $bitNoAlt = false)
{
    return AdminskinHelper::getAdminImage($strImage, $strAlt, $bitNoAlt);
}

/**
 * Determines the rights-filename of a system-record. Looks up if the record
 * uses its' own rights or inherits the rights from another record.
 *
 * @param string $strSystemid
 *
 * @return string
 * @todo move to toolkit
 */
function getRightsImageAdminName($strSystemid)
{
    if (Carrier::getInstance()->getObjRights()->isInherited($strSystemid)) {
        return "icon_key_inherited";
    }
    else {
        return "icon_key";
    }
}


/**
 * Converts a php size string (e.g. "4M") into bytes
 *
 * @param int $strBytes
 *
 * @return int
 */
function phpSizeToBytes($strBytes)
{
    $intReturn = 0;

    $strBytes = uniStrtolower($strBytes);

    if (strpos($strBytes, "m") !== false) {
        $intReturn = str_replace("m", "", $strBytes);
        $intReturn = $intReturn * 1024 * 1024;
    }
    elseif (strpos($strBytes, "k") !== false) {
        $intReturn = str_replace("m", "", $strBytes);
        $intReturn = $intReturn * 1024;
    }
    elseif (strpos($strBytes, "g") !== false) {
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
function bytesToString($intBytes, $bitPhpIni = false)
{
    $strReturn = "";
    if ($intBytes >= 0) {
        $arrFormats = array("B", "KB", "MB", "GB", "TB");

        if ($bitPhpIni) {
            $intBytes = phpSizeToBytes($intBytes);
        }

        $intTemp = $intBytes;
        $intCounter = 0;

        while ($intTemp > 1024) {
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
function timeToString($intTime, $bitLong = true)
{
    $strReturn = "";
    if ($intTime > 0) {
        if ($bitLong) {
            $strReturn = date(Carrier::getInstance()->getObjLang()->getLang("dateStyleLong", "system"), $intTime);
        }
        else {
            $strReturn = date(Carrier::getInstance()->getObjLang()->getLang("dateStyleShort", "system"), $intTime);
        }
    }
    return $strReturn;
}

/**
 * Converts a dateobject to a readable string
 *
 * @param Date $objDate
 * @param bool $bitLong
 * @param string $strFormat if given, the passed format will be used, otherwise the format defined in the i18n files
 *                          usable placeholders are: d, m, y, h, i, s
 *
 * @return string
 */
function dateToString($objDate, $bitLong = true, $strFormat = "")
{
    $strReturn = "";

    //if the $objDate is a string, convert it to date object
    if ($objDate != null && !$objDate instanceof Date && uniEreg("([0-9]){14}", $objDate)) {
        $objDate = new Date($objDate);
    }

    if ($objDate instanceof Date) {

        //convert to a current date
        if ($strFormat == "") {
            if ($bitLong) {
                $strReturn = uniStrtolower(Carrier::getInstance()->getObjLang()->getLang("dateStyleLong", "system"));
            }
            else {
                $strReturn = uniStrtolower(Carrier::getInstance()->getObjLang()->getLang("dateStyleShort", "system"));
            }
        }
        else {
            $strReturn = $strFormat;
        }

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
 *
 * @return string
 */
function numberFormat($floatNumber, $intNrOfDecimals = 2)
{
    $strDecChar = Carrier::getInstance()->getObjLang()->getLang("numberStyleDecimal", "system");
    $strThousandsChar = Carrier::getInstance()->getObjLang()->getLang("numberStyleThousands", "system");
    return number_format((float)$floatNumber, $intNrOfDecimals, $strDecChar, $strThousandsChar);
}

/**
 * Converts a hex-string to its rgb-values
 *
 * @see http://www.jonasjohn.de/snippets/php/hex2rgb.htm
 *
 * @param string $color
 *
 * @return array
 */
function hex2rgb($color)
{
    $color = str_replace('#', '', $color);
    if (strlen($color) != 6) {
        return array(0, 0, 0);
    }
    $rgb = array();
    for ($x = 0; $x < 3; $x++) {
        $rgb[$x] = hexdec(substr($color, (2 * $x), 2));
    }
    return $rgb;
}

/**
 * Converts an array of R,G,B values to its matching hex-pendant
 *
 * @param $arrRGB
 *
 * @return string
 */
function rgb2hex($arrRGB)
{
    $strHex = "";
    foreach ($arrRGB as $intColor) {
        if ($intColor > 255) {
            $intColor = 255;
        }

        $strHexVal = dechex($intColor);
        if (uniStrlen($strHexVal) == 1) {
            $strHexVal = '0'.$strHexVal;
        }
        $strHex .= $strHexVal;
    }
    return "#".$strHex;
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
 *
 * @return string
 */
function getLinkPortal($strPageI, $strPageE, $strTarget = "_self", $strText = "", $strAction = "", $strParams = "", $strSystemid = "", $strCssClass = "", $strLanguage = "", $strSeoAddon = "")
{
    return Link::getLinkPortal($strPageI, $strPageE, $strTarget, $strText, $strAction, $strParams, $strSystemid, $strCssClass, $strLanguage, $strSeoAddon);
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
 *
 * @return string
 */
function getLinkPortalHref($strPageI, $strPageE = "", $strAction = "", $strParams = "", $strSystemid = "", $strLanguage = "", $strSeoAddon = "")
{
    return Link::getLinkPortalHref($strPageI, $strPageE, $strAction, $strParams, $strSystemid, $strLanguage, $strSeoAddon);
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
function getLinkPortalPopup($strPageI, $strPageE, $strAction = "", $strParams = "", $strSystemid = "", $strTitle = "", $intWidth = "500", $intHeight = "500")
{
    return Link::getLinkPortalPopup($strPageI, $strPageE, $strAction, $strParams, $strSystemid, $strTitle, $intWidth, $intHeight);
}

/**
 * Splits up a html-link into its parts, such as
 * link, name, href
 *
 * @param string $strLink
 *
 * @return array
 */
function splitUpLink($strLink)
{
    //use regex to get href and name
    $arrHits = array();
    preg_match("/<a href=\"([^\"]+)\"\s+([^>]*)>(.*)<\/a>/i", $strLink, $arrHits);
    $arrReturn = array();
    $arrReturn["link"] = $strLink;
    $arrReturn["name"] = isset($arrHits[3]) ? $arrHits[3] : "";
    $arrReturn["href"] = isset($arrHits[1]) ? $arrHits[1] : "";
    return $arrReturn;
}

/**
 * Tries to find all links in a given string and creates a-tags around them.
 *
 * @param $strText
 *
 * @return string
 * @since 4.3
 * @todo: white-space handling is still messed up
 */
function replaceTextLinks($strText)
{
    $strReplace = preg_replace('#([^href=("|\')|^>]((http|https|ftp|file)://)[^ |^<|^>]+)#', '<a href="\1">\1</a>', $strText);
    return str_replace("a href=\" ", "a href=\"", $strReplace);
}

/**
 * Changes HTML to simple printable strings
 *
 * @param string $strHtml
 * @param bool $bitEntities
 * @param bool $bitEscapeCrlf
 *
 * @return string
 */
function htmlToString($strHtml, $bitEntities = false, $bitEscapeCrlf = true)
{
    $strReturn = $strHtml;

    if ($bitEntities) {
        $strReturn = htmlentities($strHtml, ENT_COMPAT, "UTF-8");
    }
    else {
        if (get_magic_quotes_gpc() == 0) {
            $strReturn = str_replace("'", "\'", $strHtml);
        }
    }
    $arrSearch = array();
    if ($bitEscapeCrlf) {
        $arrSearch[] = "\r\n";
        $arrSearch[] = "\n\r";
        $arrSearch[] = "\n";
        $arrSearch[] = "\r";
    }
    $arrSearch[] = "%%";

    $arrReplace = array();
    if ($bitEscapeCrlf) {
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
 *
 * @return string
 */
function htmlStripTags($strHtml, $strAllowTags = "")
{
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
 *
 * @return string
 */
function processWysiwygHtmlContent($strHtmlContent)
{
    //replace the webpath to remain flexible
    $strHtmlContent = uniStrReplace(_webpath_, "_webpath_", $strHtmlContent);

    $strHtmlContent = uniStrReplace("%%", "\%\%", $strHtmlContent);

    //synchronize the width/height style-values set via WYSIWYG editor for on-the-fly images
    $arrImages = "";
    preg_match_all('!image\.php\?image=([/\-\._a-zA-Z0-9]*)([&;=a-zA-Z0-9]*)\" ([\"\'&;:\ =a-zA-Z0-9]*)width: ([0-9]*)px; height: ([0-9]*)px;!', $strHtmlContent, $arrImages);
    for ($i = 0; $i < sizeof($arrImages[0]); ++$i) {
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
 *
 * @return string
 */
function saveUrlEncode($strText)
{
    $arraySearch = array(" ");
    $arrayReplace = array("%20");
    return str_replace($arraySearch, $arrayReplace, $strText);
}

/**
 * Replaces some special characters with url-safe characters and removes any other special characters.
 * Should be used whenever a string is placed into an URL
 *
 * @param string $strText
 *
 * @return string
 */
function urlSafeString($strText)
{
    if ($strText == "") {
        return "";
    }

    $strText = html_entity_decode($strText, ENT_COMPAT, "UTF-8");

    $arrSearch = array(" ", "/", "&", "+", ".", ":", ",", ";", "=", "ä", "Ä", "ö", "Ö", "ü", "Ü", "ß");
    $arrReplace = array("-", "-", "-", "-", "-", "-", "-", "-", "-", "ae", "Ae", "oe", "Oe", "ue", "Ue", "ss");

    $strReturn = str_replace($arrSearch, $arrReplace, $strText);

    //remove all other special characters
    $strReturn = preg_replace("/[^A-Za-z0-9_-]/", "", $strReturn);

    return $strReturn;
}

/**
 * Removes traversals like ../ from the passed string
 *
 * @param string $strFilename
 *
 * @return string
 */
function removeDirectoryTraversals($strFilename)
{
    $strFilename = urldecode($strFilename);
    return uniStrReplace("..", "", $strFilename);
}

/**
 * Creates a filename valid for filesystems
 *
 * @param string $strName
 * @param bool $bitFolder
 *
 * @return string
 */
function createFilename($strName, $bitFolder = false)
{
    $strName = uniStrtolower($strName);

    if (!$bitFolder) {
        $strEnding = uniSubstr($strName, (uniStrrpos($strName, ".") + 1));
    }
    else {
        $strEnding = "";
    }

    if (!$bitFolder) {
        $strReturn = uniSubstr($strName, 0, (uniStrrpos($strName, ".")));
    }
    else {
        $strReturn = $strName;
    }

    //Filter non allowed chars
    $arrSearch = array(" ", ".", ":", "ä", "ö", "ü", "/", "ß", "!");
    $arrReplace = array("_", "_", "_", "ae", "oe", "ue", "_", "ss", "_");

    $strReturn = uniStrReplace($arrSearch, $arrReplace, $strReturn);

    //and the ending
    if (!$bitFolder) {
        $strEnding = uniStrReplace($arrSearch, $arrReplace, $strEnding);
    }

    //remove all other special characters
    $strTemp = preg_replace("/[^A-Za-z0-9_-]/", "", $strReturn);

    //do a replacing in the ending, too
    if ($strEnding != "") {
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
 *
 * @return string
 */
function getFileExtension($strPath)
{
    return uniStrtolower(uniSubstr($strPath, uniStrrpos($strPath, ".")));
}

/**
 * Validates if the passed string is a valid mail-address
 *
 * @param string $strAddress
 *
 * @return bool
 * @deprecated use EmailValidator instead
 */
function checkEmailaddress($strAddress)
{
    $objValidator = new EmailValidator();
    return $objValidator->validate($strAddress);
}

/**
 * Validates, if the passed value is numeric
 *
 * @param int $intNumber
 *
 * @return bool
 * @deprecated use NumericValidator instead
 */
function checkNumber($intNumber)
{
    $objValidator = new NumericValidator();
    return $objValidator->validate($intNumber);
}

/**
 * Validates, if the passed Param represents a valid folder in the filesystem
 *
 * @param string $strPath
 *
 * @return bool
 */
function checkFolder($strPath)
{
    $bitTest = is_dir(_realpath_.$strPath) && strlen($strPath) > 0;
    if ($bitTest === false) {
        return false;
    }
    else {
        return true;
    }
}

/**
 * Checks the length of a passed string
 *
 * @param string $strText
 * @param int $intMin
 * @param int $intMax
 *
 * @return bool
 *
 * @deprecated replaced by @link{TextValidator}
 * @see ValidatorInterface
 */
function checkText($strText, $intMin = 1, $intMax = 0)
{
    $objValidator = new TextValidator();
    return $objValidator->validate($strText);
}


/**
 * Generates a new SystemID
 *
 * @return string The new SystemID
 */
function generateSystemid()
{
    //generate md5 key
    $strKey = md5(_realpath_);
    $strTemp = "";
    //Do the magic: take out 6 characters randomly...
    for ($intI = 0; $intI < 7; $intI++) {
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
 *
 * @return bool
 */
function validateSystemid($strID)
{

    //Check against wrong characters
    if (strlen($strID) == 20 && ctype_alnum($strID)) {
        return true;
    }
    else {
        return false;
    }
}

/**
 * Wrapper to dbSafeString of Database
 *
 * @param string $strString
 * @param bool|string $bitHtmlEntities escape html-entities?
 *
 * @deprecated use Database::dbSafeString() instead
 * @see Database::dbSafeString($strString, $bitHtmlEntities = true)
 * @return string
 */
function dbsafeString($strString, $bitHtmlEntities = true)
{
    return Carrier::getInstance()->getObjDB()->dbsafeString($strString, $bitHtmlEntities);
}

/**
 * Makes a string safe for xml-outputs
 *
 * @param string $strString
 *
 * @return string
 */
function xmlSafeString($strString)
{

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
 *
 * @return int
 * @deprecated (use Kajona\System\System\StringUtil::indexOf instead)
 */
function uniStrpos($strHaystack, $strNeedle)
{
    return StringUtil::indexOf($strHaystack, $strNeedle);
}


/**
 * Wrapper to phps strrpos
 *
 * @param string $strHaystack
 * @param string $strNeedle
 *
 * @return int
 * @deprecated (use Kajona\System\System\StringUtil::lastIndexOf instead)
 */
function uniStrrpos($strHaystack, $strNeedle)
{
    return StringUtil::lastIndexOf($strHaystack, $strNeedle);
}

/**
 * Wrapper to phps stripos
 *
 * @param string $strHaystack
 * @param string $strNeedle
 *
 * @return int
 * @deprecated (use Kajona\System\System\StringUtil::indexOf instead)
 */
function uniStripos($strHaystack, $strNeedle)
{
    return StringUtil::indexOf($strHaystack, $strNeedle, false);
}

/**
 * Wrapper to phps strlen
 *
 * @param string $strString
 *
 * @return int
 * @deprecated (use Kajona\System\System\StringUtil::length instead)
 */
function uniStrlen($strString)
{
    return StringUtil::length($strString);
}

/**
 * Wrapper to phps strtolower, due to problems with UTF-8 on some configurations
 *
 * @param string $strString
 *
 * @return string
 * @deprecated (use Kajona\System\System\StringUtil::toLowerCase instead)
 */
function uniStrtolower($strString)
{
    return StringUtil::toLowerCase($strString);
}

/**
 * Wrapper to phps strtoupper, due to problems with UTF-8 on some configurations
 *
 * @param string $strString
 *
 * @return string
 * @deprecated (use Kajona\System\System\StringUtil::toUpperCase instead)
 */
function uniStrtoupper($strString)
{
    return StringUtil::toUpperCase($strString);
}

/**
 * Wrapper to phps substr
 *
 * @param string $strString
 * @param int $intStart
 * @param int|string $intEnd
 *
 * @return string
 * @deprecated (use Kajona\System\System\StringUtil::substring instead)
 */
function uniSubstr($strString, $intStart, $intEnd = "")
{
    if($intEnd == "") {
        $intEnd = null;
    }
    return StringUtil::substring($strString, $intStart, $intEnd);
}

/**
 * Wrapper to phps ereg
 *
 * @param string $strPattern
 * @param string $strString
 *
 * @return int
 * @deprecated (use Kajona\System\System\StringUtil::matches instead)
 */
function uniEreg($strPattern, $strString)
{
    return StringUtil::matches($strString, $strPattern);
}

/**
 * Unicode-safe wrapper to strReplace
 *
 * @param mixed $mixedSearch array or string
 * @param mixed $mixedReplace array or string
 * @param string $strSubject
 * @param bool $bitUnicodesafe
 *
 * @return mixed
 * @deprecated (use Kajona\System\System\StringUtil::replace instead)
 */
function uniStrReplace($mixedSearch, $mixedReplace, $strSubject, $bitUnicodesafe = false)
{
    return StringUtil::replace($mixedSearch, $mixedReplace, $strSubject, $bitUnicodesafe);
}

/**
 * Unicode-safe string trimmer
 *
 * @param string $strString string to wrap
 * @param int $intLength
 * @param string $strAdd string to add after wrapped string
 *
 * @return string
 * @deprecated (use Kajona\System\System\StringUtil::truncate instead)
 */
function uniStrTrim($strString, $intLength, $strAdd = "…")
{
    return StringUtil::truncate($strString, $intLength, $strAdd);
}

/**
 * Sends headers to the client, to allow conditionalGets
 *
 * @param string $strChecksum Checksum of the content. Must be unique for one state.
 */
function setConditionalGetHeaders($strChecksum)
{
    ResponseObject::getInstance()->addHeader("ETag: ".$strChecksum);
    ResponseObject::getInstance()->addHeader("Cache-Control: max-age=86400, must-revalidate");

}


/**
 * Checks, if the browser sent the same checksum as provided. If so,
 * a http 304 is sent to the browser
 *
 * @param string $strChecksum
 *
 * @return bool
 */
function checkConditionalGetHeaders($strChecksum)
{
    if (issetServer("HTTP_IF_NONE_MATCH")) {
        if (getServer("HTTP_IF_NONE_MATCH") == $strChecksum) {
            //strike. no further actions needed.
            ResponseObject::getInstance()->setStrStatusCode(HttpStatuscodes::SC_NOT_MODIFIED);
            ResponseObject::getInstance()->addHeader("ETag: ".$strChecksum);
            ResponseObject::getInstance()->addHeader("Cache-Control: max-age=86400, must-revalidate");

            return true;
        }
    }

    return false;
}

/**
 * Converts a given string to an array
 *
 * @param $strString
 * @param string $strDelimiter
 * @return array|null
 * @deprecated (use Kajona\System\System\StringUtil::toArray instead)
 */
function strToArray($strString, $strDelimiter = ",") {
    return StringUtil::toArray($strString, $strDelimiter);
}

/**
 * Converts a string to an int
 *
 * @param $strString
 * @return int|null
 * @deprecated (use Kajona\System\System\StringUtil::strToInt instead)
 */
function strToInt($strString) {
    return StringUtil::toInt($strString);
}

/**
 * Converts a string to a Date
 *
 * @param $strString
 * @return Date|null
 * @deprecated (use Kajona\System\System\StringUtil::strToDate instead)
 */
function strToDate($strString) {
    return StringUtil::toDate($strString);
}


