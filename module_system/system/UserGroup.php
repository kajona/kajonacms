<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\System\System;

use Kajona\System\System\Usersources\UsersourcesGroupInterface;


/**
 * Model for a user-group, can be based on any type of usersource
 * Groups are NOT represented in the system-table.
 *
 * @package module_user
 * @author sidler@mulchprod.de
 *
 * @module user
 * @moduleId _user_modul_id_
 *
 * @targetTable user_group.group_id
 */
class UserGroup extends Model implements ModelInterface, AdminListableInterface
{

    /**
     * @var string
     * @tableColumn user_group.group_subsystem
     * @tableColumnDatatype char254
     * @tableColumnIndex
     */
    private $strSubsystem = "kajona";

    /**
     * @var string
     * @tableColumn user_group.group_name
     * @tableColumnDatatype char254
     * @tableColumnIndex
     */
    private $strName = "";

    /**
     * @var UsersourcesGroupInterface
     */
    private $objSourceGroup;


    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName()
    {
        return $this->getStrName();
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon()
    {
        return "icon_group";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo()
    {
        return $this->getNumberOfMembers();
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription()
    {
        $objUsersources = new UserSourcefactory();
        if (count($objUsersources->getArrUsersources()) > 1) {
            $objSubsystem = new UserSourcefactory();
            return $this->getLang("user_list_source", "user")." ".$objSubsystem->getUsersource($this->getStrSubsystem())->getStrReadableName();
        }
        return "";
    }


    /**
     * @inheritDoc
     */
    protected function onInsertToDb()
    {
        Logger::getInstance(Logger::USERSOURCES)->addLogRow("saved new group subsystem ".$this->getStrSubsystem()." / ".$this->getStrSystemid(), Logger::$levelInfo);
        //create the new instance on the remote-system
        $objSources = new UserSourcefactory();
        $objProvider = $objSources->getUsersource($this->getStrSubsystem());
        $objTargetGroup = $objProvider->getNewGroup();
        $objTargetGroup->updateObjectToDb();
        $objTargetGroup->setNewRecordId($this->getSystemid());
        $this->objDB->flushQueryCache();
        return true;
    }


    /**
     * Returns all groups from database
     *
     * @param FilterBase $objFilter
     * @param string $strFilter
     * @param bool|int $intStart
     * @param bool|int $intEnd
     *
     * @return UserGroup[]
     * @static
     */
    public static function getObjectListFiltered(FilterBase $objFilter = null, $strFilter = "", $intStart = null, $intEnd = null)
    {
        $objOrm = new OrmObjectlist();
        if($strFilter != "") {
            $objOrm->addWhereRestriction(new OrmPropertyCondition("strName", OrmComparatorEnum::Like(), "%".$strFilter."%"));
        }
        return $objOrm->getObjectList(UserGroup::class, "", $intStart, $intEnd);
    }


    /**
     * Fetches the number of groups available
     *
     * @param FilterBase $objFilter
     * @param string $strFilter
     *
     * @return int
     */
    public static function getObjectCountFiltered(FilterBase $objFilter = null, $strFilter = "")
    {
        $objOrm = new OrmObjectlist();
        if($strFilter != "") {
            $objOrm->addWhereRestriction(new OrmPropertyCondition("strName", OrmComparatorEnum::Like(), "%".$strFilter."%"));
        }
        return $objOrm->getObjectCount(UserGroup::class);
    }


    /**
     * Returns the number of members of the current group.
     *
     * @return int
     */
    public function getNumberOfMembers()
    {
        $this->loadSourceObject();
        return $this->objSourceGroup->getNumberOfMembers();
    }

    /**
     * Deletes the given group
     *
     * @return bool
     */
    public function deleteObjectFromDatabase()
    {
        Logger::getInstance(Logger::USERSOURCES)->addLogRow("deleted group with id ".$this->getSystemid()." (".$this->getStrName().")", Logger::$levelWarning);
        //Delete related group
        $this->getObjSourceGroup()->deleteGroup();
        return parent::deleteObjectFromDatabase();
    }

    /**
     * Loads the mapped source-object
     */
    private function loadSourceObject()
    {
        if ($this->objSourceGroup == null) {
            $objUsersources = new UserSourcefactory();
            $this->setObjSourceGroup($objUsersources->getSourceGroup($this));
        }
    }

    /**
     * Loads a group by its name, returns null of not found
     *
     * @param string $strName
     *
     * @return UserGroup
     */
    public static function getGroupByName($strName)
    {
        $objFactory = new UserSourcefactory();
        return $objFactory->getGroupByName($strName);
    }


    public function getStrSubsystem()
    {
        return $this->strSubsystem;
    }

    public function setStrSubsystem($strSubsystem)
    {
        $this->strSubsystem = $strSubsystem;
    }

    /**
     * @return UsersourcesGroupInterface
     */
    public function getObjSourceGroup()
    {
        $this->loadSourceObject();
        return $this->objSourceGroup;
    }

    public function setObjSourceGroup($objSourceGroup)
    {
        $this->objSourceGroup = $objSourceGroup;
    }

    public function getStrName()
    {
        return $this->strName;
    }

    public function setStrName($strName)
    {
        $this->strName = $strName;
    }

    public function getIntRecordStatus()
    {
        return 1;
    }

}
