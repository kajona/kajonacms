<?php

/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                               *
********************************************************************************************************/

class class_debug_helper
{

    private $arrTimestampStart = null;

    function __construct()
    {
        $this->arrTimestampStart = gettimeofday();
    }


    public function debugHelper()
    {
        echo "<pre>";
        echo "<b>Kajona V5 Debug Subsystem</b>\n\n";

        if (getGet("debugfile") != "") {


            echo "Loading path for ".getGet("debugfile")."\n";
            $strPath = array_search(getGet("debugfile"), \Kajona\System\System\Resourceloader::getInstance()->getFolderContent("/debug", array(".php")));
            if ($strPath !== false) {
                echo "Passing request to ".$strPath."\n\n";
                include $strPath;
            }

        }
        else {
            echo "Searching for debug-scripts available...\n";

            $arrFiles = \Kajona\System\System\Resourceloader::getInstance()->getFolderContent("/debug", array(".php"));

            echo "<ul>";
            foreach ($arrFiles as $strPath => $strOneFile) {
                echo "<li><a href='?debugfile=".$strOneFile."' >".$strOneFile."</a> <br />".$strPath."</li>";
            }

            echo "</ul>";
        }


        $arrTimestampEnde = gettimeofday();
        $intTimeUsed = (($arrTimestampEnde['sec'] * 1000000 + $arrTimestampEnde['usec'])
                - ($this->arrTimestampStart['sec'] * 1000000 + $this->arrTimestampStart['usec'])) / 1000000;


        echo "\n\n<b>PHP-Time:</b>                              ".number_format($intTimeUsed, 6)." sec \n";
        echo "<b>Queries db/cachesize/cached/fired:</b>     ".\Kajona\System\System\Carrier::getInstance()->getObjDB()->getNumber()."/".
            \Kajona\System\System\Carrier::getInstance()->getObjDB()->getCacheSize()."/".
            \Kajona\System\System\Carrier::getInstance()->getObjDB()->getNumberCache()."/".
            (\Kajona\System\System\Carrier::getInstance()->getObjDB()->getNumber() - \Kajona\System\System\Carrier::getInstance()->getObjDB()->getNumberCache())."\n";

        echo "<b>Memory/Max Memory:</b>                     ".bytesToString(memory_get_usage())."/".bytesToString(memory_get_peak_usage())." \n";
        echo "<b>Classes Loaded:</b>                        ".\Kajona\System\System\Classloader::getInstance()->getIntNumberOfClassesLoaded()." \n";

        echo "</pre>";

    }

}

header("Content-Type: text/html; charset=utf-8");

$objDebug = new class_debug_helper();
$objDebug->debugHelper();

\Kajona\System\System\CoreEventdispatcher::getInstance()->notifyGenericListeners(\Kajona\System\System\SystemEventidentifier::EVENT_SYSTEM_REQUEST_AFTERCONTENTSEND, array(\Kajona\System\System\RequestEntrypointEnum::DEBUG()));