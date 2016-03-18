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
 * Base class for the tree nodes based on a class of type InterfaceJStreeNodeLoader.
 * This class actually retrieves the nodes for a tree based on the given JsTreeNodeLoader
 *
 * @package module_system
 * @author stefan.meyer1@yahoo.de
 *
 * @module system
 * @moduleId _system_modul_id_
 *
 */
class SystemJSTreeBuilder {

    private $objNodeLoader = null;

    /**
     * SystemJSTreeBuilder constructor.
     * @param InterfaceJStreeNodeLoader $objNodeGetter
     */
    public function __construct(InterfaceJStreeNodeLoader $objNodeGetter)
    {
        $this->objNodeLoader = $objNodeGetter;
    }

    /**
     * Retrieves nodes for a tree by the given path.
     *
     * @param array $arrSystemIdPath - array of system id's
     * @return bool|mixed
     */
    public function getNodesByPath(array $arrSystemIdPath) {

        if(empty($arrSystemIdPath)) {
            return true;
        }

        $strSystemId = array_shift($arrSystemIdPath);
        $arrChildren = $this->objNodeLoader->getChildNodes($strSystemId);

        $strSubId = array_key_exists(0, $arrSystemIdPath) ? $arrSystemIdPath[0] : null;
        foreach ($arrChildren as &$arrChildNode) {

            if ($strSubId !== null && $arrChildNode["id"] == $strSubId) {
                $arrChildNode['state'] = array(
                    "opened"  => true
                );

                $arrSubchildNodes = $this->getNodesByPath($arrSystemIdPath);
                $arrChildNode["children"] = $arrSubchildNodes;
            }
        }

        return $arrChildren;
    }


    /**
     * Returns the JSON reprensentation for the JSTree based on the retrieved nodes
     *
     * @param $arrSystemIds
     * @param bool $bitInitialLoading
     * @return string
     */
    public function getJson($arrSystemIds, $bitInitialLoading = false) {
        $arrNodes = $this->getNodesByPath($arrSystemIds);

        if($bitInitialLoading) {
            //root node is always first node in the array
            $arrNode = $this->objNodeLoader->getNode($arrSystemIds[0]);
            $arrNode['state'] = array(
                "opened"  => true
            );
            $arrNode["children"] = $arrNodes;
            $arrNodes = $arrNode;
        }
        else {
            foreach($arrNodes as &$arrSingleNode) {
                if ($arrSingleNode["children"] == true) {
                    $arrSingleNode["children"] = $this->getNodesByPath(array($arrSingleNode["id"]));
                }
            }
        }

        return json_encode($arrNodes);
    }


}
