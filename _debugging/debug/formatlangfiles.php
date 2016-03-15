<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                           *
********************************************************************************************************/
namespace Kajona\Debugging\Debug;

use Kajona\System\System\Classloader;

echo "+-------------------------------------------------------------------------------+\n";
echo "| Kajona Debug Subsystem                                                        |\n";
echo "|                                                                               |\n";
echo "| Format Lang Files                                                             |\n";
echo "|                                                                               |\n";
echo "| Use this script to format all language files.                                 |\n";
echo "| The formatter is based on the Kajona language editor, so you need Java >= 1.6.|\n";
echo "+-------------------------------------------------------------------------------+\n";


if(issetPost("format")) {

    foreach(Classloader::getInstance()->getCoreDirectories() as $strOneCore) {

        $strJavaCommand = "java -jar '"._realpath_."/".$strOneCore."/_debugging/debug/KajonaLanguageEditorCore.jar' --formatLangfiles --projectFolder '"._realpath_."' ";

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
}
else {
    echo "<form method=\"post\">";
	echo "\n\nFormat all lang-files\n\n\n";
	echo "<input type=\"hidden\" name=\"format\" value=\"1\" />";
	echo "<input type=\"submit\" value=\"format\" />";
	echo "</form>";
}


echo "\n\n";
echo "+-------------------------------------------------------------------------------+\n";
echo "| (c) www.kajona.de                                                             |\n";
echo "+-------------------------------------------------------------------------------+\n";


