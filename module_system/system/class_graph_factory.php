<?php
/*"******************************************************************************************************
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
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

    public static $STR_TYPE_EZC = "CHART_TYPE_EZC";
    public static $STR_TYPE_PCHART = "CHART_TYPE_PCHART";


    /**
     * Creates a graph-instance either based on the current config or
     * based on the passed param
     *
     * @param string $strType
     * @return interface_chart
     */
    public static function getGraphInstance($strType = "") {
        if($strType == self::$STR_TYPE_EZC) {
            return new class_graph_ezc();
        }
        else if($strType == self::$STR_TYPE_PCHART) {
            return new class_graph_pchart();
        }
        else {
            if(defined("_system_graph_type_")) {
                if(_system_graph_type_ == "pchart") {
                    return new class_graph_pchart();
                }
            }
        }

        return new class_graph_ezc();
    }
}
