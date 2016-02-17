<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                     *
********************************************************************************************************/

echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| CACHEVIEW                                                                     |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";

echo "Creating 200 records with sortmanager...\n";

$arrTimestampStart = gettimeofday();

$objNaviTree = new class_module_navigation_tree();
$objNaviTree->updateObjectToDb();

/** @var \Kajona\System\System\Model[] $arrRecords */
$arrRecords = array();
for($intI = 0; $intI < 200; $intI++) {
    $objPoint = new class_module_navigation_point();
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

$objRootAspect = new class_module_system_aspect();
$objRootAspect->updateObjectToDb();

/** @var \Kajona\System\System\Model[] $arrRecords */
$arrRecords = array();
for($intI = 0; $intI < 200; $intI++) {
    $objAspect = new class_module_system_aspect();
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
echo  "<b>Queries db/cachesize/cached/fired:</b>     " . class_carrier::getInstance()->getObjDB()->getNumber() . "/" .
    class_carrier::getInstance()->getObjDB()->getCacheSize() . "/" .
    class_carrier::getInstance()->getObjDB()->getNumberCache() . "/" .
    (class_carrier::getInstance()->getObjDB()->getNumber() - class_carrier::getInstance()->getObjDB()->getNumberCache()) . "\n";



