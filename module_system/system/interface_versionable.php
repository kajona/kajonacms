<?php
/*"******************************************************************************************************
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * The interface_versionable lists all methods an object has to implement in
 * order to comply with the internal changelog-system.
 * The mechanism follows parts of the memento-pattern (@see Gang Of Four, Gamma et. al)
 *
 * For future releases it is planned to extend the capabilities to a full
 * versioning system.
 *
 * @author sidler@mulchprod.de
 * @package module_system
 * @since 3.4.0
 */
interface interface_versionable {

    /**
     * Returns the classname of the logged record.
     * Used afterwards to instantiate the related object
     *
     * @return string
     */
    public function getClassname();

    /**
     * Returns the list of changed fields in order to be processed by the
     * changlog-class. The passed class indicates what fields to return
     *
     * Expects a multi-dim array using the structure:
     * array( array("property", "oldvalue", "newvalue" ));
     *
     * @param string $strAction
     * @return array
     */
    public function getChangedFields($strAction);

    /**
     * Returns a human readable name of the action stored with the changeset.
     *
     * @param string $strAction the technical actionname
     * @return string the human readable name
     */
    public function getActionName($strAction);

    /**
     * Returns a human readable name of the record / object stored with the changeset.
     *
     * @return string the human readable name
     */
    public function getRecordName();

    /**
     * Returns a human readable name of the property-name stored with the changeset.
     *
     * @param string $strProperty the technical property-name
     * @return string the human readable name
     */
    public function getPropertyName($strProperty);

    /**
     * Returns the modules' name of the logged entry.
     *
     * @return string
     */
    public function getModuleName();

    /**
     * Renders a stored value. Allows the class to modify the value to display, e.g. to
     * replace a timestamp by a readable string.
     *
     * @param string $strProperty
     * @param string $strValue
     * @return string
     */
    public function renderValue($strProperty, $strValue);
}
