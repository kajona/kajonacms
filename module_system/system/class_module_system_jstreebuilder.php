<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/


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
