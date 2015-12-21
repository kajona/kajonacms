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
 *
 */
interface interface_module_system_jstree_node_loader {

    public function getChildNodes($strSystemId);

    public function getNode($strSystemId);


}
