<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_adminskin_imageresolver.php 5495 2013-02-05 16:30:28Z sidler $                           *
********************************************************************************************************/

/**
 * Class class_adminskin_imageresolver
 *
 * @author sidler@mulchprod.de
 * @since 4.2
 * @package module_v4skin
 */
class class_adminskin_imageresolver implements interface_adminskin_imageresolver {

    /**
     * Converts the passed image-name into a real, resolvable code-fragment (such as an image-tag or an
     * i-tag with css-code).
     *
     * @param $strName
     * @param string $strAlt
     * @param bool $bitBlockTooltip
     * @param string $strEntryId
     *
     * @return string
     */
    public function getImage($strName, $strAlt = "", $bitBlockTooltip = false, $strEntryId = "") {
        $strName = uniStrReplace(".png", "", $strName);
        return "<img src=\""._skinwebpath_."/pics/".$strName.".png\"  alt=\"".$strAlt."\"  ".(!$bitBlockTooltip ? "rel=\"tooltip\" title=\"".$strAlt."\" " : "" )." ".($strEntryId != "" ? " id=\"".$strEntryId."\" " : "" )."  />";
    }



}
