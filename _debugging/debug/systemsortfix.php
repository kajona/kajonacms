<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: systemtablevisualizer.php 4579 2012-04-05 14:45:26Z sidler $                                           *
********************************************************************************************************/

echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| System Sort Fix                                                               |\n";
echo "|                                                                               |\n";
echo "| Analyzes the sort-values of the system-table and tries to fix them.           |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "|loading system kernel...                                                       |\n";
$objCarrier = class_carrier::getInstance();
echo "|loaded.                                                                        |\n";
echo "+-------------------------------------------------------------------------------+\n\n";

$objDb = class_carrier::getInstance()->getObjDB();

echo "scanning system-table...\n";
echo "traversing internal tree structure...\n\n";


if(getGet("doFix") == "")
    echo "Auto-fixing is DISABLED. To enable the automatic fixing, click <a href='debug.php?debugfile=".basename(__FILE__)."&doFix=true'>here.</a>\nEnable it with care and only if you understand and verified the operations to be made.\n";
else
    echo "Auto-fixing is ENABLED. To disable the automatic fixing, click <a href='debug.php?debugfile=".basename(__FILE__)."'>here</a>\n";

echo "\nroot-record / 0\n";

validateSingleLevelSort("0");




echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";


function validateSingleLevelSort($strParentId) {
    $objCommon = new class_module_system_common($strParentId);

    if($objCommon->getIntModuleNr() == _pages_modul_id_ || $objCommon->getIntModuleNr() == _pages_folder_id_) {
        $strQuery = "SELECT system_id
                         FROM "._dbprefix_."system
                         WHERE system_prev_id=? AND system_id != '0'
                           AND system_module_nr IN (?, ?)
                         ORDER BY system_sort ASC, system_comment ASC";

        $arrNodesRaw = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strParentId, _pages_modul_id_, _pages_folder_id_));
        $arrNodes = array();
        foreach($arrNodesRaw as $arrOneRow)
            $arrNodes[] = $arrOneRow["system_id"];
    }
    else {
        $arrNodes = $objCommon->getChildNodesAsIdArray($strParentId);
    }

    echo "<div style='padding-left: 25px;'>";
    for($intI = 1; $intI <= count($arrNodes); $intI++) {

        $objCurNode = class_objectfactory::getInstance()->getObject($arrNodes[$intI-1]);
        if($objCurNode == null) {
            $objCommon = new class_module_system_common($arrNodes[$intI-1]);
            $strCurLevel = "<span style='color: red'>error loading node for: ".$intI." @ ".$arrNodes[$intI-1]." - ".$objCommon->getStrRecordClass()."</span>";
            echo "<div>".$strCurLevel."</div>";
        }


        $strCurLevel = $objCurNode->getSystemid()." - ".$objCurNode->getIntSort()." - ".$objCurNode->getStrRecordClass()." - ".$objCurNode->getStrRecordComment();

        if($intI != $objCurNode->getIntSort()) {
            $strCurLevel = "<span style='color: red'>expected: ".$intI.", got ".$objCurNode->getIntSort()." @ ".$strCurLevel."</span>";

            if(getGet("doFix") != "") {
                $strCurLevel .= "\nSetting new sort-id to ".$intI."\n";
                $strQuery = "UPDATE "._dbprefix_."system SET system_sort = ? WHERE system_id = ? ";
                class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array($intI, $objCurNode->getSystemid()));
            }
        }
        else
            $strCurLevel = "<span style='color: green'>".$strCurLevel."</span>";

        echo "<div>".$strCurLevel."</div>";

        validateSingleLevelSort($objCurNode->getSystemid());
    }
    echo "</div>";
}