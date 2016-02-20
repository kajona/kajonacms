<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$ *
********************************************************************************************************/

namespace Kajona\Packagemanager\System;


/**
 * A simple content-provider used to upload archives from the kajonabase-repo.
 * Provides both, a search and a download-part.
 *
 * @package module_packagemanager
 * @author sidler@mulchprod.de
 * @author flo@mediaskills.org
 * @since 4.0
 */
class PackagemanagerContentproviderKajonabase extends PackagemanagerContentproviderRemoteBase
{

    function __construct()
    {
        parent::__construct(
            "provider_kajonabase",
            "www.kajonabase.net",
            "/xml.php?module=packageserver&action=list",
            "/download.php",
            __CLASS__
        );
    }
}