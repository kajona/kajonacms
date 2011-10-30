<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                           *
********************************************************************************************************/

header("Content-Type: text/html; charset=utf-8");
require_once("../system/includes.php");


echo "<pre>\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| Format Lang Files                                                             |\n";
echo "|                                                                               |\n";
echo "| Use this script to format all language files.                                 |\n";
echo "| The formatter is based on the Kajona language editor, so you need Java >= 1.6.|\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "|loading system kernel...                                                       |\n";
        $objCarrier = class_carrier::getInstance();
echo "|loaded.                                                                        |\n";
echo "+-------------------------------------------------------------------------------+\n";




//example for full path
//$strJavaCommand = "/opt/jdk1.6.0_22/jre/bin/java -jar '"._realpath_."/debug/KajonaLanguageEditorCore.jar' --formatLangfiles --projectFolder '"._realpath_."' ";


$strJavaCommand = "java -jar '"._realpath_."/debug/KajonaLanguageEditorCore.jar' --formatLangfiles --projectFolder '"._realpath_."' ";





if(issetPost("format")) {
    echo "starting formatting...\n";
    echo "\rcalling ".$strJavaCommand."\n";
    $intTemp = "";
    $arrOuput = array();
    exec($strJavaCommand, $arrOuput, $intTemp);

    echo  "\n\texit code: ".$intTemp."\n\n";
    foreach($arrOuput as $strOneLine)
        echo "\t".$strOneLine."\n";

    echo "\n...finished\n";

    echo "\nIf you encounter an exit code of 127, provide the full path to java in the header of the file.\n";
}
else {
    echo "<form method=\"post\">";
	echo "\n\nCurrent config:\n";
	echo $strJavaCommand."\n\n\n";
	echo "<input type=\"hidden\" name=\"format\" value=\"1\" />";
	echo "<input type=\"submit\" value=\"format\" />";
	echo "</form>";
}


echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "</pre>";


?>