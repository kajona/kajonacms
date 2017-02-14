<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/


namespace Kajona\System\System;

use JsonSerializable;

/**
 * Class for representing a node in the tree
 *
 * @package module_system
 * @author stefan.meyer1@yahoo.de
 *
 * @module system
 * @moduleId _system_modul_id_
 *
 */
class SystemJSTreeNode implements JsonSerializable
{
    const STR_NODE_ID = "id";
    const STR_NODE_TEXT = "text";
    const STR_NODE_CHILDREN = "children";
    const STR_NODE_TYPE = "type";

    const STR_NODE_DATA = "data";
    const STR_NODE_DATA_RIGHTEDIT = "rightedit";

    const STR_NODE_AATTR = "a_attr";
    const STR_NODE_AATTR_HREF = "href";
    const STR_NODE_AATTR_ONCLICK = "onclick";

    const STR_NODE_LIATTR = "li_attr";

    const STR_NODE_STATE = "state";
    const STR_NODE_STATE_OPENED = "opened";
    const STR_NODE_STATE_DISABLED = "disabled";
    const STR_NODE_STATE_SELECTED = "selected";


    /** @var string */
    private $strId = null;

    /** @var string */
    private $strText = null;

    /** @var string */
    private $strType = null;

    /**
     * @var SystemJSTreeNode[]|boolean
     */
    private $arrChildren = null;//value is boolean if node has children and children are not loaded

    /** @var array  */
    private $arrData = array(
        self::STR_NODE_DATA_RIGHTEDIT => false
    );

    /** @var array */
    private $arrAAttr = null;

    /** @var array */
    private $arrLiAttr = null;

    /** @var array */
    private $arrState = null;


    /**
     * @return string
     */
    public function getStrId()
    {
        return $this->strId;
    }

    /**
     * @param string $strId
     */
    public function setStrId($strId)
    {
        $this->strId = $strId;
        return $this;
    }

    /**
     * @return string
     */
    public function getStrText()
    {
        return $this->strText;
    }

    /**
     * @param string $strText
     */
    public function setStrText($strText)
    {
        $this->strText = $strText;
        return $this;
    }

    /**
     * @return string
     */
    public function getStrType()
    {
        return $this->strType;
    }

    /**
     * @param string $strType
     */
    public function setStrType($strType)
    {
        $this->strType = $strType;
        return $this;
    }

    /**
     * @return SystemJSTreeNode[]|boolean
     */
    public function getArrChildren()
    {
        return $this->arrChildren;
    }

    /**
     * @param SystemJSTreeNode[]|boolean $arrChildren
     */
    public function setArrChildren($arrChildren)
    {
        $this->arrChildren = $arrChildren;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getArrData()
    {
        return $this->arrData;
    }

    /**
     * Adds a new data attribute with the given name and value
     *
     * @param $strAttributeName
     * @param $strAttributeValue
     * @return $this
     */
    public function addDataAttr($strAttributeName, $strAttributeValue)
    {
        if($this->arrData === null) {
            $this->arrData = array();
        }
        $this->arrData[$strAttributeName] = $strAttributeValue;
        return $this;
    }

    /**
     * @return array
     */
    public function getArrAAttr()
    {
        return $this->arrAAttr;
    }

    /**
     * Adds a new aattr attribute with the given name and value
     *
     * @param $strAttributeName
     * @param $strAttributeValue
     * @return $this
     */
    public function addAAttrAttr($strAttributeName, $strAttributeValue)
    {
        if($this->arrAAttr === null) {
            $this->arrAAttr = array();
        }
        $this->arrAAttr[$strAttributeName] = $strAttributeValue;
        return $this;
    }

    /**
     * @return array
     */
    public function getArrLiAttr()
    {
        return $this->arrLiAttr;
    }

    /**
     * Adds a new li attribute with the given name and value
     *
     * @param $strAttributeName
     * @param $strAttributeValue
     * @return $this
     */
    public function addLiAttrAttr($strAttributeName, $strAttributeValue)
    {
        if($this->arrLiAttr === null) {
            $this->arrLiAttr = array();
        }
        $this->arrLiAttr[$strAttributeName] = $strAttributeValue;
        return $this;
    }

    /**
     * @return array
     */
    public function getArrState()
    {
        return $this->arrState;
    }

    /**
     * Adds a new state attribute with the given name and value
     *
     * @param $strAttributeName
     * @param $strAttributeValue
     * @return $this
     */
    public function addStateAttr($strAttributeName, $strAttributeValue)
    {
        if($this->arrState === null) {
            $this->arrState = array();
        }
        $this->arrState[$strAttributeName] = $strAttributeValue;
        return $this;
    }

    /**
     * Converst the node to an array
     *
     * @return array
     */
    public function getArrNode()
    {
        return array(
            self::STR_NODE_ID       => $this->getStrId(),
            self::STR_NODE_TEXT     => $this->getStrText(),
            self::STR_NODE_DATA     => $this->getArrData(),
            self::STR_NODE_AATTR    => $this->getArrAAttr(),
            self::STR_NODE_LIATTR   => $this->getArrLiAttr(),
            self::STR_NODE_STATE    => $this->getArrState(),
            self::STR_NODE_TYPE     => $this->getStrType(),
            self::STR_NODE_CHILDREN => $this->getArrChildren()
        );
    }

    /**
     * Returns the node as array which can be serialzed to json
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->getArrNode();
    }

}
