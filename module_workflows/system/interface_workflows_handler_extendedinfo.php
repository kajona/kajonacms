<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Adds some descriptive text to the current workflow-instance.
 * Used when rendering the workflow in the backend.
 *
 * @since 4.6
 * @package module_workflows
 */
interface interface_workflows_handler_extendedinfo extends interface_workflows_handler {

    /**
     * Generate some more details about the current instance, e.g. about the linked object or similar things.
     *
     * @return string
     */
    public function getInstanceInfo();
}
