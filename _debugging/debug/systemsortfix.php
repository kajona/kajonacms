<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                           *
********************************************************************************************************/

namespace Kajona\Debugging\Debug;

use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\SystemCommon;

echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| System Sort Fix                                                               |\n";
echo "|                                                                               |\n";
echo "| Analyzes the sort-values of the system-table and tries to fix them.           |\n";
echo "+-------------------------------------------------------------------------------+\n";


echo "scanning system-table...\n";
echo "traversing internal tree structure...\n\n";


if (getGet("doFix") == "") {
    echo "Auto-fixing is DISABLED. To enable the automatic fixing, click <a href='debug.php?debugfile=".basename(__FILE__)."&doFix=true'>here.</a>\nEnable it with care and only if you understand and verified the operations to be made.\n";
} else {
    echo "Auto-fixing is ENABLED. To disable the automatic fixing, click <a href='debug.php?debugfile=".basename(__FILE__)."'>here</a>\n";
}

echo "\nroot-record / 0\n";

validateSingleLevelSort("0");


echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";


function validateSingleLevelSort($strParentId)
{
    $objCommon = new SystemCommon($strParentId);

    if ($objCommon->getIntModuleNr() == 10 || $objCommon->getIntModuleNr() == 14) {
        $strQuery = "SELECT system_id
                         FROM "._dbprefix_."system
                         WHERE system_prev_id=? AND system_id != '0'
                           AND system_module_nr IN (?, ?)
                           AND system_deleted != 1
                         ORDER BY system_sort ASC";

        $arrNodesRaw = \Kajona\System\System\Carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strParentId, 10, 14));
        $arrNodes = array();
        foreach ($arrNodesRaw as $arrOneRow) {
            $arrNodes[] = $arrOneRow["system_id"];
        }
    } else {
        $arrNodes = $objCommon->getChildNodesAsIdArray($strParentId);
    }

    echo "<div style='padding-left: 25px;'>";

    $intExpected = 0;
    foreach ($arrNodes as $strOneId) {
        $objCurNode = Objectfactory::getInstance()->getObject($strOneId);
        if ($objCurNode == null) {
            $strCurLevel = "<span style='color: red'>error loading node for: ".$strOneId."</span>";
            echo "<div>".$strCurLevel."</div>";
        }

        $strCurLevel = $objCurNode->getSystemid()." - ".$objCurNode->getIntSort()." - ".$objCurNode->getStrRecordClass();


        if ($objCurNode->getIntSort() != -1 && ++$intExpected != $objCurNode->getIntSort()) {
            $strCurLevel = "<span style='color: red'>expected: ".$intExpected.", got ".$objCurNode->getIntSort()." @ ".$strCurLevel."</span>";

            if (getGet("doFix") != "") {
                $strCurLevel .= "\nSetting new sort-id to ".$intExpected."\n";
                $strQuery = "UPDATE "._dbprefix_."system SET system_sort = ? WHERE system_id = ? ";
                \Kajona\System\System\Carrier::getInstance()->getObjDB()->_pQuery($strQuery, array($intExpected, $objCurNode->getSystemid()));
            }
        } else {
            $strCurLevel = "<span style='color: green'>".$strCurLevel."</span>";
        }

        echo "<div>".$strCurLevel."</div>";

        if ($objCurNode instanceof PagesPage) {
            validateSinglePage($objCurNode);
        }
        validateSingleLevelSort($objCurNode->getSystemid());
    }
    echo "</div>";
    ob_flush();
    flush();
}


function validateSinglePage(PagesPage $objPage)
{
    $arrElements = PagesPageelement::getAllElementsOnPage($objPage->getSystemid());

    $intI = 0;
    $strPrevPlaceholder = "";
    $strPrevLanguage = "";
    foreach ($arrElements as $objOneElement) {
        $strCurLevel = $objOneElement->getSystemid()." - ".$objOneElement->getIntSort()." - ".$objOneElement->getStrRecordClass()." - ".$objOneElement->getStrDisplayName()." - ".$objOneElement->getStrPlaceholder();

        if ($strPrevPlaceholder != $objOneElement->getStrPlaceholder() || $strPrevLanguage != $objOneElement->getStrLanguage()) {
            $intI = 1;
        }

        if ($objOneElement->getIntSort() != $intI) {
            $strCurLevel = "<span style='color: red'>expected: ".$intI.", got ".$objOneElement->getIntSort()." @ ".$strCurLevel."</span>";

            if (getGet("doFix") != "") {
                $strCurLevel .= "\nSetting new sort-id to ".$intI."\n";
                $strQuery = "UPDATE "._dbprefix_."system SET system_sort = ? WHERE system_id = ? ";
                \Kajona\System\System\Carrier::getInstance()->getObjDB()->_pQuery($strQuery, array($intI, $objOneElement->getSystemid()));
            }
        } else {
            $strCurLevel = "<span style='color: green'>".$strCurLevel."</span>";
        }

        echo "<div style='padding-left: 25px;'>".$strCurLevel."</div>";
        $strPrevPlaceholder = $objOneElement->getStrPlaceholder();
        $strPrevLanguage = $objOneElement->getStrLanguage();
        $intI++;
    }
}