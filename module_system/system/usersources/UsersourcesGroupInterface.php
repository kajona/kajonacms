<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System\Usersources;

use Kajona\System\Admin\AdminFormgenerator;

/**
 * Global interface for all groups. Defines the common methods for all groups.
 *
 * @author sidler@mulchprod.de
 * @since 3.4.1
 * @package module_usersource
 */
interface UsersourcesGroupInterface
{

    /**
     * Adds a new member to the group - if possible
     *
     * @param UsersourcesUserInterface $objUser
     *
     * @return bool
     */
    public function addMember(UsersourcesUserInterface $objUser);

    /**
     * Returns an array of user-ids associated with the current group.
     * If possible, paging should be supported
     *
     * @param int $intStart
     * @param int $intEnd
     *
     * @return array
     */
    public function getUserIdsForGroup($intStart = null, $intEnd = null);

    /**
     * Returns the number of members of the current group.
     *
     * @return int
     */
    public function getNumberOfMembers();

    /**
     * Defines whether the current group-properties (e.g. the name) may be edited or is read-only
     *
     * @return bool
     */
    public function isEditable();


    /**
     * Removes a member from the current group - if possible.
     *
     * @param UsersourcesUserInterface $objUser
     *
     * @return bool
     */
    public function removeMember(UsersourcesUserInterface $objUser);

    /**
     * Deletes the current group
     *
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
     *
     * @return void
     */
    public function setNewRecordId($strId);

    /**
     * Hook to update the admin-form when editing / creating a single group
     *
     * @param AdminFormgenerator $objForm
     *
     * @return mixed
     */
    public function updateAdminForm(AdminFormgenerator $objForm);
}
