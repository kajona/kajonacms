<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                      *
********************************************************************************************************/

/**
 * Generates a graph-instance based on the current config.
 * Therefore either ez components or pChart will be used.
 * Since pChart won't be shipped with kajona, the user has to download it manually.
 *
 * @author sidler@mulchprod.de
 * @since 3.4
 * @package module_system
 */
class class_graph_factory {
    //put your code here

    public static $STR_TYPE_EZC = "ezc";
    public static $STR_TYPE_PCHART = "pchart";
    public static $STR_TYPE_FLOT = "flot";
    public static $STR_TYPE_JQPLOT = "jqplot";


    /**
     * Creates a graph-instance either based on the current config or
     * based on the passed param
     *
     * @param string $strType
     *
     * @throws class_exception
     * @return interface_graph
     */
    public static function getGraphInstance($strType = "") {

        if($strType == "") {
            if(defined("_system_graph_type_"))
                $strType = _system_graph_type_;
            else
                $strType = "jqplot";
        }

        $strClassname = "class_graph_".$strType;
        $strPath = class_resourceloader::getInstance()->getPathForFile("/system/".$strClassname.".php");
        if($strPath !== false) {
            $objReflection = new ReflectionClass($strClassname);
            if(!$objReflection->isAbstract() && $objReflection->implementsInterface("interface_graph"))
                return $objReflection->newInstance();
        }

        throw new class_exception("Requested charts-plugin ".$strType." not existing", class_exception::$level_FATALERROR);
    }
}
