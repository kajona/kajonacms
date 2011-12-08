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
     * Deletes the current object from the system
     * @abstract
     * @return bool
     */
    public function deleteObject();

    /**
     * Returns a human readable description of the current object. Used mainly for internal reasons, e.g. in database-descriptions
     * @abstract
     * @return string
     *
     * @deprecated will be removed
     */
    public function getObjectDescription();

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @abstract
     * @return string
     */
    public function getStrDisplayName();

    /**
     * Returns a list of tables the current object is persisted to.
     * A new record is created in each table, as soon as a save-/update-request was triggered by the framework.
     * The array should contain the name of the table as the key and the name
     * of the primary-key (so the column name) as the matching value.
     * E.g.: array(_dbprefix_."pages" => "page_id)
     *
     * @abstract
     * @return array [table => primary row name]
     */
    public function getObjectTables();

    /**
     * Called whenever a update-request was fired.
     * Use this method to synchronize yourselves with the database.
     * Use only updates, inserts are not required to be implemented.
     *
     * @abstract
     * @return bool
     */
    public function updateStateToDb();


}
