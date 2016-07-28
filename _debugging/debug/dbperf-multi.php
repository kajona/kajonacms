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
use Kajona\System\System\Filesystem;

$strReturn = "";

$strReturn .= "+-------------------------------------------------------------------------------+\n";
$strReturn .= "| Kajona Debug Subsystem                                                        |\n";
$strReturn .= "+-------------------------------------------------------------------------------+\n";

$strReturn .= "\nHint: You can give get parameter like output=file and/or runs=n (n=integer)!";

if (issetGet("runs") && intval(getGet("runs")) >= 1) {
    $intRuns2Do = getGet("runs");
    $strReturn .= "\n\nRunning ".$intRuns2Do." times!\n";
} else {
    $intRuns2Do = 1;
    $strReturn .= "\nNo get parameter for 'runs'. Running just 1 time!\n";
}

$strDBDriver = Carrier::getInstance()->getObjConfig()->getConfig("dbdriver");

$strCsvFileName = "dbperf-multi-".$strDBDriver.".csv";
$strCsvFile = _realpath_."/files/public/".$strCsvFileName;
@unlink($strCsvFile);

$arrResultTable = array();

file_put_contents($strCsvFile, _webpath_."; \r\n", FILE_APPEND | LOCK_EX);
file_put_contents($strCsvFile, date('d.m.Y - H:i:s')."; \r\n", FILE_APPEND | LOCK_EX);
file_put_contents($strCsvFile, $strDBDriver."; \r\n", FILE_APPEND | LOCK_EX);

file_put_contents($strCsvFile, "Create (".$strDBDriver.");Delete (".$strDBDriver.");\r\n", FILE_APPEND | LOCK_EX);

for ($intRun = 1; $intRun <= $intRuns2Do; $intRun++) {
    $arrTimeSteps = array();

    $strReturn .= "\n\n-----------------------------------------------------";
    $strReturn .= "\n---------------    Run ".$intRun." of ".$intRuns2Do." -----------------------";
    $strReturn .= "\n-----------------------------------------------------";

    $strReturn .= "\n\n<b>Step 1:</b> Creating 200 records with sortmanager...\n";
    $arrTimestampStart = gettimeofday();

    $objNaviTree = new NavigationTree();
    $objNaviTree->updateObjectToDb();

    /** @var \Kajona\System\System\Model[] $arrRecords */
    $arrRecords = array();
    for ($intI = 0; $intI < 200; $intI++) {
        $objPoint = new NavigationPoint();
        $objPoint->updateObjectToDb($objNaviTree->getSystemid());
        $arrRecords[] = $objPoint;
    }

    $arrTimestampEnde = gettimeofday();
    $intTimeUsed = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
            - ($arrTimestampStart['sec'] * 1000000 + $arrTimestampStart['usec'])) / 1000000;
    $strReturn .= "<b>PHP-Time</b> of creating records with sortmanager:             ".number_format($intTimeUsed, 6)." sec";
    $arrTimeSteps[1] = number_format($intTimeUsed, 3, ',', '.');


    $strReturn .= "\n\n<b>Step 2:</b> Deletion of entries...\n";
    $arrTimestampStart = gettimeofday();
    for ($intI = 40; $intI <= 60; $intI++) {
        $arrRecords[$intI]->deleteObjectFromDatabase();
        unset($arrRecords[$intI]);
    }

    foreach ($arrRecords as $objOnePoint) {
        $objOnePoint->deleteObjectFromDatabase();
    }

    $objNaviTree->deleteObjectFromDatabase();

    $arrTimestampEnde = gettimeofday();
    $intTimeUsed = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
            - ($arrTimestampStart['sec'] * 1000000 + $arrTimestampStart['usec'])) / 1000000;
    $strReturn .= "<b>PHP-Time</b> of deleting entries:                              ".number_format($intTimeUsed, 6)." sec";
    $arrTimeSteps[2] = number_format($intTimeUsed, 3, ',', '.');

    file_put_contents($strCsvFile, $arrTimeSteps[1].";".$arrTimeSteps[2].";\r\n  ", FILE_APPEND | LOCK_EX);
    $arrResultTable[] = array($arrTimeSteps[1], $arrTimeSteps[2]);
}


$strReturn .= "\n\n\n-----------------------------------------------------";
$strReturn .= "\nDownload <a href=\""._webpath_."/files/public/".$strCsvFileName."\">CSV file with results</a>";
$strReturn .= "\n-----------------------------------------------------";


$strReturn .= "\n\n<table border='1'>";
$strReturn .= "<tr><td>Create (".$strDBDriver.")</td><td>Delete (".$strDBDriver.")</td></tr>";
foreach ($arrResultTable as $oneRow) {
    $strReturn .= "<tr><td>".$oneRow[0]."</td><td>".$oneRow[1]."</td></tr>";
}
$strReturn .= "</table>";


if (issetGet("output") && getGet("output") == "file") {
    $objFilesystem = new Filesystem();
    $objFilesystem->streamFile($strCsvFile);
    die;
} else {
    echo $strReturn;
}

