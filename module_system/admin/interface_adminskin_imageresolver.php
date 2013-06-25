<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: interface_adminskin_imageresolver.php 5495 2013-02-05 16:30:28Z sidler $                       *
********************************************************************************************************/

/**
 * Each skin should provide a class called "class_adminskin_imageresolver.php" implementing this interface.
 * The file maps logical image-names such as icon_edit to an absolute path / element.
 * The example would transform
 *    icon_edit ==> <img src='_skinwebpath_/img/icon_edit.png' alt='alt' />
 * or some equivalent code.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.2
 */
interface interface_adminskin_imageresolver {

    /**
     * Converts the passed image-name into a real, resolvable code-fragment (such as an image-tag or a
     * i-tag with css-code).
     *
     * @param $strName
     * @param string $strAlt
     * @param bool $bitBlockTooltip
     * @param string $strEntryId
     *
     * @return string
     */
    public function getImage($strName, $strAlt = "", $bitBlockTooltip = false, $strEntryId = "");



}
