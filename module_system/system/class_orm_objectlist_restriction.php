<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

/**
 * A objectlist restriction may be used to create where restrictions for the objectList and objectCount queries
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.6
 */
class class_orm_objectlist_restriction {

    private $strWhere = "";
    private $arrParams = array();

    /**
     * @param string $strWhere
     * @param string|string[] $arrParams either a single value or an array of params
     */
    function __construct($strWhere, $arrParams = array()) {

        if(!is_array($arrParams))
            $arrParams = array($arrParams);

        $this->arrParams = $arrParams;
        $this->strWhere = " ".$strWhere." ";
    }

    /**
     * @param array $arrParams
     */
    public function setArrParams($arrParams) {
        $this->arrParams = $arrParams;
    }

    /**
     * @return array
     */
    public function getArrParams() {
        return $this->arrParams;
    }

    /**
     * @param string $strWhere
     */
    public function setStrWhere($strWhere) {
        $this->strWhere = $strWhere;
    }

    /**
     * @return string
     */
    public function getStrWhere() {
        return $this->strWhere;
    }



}
