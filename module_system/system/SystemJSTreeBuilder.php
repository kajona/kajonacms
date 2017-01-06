<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


namespace Kajona\System\System;

/**
 * Base class for the tree nodes based on a class of type JStreeNodeLoaderInterface.
 * This class actually retrieves the nodes for a tree based on the given JsTreeNodeLoader
 *
 * @package module_system
 * @author stefan.meyer1@yahoo.de
 *
 * @module system
 * @moduleId _system_modul_id_
 *
 */
class SystemJSTreeBuilder
{
    const STR_PARAM_INITIALTOGGLING = "jstree_initialtoggling";
    const STR_PARAM_LOADALLCHILDNOES = "jstree_loadallchildnodes";
    const STR_PARAM_SELECTNODE = "jstree_selectednode";


    private $objNodeLoader = null;

    /**
     * SystemJSTreeBuilder constructor.
     *
     * @param JStreeNodeLoaderInterface $objNodeGetter
     */
    public function __construct(JStreeNodeLoaderInterface $objNodeGetter)
    {
        $this->objNodeLoader = $objNodeGetter;
    }

    /**
     * Retrieves nodes for a tree by the given path.
     *
     * @param array $arrSystemIdPath - array of system id's, id's contained in this array will be loaded
     *
     * @return SystemJSTreeNode[]
     */
    public function getNodesByPath(array $arrSystemIdPath)
    {

        if(empty($arrSystemIdPath)) {
            return true;
        }

        $strSystemId = array_shift($arrSystemIdPath);
        $arrChildren = $this->objNodeLoader->getChildNodes($strSystemId);

        $strSubId = array_key_exists(0, $arrSystemIdPath) ? $arrSystemIdPath[0] : null;
        foreach($arrChildren as $objChildNode) {

            if($strSubId !== null && $objChildNode->getStrId() == $strSubId) {
                $objChildNode->addStateAttr(SystemJSTreeNode::STR_NODE_STATE_OPENED, true);

                $arrSubchildNodes = $this->getNodesByPath($arrSystemIdPath);
                $objChildNode->setArrChildren($arrSubchildNodes);
            }
        }

        return $arrChildren;
    }


    /**
     * Method to get all child nodes for a given system id
     *
     * @param $strSystemId
     *
     * @return SystemJSTreeNode[]
     */
    public function getChildAllNodes($strSystemId)
    {
        $arrChildren = $this->objNodeLoader->getChildNodes($strSystemId);

        foreach($arrChildren as $objChildNode) {
            $objChildNode->addStateAttr(SystemJSTreeNode::STR_NODE_STATE_OPENED, true);

            $arrSubchildNodes = $this->getChildAllNodes($objChildNode->getStrId());
            $objChildNode->setArrChildren($arrSubchildNodes);
        }

        return $arrChildren;
    }


    /**
     * Returns the JSON reprensentation for the JSTree based on the retrieved nodes
     *
     * @param $arrSystemIdPath - array of system id's, id's contained in this array will be loaded
     * @param bool $bitInitialLoading
     *
     * @return string
     */
    public function getJson($arrSystemIdPath, $bitInitialLoading = false, $bitLoadAllSubnodes = false)
    {
        $arrNodes = $this->getNodesByPath($arrSystemIdPath);

        if($bitInitialLoading) {
            //root node is always first node in the array
            $objNode = $this->objNodeLoader->getNode($arrSystemIdPath[0]);
            $objNode->addStateAttr(SystemJSTreeNode::STR_NODE_STATE_OPENED, true);

            $objNode->setArrChildren($arrNodes);
            $arrNodes = $objNode;
        }
        else {
            foreach($arrNodes as $objSingleNode) {
                if($objSingleNode->getArrChildren() === true) {
                    if($bitLoadAllSubnodes) {
                        $objSingleNode->setArrChildren($this->getChildAllNodes($objSingleNode->getStrId()));
                    }
                    else {
                        $objSingleNode->setArrChildren($this->getNodesByPath(array($objSingleNode->getStrId())));
                    }
                }
            }
        }

        return json_encode($arrNodes);
    }
}
