<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

/**
 * A single order-by statement
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.6
 */
class class_orm_objectlist_orderby {

    private $strOrderBy = "";

    /**
     * @param string $strOrderBy
     */
    function __construct($strOrderBy) {
        $this->strOrderBy = " ".$strOrderBy." ";
    }

    /**
     * @param string $strWhere
     */
    public function setStrOrderBy($strWhere) {
        $this->strOrderBy = $strWhere;
    }

    /**
     * @return string
     */
    public function getStrOrderBy() {
        return $this->strOrderBy;
    }



}
