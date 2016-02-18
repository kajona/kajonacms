<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

namespace Kajona\Packagemanager\System;


/**
 * A simple content-provider used to upload archives from the official kajona-repo.
 * Provides both, a search and a download-part.
 *
 * @package module_packagemanager
 * @author sidler@mulchprod.de
 * @author flo@mediaskills.org
 * @since 4.0
 */
class PackagemanagerContentproviderKajona extends PackagemanagerContentproviderRemoteBase
{

    function __construct()
    {
        parent::__construct(
            "provider_kajona",
            "www.kajona.de",
            "/xml.php?module=packageserver&action=list",
            "/download.php",
            __CLASS__,
            "https://",
            443
        );
    }
}