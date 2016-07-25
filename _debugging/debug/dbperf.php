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
use Kajona\System\System\SystemAspect;

echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";


$strCsvFile = _realpath_."/files/public/dbperf.csv";
@unlink($strCsvFile);

echo "\n-----------------------------------------------------";

echo "\n\n<b>Step 1:</b> Creating 200 records with sortmanager...\n";
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
echo  "<b>PHP-Time</b> of creating records with sortmanager:             " . number_format($intTimeUsed, 6) . " sec \n";
file_put_contents($strCsvFile, "1) Create with sm;".number_format($intTimeUsed, 3, ',','.')."\r\n", FILE_APPEND | LOCK_EX);



echo "\n\n<b>Step 2:</b> Deletion of entries...\n";
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
echo  "<b>PHP-Time</b> of deleting entries:                              " . number_format($intTimeUsed, 6) . " sec \n";
file_put_contents($strCsvFile, "2) Delete;".number_format($intTimeUsed, 3, ',','.')."\r\n", FILE_APPEND | LOCK_EX);



echo "\n-----------------------------------------------------";

echo "\n\n<b>Step 3:</b> Creating 200 records without sortmanager...\n";
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
echo  "<b>PHP-Time</b> of creating without sortmanager:               " . number_format($intTimeUsed, 6) . " sec \n";
file_put_contents($strCsvFile, "3) Create without sm;".number_format($intTimeUsed, 3, ',','.')."\r\n", FILE_APPEND | LOCK_EX);



echo "\n\n<b>Step 4:</b> Deletion of entries...\n";
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
echo  "<b>PHP-Time</b> deleting entries:                              " . number_format($intTimeUsed, 6) . " sec \n";
file_put_contents($strCsvFile, "4) Delete;".number_format($intTimeUsed, 3, ',','.')."\r\n", FILE_APPEND | LOCK_EX);

echo "\n-----------------------------------------------------";

echo "\n\nDownload <a href=\""._webpath_."/files/public/dbperf.csv\">CSV file with results</a>";

echo "\n-----------------------------------------------------";


