<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: interface_scriptlet.php 4662 2012-05-24 14:43:41Z sidler $                               *
********************************************************************************************************/

/**
 * A sort-manager for pages & folders
 */
class class_pages_sortmanager extends class_common_sortmanager {

    public function setAbsolutePosition($intNewPosition, $arrRestrictionModules = false) {
        parent::setAbsolutePosition($intNewPosition, array(_pages_modul_id_, _pages_folder_id_));
    }

    function fixSortOnDelete($arrRestrictionModules = false) {
        parent::fixSortOnDelete(array(_pages_modul_id_, _pages_folder_id_));
    }

}
