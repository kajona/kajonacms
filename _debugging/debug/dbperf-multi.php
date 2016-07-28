<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                     *
********************************************************************************************************/
namespace Kajona\Debugging\Debug;

use Kajona\Navigation\System\NavigationPoint;
use Kajona\Navigation\System\NavigationTree;
use Kajona\System\System\Carrier;
use Kajona\System\System\OrmObjectlist;

echo  "\nHint: You can give get parameter like output=file and/or runs=n (n=integer)!";

if (Carrier::getInstance()->issetParam("runs") && intval(Carrier::getInstance()->getParam("runs")) >= 1) {
    $intRuns2Do = Carrier::getInstance()->getParam("runs");
    echo  "\n\nRunning ".$intRuns2Do." times!\n";
} else {
    $intRuns2Do = 1;
    echo  "\nNo get parameter for 'runs'. Running just 1 time!\n";
}

$strDBDriver = Carrier::getInstance()->getObjConfig()->getConfig("dbdriver");

$strCsvFileName = "dbperf-multi-".$strDBDriver.".csv";
$strCsvFile = _realpath_."/files/public/".$strCsvFileName;
@unlink($strCsvFile);

$arrResultTable = array();

file_put_contents($strCsvFile, _webpath_."; \r\n", FILE_APPEND | LOCK_EX);
file_put_contents($strCsvFile, date('d.m.Y - H:i:s')."; \r\n", FILE_APPEND | LOCK_EX);
file_put_contents($strCsvFile, $strDBDriver."; \r\n", FILE_APPEND | LOCK_EX);

file_put_contents($strCsvFile, "Create (".$strDBDriver.");Read (".$strDBDriver.");Delete (".$strDBDriver.");\r\n", FILE_APPEND | LOCK_EX);


ob_flush(); flush();

for ($intRun = 1; $intRun <= $intRuns2Do; $intRun++) {
    $arrTimeSteps = array();

    echo  "\n\n-----------------------------------------------------";
    echo  "\n---------------    Run ".$intRun." of ".$intRuns2Do." -----------------------";
    echo  "\n-----------------------------------------------------";




    echo  "\n\n<b>Step 1:</b> Creating 200 records with sortmanager...\n";
    ob_flush();
    flush();
    $intTimer = -microtime(true);

    $objNaviTree = new NavigationTree();
    $objNaviTree->updateObjectToDb();

    /** @var \Kajona\System\System\Model[] $arrRecords */
    $arrRecords = array();
    for ($intI = 0; $intI < 200; $intI++) {
        $objPoint = new NavigationPoint();
        $objPoint->updateObjectToDb($objNaviTree->getSystemid());
        $arrRecords[] = $objPoint;
    }


    $intTimer += microtime(true);
    echo  "<b>PHP-Time</b> of creating records with sortmanager:             ".sprintf('%f', $intTimer)." sec";
    $arrTimeSteps[1] = number_format(sprintf('%f', $intTimer), 3, ',', '.');





    echo  "\n\n<b>Step 2:</b> Reading of entries...\n";
    ob_flush();
    flush();
    $intTimer = -microtime(true);

    $objOrm = new OrmObjectlist();
    $objOrm->getObjectList(NavigationPoint::class, $objNaviTree->getSystemid());

    $intTimer += microtime(true);
    echo  "<b>PHP-Time</b> of deleting entries:                              ".sprintf('%f', $intTimer)." sec";
    $arrTimeSteps[2] = number_format(sprintf('%f', $intTimer), 3, ',', '.');




    echo  "\n\n<b>Step 3:</b> Deletion of entries...\n";
    ob_flush();
    flush();
    $intTimer = -microtime(true);
    for ($intI = 40; $intI <= 60; $intI++) {
        $arrRecords[$intI]->deleteObjectFromDatabase();
        unset($arrRecords[$intI]);
    }

    foreach ($arrRecords as $objOnePoint) {
        $objOnePoint->deleteObjectFromDatabase();
    }

    $objNaviTree->deleteObjectFromDatabase();

    $intTimer += microtime(true);
    echo  "<b>PHP-Time</b> of deleting entries:                              ".sprintf('%f', $intTimer)." sec";
    $arrTimeSteps[3] = number_format(sprintf('%f', $intTimer), 3, ',', '.');
    ob_flush();
    flush();



    file_put_contents($strCsvFile, $arrTimeSteps[1].";".$arrTimeSteps[2].";".$arrTimeSteps[3].";\r\n  ", FILE_APPEND | LOCK_EX);
    $arrResultTable[] = array($arrTimeSteps[1], $arrTimeSteps[2], $arrTimeSteps[3]);
}


echo  "\n\n\n-----------------------------------------------------";
echo  "\nDownload <a href=\""._webpath_."/files/public/".$strCsvFileName."\">CSV file with results</a>";
echo  "\n-----------------------------------------------------";


echo  "\n\n<table border='1'>";
echo  "<tr><td>Create (".$strDBDriver.")</td><td>Read (".$strDBDriver.")</td><td>Delete (".$strDBDriver.")</td></tr>";
foreach ($arrResultTable as $oneRow) {
    echo  "<tr><td>".$oneRow[0]."</td><td>".$oneRow[1]."</td><td>".$oneRow[2]."</td></tr>";
}
echo  "</table>";





