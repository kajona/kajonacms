<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;

/**
 * Class to handle all link-generations, backend and portal.
 * Moved from functions.php to a central class in order to avoid duplicated code.
 * As a side-effect, the class may be overridden using the /project schema in order
 * to modify the link generation (if required).
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.3
 */
class Link
{

    private static $intNrOfPortalLanguages = null;
    private static $strPortalLanguage = null;


    /**
     * Generates a link using the content passed. The content is either a string or an associative array.
     * If its an array the values are escaped. Returns a link in the format: <a [name]=[value]>[text]</a>
     *
     * @param string|array $strLinkContent
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
    public static function getLinkAdminManual($strLinkContent, $strText, $strAlt = "", $strImage = "", $strImageId = "", $strLinkId = "", $bitTooltip = true, $strCss = "")
    {
        $arrAttr = [];

        if (!empty($strImage)) {
            $strText = AdminskinHelper::getAdminImage($strImage, $strAlt, true, $strImageId);
        } elseif (!empty($strText)) {
            if ($bitTooltip && (trim($strAlt) == "" || $strAlt == $strText)) {
                $bitTooltip = false;
                $strAlt = empty($strAlt) ? strip_tags($strText) : $strAlt;
            }
        }

        if (!empty($strAlt)) {
            $arrAttr["title"] = $strAlt;
        }

        if (!empty($strLinkId)) {
            $arrAttr["id"] = $strLinkId;
        }

        if ($bitTooltip) {
            $arrAttr["rel"] = "tooltip";
        }

        if (!empty($strCss)) {
            $arrAttr["class"] = $strCss;
        }

        if (is_array($strLinkContent)) {
            $arrAttr = array_merge($arrAttr, $strLinkContent);
        }

        $arrParts = [];
        foreach ($arrAttr as $strAttrName => $strAttrValue) {
            if (!empty($strAttrValue)) {
                if (is_scalar($strAttrValue)) {
                    $arrParts[] = $strAttrName . "=\"" . htmlspecialchars($strAttrValue) . "\"";
                } else {
                    throw new \InvalidArgumentException("Array must contain only scalar values");
                }
            }
        }

        if (is_string($strLinkContent)) {
            array_unshift($arrParts, $strLinkContent);
        }

        return "<a " . implode(" ", $arrParts) . ">" . $strText . "</a>";
    }

    /**
     * Generates a link for the admin-area
     *
     * @param string $strModule
     * @param string $strAction
     * @param string|array $strParams - may be a string of params or an array
     * @param string $strText
     * @param string $strAlt
     * @param string $strImage
     * @param bool $bitTooltip
     * @param string $strCss
     *
     * @return string
     */
    public static function getLinkAdmin($strModule, $strAction, $strParams = "", $strText = "", $strAlt = "", $strImage = "", $bitTooltip = true, $strCss = "")
    {
        $strHref = "href=\"".Link::getLinkAdminHref($strModule, $strAction, $strParams)."\"";
        return self::getLinkAdminManual($strHref, $strText, $strAlt, $strImage, "", "", $bitTooltip, $strCss);
    }


    /**
     * Generates a link for the admin-area
     *
     * @param string $strModule
     * @param string $strAction
     * @param string|array $strParams - may be a string of params or an array
     * @param bool $bitEncodedAmpersand
     *
     * @return string
     */
    public static function getLinkAdminHref($strModule, $strAction = "", $strParams = "", $bitEncodedAmpersand = true)
    {
        //systemid in params?
        $strSystemid = "";
        $strParams = self::sanitizeUrlParams($strParams, $strSystemid);
        $arrParams = array();
        if($strParams !== "") {
            $arrParams = explode("&", $strParams);
        }

        //urlencoding
        $strModule = urlencode($strModule);
        $strAction = urlencode($strAction);

        //rewriting enabled?
        if (SystemSetting::getConfigValue("_system_mod_rewrite_") == "true") {

            $strPrefix = "/admin";
            if(SystemSetting::getConfigValue("_system_mod_rewrite_admin_only_") == "true") {
                $strPrefix = "";
            }

            //scheme: /admin/module.action.systemid
            if ($strModule != "" && $strAction == "" && $strSystemid == "") {
                $strLink = _webpath_.$strPrefix."/".$strModule.".html";
            }
            elseif ($strModule != "" && $strAction != "" && $strSystemid == "") {
                $strLink = _webpath_.$strPrefix."/".$strModule."/".$strAction.".html";
            }
            else {
                $strLink = _webpath_.$strPrefix."/".$strModule."/".$strAction."/".$strSystemid.".html";
            }

            if (count($arrParams) > 0) {
                $strLink .= "?".implode("&amp;", $arrParams);
            }

        }
        else {
            $strLink = ""._indexpath_."?admin=1&amp;module=".$strModule.
                ($strAction != "" ? "&amp;action=".$strAction : "").
                ($strSystemid != "" ? "&amp;systemid=".$strSystemid : "");

            if (count($arrParams) > 0) {
                $strLink .= "&amp;".(implode("&amp;", $arrParams));
            }
        }

        if (!$bitEncodedAmpersand) {
            $strLink = StringUtil::replace("&amp;", "&", $strLink);
        }

        return $strLink;
    }

