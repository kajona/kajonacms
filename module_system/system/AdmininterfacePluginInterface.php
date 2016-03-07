<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;

use Kajona\System\Admin\ToolkitAdmin;

/**
 * Interface for admin-plugins
 *
 * @package module_system
 * @deprecated
 */
interface AdmininterfacePluginInterface
{

    /**
     * Contructor, used to init the plugin
     *
     * @param Database $objDB
     * @param ToolkitAdmin $objToolkit
     * @param Lang $objTexts
     */
    public function __construct(Database $objDB, ToolkitAdmin $objToolkit, Lang $objTexts);

    /**
     * Method used to fetch the title of the plugin.
     * This title may be used for the admin-navigation
     */
    public function getTitle();

    /**
     */
    public function registerPlugin($objPluginmanager);

    public function getPluginCommand();

}
