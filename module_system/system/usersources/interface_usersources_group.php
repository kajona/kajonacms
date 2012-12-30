<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                             *
********************************************************************************************************/

/**
 * Global interface for all groups. Defines the common methods for all groups.
 *
 * @author sidler@mulchprod.de
 * @since 3.4.1
 * @package module_usersource
 */
interface interface_usersources_group {

	/**
	 * Adds a new member to the group - if possible
	 * @param interface_usersources_user $objUser
	 */
	public function addMember(interface_usersources_user $objUser);

    /**
     * Returns an array of user-ids associated with the current group.
     * If possible, pageing should be supported
     *
     * @param int $intStart
     * @param int $intEnd
     * @return array
     */
	public function getUserIdsForGroup($intStart = null, $intEnd = null);

    /**
     * Returns the number of members of the current group.
     * @return int
     */
    public function getNumberOfMembers();

    /**
     * Defines whether the current group-properties (e.g. the name) may be edited or is read-only
     * @return bool
     */
	public function isEditable();


	/**
	 * Removes a member from the current group - if possible.
	 * @param interface_usersources_user $objUser
	 */
	public function removeMember(interface_usersources_user $objUser);

    /**
     * Deletes the current group
     * @return bool
     */
    public function deleteGroup();

    /**
     * Passes a new system-id to the object.
     * This id has to be used for newly created objects,
     * otherwise the mapping of kajona-users to users in the
     * subsystem may fail.
     *
     * @param string $strId
     * @return void
     */
    public function setNewRecordId($strId);
}
