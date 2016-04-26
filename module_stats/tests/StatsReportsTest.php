<?php

namespace Kajona\Stats\Tests;

use Kajona\Stats\Admin\AdminStatsreportsInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\Classloader;
use Kajona\System\System\Date;
use Kajona\System\System\Resourceloader;
use Kajona\System\Tests\Testbase;

class StatsReportTest extends Testbase
{

    public function testReports()
    {

        if (!defined("_skinwebpath_")) {
            define("_skinwebpath_", "1");
        }

        echo "processing reports...\n";

        $arrFiles = Resourceloader::getInstance()->getFolderContent("/admin/reports", array(".php"), false, null, function (&$strOneFile, $strPath) {

            $objInstance = Classloader::getInstance()->getInstanceFromFilename($strPath, null, "Kajona\\Stats\\Admin\\AdminStatsreportsInterface", array(Carrier::getInstance()->getObjDB(), Carrier::getInstance()->getObjToolkit("admin"), Carrier::getInstance()->getObjLang()));

            if($objInstance != null) {
                $strOneFile = $objInstance;
            }
            else {
                $strOneFile = null;
            }

        });

        $arrFiles = array_filter($arrFiles, function ($strClass) {
            return $strClass != null;
        });

        $arrReports = array();
        foreach ($arrFiles as $objReport) {

            if ($objReport instanceof AdminStatsreportsInterface) {
                $arrReports[$objReport->getTitle()] = $objReport;
            }

            $objStartDate = new Date();
            $objStartDate->setPreviousDay();
            $objEndDate = new Date();
            $objEndDate->setNextDay();
            $intStartDate = mktime(0, 0, 0, $objStartDate->getIntMonth(), $objStartDate->getIntDay(), $objStartDate->getIntYear());
            $intEndDate = mktime(0, 0, 0, $objEndDate->getIntMonth(), $objEndDate->getIntDay(), $objEndDate->getIntYear());
            $objReport->setEndDate($intEndDate);
            $objReport->setStartDate($intStartDate);
            $objReport->setInterval(2);
        }

        /** @var AdminStatsreportsInterface $objReport */
        foreach ($arrReports as $objReport) {
            echo "processing report " . $objReport->getTitle() . "\n";

            $objReport->getReport();
            $objReport->getReportGraph();
        }

    }
}

