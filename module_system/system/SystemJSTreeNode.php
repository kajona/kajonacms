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

    const STR_NODE_LIATTR = "li_attr";

    const STR_NODE_STATE = "state";
    const STR_NODE_STATE_OPENED = "opened";
    const STR_NODE_STATE_DISABLED = "disabled";
    const STR_NODE_STATE_SELECTED = "selected";


    private $strId = null;

    private $strText = null;

    private $strType = null;

    /**
     * @var null|boolean|SystemJSTreeNode[]
     */
    private $arrChildren = null;

    private $arrData = array(
        self::STR_NODE_DATA_RIGHTEDIT => false
    );

    private $arrAAttr = null;

    private $arrLiAttr = null;

    private $arrState = null;


    /**
     * @return null
     */
    public function getStrId()
    {
        return $this->strId;
    }

    /**
     * @param null $strId
     */
    public function setStrId($strId)
    {
        $this->strId = $strId;
        return $this;
    }

    /**
     * @return null
     */
    public function getStrText()
    {
        return $this->strText;
    }

    /**
     * @param null $strText
     */
    public function setStrText($strText)
    {
        $this->strText = $strText;
        return $this;
    }

    /**
     * @return null
     */
    public function getStrType()
    {
        return $this->strType;
    }

    /**
     * @param null $strType
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
     * @param array|boolean $arrChildren
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
     * @param null $arrData
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
     * @return array|null
     */
    public function getArrAAttr()
    {
        return $this->arrAAttr;
    }

    /**
     * @param null $arrAttr
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
     * @return array|null
     */
    public function getArrLiAttr()
    {
        return $this->arrLiAttr;
    }

    /**
     * @param null $arrAttr
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
     * @return array|null
     */
    public function getArrState()
    {
        return $this->arrState;
    }

    /**
     * @param null $arrState
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

    public function jsonSerialize()
    {
        return $this->getArrNode();
    }

}
