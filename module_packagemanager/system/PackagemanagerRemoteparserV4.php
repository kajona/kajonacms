<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$             *
********************************************************************************************************/

namespace Kajona\Packagemanager\System;

use Kajona\System\System\ArraySectionIterator;
use Kajona\System\System\Carrier;


/**
 * A remote parser which handles results of Kajona's package manager API provided by version 4.x.
 *
 * @package module_packagemanager
 * @author flo@mediaskills.org
 * @since 4.0
 */
class PackagemanagerRemoteparserV4 implements PackagemanagerRemoteparserInterface {

    private $arrPageViews = array();

    function __construct($arrRemoteResponse, $intPageNumber, $intStart, $intEnd, $strProviderName, $strPagerAddon) {
        $intNumberOfTotalItems = (int) $arrRemoteResponse['numberOfTotalItems'];
        $arrPackages = $arrRemoteResponse['items'];

        $objIterator = new ArraySectionIterator($intNumberOfTotalItems);
        $objIterator->setPageNumber($intPageNumber);
        $objIterator->setArraySection($arrPackages);

        $objToolkit = Carrier::getInstance()->getObjToolkit("admin");

        $this->arrPageViews["pageview"] = $objToolkit->getPageview(
            $objIterator,
            "packagemanager",
            "addPackage",
            "&provider=".$strProviderName.$strPagerAddon
        );

        $this->arrPageViews["elements"] = array();
        foreach($objIterator as $objOneEntry)
            $this->arrPageViews["elements"][] = $objOneEntry;
    }

    public function getArrPackages() {
        return $this->arrPageViews["elements"];
    }

    public function paginationFooter() {
        return $this->arrPageViews["pageview"];
    }


}