<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/


namespace Kajona\System\System;

/**
 * Config Object for a JsTree.
 *
 * @package module_system
 * @author stefan.meyer1@yahoo.de
 *
 * @module system
 * @moduleId _system_modul_id_
 *
 */
class SystemJSTreeConfig
{
    const STR_CONTEXTMENU_OPEN_ALL_NODES = "OPEN_ALL_NODES";

    /**
     * The rrot id of the tree
     *
     * @var null
     */
    private $strRootNodeId = null;

    /**
     * Endpoint (URL) which is being called for loading the nodes
     *
     * @var null
     */
    private $strNodeEndpoint = null;

    /**
     * Contains an array of id's. If set, then treepath will be opened
     *
     * @var null
     */
    private $arrNodesToExpand = null;

    /**
     * @var bool - if true dnd is enabled on the tree (@see dnd plugin from jstree)
     */
    private $bitDndEnabled = true;

    /**
     * @var bool - if true checkboxeds are rendered for each node
     */
    private $bitCheckboxEnabled = false;

    /**
     * @var null|array - array which keeps the typ information (@see types plugin from jstree)
     */
    private $arrTypes = null;

    /**
     * @var null (@see contextmenu plugin from jstree)
     */
    private $arrContextMenu = null;

    /**
     * SystemJSTreeConfig constructor.
     */
    public function __construct()
    {
        $this->initTreeConfig();
    }


    protected function initTreeConfig() {
        //Add default context menu items
        $this->addPredefinedContextMenuItems(SystemJSTreeConfig::STR_CONTEXTMENU_OPEN_ALL_NODES);
    }


    /**
     * Checks if Dnd is enabled.
     *
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


    /**
     * Method to control nesting rules of a node, e.g.
     *
     * nodetypeX => array(nodetypeY, nodetypeZ) means that nodetypeX cann only have nodetypeY and nodetypeZ as children.
     *
     * @param $strNodeType
     * @param array $arrValidChildren
     */
    public function addType($strNodeType, array $arrValidChildren)
    {
        if($this->arrTypes == null) {
            $this->arrTypes = array();
        }
        $this->arrTypes[$strNodeType] = array(
            "valid_children" => $arrValidChildren
        );
    }

    /**
     * @param $arrItem
     */
    public function addContextMenuItem($strIndex, $arrItem)
    {
        if($this->arrContextMenu == null) {
            $this->arrContextMenu = array();
            $this->arrContextMenu["items"] = array();
        }

        $this->arrContextMenu["items"][$strIndex] = $arrItem;
    }


    /**
     * Converst the set configs to Json
     *
     * @return string
     */
    public function toJson()
    {
        //build array
        $arrJson = array(
            "dnd"         => $this->bitDndEnabled,
            "checkbox"    => $this->bitCheckboxEnabled,
            "types"       => $this->arrTypes,
            "contextmenu" => $this->arrContextMenu
        );

        $strJson = json_encode($arrJson);

        $strJson = preg_replace('/:"function(.*?)"/', ":function$1", $strJson);//remove '"' at end and beginning where a function(..) {...} as value is defined

        return $strJson;
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


    /**
     * Method fo adding predefined context menu items for a tree
     *
     * @param $strItemName
     */
    public function addPredefinedContextMenuItems($strItemName)
    {
        switch($strItemName) {
            case self::STR_CONTEXTMENU_OPEN_ALL_NODES:
                //contextmenu item for opening all nodes
                $arrItem = array(
                    "label"  => Lang::getInstance()->getLang("commons_tree_contextmenu_loadallsubnodes", "system"),
                    "action" => "function(objNode, event){KAJONA.kajonatree.contextmenu.openAllNodes(objNode, event);}",
                    "icon"   => "fa fa-sitemap"
                );

                $this->addContextMenuItem(self::STR_CONTEXTMENU_OPEN_ALL_NODES, $arrItem);
        }
    }
}
