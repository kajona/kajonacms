<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: interface_sortable_rating.php 3530 2011-01-06 12:30:26Z sidler $                               *
********************************************************************************************************/

/**
 * A changelog provider creates a mapping of objects to target-tables
 *
 * @package module_system
 */
interface interface_changelog_provider {

    /**
     * Returns the name of the table used by the current provider.
     * The table itself is created by the system-installer during the installation / update of a module, so there's
     * no need to
     * The name should be returned without the _dbprefix_ part
     *
     * @return string
     */
    public function getTargetTable();

    /**
     * Returns an array of classes the current provider (so the target table) should cover
     *
     * @return array
     */
    public function getHandledClasses();

}
