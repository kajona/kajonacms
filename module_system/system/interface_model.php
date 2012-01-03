<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
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
     * Commonly used constructor, given a systemid. use "" as systemid for new records
     *
     * @param string $strSystemid
     */
    public function __construct($strSystemid = "");


    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @abstract
     * @return string
     */
    public function getStrDisplayName();




}
