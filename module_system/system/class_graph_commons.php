<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                             *
********************************************************************************************************/


/**
 * Class which provides common methods for graphs
 *
 * @package module_system
 * @since 4.6
 * @author stefan.meyer1@yahoo.de
 */

class class_graph_commons {

    /**
     * Converts a simple array (e.g. array(1,2,3,4,5)) to a data point array.
     * Each element of the given array will be set as the $floatValue of the data point
     *
     * @param $arrValues
     *
     * @return array of class_graph_datapoints
     */
    public static function convertArrValuesToDataPointArray(array $arrValues) {
        $arrDataPoints = array();

        foreach($arrValues as $objValue) {
            $objDataPoint = $objValue;
            if(!($objValue instanceof class_graph_datapoint)) {
                $objDataPoint = new class_graph_datapoint($objValue);
            }
            $arrDataPoints[] = $objDataPoint;
        }

        return $arrDataPoints;
    }

    /**
     * Returns an array only containing the floatValue of the given data points
     *
     * @param $arrDataPoints
     *
     * @return array
     */
    public static function getDataPointFloatValues(array $arrDataPoints) {
        $arrValues = array();
        foreach($arrDataPoints as $objDataPoint) {
            $arrValues[] = $objDataPoint->getFloatValue();
        }
        return $arrValues;
    }

} 