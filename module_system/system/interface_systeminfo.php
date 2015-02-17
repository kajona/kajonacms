<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Interface for systeminfo plugins.
 * Rendered when opening the systeminformation in the backend
 *
 * @package module_system
 * @since 4.5
 * @author sidler@mulchprod.de
 */
interface interface_systeminfo extends interface_generic_plugin {

    const STR_EXTENSION_POINT = "core.system.systeminfo";

    /**
     * Returns the title of the info-block
     * @return string
     */
    public function getStrTitle();

    /**
     * Returns the contents of the info-block
     * @return array
     */
    public function getArrContent();
}