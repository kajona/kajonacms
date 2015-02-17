<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$             *
********************************************************************************************************/

/**
 * A remote parser which handles results of Kajona's package manager API provided by version 4.x.
 *
 * @package module_packagemanager
 * @author flo@mediaskills.org
 * @since 4.0
 */
class class_module_packagemanager_remoteparser_v4 implements interface_packagemanager_remoteparser {

    private $arrPageViews = array();

    function __construct($arrRemoteResponse, $intPageNumber, $intStart, $intEnd, $strProviderName, $strPagerAddon) {
        $intNumberOfTotalItems = (int) $arrRemoteResponse['numberOfTotalItems'];
        $arrPackages = $arrRemoteResponse['items'];

        $objIterator = new class_array_section_iterator($intNumberOfTotalItems);
        $objIterator->setPageNumber($intPageNumber);
        $objIterator->setArraySection($arrPackages, $intStart, $intEnd);

        $objToolkit = class_carrier::getInstance()->getObjToolkit("admin");

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