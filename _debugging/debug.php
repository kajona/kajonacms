<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                               *
********************************************************************************************************/

class class_debug_helper {

    private $arrTimestampStart = null;

    function __construct() {
        $this->arrTimestampStart = gettimeofday();
    }


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


        $arrTimestampEnde = gettimeofday();
        $intTimeUsed = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
                - ($this->arrTimestampStart['sec'] * 1000000 + $this->arrTimestampStart['usec'])) / 1000000;


        echo  "\n\n<b>PHP-Time:</b>                              " . number_format($intTimeUsed, 6) . " sec \n";
        echo  "<b>Queries db/cachesize/cached/fired:</b>     " . class_carrier::getInstance()->getObjDB()->getNumber() . "/" .
            class_carrier::getInstance()->getObjDB()->getCacheSize() . "/" .
            class_carrier::getInstance()->getObjDB()->getNumberCache() . "/" .
            (class_carrier::getInstance()->getObjDB()->getNumber() - class_carrier::getInstance()->getObjDB()->getNumberCache()) . "\n";

        echo "<b>Templates cached:</b>                      " . class_carrier::getInstance()->getObjTemplate()->getNumberCacheSize() . " \n";

        echo "<b>Memory/Max Memory:</b>                     " . bytesToString(memory_get_usage()) . "/" . bytesToString(memory_get_peak_usage()) . " \n";
        echo "<b>Classes Loaded:</b>                        " . class_classloader::getInstance()->getIntNumberOfClassesLoaded() . " \n";

        echo "<b>Cache requests/hits/saves/cachesize:</b>   " .
        class_cache::getIntRequests() . "/" . class_cache::getIntHits() . "/" . class_cache::getIntSaves() . "/" . class_cache::getIntCachesize() . " \n";
        echo "</pre>";

    }

}

header("Content-Type: text/html; charset=utf-8");

$objDebug = new class_debug_helper();
$objDebug->debugHelper();

class_core_eventdispatcher::getInstance()->notifyGenericListeners(class_system_eventidentifier::EVENT_SYSTEM_REQUEST_AFTERCONTENTSEND, array(class_request_entrypoint_enum::INDEX()));