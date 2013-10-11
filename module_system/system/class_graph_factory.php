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

    public static $STR_TYPE_EZC = "CHART_TYPE_EZC";
    public static $STR_TYPE_PCHART = "CHART_TYPE_PCHART";
    public static $STR_TYPE_FLOT = "CHART_TYPE_FLOT";
    public static $STR_TYPE_JQPLOT = "CHART_TYPE_JQPLOT";


    /**
     * Creates a graph-instance either based on the current config or
     * based on the passed param
     *
     * @param string $strType
     * @return interface_graph
     */
    public static function getGraphInstance($strType = "") {

        $arrTypes = array(
            self::$STR_TYPE_EZC,
            self::$STR_TYPE_FLOT,
            self::$STR_TYPE_PCHART,
            self::$STR_TYPE_JQPLOT
        );

        if(!in_array($strType, $arrTypes)) {
            if(defined("_system_graph_type_")) {
                if(_system_graph_type_ == "flot")
                    $strType = self::$STR_TYPE_FLOT;
                else if(_system_graph_type_ == "ezc")
                    $strType = self::$STR_TYPE_EZC;
                else if(_system_graph_type_ == "pchart")
                    $strType = self::$STR_TYPE_PCHART;
                else if(_system_graph_type_ == "jqplot")
                    $strType = self::$STR_TYPE_JQPLOT;
                else
                    $strType = _system_graph_type_;
            }
            else
                $strType = self::$STR_TYPE_FLOT;
        }

        if($strType == self::$STR_TYPE_EZC) {
            return new class_graph_ezc();
        }
        else if($strType == self::$STR_TYPE_PCHART) {
            return new class_graph_pchart();
        }
        else if($strType == self::$STR_TYPE_FLOT) {
            return new class_graph_flot();
        }
        else if($strType == self::$STR_TYPE_JQPLOT) {
            return new class_graph_jqplot();
        }

        return new class_graph_flot();
    }
}
