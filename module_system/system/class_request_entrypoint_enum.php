<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * A enum indicating the entry-point for the current request, e.g. INDEX or XML
 *
 * @method static class_request_entrypoint_enum INDEX()
 * @method static class_request_entrypoint_enum XML()
 * @method static class_request_entrypoint_enum DOWNLOAD()
 * @method static class_request_entrypoint_enum IMAGE()
 * @method static class_request_entrypoint_enum INSTALLER()
 * @method static class_request_entrypoint_enum DEBUG()
 *
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.6
 */
class class_request_entrypoint_enum extends class_enum {
    /**
     * Return the array of possible, so allowed values for the current enum
     *
     * @return string[]
     */
    protected function getArrValues() {
        return array("INDEX", "XML", "DOWNLOAD", "IMAGE", "INSTALLER", "DEBUG");
    }

}

