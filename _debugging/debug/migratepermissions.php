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

echo "Creating new permissions table...\n";

$arrFields = array();
$arrFields["right_groupid"] = array("char20", false);
$arrFields["right_systemid"] = array("char20", false);
$arrFields["right_view"] = array("int", false, 0);
$arrFields["right_edit"] = array("int", false, 0);
$arrFields["right_delete"] = array("int", false, 0);
$arrFields["right_right"] = array("int", false, 0);
$arrFields["right_right1"] = array("int", false, 0);
$arrFields["right_right2"] = array("int", false, 0);
$arrFields["right_right3"] = array("int", false, 0);
$arrFields["right_right4"] = array("int", false, 0);
$arrFields["right_right5"] = array("int", false, 0);
$arrFields["right_changelog"] = array("int", false, 0);

$objDb = Carrier::getInstance()->getObjDB();
$objDb->createTable("system_right_2", $arrFields, array("right_groupid", "right_systemid"), array("right_groupid", "right_systemid"));

echo "Migrating old table to new table data...\n";

const INT_PAGESIZE = 2500;

$intStart = 0;
$intEnd = INT_PAGESIZE;

$arrResultSet = $objDb->getPArray("SELECT * FROM "._dbprefix_."system_right ORDER BY right_id DESC", array(), $intStart, $intEnd-1);
while (count($arrResultSet) > 0) {

    echo "Fetching records ".$intStart." to ".($intEnd-1).PHP_EOL;
    $arrInserts = array();

    foreach ($arrResultSet as $arrSingleRow) {


        $strSystemid = $arrSingleRow["right_id"];

        foreach (["right_changelog", "right_delete", "right_edit", "right_right", "right_right1", "right_right2", "right_right3", "right_right4", "right_right5", "right_view"] as $strOneCol) {
            foreach (explode(",", $arrSingleRow[$strOneCol]) as $strOneGroup) {
                if (!empty($strOneGroup)) {
                    if (!isset($arrInserts[$strSystemid.$strOneGroup])) {
                        $arrInserts[$strSystemid.$strOneGroup] = [
                            "right_groupid" => $strOneGroup,
                            "right_systemid" => $strSystemid,
                            "right_view"      => 0,
                            "right_edit"      => 0,
                            "right_delete"    => 0,
                            "right_changelog" => 0,
                            "right_right"     => 0,
                            "right_right1"    => 0,
                            "right_right2"    => 0,
                            "right_right3"    => 0,
                            "right_right4"    => 0,
                            "right_right5"    => 0,
                        ];

                        $arrInserts[$strSystemid.$strOneGroup]["right_groupid"] = $strOneGroup;
                        $arrInserts[$strSystemid.$strOneGroup]["right_systemid"] = $strSystemid;
                    }

                    $arrInserts[$strSystemid.$strOneGroup][$strOneCol] = 1;
                }
            }
        }


    }

    $objDb->multiInsert(
        "system_right_2",
        ["right_groupid", "right_systemid", "right_view" , "right_edit", "right_delete", "right_changelog", "right_right", "right_right1", "right_right2", "right_right3", "right_right4", "right_right5"],
        array_values($arrInserts)
    );

    echo "Converted ".count($arrResultSet)." source rows to ".count($arrInserts)." target rows ".PHP_EOL;
    flush();
    ob_flush();

    $intStart += INT_PAGESIZE;
    $intEnd += INT_PAGESIZE;
    $arrResultSet = $objDb->getPArray("SELECT * FROM "._dbprefix_."system_right ORDER BY right_id DESC", array(), $intStart, $intEnd-1);

    $objDb->flushQueryCache();

//    break;
//    if($intStart > 1000) {
//        break;
//    }


}
