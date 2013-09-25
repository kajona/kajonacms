<?php

echo "<pre>\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";



$objStartDate = new class_date();
$objEndDate = new class_date();

$intOuter = 1;
for($intYear = 2012; $intYear <= 2013; $intYear++) {
    for($intMonth = 1; $intMonth <= 12; $intMonth++) {

        $objStartDate->setIntDay(1)->setIntMonth($intMonth)->setIntYear($intYear)->setIntHour(0)->setIntMin(0)->setIntSec(1);

        $objEndDate = clone $objStartDate;

        $objEndDate->setNextDay();
        while($objEndDate->getIntDay() != 1)
            $objEndDate->setNextDay();

        $objEndDate->setPreviousDay()->setIntHour(23)->setIntMin(59)->setIntSec(59);

        if($intOuter++ == 1)
            echo dateToString($objStartDate) ." - ".dateToString($objEndDate).": <br />";

//        echo "Hits:      ";
//        echo "".getHits($objStartDate->getTimeInOldStyle(), $objEndDate->getTimeInOldStyle())."<br />";
//        echo "Visitors:      ";
//        echo "".getVisitors($objStartDate->getTimeInOldStyle(), $objEndDate->getTimeInOldStyle())."<br />";
//        echo "Downloads: ";
//        echo "".getDownloads($objStartDate->getTimeInOldStyle(), $objEndDate->getTimeInOldStyle())."<br />";
//        echo "PServer-Requests: ";
//        echo "".getPackageserverRequests($objStartDate->getLongTimestamp(), $objEndDate->getLongTimestamp())."<br />";
//        echo "PServer-Unique Systems: ";
//        echo "".getUniquePackageserverSystems($objStartDate->getLongTimestamp(), $objEndDate->getLongTimestamp())."<br />";
//        echo "<br />";

    }
}
getTotalUniquePackagesererSystems();

function getTotalUniquePackagesererSystems() {
    $strQuery = "SELECT log_hostname, count(*) AS ANZ
                FROM "._dbprefix_."packageserver_log
                GROUP BY log_hostname
                ORDER BY ANZ DESC   ";

    foreach(class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array()) as $arrOneRow) {
        if(uniStrpos($arrOneRow["log_hostname"], "localhost/") === false
            && uniStrpos($arrOneRow["log_hostname"], "kajona.de") === false
            && uniStrpos($arrOneRow["log_hostname"], "kajonabase") === false
            && uniStrpos($arrOneRow["log_hostname"], "aquarium") === false
            && uniStrpos($arrOneRow["log_hostname"], "stb400s") === false
            && $arrOneRow["log_hostname"] != ""
        )
            echo sprintf("%4d", $arrOneRow["ANZ"]) ." => ". $arrOneRow["log_hostname"]."<br />";
    }
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


