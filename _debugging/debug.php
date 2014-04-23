<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                               *
********************************************************************************************************/

class class_debug_helper {

    public function debugHelper() {
        echo "<pre>";
        echo "<b>Kajona V4 Debug Subsystem</b>\n\n";

        if(getGet("debugfile") != "") {


            echo "Loading path for ".getGet("debugfile")."\n";
            $strPath = array_search(getGet("debugfile"), class_resourceloader::getInstance()->getFolderContent("/debug", array(".php")));
            if($strPath !== false) {
                echo "Passing request to ".$strPath."\n\n";
                include _realpath_.$strPath;
            }

        }
        else {
            echo "Searching for debug-scripts available...\n";

            $arrFiles = class_resourceloader::getInstance()->getFolderContent("/debug", array(".php"));

            echo "<ul>";
            foreach($arrFiles as $strPath => $strOneFile) {
                echo "<li><a href='?debugfile=".$strOneFile."' >".$strOneFile."</a> <br />".$strPath."</li>";
            }

            echo "</ul>";
        }

        echo "</pre>";

    }
}

header("Content-Type: text/html; charset=utf-8");

$objDebug = new class_debug_helper();
$objDebug->debugHelper();