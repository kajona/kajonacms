<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/

/**
 * Interface for admin-plugins
 *
 * @package module_stats
 */
interface interface_admin_plugin {

    /**
     * Contructor, used to init the plugin
     *
     * @param \class_db $objDB
     * @param \class_toolkit_admin $objToolkit
     * @param \class_lang $objTexts
     */
    public function __construct(class_db $objDB, class_toolkit_admin $objToolkit, class_lang $objTexts);

    /**
     * Method used to fetch the title of the plugin.
     * This title may be used for the admin-navigation
     */
    public function getTitle();

    /**
     * @param \class_pluginmanager
     */
    public function registerPlugin(class_pluginmanager $objPluginmanager);

    public function getPluginCommand();

}
