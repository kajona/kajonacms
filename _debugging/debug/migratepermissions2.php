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


$objDb = Carrier::getInstance()->getObjDB();

echo "Creating new permissions table...\n";

$arrFields = array();
$arrFields["right_id"] = array("char20", false);
$arrFields["right_inherit"] = array("int", true);
$arrFields["right_view"] = array("text", true);
$arrFields["right_edit"] = array("text", true);
$arrFields["right_delete"] = array("text", true);
$arrFields["right_right"] = array("text", true);
$arrFields["right_right1"] = array("text", true);
$arrFields["right_right2"] = array("text", true);
$arrFields["right_right3"] = array("text", true);
$arrFields["right_right4"] = array("text", true);
$arrFields["right_right5"] = array("text", true);
$arrFields["right_changelog"] = array("text", true);

$objDb->createTable("system_right_3", $arrFields, array("right_id"));

echo "Migrating old table to new table data...\n";

$arrIdToInt = array();
foreach ($objDb->getPArray("SELECT group_id FROM "._dbprefix_."user_group ORDER BY group_id DESC", array()) as $intNr => $arrOneRow) {
    $arrIdToInt[$arrOneRow["group_id"]] = $intNr;
}

const INT_PAGESIZE = 2500;

$intStart = 0;
$intEnd = INT_PAGESIZE;

$arrResultSet = $objDb->getPArray("SELECT right_id, right_inherit, right_changelog, right_delete, right_edit, right_right, right_right1, right_right2, right_right3, right_right4, right_right5, right_view FROM "._dbprefix_."system_right ORDER BY right_id DESC", array(), $intStart, $intEnd-1);
while (count($arrResultSet) > 0) {

    echo "Fetching records ".$intStart." to ".($intEnd-1).PHP_EOL;
    $arrInserts = array();

    foreach ($arrResultSet as $arrSingleRow) {

        foreach (["right_changelog", "right_delete", "right_edit", "right_right", "right_right1", "right_right2", "right_right3", "right_right4", "right_right5", "right_view"] as $strOneCol) {
            $strNewString = ",";
            foreach (explode(",", $arrSingleRow[$strOneCol]) as $strOneGroup) {
                if (!empty($strOneGroup) && isset($arrIdToInt[$strOneGroup])) {
                    $strNewString .= $arrIdToInt[$strOneGroup].",";
                }


            }
            $arrSingleRow[$strOneCol] = $strNewString;
        }

        $arrInserts[] = $arrSingleRow;

    }

    $objDb->multiInsert(
        "system_right_3",
        ["right_id", "right_inherit", "right_changelog", "right_delete", "right_edit", "right_right", "right_right1", "right_right2", "right_right3", "right_right4", "right_right5", "right_view"],
        array_values($arrInserts)
    );

    echo "Converted ".count($arrResultSet)." source rows to ".count($arrInserts)." target rows ".PHP_EOL;
    flush();
    ob_flush();

    $intStart += INT_PAGESIZE;
    $intEnd += INT_PAGESIZE;
    $arrResultSet = $objDb->getPArray("SELECT right_id, right_inherit, right_changelog, right_delete, right_edit, right_right, right_right1, right_right2, right_right3, right_right4, right_right5, right_view FROM "._dbprefix_."system_right ORDER BY right_id DESC", array(), $intStart, $intEnd-1);

    $objDb->flushQueryCache();

//    if($intStart > 1000) {
//        break;
//    }


}
