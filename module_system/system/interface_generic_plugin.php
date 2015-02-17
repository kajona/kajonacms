<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: interface_objectindexed_listener.php 6425 2014-02-10 14:33:08Z sidler $                               *
********************************************************************************************************/


/**
 * A generic plugin is an object implementing a given extension point / plugin point.
 * A plugin point is referenced by its string-based name.
 * The generic pluginmanager may be used to find all objects implementing the extension point.
 *
 * @author sidler@mulchprod.de
 * @package module_system
 * @since 4.5
 */
interface interface_generic_plugin {

    /**
     * Returns the name of extension/plugin the objects wants to contribute to.
     * @return string
     */
    public static function getExtensionName();

}
