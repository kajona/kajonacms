<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
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
     * responsible to create a valid object. being called at time of
     * object creation, if systemid given.
     * The model-class itself is responsible to invoke this method!
     *
     */
    public function initObject();

    /**
     * Method to update the existing record with the new values
     * @return bool
     *
     * @todo remove method pre 3.3.0
     * @deprecated use updateStateToDb() instead
     */
    public function updateObjectToDb();




}
?>