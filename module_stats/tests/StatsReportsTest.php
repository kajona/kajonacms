<?php

namespace Kajona\Stats\Tests;

use Kajona\Stats\Admin\AdminStatsreportsInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\Testbase;

class StatsReportTest extends Testbase
{

    public function testReports()
    {

        if (!defined("_skinwebpath_")) {
            define("_skinwebpath_", "1");
        }

        echo "processing reports...\n";

        $arrReportsInFs = Resourceloader::getInstance()->getFolderContent("/admin/reports", array(".php"), false, function ($strOneFile) {
            if (uniStripos($strOneFile, "class_stats_report") === false) {//TODO use namespace
                return false;
            }

            return true;
        },
            function (&$strOneFile) {
                $strOneFile = uniSubstr($strOneFile, 0, -4);
                $strOneFile = new $strOneFile(Carrier::getInstance()->getObjDB(), Carrier::getInstance()->getObjToolkit("admin"), Carrier::getInstance()->getObjLang());
            });

        $arrReports = array();
        foreach ($arrReportsInFs as $objReport) {

            if ($objReport instanceof AdminStatsreportsInterface) {
                $arrReports[$objReport->getTitle()] = $objReport;
            }

            $objStartDate = new \Kajona\System\System\Date();
            $objStartDate->setPreviousDay();
            $objEndDate = new \Kajona\System\System\Date();
            $objEndDate->setNextDay();
            $intStartDate = mktime(0, 0, 0, $objStartDate->getIntMonth(), $objStartDate->getIntDay(), $objStartDate->getIntYear());
            $intEndDate = mktime(0, 0, 0, $objEndDate->getIntMonth(), $objEndDate->getIntDay(), $objEndDate->getIntYear());
            $objReport->setEndDate($intEndDate);
            $objReport->setStartDate($intStartDate);
            $objReport->setInterval(2);
        }

        /** @var AdminStatsreportsInterface $objReport */
        foreach ($arrReports as $objReport) {
            ob_start();
            echo "processing report " . $objReport->getTitle() . "\n";

            $objReport->getReport();
            $objReport->getReportGraph();
        }

    }
}

