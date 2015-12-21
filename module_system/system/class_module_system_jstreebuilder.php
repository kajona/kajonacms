<?php
/*"******************************************************************************************************
*   (c) 2010-2015 ARTEMEON                                                                              *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                         *
********************************************************************************************************/


/**
 *
 *
 * @package module_prozessverwaltung
 * @author stefan.meyer@artemeon.de
 *
 */
class class_module_system_jstreebuilder {

    private $objNodeLoader = null;

    /**
     * interface_module_prozessverwaltung_node constructor.
     * @param null $objNodeGetter
     */
    public function __construct(interface_module_system_jstree_node_loader $objNodeGetter)
    {
        $this->objNodeLoader = $objNodeGetter;
    }

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

        return json_encode($arrNodes);
    }


}
