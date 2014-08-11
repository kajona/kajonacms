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
class class_orm_schemamanager_row  {

    /**
     * @var string
     */
    private $strName = "";

    /**
     * @var string
     */
    private $strDatatype = "";

    /**
     * @var bool
     */
    private $bitPrimaryKey = false;

    /**
     * @var bool
     */
    private $bitNull = true;

    /**
     * @var bool
     */
    private $bitIndex = false;

    /**
     * @param string $strName
     * @param string $strDatatype
     * @param bool $bitNull
     * @param bool $bitPrimaryKey
     * @param bool $bitIndex
     */
    function __construct($strName, $strDatatype, $bitNull = true, $bitPrimaryKey = false, $bitIndex = false) {
        $this->strDatatype = $strDatatype;
        $this->strName = $strName;
        $this->bitNull = $bitNull;
        $this->bitPrimaryKey = $bitPrimaryKey;
        $this->bitIndex = $bitIndex;
    }


    /**
     * @param string $strDatatype
     * @return void
     */
    public function setStrDatatype($strDatatype) {
        $this->strDatatype = $strDatatype;
    }

    /**
     * @return string
     */
    public function getStrDatatype() {
        return $this->strDatatype;
    }

    /**
     * @param string $strName
     * @return void
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

    /**
     * @param boolean $bitIndex
     * @return void
     */
    public function setBitIndex($bitIndex) {
        $this->bitIndex = $bitIndex;
    }

    /**
     * @return boolean
     */
    public function getBitIndex() {
        return $this->bitIndex;
    }

    /**
     * @param boolean $bitNull
     * @return void
     */
    public function setBitNull($bitNull) {
        $this->bitNull = $bitNull;
    }

    /**
     * @return boolean
     */
    public function getBitNull() {
        return $this->bitNull;
    }

    /**
     * @param boolean $bitPrimaryKey
     * @return void
     */
    public function setBitPrimaryKey($bitPrimaryKey) {
        $this->bitPrimaryKey = $bitPrimaryKey;
    }

    /**
     * @return boolean
     */
    public function getBitPrimaryKey() {
        return $this->bitPrimaryKey;
    }





}