    /**
     * Generates an admin-url to trigger xml-requests. Takes care of url-rewriting
     *
     * @param string $strModule
     * @param string $strAction
     * @param string|array $strParams - may be a string of params or an array
     * @param bool $bitEncodedAmpersand
     *
     * @return mixed|string
     */
    public static function getLinkAdminXml($strModule, $strAction = "", $strParams = "", $bitEncodedAmpersand = false)
    {
        //systemid in params?
        $strSystemid = "";
        $strParams = self::sanitizeUrlParams($strParams, $strSystemid);
        $arrParams = array();
        if($strParams !== "") {
            $arrParams = explode("&", $strParams);
        }

        //urlencoding
        $strModule = urlencode($strModule);
        $strAction = urlencode($strAction);

        //rewriting enabled?
        if (SystemSetting::getConfigValue("_system_mod_rewrite_") == "true") {

            $strPrefix = "/admin";
            if(SystemSetting::getConfigValue("_system_mod_rewrite_admin_only_") == "true") {
                $strPrefix = "";
            }

            //scheme: /admin/module.action.systemid
            if ($strModule != "" && $strAction == "" && $strSystemid == "") {
                $strLink = _webpath_."/xml".$strPrefix."/".$strModule;
            }
            else if ($strModule != "" && $strAction != "" && $strSystemid == "") {
                $strLink = _webpath_."/xml".$strPrefix."/".$strModule."/".$strAction;
            }
            else {
                $strLink = _webpath_."/xml".$strPrefix."/".$strModule."/".$strAction."/".$strSystemid;
            }

            if (count($arrParams) > 0) {
                $strLink .= "?".implode("&amp;", $arrParams);
            }

        }
        else {
            $strLink = ""._webpath_."/xml.php?admin=1&amp;module=".$strModule.
                ($strAction != "" ? "&amp;action=".$strAction : "").
                ($strSystemid != "" ? "&amp;systemid=".$strSystemid : "");

            if (count($arrParams) > 0) {
                $strLink .= "&amp;".(implode("&amp;", $arrParams));
            }
        }

        if (!$bitEncodedAmpersand) {
            $strLink = StringUtil::replace("&amp;", "&", $strLink);
        }

        return $strLink;
    }

