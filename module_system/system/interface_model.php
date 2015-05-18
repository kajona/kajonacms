<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                          *
********************************************************************************************************/

/**
 * Interface for all model-classes
 *
 * @package module_system
 */
interface interface_model {

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @abstract
     * @todo move this to class_model, making this interface obsolete
     * @return string
     */
    public function getStrDisplayName();




}
