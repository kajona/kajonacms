<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                      *
********************************************************************************************************/

/**
 * A simple provider to separate settings-changes from the other changes
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_changelog_provider_settings implements interface_changelog_provider  {

    /**
     * Returns the name of the table used by the current provider.
     * The table itself is created by the system-installer during the installation / update of a module, so there's
     * no need to
     * The name should be returned without the _dbprefix_ part
     *
     * @return string
     */
    public function getTargetTable() {
        return "changelog_setting";
    }

    /**
     * Returns an array of classes the current provider (so the target table) should cover
     *
     * @return array
     */
    public function getHandledClasses() {
        return array("class_module_system_setting");
    }

}
