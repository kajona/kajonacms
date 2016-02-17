<?php

echo "<pre>\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";



$objStartDate = new \Kajona\System\System\Date();
$objEndDate = new \Kajona\System\System\Date();


echo str_pad("Month", 15);
echo str_pad("Hits", 15);
echo str_pad("Visitors", 15);
echo str_pad("Downloads", 15);
echo str_pad("PServer-Requests", 20);
echo str_pad("PServer-Unique", 20);
echo "\n";

for($intYear = 2012; $intYear <= 2015; $intYear++) {
    for($intMonth = 1; $intMonth <= 12; $intMonth++) {

        $objStartDate->setIntDay(1)->setIntMonth($intMonth)->setIntYear($intYear)->setIntHour(0)->setIntMin(0)->setIntSec(1);

        $objEndDate = clone $objStartDate;

        $objEndDate->setNextDay();
        while($objEndDate->getIntDay() != 1)
            $objEndDate->setNextDay();

        $objEndDate->setPreviousDay()->setIntHour(23)->setIntMin(59)->setIntSec(59);


        echo str_pad($objStartDate->getIntMonth()."/".$objStartDate->getIntYear(), 10);
        echo str_pad(getHits($objStartDate->getTimeInOldStyle(), $objEndDate->getTimeInOldStyle()), 15, " ", STR_PAD_LEFT);
        echo str_pad(getVisitors($objStartDate->getTimeInOldStyle(), $objEndDate->getTimeInOldStyle()), 15, " ", STR_PAD_LEFT);
        echo str_pad(getDownloads($objStartDate->getTimeInOldStyle(), $objEndDate->getTimeInOldStyle()), 15, " ", STR_PAD_LEFT);
        echo str_pad(getPackageserverRequests($objStartDate->getLongTimestamp(), $objEndDate->getLongTimestamp()), 20, " ", STR_PAD_LEFT);
        echo str_pad(getUniquePackageserverSystems($objStartDate->getLongTimestamp(), $objEndDate->getLongTimestamp()), 20, " ", STR_PAD_LEFT);
        echo "\n";

        flush();
        ob_flush();
    }
}

echo "Total unique installations: \n";
getTotalUniquePackagesererSystems();

function getTotalUniquePackagesererSystems() {
    $strQuery = "SELECT log_hostname, count(*) AS ANZ
                FROM "._dbprefix_."packageserver_log
                GROUP BY log_hostname
                ORDER BY ANZ DESC   ";

    $intI = 0;
    foreach(class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array()) as $arrOneRow) {
        if(uniStrpos($arrOneRow["log_hostname"], "localhost/") === false
            && uniStrpos($arrOneRow["log_hostname"], "kajona.de") === false
            && uniStrpos($arrOneRow["log_hostname"], "kajonabase") === false
            && uniStrpos($arrOneRow["log_hostname"], "aquarium") === false
            && uniStrpos($arrOneRow["log_hostname"], "stb400s") === false
            && $arrOneRow["log_hostname"] != ""
        ) {
            echo sprintf("%4d", $arrOneRow["ANZ"])." => ".$arrOneRow["log_hostname"]."<br />";
            $intI++;
        }
    }
    echo "Total: ".$intI." systems<br />";
}


function getUniquePackageserverSystems($intStartDate, $intEndDate) {
    $strQuery = "SELECT count(DISTINCT(log_hostname)) AS ANZ
              FROM "._dbprefix_."packageserver_log
              WHERE log_date >= ?
                AND log_date <= ?";

    $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($intStartDate, $intEndDate));
    $intReturn = $arrRow["ANZ"];

    return $intReturn;
}

function getPackageserverSystems($intStartDate, $intEndDate) {
    $strQuery = "SELECT log_hostname, count(*) AS ANZ
              FROM "._dbprefix_."packageserver_log
              WHERE log_date >= ?
                AND log_date <= ?
           GROUP BY log_hostname
           ORDER BY ANZ desc";

    $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($intStartDate, $intEndDate));


    return $arrRows;
}

function getPackageserverRequests($intStartDate, $intEndDate) {
    $strQuery = "SELECT count(*)
              FROM "._dbprefix_."packageserver_log
              WHERE log_date >= ?
                AND log_date <= ?";

    $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($intStartDate, $intEndDate));
    $intReturn = $arrRow["count(*)"];

    return $intReturn;
}


function getHits($intStartDate, $intEndDate) {
    $strQuery = "SELECT count(*)
                              FROM "._dbprefix_."stats_data
                              WHERE stats_date >= ?
                                AND stats_date <= ?";

    $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($intStartDate, $intEndDate));
    $intReturn = $arrRow["count(*)"];

    return $intReturn;
}

function getDownloads($intStartDate, $intEndDate) {
    $strQuery = "SELECT count(*)
                              FROM "._dbprefix_."mediamanager_dllog
                              WHERE downloads_log_date >= ?
                                AND downloads_log_date <= ?";

    $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array($intStartDate, $intEndDate));
    $intReturn = $arrRow["count(*)"];

    return $intReturn;
}

function getVisitors($intStartDate, $intEndDate) {

    $strQuery = "SELECT stats_ip , stats_browser, stats_date
                              FROM "._dbprefix_."stats_data
                              WHERE stats_date >= ?
                                        AND stats_date <= ?
                              GROUP BY stats_ip, stats_browser
                              ORDER BY stats_date ASC";

    $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($intStartDate, $intEndDate));
    $intReturn = count($arrRows);
    return $intReturn;
}

echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "</pre>";


