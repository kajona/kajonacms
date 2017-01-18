<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                     *
********************************************************************************************************/
namespace Kajona\Debugging\Debug;

use Kajona\System\System\Carrier;

echo "Fetching permission rows...\n";
$objDb = Carrier::getInstance()->getObjDB();

//echo "Consistency check:\n";
//echo "Old table: ".$objDb->getPRow("SELECT COUNT(*) as anz FROM "._dbprefix_."system_right", array())["anz"]." records\n";
//echo "New  denormed table: ".$objDb->getPRow("SELECT COUNT(DISTINCT right_systemid) as anz FROM "._dbprefix_."system_right_2", array())["anz"]." records\n";
//echo "New short table: ".$objDb->getPRow("SELECT COUNT(*) as anz FROM "._dbprefix_."system_right_3", array())["anz"]." records\n";

$intOld = [];
$intShortId = [];
$intMatrix= [];

for($intI = 0; $intI < 10; $intI++) {

    echo "\n------- Single Systemid-----------------\n";
    $strQuery = "SELECT * FROM "._dbprefix_."system_right WHERE right_id = ? AND 1 != {$intI}";
    $arrParams = array("0000fd4559a12d11a425");
    $intOld []= execQuery($strQuery, $arrParams);


    $strQuery = "SELECT * FROM "._dbprefix_."system_right_3 WHERE right_id = ? AND 1 != {$intI}";
    $arrParams = array("0000fd4559a12d11a425");
    $intShortId []= execQuery($strQuery, $arrParams);

    $strQuery = "SELECT right_systemid, right_groupid, sum(right_view), sum(right_edit), sum(right_delete), sum(right_right), sum(right_right1), sum(right_right2), sum(right_right3), sum(right_right4), sum(right_right5), sum(right_changelog)
             FROM "._dbprefix_."system_right_2 WHERE  right_systemid = ? AND 1 != {$intI} GROUP BY right_groupid ";
    $arrParams = array("0000fd4559a12d11a425");
    $intMatrix []= execQuery($strQuery, $arrParams);


    echo "\n------- Single Systemid and single group id-----------------\n";

    $strQuery = "SELECT * FROM "._dbprefix_."system_right WHERE right_id = ? AND right_view LIKE ?  AND 1 != {$intI}";
    $arrParams = array("0000fd4559a12d11a425", "%c2114ee54d9096c5ad84%");
    $intOld []= execQuery($strQuery, $arrParams);

    $strQuery = "SELECT * FROM "._dbprefix_."system_right_3 WHERE right_id = ? AND right_view LIKE ?  AND 1 != {$intI}";
    $arrParams = array("0000fd4559a12d11a425", "%,525,%");
    $intShortId []= execQuery($strQuery, $arrParams);

    $strQuery = "SELECT right_systemid, sum(right_view), sum(right_edit), sum(right_delete), sum(right_right), sum(right_right1), sum(right_right2), sum(right_right3), sum(right_right4), sum(right_right5), sum(right_changelog)
             FROM "._dbprefix_."system_right_2 WHERE  right_systemid = ? AND right_groupid = ?  AND 1 != {$intI}";
    $arrParams = array("0000fd4559a12d11a425", "c2114ee54d9096c5ad84");
    $intMatrix []= execQuery($strQuery, $arrParams);


    echo "\n------- Single Systemid and multiple group ids for right view----------------\n";

    $strQuery = "SELECT * FROM "._dbprefix_."system_right WHERE right_id = ? AND (right_view LIKE ? OR right_view LIKE ?) AND 1 != {$intI}";
    $arrParams = array("0000fd4559a12d11a425", "%c2114ee54d9096c5ad84%", "%727e63c54d8a792ca0d4%");
    $intOld []= execQuery($strQuery, $arrParams);

    $strQuery = "SELECT * FROM "._dbprefix_."system_right_3 WHERE right_id = ? AND (right_view LIKE ? OR right_view LIKE ?) AND 1 != {$intI}";
    $arrParams = array("0000fd4559a12d11a425", "%,525,%", "%,811,%");
    $intShortId []= execQuery($strQuery, $arrParams);

    $strQuery = "SELECT right_systemid, sum(right_view), sum(right_edit), sum(right_delete), sum(right_right), sum(right_right1), sum(right_right2), sum(right_right3), sum(right_right4), sum(right_right5), sum(right_changelog)
             FROM "._dbprefix_."system_right_2 WHERE  right_systemid = ? AND (right_groupid = ? OR right_groupid = ?) AND 1 != {$intI}";
    $arrParams = array("0000fd4559a12d11a425", "c2114ee54d9096c5ad84", "727e63c54d8a792ca0d4");
    $intMatrix []= execQuery($strQuery, $arrParams);


    echo "\n------- All rows for single group with edit permissions----------------\n";

    $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."system_right WHERE right_edit LIKE ? AND 1 != {$intI}";
    $arrParams = array("%c2114ee54d9096c5ad84%");
    $intOld []= execQuery($strQuery, $arrParams);

    $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."system_right_3 WHERE right_edit LIKE ? AND 1 != {$intI}";
    $arrParams = array("%,525,%");
    $intShortId []= execQuery($strQuery, $arrParams);

    $strQuery = "SELECT COUNT(*)
             FROM "._dbprefix_."system_right_2 WHERE  right_groupid = ? AND right_edit = 1 AND 1 != {$intI}";
    $arrParams = array("c2114ee54d9096c5ad84");
    $intMatrix []= execQuery($strQuery, $arrParams);

    $objDb->flushQueryCache();
}

for($intI = 0; $intI < count($intOld); $intI++) {
    echo str_pad($intI, 10)."|";
}
echo "\n";

for($intI = 0; $intI < count($intOld); $intI++) {
    echo str_pad($intOld[$intI], 10)."|";
}
echo "\n";
for($intI = 0; $intI < count($intOld); $intI++) {
    echo str_pad($intShortId[$intI], 10)."|";
}
echo "\n";
for($intI = 0; $intI < count($intOld); $intI++) {
    echo str_pad($intMatrix[$intI], 10)."|";
}
echo "\n";


echo "<b>Old:</b>    ".number_format(array_sum($intOld), 6)." sec \n";
echo "<b>Short:</b>  ".number_format(array_sum($intShortId), 6)." sec \n";
echo "<b>Matrix:</b> ".number_format(array_sum($intMatrix), 6)." sec \n";


function execQuery($strQuery, $arrParams) {

    $objDb = Carrier::getInstance()->getObjDB();

    $arrTimestampStart = gettimeofday();
    var_dump($objDb->prettifyQuery($strQuery, $arrParams));
    ($objDb->getPArray($strQuery, $arrParams));

    $arrTimestampEnde = gettimeofday();
    $intTimeUsed = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
            - ($arrTimestampStart['sec'] * 1000000 + $arrTimestampStart['usec'])) / 1000000;

    echo "<b>PHP-Time:</b> ".number_format($intTimeUsed, 6)." sec \n\n\n";

    return $intTimeUsed;
}