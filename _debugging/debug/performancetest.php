<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                     *
********************************************************************************************************/

header("Content-Type: text/html; charset=utf-8");
require_once("../system/bootstrap.php");


echo "<pre>\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| Systembenchmark                                                               |\n";
echo "|                                                                               |\n";
echo "+-------------------------------------------------------------------------------+\n";



$arrStart = gettimeofday();
$objCarrier = class_carrier::getInstance();
$arrEnd = gettimeofday();
$intTimeUsed = (($arrEnd['sec'] * 1000000 + $arrEnd['usec']) -($arrStart['sec'] * 1000000 + $arrStart['usec']))/1000000;
echo "init took              ".$intTimeUsed." sec\n";

echo "\n\tdatabase: ".  class_carrier::getInstance()->getObjConfig()->getConfig("dbdriver")."\n\n";

echo "Plain queries...\n\n";

$arrStart = gettimeofday();
$objDb = class_carrier::getInstance()->getObjDB();
    for($i = 0; $i<100; $i++) {
        $strQuery = "SELECT * FROM "._dbprefix_."system WHERE system_id = '0'";
        $objDb->getArray($strQuery);
        $objDb->getTables();
    }
$arrEnd = gettimeofday();
$intTimeUsed = (($arrEnd['sec'] * 1000000 + $arrEnd['usec']) -($arrStart['sec'] * 1000000 + $arrStart['usec']))/1000000;
echo "simple queries took    ".$intTimeUsed." sec\n";


$arrStart = gettimeofday();
$objDb = class_carrier::getInstance()->getObjDB();
    $arrSystemidsCreated = array();
    for($intI =0; $intI < 100; $intI++) {
        $objSystemCommon = new class_modul_system_common();
        $arrSystemidsCreated[] = $objSystemCommon->createSystemRecord(0, "test record");
    }
$arrEnd = gettimeofday();
$intTimeUsed = (($arrEnd['sec'] * 1000000 + $arrEnd['usec']) -($arrStart['sec'] * 1000000 + $arrStart['usec']))/1000000;
echo "TX inserts took        ".$intTimeUsed." sec\n";

$arrStart = gettimeofday();
    foreach($arrSystemidsCreated as $strOneSysID) {
        $objSystemCommon = new class_modul_system_common();
        $objSystemCommon->deleteSystemRecord($strOneSysID);
    }
$arrEnd = gettimeofday();
$intTimeUsed = (($arrEnd['sec'] * 1000000 + $arrEnd['usec']) -($arrStart['sec'] * 1000000 + $arrStart['usec']))/1000000;
echo "TX deletes took        ".$intTimeUsed." sec\n";



$arrStart = gettimeofday();
$objDb = class_carrier::getInstance()->getObjDB();
    $arrSystemidsCreated = array();
    for($intI =0; $intI < 100; $intI++) {
        $strId = generateSystemid();
        $arrSystemidsCreated[] = $strId;
        $strQuery = "INSERT INTO "._dbprefix_."system (system_id, system_prev_id, system_module_nr) VALUES ('".$strId."', '0', 10)";
        $objDb->_query($strQuery);
    }
$arrEnd = gettimeofday();
$intTimeUsed = (($arrEnd['sec'] * 1000000 + $arrEnd['usec']) -($arrStart['sec'] * 1000000 + $arrStart['usec']))/1000000;
echo "TX2 inserts took       ".$intTimeUsed." sec\n";

$arrStart = gettimeofday();
    foreach($arrSystemidsCreated as $strOneSysID) {
        $strId = $strOneSysID;
        $strQuery = "DELETE FROM "._dbprefix_."system WHERE system_id = '".$strId."'";
        $objDb->_query($strQuery);
    }
$arrEnd = gettimeofday();
$intTimeUsed = (($arrEnd['sec'] * 1000000 + $arrEnd['usec']) -($arrStart['sec'] * 1000000 + $arrStart['usec']))/1000000;
echo "TX2 deletes took       ".$intTimeUsed." sec\n";




$arrStart = gettimeofday();
$objDb = class_carrier::getInstance()->getObjDB();
    $arrSystemidsCreated = array();
    for($intI =0; $intI < 100; $intI++) {
        $strId = generateSystemid();
        $arrSystemidsCreated[] = $strId;
        $strQuery = "INSERT INTO "._dbprefix_."stats_data (stats_id) VALUES ('".$strId."')";
        $objDb->_query($strQuery);
    }
$arrEnd = gettimeofday();
$intTimeUsed = (($arrEnd['sec'] * 1000000 + $arrEnd['usec']) -($arrStart['sec'] * 1000000 + $arrStart['usec']))/1000000;
echo "inserts took           ".$intTimeUsed." sec\n";

$arrStart = gettimeofday();
    foreach($arrSystemidsCreated as $strOneSysID) {
        $strId = $strOneSysID;
        $strQuery = "DELETE FROM "._dbprefix_."stats_data WHERE stats_id = '".$strId."'";
        $objDb->_query($strQuery);
    }
