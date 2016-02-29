<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                       *
********************************************************************************************************/

namespace Kajona\System\Admin;

/**
 * Each skin should provide a class called "AdminskinImageresolver.php" implementing this interface.
 * The file maps logical image-names such as icon_edit to an absolute path / element.
 * The example would transform
 *    icon_edit ==> <img src='_skinwebpath_/img/icon_edit.png' alt='alt' />
 * or some equivalent code.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.2
 */
interface AdminskinImageresolverInterface
{

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
