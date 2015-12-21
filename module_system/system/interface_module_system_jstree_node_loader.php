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
 *
 */
interface interface_module_system_jstree_node_loader {

    /**
     * Returns all child nodes for the given system id
     *
     * @param $strSystemId
     * @return mixed
     */
    public function getChildNodes($strSystemId);


    /**
     * Returns a node for the tree
     *
     * @param $strSystemId
     * @return mixed
     */
    public function getNode($strSystemId);


}
