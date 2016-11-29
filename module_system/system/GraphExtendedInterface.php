<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;

/**
 * Interface for all chart-engines.
 * Concrete instances may be returned by GraphFactory.
 * This interface defines only the least subset of methods, so each implementation may
 * provide additional methods.
 *
 * @author sidler@mulchprod.de
 * @since 3.4
 * @see GraphFactory
 * @package module_system
 */
interface GraphExtendedInterface extends GraphInterface
{



    /**
     * Used to create a stacked bar-chart.
     * For each set of bar-values you can call this method once.
     * A sample-code could be:
     *  $objGraph = new Graph();
     *  $objGraph->setStrXAxisTitle("x-axis");
     *  $objGraph->setStrYAxisTitle("y-axis");
     *  $objGraph->setStrGraphTitle("Test Graph");
     *
     *
     *  //simple array
     *      $objGraph->addStackedBarChartSet(array(1,2,4,5) "serie 1");
     *      $objGraph->addStackedBarChartSet(array(1,2,4,5) "serie 2");
     *
     * //datapoints array
     *      $objDataPoint1 = new GraphDatapoint(1);
     *      $objDataPoint2 = new GraphDatapoint(2);
     *      $objDataPoint3 = new GraphDatapoint(4);
     *      $objDataPoint4 = new GraphDatapoint(5);
     *
     *      //set action handler example
     *      $objDataPoint1->setObjActionHandler("<javascript code here>");
     *      $objDataPoint1->getObjActionHandlerValue("<value_object> e.g. some json");
     *
     *      $objGraph->addStackedBarChartSet(array($objDataPoint1, $objDataPoint2, $objDataPoint3, $objDataPoint4) "serie 1");
     *
     *
     * @param array $arrValues - an array with simple values or an array of data points (GraphDatapoint).
     *                           The advantage of a data points are that action handlers can be defined for each data point which will be executed when clicking on the data point in the chart.
     * @param bool $bitWriteValues Enables the rendering of values on top of the graphs
     * @param string $strLegend
     */
    public function addStackedBarChartSet($arrValues, $strLegend, $bitWriteValues = true);




}
