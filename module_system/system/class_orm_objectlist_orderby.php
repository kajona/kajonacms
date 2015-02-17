<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

/**
 * A single order-by statement.
 * Pass them to the objectlist-instance before loading the resultset.
 * Pass values using the syntax "columnmame ORDER". Don't add "ORDER BY" or commas since this
 * will be done by the mapper.
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
     * @return void
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