    /**
     * Generates a link opening in a popup in admin-area
     *
     * @param string $strModule
     * @param string $strAction
     * @param string|array $strParams - may be a string of params or an array
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
    public static function getLinkAdminPopup($strModule, $strAction, $strParams = "", $strText = "", $strAlt = "", $strImage = "", $intWidth = "500", $intHeight = "500", $strTitle = "", $bitTooltip = true, $bitPortalEditor = false)
    {
        $strLink = "";
        //if($strParams != "")
        //    $strParams = str_replace("&", "&amp;", $strParams);
        $strTitle = addslashes(StringUtil::replace(array("\n", "\r"), array(), strip_tags(nl2br($strTitle))));

        if ($bitPortalEditor && $intHeight == "500") {
            $intHeight = 690;
        }

        //urlencoding
        $strModule = urlencode($strModule);
        $strAction = urlencode($strAction);

        if ($bitPortalEditor) {
            if(is_string($strParams)){
                $strParams .= "&pe=1";
            }
            elseif (is_array($strParams)){
                $strParams["pe"] = "1";
            }
        }

        if ($strImage != "") {
            if ($strAlt == "") {
                $strAlt = $strAction;
            }

            if (!$bitTooltip) {
                $strLink = "<a href=\"#\" onclick=\"window.open('".Link::getLinkAdminHref($strModule, $strAction, $strParams)."','".$strTitle."','scrollbars=yes,resizable=yes,width=".$intWidth.",height=".$intHeight."'); return false;\" ".
                    "title=\"".$strAlt."\">".AdminskinHelper::getAdminImage($strImage, $strAlt, true)."</a>";
            }
            else {
                $strLink = "<a href=\"#\" onclick=\"window.open('".Link::getLinkAdminHref($strModule, $strAction, $strParams)."','".$strTitle."','scrollbars=yes,resizable=yes,width=".$intWidth.",height=".$intHeight."'); return false;\" ".
                    "title=\"".$strAlt."\" rel=\"tooltip\">".AdminskinHelper::getAdminImage($strImage, $strAlt, true)."</a>";
            }
        }

        if ($strImage == "" && $strText != "") {
            $bitTooltip = $bitTooltip && $strAlt != "";

            $strLink = "<a href=\"#\" ".($bitPortalEditor ? "class=\"pe_link\"" : "")." ".($bitTooltip ? "title=\"".$strAlt."\" rel=\"tooltip\" " : "")." ".
                "onclick=\"window.open('".Link::getLinkAdminHref($strModule, $strAction, $strParams)."','".$strTitle."','scrollbars=yes,resizable=yes,width=".$intWidth.",height=".$intHeight."'); return false;\">".$strText."</a>";
        }
        return $strLink;
    }

    /**
     * Generates a link opening in a dialog in admin-area
     *
     * @param string $strModule
     * @param string $strAction
     * @param string|array $strParams - may be a string of params or an array
     * @param string $strText
     * @param string $strAlt
     * @param string $strImage
     * @param string $strTitle
     * @param bool $bitTooltip
     * @param bool $bitPortalEditor
     * @param bool|string $strOnClick
     *
     * @return string
     */
    public static function getLinkAdminDialog($strModule, $strAction, $strParams = "", $strText = "", $strAlt = "", $strImage = "", $strTitle = "", $bitTooltip = true, $bitPortalEditor = false, $strOnClick = "")
    {
        $strLink = "";
        $strTitle = addslashes(StringUtil::replace(array("\n", "\r"), array(), strip_tags(nl2br($strTitle))));

        if ($bitPortalEditor) {
            if(is_string($strParams)){
                $strParams .= "&pe=1";
            }
            elseif (is_array($strParams)){
                $strParams["pe"] = "1";
            }
        }

        //urlencoding
        $strModule = urlencode($strModule);
        $strAction = urlencode($strAction);

        if ($strOnClick == "") {
            $strLink = Link::getLinkAdminHref($strModule, $strAction, $strParams);
            $strOnClick = "require('dialogHelper').showIframeDialog('{$strLink}', '{$strTitle}'); return false;";
        }


        if ($strImage != "") {
            if ($strAlt == "") {
                $strAlt = $strAction;
            }

            if (!$bitTooltip) {
                $strLink = "<a href=\"#\" onclick=\"".$strOnClick."\" title=\"".$strAlt."\">".AdminskinHelper::getAdminImage($strImage, $strAlt, true)."</a>";
            }
            else {
                $strLink = "<a href=\"#\" onclick=\"".$strOnClick."\" title=\"".$strAlt."\" rel=\"tooltip\">".AdminskinHelper::getAdminImage($strImage, $strAlt, true)."</a>";
            }
        }

        if ($strImage == "" && $strText != "") {
            if ($strAlt == "") {
                $strAlt = $strText;
            }
            $strLink = "<a href=\"#\" ".($bitPortalEditor ? "class=\"pe_link\"" : "")." ".($bitTooltip ? "title=\"".$strAlt."\" rel=\"tooltip\" " : "")." onclick=\"".$strOnClick."\">".$strText."</a>";
        }
        return $strLink;
    }


    /**
     * Creates a Link for the portal
     *
     * @param string $strPageI
     * @param string $strPageE
     * @param string $strTarget
     * @param string $strText
     * @param string $strAction
     * @param string|array $strParams - may be a string of params or an array
     * @param string $strSystemid
     * @param string $strCssClass
     * @param string $strLanguage
     * @param string $strSeoAddon
     *
     * @return string
     */
    public static function getLinkPortal($strPageI, $strPageE, $strTarget = "_self", $strText = "", $strAction = "", $strParams = "", $strSystemid = "", $strCssClass = "", $strLanguage = "", $strSeoAddon = "")
    {
        $strReturn = "";
        $strHref = Link::getLinkPortalHref($strPageI, $strPageE, $strAction, $strParams, $strSystemid, $strLanguage, $strSeoAddon);

        if ($strTarget == "") {
            $strTarget = "_self";
        }

        $strReturn .= "<a href=\"".$strHref."\" target=\"".$strTarget."\" ".($strCssClass != "" ? " class=\"".$strCssClass."\" " : "").">".$strText."</a>";

        return $strReturn;
    }

