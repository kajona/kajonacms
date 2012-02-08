<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_tags_tag.php 4471 2012-01-25 16:49:25Z sidler $                                    *
********************************************************************************************************/

/**
 * Model-Class for tags.
 * There are two main purposes for this class:
 * - Representing the tag itself
 * - Acting as a wrapper to all tag-handling related methods such as assigning a tag
 *
 *
 * @package module_templatemanager
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_module_templatemanager_manager extends class_model implements interface_model  {

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {

        $this->setArrModuleEntry("modul", "tags");
        $this->setArrModuleEntry("moduleId", _tags_modul_id_);

		parent::__construct($strSystemid);

    }

    public function getStrDisplayName() {
        return "not implemented";
    }




    /**
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array();
    }

    /**
     * Initialises the current object, if a systemid was given
     *
     */
    protected function initObjectInternal() {

    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    protected function updateStateToDb() {
        return true;
    }


    /**
     * Deletes the tag with the given systemid from the system
     *
     * @return bool
     */
    protected function deleteObjectInternal() {
        return true;
    }




}
