<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/


namespace Kajona\System\System;

/**
 *
 *
 * @package module_system
 * @author stefan.meyer1@yahoo.de
 *
 * @module system
 * @moduleId _system_modul_id_
 *
 */
class SystemJSTreeConfig {

    private $strRootNodeId = null;
    private $strNodeEndpoint = null;
    private $arrNodesToExpand = null;

    private $bitDndEnabled = true;
    private $bitCheckboxEnabled = false;
    private $arrTypes = null;

    /**
     * @return boolean
     */
    public function isBitDndEnabled()
    {
        return $this->bitDndEnabled;
    }

    /**
     * @param boolean $bitDndEnabled
     */
    public function setBitDndEnabled($bitDndEnabled)
    {
        $this->bitDndEnabled = $bitDndEnabled;
    }

    /**
     * @return boolean
     */
    public function isBitCheckboxEnabled()
    {
        return $this->bitCheckboxEnabled;
    }

    /**
     * @param boolean $bitCheckboxEnabled
     */
    public function setBitCheckboxEnabled($bitCheckboxEnabled)
    {
        $this->bitCheckboxEnabled = $bitCheckboxEnabled;
    }



    public function addType($strNodeType, array $arrValidChildren) {
        if($this->arrTypes == null) {
            $this->arrTypes = array();
        }
        $this->arrTypes[$strNodeType] = array(
            "valid_children" => $arrValidChildren
        );
    }


    public function toJson() {
        return json_encode(
            array(
                "dnd" => $this->bitDndEnabled,
                "checkbox" => $this->bitCheckboxEnabled,
                "types" => $this->arrTypes
            )
        );
    }

    /**
     * @return null
     */
    public function getStrRootNodeId()
    {
        return $this->strRootNodeId;
    }

    /**
     * @param null $strRootNodeId
     */
    public function setStrRootNodeId($strRootNodeId)
    {
        $this->strRootNodeId = $strRootNodeId;
    }

    /**
     * @return null
     */
    public function getStrNodeEndpoint()
    {
        return $this->strNodeEndpoint;
    }

    /**
     * @param null $strNodeEndpoint
     */
    public function setStrNodeEndpoint($strNodeEndpoint)
    {
        $this->strNodeEndpoint = $strNodeEndpoint;
    }

    /**
     * @return null
     */
    public function getArrNodesToExpand()
    {
        return $this->arrNodesToExpand;
    }

    /**
     * @param null $arrNodesToExpand
     */
    public function setArrNodesToExpand($arrNodesToExpand)
    {
        $this->arrNodesToExpand = $arrNodesToExpand;
    }
}
