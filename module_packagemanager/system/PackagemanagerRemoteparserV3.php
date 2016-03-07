<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$             *
********************************************************************************************************/

namespace Kajona\Packagemanager\System;


/**
 * A remote parser which handles results of Kajona's package manager API provided by version 3.x.
 *
 * @package module_packagemanager
 * @author flo@mediaskills.org
 * @since 4.0
 * @deprecated
 */
class PackagemanagerRemoteparserV3 implements PackagemanagerRemoteparserInterface {

    private $arrPackages;

    function __construct($arrRemoteResponse) {
        $this->arrPackages = $arrRemoteResponse;
    }

    public function getArrPackages() {
        return $this->arrPackages;
    }

    public function paginationFooter() {
        return "";
    }


}