<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Packagemanager\System;


/**
 * List of events managed by the packagemanager module.
 *
 * @since 5.1
 */
interface PackagemanagerEventidentifier
{


    /**
     * Event thrown as soon as a package was either installed or updated.
     *
     * Use this listener-identifier to fire additional ation, e.g. auto-deployments.
     * The params-array contains two entries:
     *
     * @param \Kajona\Packagemanager\System\PackagemanagerPackagemanagerInterface $objManager the installed / updated package
     * @since 5.1
     */
    const EVENT_PACKAGEMANAGER_PACKAGEUPDATED = "core.packagemanager.packageupdated";


}
