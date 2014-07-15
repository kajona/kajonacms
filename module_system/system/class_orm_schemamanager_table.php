<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                        *
********************************************************************************************************/

/**
 * Data-object used by the schema-manager internally.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.6
 */
class class_orm_schemamanager_table  {

    /**
     * @var class_orm_schemamanager_row[]
     */
    private $arrRows = array();

    private $bitTxSafe = true;

    private $strName = "";

    /**
     * @param string $strName
     * @param bool $bitTxSafe
     */
    function __construct($strName, $bitTxSafe = true) {
        $this->strName = $strName;
        $this->bitTxSafe = $bitTxSafe;
    }

    /**
     * @param \class_orm_schemamanager_row[] $arrRows
     */
    public function setArrRows($arrRows) {
        $this->arrRows = $arrRows;
    }

    /**
     * @return \class_orm_schemamanager_row[]
     */
    public function getArrRows() {
        return $this->arrRows;
    }

    /**
     * @param boolean $bitTxSafe
     */
    public function setBitTxSafe($bitTxSafe) {
        $this->bitTxSafe = $bitTxSafe;
    }

    /**
     * @return boolean
     */
    public function getBitTxSafe() {
        return $this->bitTxSafe;
    }

    public function addRow(class_orm_schemamanager_row $objRow) {
        $this->arrRows[] = $objRow;
    }

    /**
     * @param string $strName
     */
    public function setStrName($strName) {
        $this->strName = $strName;
    }

    /**
     * @return string
     */
    public function getStrName() {
        return $this->strName;
    }



}
