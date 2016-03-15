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

echo "Creating 200 records with sortmanager...\n";

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
echo  "\n\n<b>PHP-Time:</b>                              " . number_format($intTimeUsed, 6) . " sec \n";
$arrTimestampStart = gettimeofday();

echo "Deletion of entries...\n";
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
echo  "\n\n<b>PHP-Time:</b>                              " . number_format($intTimeUsed, 6) . " sec \n";
$arrTimestampStart = gettimeofday();



echo "\n\nCreating 200 records without sortmanager...\n";

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
echo  "\n\n<b>PHP-Time:</b>                              " . number_format($intTimeUsed, 6) . " sec \n";
$arrTimestampStart = gettimeofday();

echo "Deletion of entries...\n";
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


echo  "\n\n<b>PHP-Time:</b>                              " . number_format($intTimeUsed, 6) . " sec \n";
echo  "<b>Queries db/cachesize/cached/fired:</b>     " . \Kajona\System\System\Carrier::getInstance()->getObjDB()->getNumber() . "/" .
    \Kajona\System\System\Carrier::getInstance()->getObjDB()->getCacheSize() . "/" .
    \Kajona\System\System\Carrier::getInstance()->getObjDB()->getNumberCache() . "/" .
    (\Kajona\System\System\Carrier::getInstance()->getObjDB()->getNumber() - \Kajona\System\System\Carrier::getInstance()->getObjDB()->getNumberCache()) . "\n";



