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
use Kajona\System\System\SystemAspect;
use Kajona\System\System\Filesystem;

$strReturn="";

$strReturn.= "+-------------------------------------------------------------------------------+\n";
$strReturn.= "| Kajona Debug Subsystem                                                        |\n";
$strReturn.= "|                                                                               |\n";
$strReturn.= "+-------------------------------------------------------------------------------+\n";

$strReturn.= "\nHint: You can give get parameter like output=file and/or runs=n (n=integer)!";

// TODO: runs=n bauen!

$strCsvFile = _realpath_."/files/public/dbperf-multi.csv";
@unlink($strCsvFile);



$strDBDriver = Carrier::getInstance()->getObjConfig()->getConfig("dbdriver");

file_put_contents($strCsvFile, _webpath_."; \r\n", FILE_APPEND | LOCK_EX);
file_put_contents($strCsvFile, date('d.m.Y - H:i:s')."; \r\n", FILE_APPEND | LOCK_EX);
file_put_contents($strCsvFile, $strDBDriver."; \r\n", FILE_APPEND | LOCK_EX);

file_put_contents($strCsvFile, "1) Create with sm Delete;2) Delete;3) Create without sm;4) Delete\r\n", FILE_APPEND | LOCK_EX);

for($intRun = 1; $intRun <= 1; $intRun++) {
    $arrTimeSteps=array();

    $strReturn.= "\n-----------------------------------------------------";
    $strReturn.= "\n---------------    Run ".$intRun."    -------------------------";
    $strReturn.= "\n-----------------------------------------------------";

    $strReturn.= "\n\n<b>Step 1:</b> Creating 200 records with sortmanager...\n";
    $arrTimestampStart = gettimeofday();

    $objNaviTree = new NavigationTree();
    $objNaviTree->updateObjectToDb();

    /** @var \Kajona\System\System\Model[] $arrRecords */
    $arrRecords = array();
    for($intI = 0; $intI < 200; $intI++) {
        $objPoint = new NavigationPoint();
        $objPoint->updateObjectToDb($objNaviTree->getSystemid());
        $arrRecords[] = $objPoint;
    }

    $arrTimestampEnde = gettimeofday();
    $intTimeUsed = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
            - ($arrTimestampStart['sec'] * 1000000 + $arrTimestampStart['usec'])) / 1000000;
    $strReturn.=  "<b>PHP-Time</b> of creating records with sortmanager:             " . number_format($intTimeUsed, 6) . " sec \n";
    $arrTimeSteps[1]=number_format($intTimeUsed, 3, ',','.');


    $strReturn.= "\n\n<b>Step 2:</b> Deletion of entries...\n";
    $arrTimestampStart = gettimeofday();
    for($intI = 40; $intI <= 60; $intI++) {
        $arrRecords[$intI]->deleteObjectFromDatabase();
        unset($arrRecords[$intI]);
    }

    foreach($arrRecords as $objOnePoint) {
        $objOnePoint->deleteObjectFromDatabase();
    }

    $objNaviTree->deleteObjectFromDatabase();

    $arrTimestampEnde = gettimeofday();
    $intTimeUsed = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
            - ($arrTimestampStart['sec'] * 1000000 + $arrTimestampStart['usec'])) / 1000000;
    $strReturn.=  "<b>PHP-Time</b> of deleting entries:                              " . number_format($intTimeUsed, 6) . " sec \n";
    $arrTimeSteps[2]=number_format($intTimeUsed, 3, ',','.');



    $strReturn.= "\n-----------------------------------------------------";

    $strReturn.= "\n\n<b>Step 3:</b> Creating 200 records without sortmanager...\n";
    $arrTimestampStart = gettimeofday();

    $objRootAspect = new SystemAspect();
    $objRootAspect->updateObjectToDb();

    /** @var \Kajona\System\System\Model[] $arrRecords */
    $arrRecords = array();
    for($intI = 0; $intI < 200; $intI++) {
        $objAspect = new SystemAspect();
        $objAspect->updateObjectToDb($objRootAspect->getSystemid());
        $arrRecords[] = $objAspect;
    }

    $arrTimestampEnde = gettimeofday();
    $intTimeUsed = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
            - ($arrTimestampStart['sec'] * 1000000 + $arrTimestampStart['usec'])) / 1000000;
    $strReturn.=  "<b>PHP-Time</b> of creating without sortmanager:               " . number_format($intTimeUsed, 6) . " sec \n";
    $arrTimeSteps[3]=number_format($intTimeUsed, 3, ',','.');



    $strReturn.= "\n\n<b>Step 4:</b> Deletion of entries...\n";
    $arrTimestampStart = gettimeofday();
    for($intI = 40; $intI <= 60; $intI++) {
        $arrRecords[$intI]->deleteObjectFromDatabase();
        unset($arrRecords[$intI]);
    }

    foreach($arrRecords as $objOnePoint) {
        $objOnePoint->deleteObjectFromDatabase();
    }

    $objRootAspect->deleteObjectFromDatabase();

    $arrTimestampEnde = gettimeofday();
    $intTimeUsed = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
            - ($arrTimestampStart['sec'] * 1000000 + $arrTimestampStart['usec'])) / 1000000;
    $strReturn.=  "<b>PHP-Time</b> deleting entries:                              " . number_format($intTimeUsed, 6) . " sec \n";
    $arrTimeSteps[4]=number_format($intTimeUsed, 3, ',','.');

    file_put_contents($strCsvFile, $arrTimeSteps[1].";".$arrTimeSteps[2].";".$arrTimeSteps[3].";".$arrTimeSteps[4].";\r\n  ", FILE_APPEND | LOCK_EX);
}


$strReturn.= "\n-----------------------------------------------------";
$strReturn.= "\n\nDownload <a href=\""._webpath_."/files/public/dbperf-multi.csv\">CSV file with results</a>";
$strReturn.= "\n-----------------------------------------------------";

if (issetGet("output") && getGet("output") == "file") {
    $objFilesystem = new Filesystem();
    $objFilesystem->streamFile($strCsvFile);
}
else
    echo $strReturn;


