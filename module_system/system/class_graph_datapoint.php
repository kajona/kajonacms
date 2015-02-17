<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                             *
********************************************************************************************************/


/**
 * Class which represents a data point of a graph
 *
 * @package module_system
 * @since 4.6
 * @author stefan.meyer1@yahoo.de
 */
class class_graph_datapoint {
    /**
     *
     */
    private $floatValue = null;

    /**
     * Value passed to the action handler
     */
    private $objActionHandlerValue = null;

    /**
     * Action handler which will be executed if the user clicks on the data point
     */
    private $objActionHandler = null;

    function __construct($floatValue) {
        $this->floatValue = $floatValue;
    }


    /**
     * @return mixed
     */
    public function getFloatValue() {
        return $this->floatValue;
    }

    /**
     * @param mixed $floatValue
     */
    public function setFloatValue($floatValue) {
        $this->floatValue = $floatValue;
    }

    /**
     * @return mixed
     */
    public function getObjActionHandler() {
        return $this->objActionHandler;
    }

    /**
     * @param mixed $objActionHandler
     */
    public function setObjActionHandler($objActionHandler) {
        $this->objActionHandler = $objActionHandler;
    }

    /**
     * @return mixed
     */
    public function getObjActionHandlerValue() {
        return $this->objActionHandlerValue;
    }

    /**
     * @param mixed $objActionValue
     */
    public function setObjActionHandlerValue($objActionValue) {
        $this->objActionHandlerValue = $objActionValue;
    }


}