$arrEnd = gettimeofday();
$intTimeUsed = (($arrEnd['sec'] * 1000000 + $arrEnd['usec']) -($arrStart['sec'] * 1000000 + $arrStart['usec']))/1000000;
echo "deletes took           ".$intTimeUsed." sec\n";





echo "\n\nPrepared statements...\n\n";

$arrStart = gettimeofday();
$objDb = class_carrier::getInstance()->getObjDB();
    for($i = 0; $i<100; $i++) {
        $strQuery = "SELECT * FROM "._dbprefix_."system WHERE system_id = ?";
        $objDb->getPArray($strQuery, array('0'));
        $objDb->getTables();
    }
$arrEnd = gettimeofday();
$intTimeUsed = (($arrEnd['sec'] * 1000000 + $arrEnd['usec']) -($arrStart['sec'] * 1000000 + $arrStart['usec']))/1000000;
echo "simple queries took    ".$intTimeUsed." sec\n";


$arrStart = gettimeofday();
$objDb = class_carrier::getInstance()->getObjDB();
    $arrSystemidsCreated = array();
    for($intI =0; $intI < 100; $intI++) {
        $objSystemCommon = new class_modul_system_common();
        $arrSystemidsCreated[] = $objSystemCommon->createSystemRecord(0, "test record");
    }
$arrEnd = gettimeofday();
$intTimeUsed = (($arrEnd['sec'] * 1000000 + $arrEnd['usec']) -($arrStart['sec'] * 1000000 + $arrStart['usec']))/1000000;
echo "TX inserts took        ".$intTimeUsed." sec\n";

$arrStart = gettimeofday();
    foreach($arrSystemidsCreated as $strOneSysID) {
        $objSystemCommon = new class_modul_system_common();
        $objSystemCommon->deleteSystemRecord($strOneSysID);
    }
$arrEnd = gettimeofday();
$intTimeUsed = (($arrEnd['sec'] * 1000000 + $arrEnd['usec']) -($arrStart['sec'] * 1000000 + $arrStart['usec']))/1000000;
echo "TX deletes took        ".$intTimeUsed." sec\n";



$arrStart = gettimeofday();
$objDb = class_carrier::getInstance()->getObjDB();
    $arrSystemidsCreated = array();
    for($intI =0; $intI < 100; $intI++) {
        $strId = generateSystemid();
        $arrSystemidsCreated[] = $strId;
        $strQuery = "INSERT INTO "._dbprefix_."system (system_id, system_prev_id, system_module_nr) VALUES (?, ?, ?)";
        $objDb->_pQuery($strQuery, array($strId, 0, 10));
    }
$arrEnd = gettimeofday();
$intTimeUsed = (($arrEnd['sec'] * 1000000 + $arrEnd['usec']) -($arrStart['sec'] * 1000000 + $arrStart['usec']))/1000000;
echo "TX2 inserts took       ".$intTimeUsed." sec\n";

$arrStart = gettimeofday();
    foreach($arrSystemidsCreated as $strOneSysID) {
        $strId = $strOneSysID;
        $strQuery = "DELETE FROM "._dbprefix_."system WHERE system_id = ?";
        $objDb->_pQuery($strQuery, array($strId));
    }
$arrEnd = gettimeofday();
$intTimeUsed = (($arrEnd['sec'] * 1000000 + $arrEnd['usec']) -($arrStart['sec'] * 1000000 + $arrStart['usec']))/1000000;
echo "TX2 deletes took       ".$intTimeUsed." sec\n";




$arrStart = gettimeofday();
$objDb = class_carrier::getInstance()->getObjDB();
    $arrSystemidsCreated = array();
    for($intI =0; $intI < 100; $intI++) {
        $strId = generateSystemid();
        $arrSystemidsCreated[] = $strId;
        $strQuery = "INSERT INTO "._dbprefix_."stats_data (stats_id) VALUES (?)";
        $objDb->_pQuery($strQuery, array($strId));
    }
$arrEnd = gettimeofday();
$intTimeUsed = (($arrEnd['sec'] * 1000000 + $arrEnd['usec']) -($arrStart['sec'] * 1000000 + $arrStart['usec']))/1000000;
echo "inserts took           ".$intTimeUsed." sec\n";

$arrStart = gettimeofday();
    foreach($arrSystemidsCreated as $strOneSysID) {
        $strId = $strOneSysID;
        $strQuery = "DELETE FROM "._dbprefix_."stats_data WHERE stats_id = ?";
        $objDb->_pQuery($strQuery, array($strId));
    }
$arrEnd = gettimeofday();
$intTimeUsed = (($arrEnd['sec'] * 1000000 + $arrEnd['usec']) -($arrStart['sec'] * 1000000 + $arrStart['usec']))/1000000;
echo "deletes took           ".$intTimeUsed." sec\n";


echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "</pre>";


?>