    /**
     * Creates a raw Link for the portal (just the href)
     *
     * @param string $strPageI
     * @param string $strPageE
     * @param string $strAction
     * @param string|array $strParams - may be a string of params or an array
     * @param string $strSystemid
     * @param string $strLanguage
     * @param string $strSeoAddon Only used if using mod_rewrite
     *
     * @return string
     */
    public static function getLinkPortalHref($strPageI, $strPageE = "", $strAction = "", $strParams = "", $strSystemid = "", $strLanguage = "", $strSeoAddon = "")
    {
        $strReturn = "";
        $bitInternal = true;

        //return "#" if neither an internal nor an external page is set
        if ($strPageI == "" && $strPageE == "") {
            return "#";
        }

        //Internal links are more important than external links!
        if ($strPageI == "" && $strPageE != "") {
            $bitInternal = false;
        }


        //create an array out of the params
        if ($strSystemid != "") {
            if(is_string($strParams)){
                $strParams .= "&systemid=".$strSystemid;
            }
            elseif (is_array($strParams)){
                $strParams["systemid"] = $strSystemid;
            }
            $strSystemid = "";
        }


        $strParams = self::sanitizeUrlParams($strParams, $strSystemid);
        $arrParams = array();
        if($strParams !== "") {
            $arrParams = explode("&", $strParams);
        }

        // any anchors set to the page?
        $strAnchor = "";
        if (StringUtil::indexOf($strPageI, "#") !== false) {
            //get anchor, remove anchor from link
            $strAnchor = urlencode(StringUtil::substring($strPageI, StringUtil::indexOf($strPageI, "#") + 1));
            $strPageI = StringUtil::substring($strPageI, 0, StringUtil::indexOf($strPageI, "#"));
        }

        //urlencoding
        $strPageI = urlencode($strPageI);
        $strAction = urlencode($strAction);

        //more than one language installed?
        if ($strLanguage == "" && self::getIntNumberOfPortalLanguages() > 1) {
            $strLanguage = self::getStrPortalLanguage();
        }
        else if ($strLanguage != "" && self::getIntNumberOfPortalLanguages() <= 1) {
            $strLanguage = "";
        }

        $strHref = "";
        if ($bitInternal) {
            //check, if we could use mod_rewrite
            $bitRegularLink = true;
            if (SystemSetting::getConfigValue("_system_mod_rewrite_") == "true") {

                $strAddKeys = "";

                //used later to add seo-relevant keywords
                $objPage = \Kajona\Pages\System\PagesPage::getPageByName($strPageI);
                if ($objPage !== null) {
                    if ($strLanguage != "") {
                        $objPage->setStrLanguage($strLanguage);
                        $objPage->initObject();
                    }

                    $strAddKeys = $objPage->getStrSeostring().($strSeoAddon != "" && $objPage->getStrSeostring() != "" ? "-" : "").urlSafeString($strSeoAddon);
                    if (StringUtil::length($strAddKeys) > 0 && StringUtil::length($strAddKeys) <= 2) {
                        $strAddKeys .= "__";
                    }

                    //trim string
                    $strAddKeys = StringUtil::truncate($strAddKeys, 100, "");

                    if ($strLanguage != "") {
                        $strHref .= $strLanguage."/";
                    }

                    $strPath = $objPage->getStrPath();
                    if ($strPath == "") {
                        $objPage->updatePath();
                        $strPath = $objPage->getStrPath();
                        $objPage->updateObjectToDb();
                    }
                    if ($strPath != "") {
                        $strHref .= $strPath."/";
                    }

                }

                //ok, here we go. schema for rewrite_links: pagename.addKeywords.action.systemid.language.html
                //but: special case: just pagename & language
                if ($strAction == "" && $strSystemid == "" && $strAddKeys == "") {
                    $strHref .= $strPageI.".html";
                }
                elseif ($strAction == "" && $strSystemid == "") {
                    $strHref .= $strPageI.($strAddKeys == "" ? "" : ".".$strAddKeys).".html";
                }
                elseif ($strAction != "" && $strSystemid == "") {
                    $strHref .= $strPageI.".".$strAddKeys.".".$strAction.".html";
                }
                else {
                    $strHref .= $strPageI.".".$strAddKeys.".".$strAction.".".$strSystemid.".html";
                }

                //params?
                if (count($arrParams) > 0) {
                    $strHref .= "?".implode("&amp;", $arrParams);
                }

                // add anchor if given
                if ($strAnchor != "") {
                    $strHref .= "#".$strAnchor;
                }

                //plus the domain as a prefix
                $strHref = "_webpath_"."/".$strHref;


                $bitRegularLink = false;

            }

            if ($bitRegularLink) {
                $strHref = "_indexpath_"."?".
                    ($strPageI != "" ? "page=".$strPageI : "")."".
                    ($strSystemid != "" ? "&amp;systemid=".$strSystemid : "").
                    ($strAction != "" ? "&amp;action=".$strAction : "").
                    ($strLanguage != "" ? "&amp;language=".$strLanguage : "").
                    (count($arrParams) > 0 ? "&amp;".implode("&amp;", $arrParams) : "").
                    ($strAnchor != "" ? "#".$strAnchor : "")."";
            }
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
     * @param string|array $strParams - may be a string of params or an array
     * @param string $strSystemid
     * @param string $strTitle
     * @param int|string $intWidth
     * @param int|string $intHeight
     *
     * @return string
     */
    public static function getLinkPortalPopup($strPageI, $strPageE, $strAction = "", $strParams = "", $strSystemid = "", $strTitle = "", $intWidth = "500", $intHeight = "500")
    {
        $strLink = Link::getLinkPortalHref($strPageI, $strPageE, $strAction, $strParams, $strSystemid);
        $strLink = "<a href=\"$strLink\" onclick=\"return !window.open('".$strLink."','".$strTitle."','scrollbars=yes,resizable=yes,width=".$intWidth.",height=".$intHeight."')\" title=\"".$strTitle."\">".$strTitle."</a>";
        return $strLink;
    }


    /**
     * Converts the given array to an urlencoded array.
     *
     * Extracts the systemid out of the string|array and updates the passed reference with the
     * systemid.
     *
     * If $arrParams is null, an empty array is being returned.
     *
     * @param array|string $arrParams
     * @param string &$strSystemid
     *
     * @return array
     */
    private static function sanitizeUrlParams($arrParams, &$strSystemid = "")
    {
        if($arrParams === null) {
            $arrParams = array();
        }

        /*In case it is a string -> build associative array*/
        if(is_string($arrParams)) {
            $strParams = StringUtil::replace("&amp;", "&", $arrParams);

            //if given, remove first ampersand from params
            if (substr($strParams, 0, 1) == "&") {
                $strParams = substr($strParams, 1);
            }

            $arrParams = [];
            foreach(explode("&", $strParams) as $strOneSet) {
                $arrEntry = explode("=", $strOneSet);
                if (count($arrEntry) == 2) {
                    $arrParams[$arrEntry[0]] = urldecode($arrEntry[1]);
                }
            }
        }

        /* Create string params*/
        foreach($arrParams as $strParamKey => $strValue) {

            //First convert boolean values to string representation "true", "false", then use http_build_query
            //This is done because http_build_query converts booleans to "1"(true) or "0"(false) and not to "true", "false"
            if (is_bool($strValue)) {
                $arrParams[$strParamKey] = $strValue === true ? "true" : "false";
            }

            //Handle systemid param -> removes system from the array and sets reference variable $strSystemid
            if ($strParamKey === "systemid") {
                unset($arrParams[$strParamKey]);
                $strSystemid = $strValue;

                if (!validateSystemid($strValue) && $strValue != "%systemid%") {
                    $strSystemid = "";
                }
            }
        }
        $strParams = http_build_query($arrParams, null, "&");
        return $strParams;
    }

    /**
     * Helper to determin the number of portal languages only once.
     *
     * @return int
     */
    private static function getIntNumberOfPortalLanguages()
    {
        if (self::$intNrOfPortalLanguages == null) {
            self::$intNrOfPortalLanguages = LanguagesLanguage::getNumberOfLanguagesAvailable(true);
        }

        return self::$intNrOfPortalLanguages;
    }


    /**
     * Helper to fetch the portal language
     *
     * @return null|string
     */
    private static function getStrPortalLanguage()
    {
        if (self::$strPortalLanguage == null) {
            $objLang = new LanguagesLanguage();
            self::$strPortalLanguage = $objLang->getStrPortalLanguage();
        }

        return self::$strPortalLanguage;
    }
}