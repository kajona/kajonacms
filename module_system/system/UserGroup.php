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
 * @blockFromAutosave
 */
class UserGroup extends Model implements ModelInterface, AdminListableInterface
{

    private $strSubsystem = "kajona";
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


    public function rightView()
    {
        return SystemModule::getModuleByName("user")->rightView();
    }

    public function rightEdit()
    {
        return SystemModule::getModuleByName("user")->rightEdit();
    }

    public function rightDelete()
    {
        return SystemModule::getModuleByName("user")->rightDelete();
    }


    /**
     * Initialises the current object, if a systemid was given
     */
    protected function initObjectInternal()
    {
        $strQuery = "SELECT * FROM "._dbprefix_."user_group WHERE group_id = ?";
        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));

        if (count($arrRow) > 0) {
            $this->setStrName($arrRow["group_name"]);
            $this->setStrSubsystem($arrRow["group_subsystem"]);
        }
    }

    /**
     * Updates the current object to the database
     *
     * @param bool $strPrevId
     *
     * @return bool
     */
    public function updateObjectToDb($strPrevId = false)
    {
        //mode-splitting
        if ($this->getSystemid() == "") {
            Logger::getInstance(Logger::USERSOURCES)->addLogRow("saved new group subsystem ".$this->getStrSubsystem()." / ".$this->getStrSystemid(), Logger::$levelInfo);
            $strGrId = generateSystemid();
            $this->setSystemid($strGrId);
            $strQuery = "INSERT INTO "._dbprefix_."user_group
                          (group_id, group_subsystem, group_name) VALUES
                          (?, ?, ?)";


            $bitReturn = $this->objDB->_pQuery($strQuery, array($strGrId, $this->getStrSubsystem(), $this->getStrName()));

            //create the new instance on the remote-system
            $objSources = new UserSourcefactory();
            $objProvider = $objSources->getUsersource($this->getStrSubsystem());
            $objTargetGroup = $objProvider->getNewGroup();
            $objTargetGroup->updateObjectToDb();
            $objTargetGroup->setNewRecordId($this->getSystemid());
            $this->objDB->flushQueryCache();

            return $bitReturn;
        }
        else {
            Logger::getInstance(Logger::USERSOURCES)->addLogRow("updated group ".$this->getStrName(), Logger::$levelInfo);
            $strQuery = "UPDATE "._dbprefix_."user_group
                            SET group_subsystem=?,
                                group_name=?
                            WHERE group_id=?";
            return $this->objDB->_pQuery($strQuery, array($this->getStrSubsystem(), $this->getStrName(), $this->getSystemid()));
        }
    }

    /**
     * Called whenever a update-request was fired.
     * Use this method to synchronize yourselves with the database.
     * Use only updates, inserts are not required to be implemented.
     *
     * @return bool
     */
    protected function updateStateToDb()
    {
        return true;
    }


    /**
     * @deprecated
     */
    public static function getObjectList($strFilter = "", $intStart = null, $intEnd = null)
    {
        return self::getObjectListFiltered(null, $strFilter, $intStart, $intEnd);
    }

    /**
     * @deprecated
     */
    public static function getObjectCount($strFilter = "")
    {
        return self::getObjectCountFiltered(null, $strFilter);
    }


    /**
     * Returns all groups from database
     *
     * @param string $strFilter
     * @param bool|int $intStart
     * @param bool|int $intEnd
     *
     * @return UserGroup[]
     * @static
     */
    public static function getObjectListFiltered(FilterBase $objFilter = null, $strFilter = "", $intStart = null, $intEnd = null)
    {
        $strQuery = "SELECT group_id
                       FROM "._dbprefix_."user_group
                    ".($strFilter != "" ? " WHERE group_name LIKE ? " : "")."
                   ORDER BY group_name";

        $arrFilter = array();
        if ($strFilter != "") {
            $arrFilter[] = "%".$strFilter."%";
        }

        $arrIds = Carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrFilter, $intStart, $intEnd);
        $arrReturn = array();
        foreach ($arrIds as $arrOneId) {
            $arrReturn[] = new UserGroup($arrOneId["group_id"]);
        }

        return $arrReturn;
    }


    /**
     * Fetches the number of groups available
     *
     * @param string $strFilter
     *
     * @return int
     */
    public static function getObjectCountFiltered(FilterBase $objFilter = null, $strFilter = "", $intStart = null, $intEnd = null)
    {
        $strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."user_group
               ".($strFilter != "" ? " WHERE group_name LIKE ? " : "");

        $arrFilter = array();
        if ($strFilter != "") {
            $arrFilter[] = "%".$strFilter."%";
        }

        $arrRow = Carrier::getInstance()->getObjDB()->getPRow($strQuery, $arrFilter);
        return $arrRow["COUNT(*)"];
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


    public function deleteObject()
    {
        return $this->deleteObjectFromDatabase();
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

        $strQuery = "DELETE FROM "._dbprefix_."user_group WHERE group_id=?";
        $bitReturn = $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
        CoreEventdispatcher::getInstance()->notifyGenericListeners(SystemEventidentifier::EVENT_SYSTEM_RECORDDELETED, array($this->getSystemid(), get_class($this)));
        return $bitReturn;
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


    // --- GETTERS / SETTERS --------------------------------------------------------------------------------
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
