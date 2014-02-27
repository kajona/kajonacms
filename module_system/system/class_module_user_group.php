<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

/**
 * Model for a user-group, can be based on any type of usersource
 * Groups are NOT represented in the system-table.
 *
 * @package module_user
 * @author sidler@mulchprod.de
 *
 * @module user
 * @moduleId _user_modul_id_
 */
class class_module_user_group extends class_model implements interface_model, interface_admin_listable {

    private $strSubsystem = "kajona";
    private $strName = "";

    /**
     * @var interface_usersources_group
     */
    private $objSourceGroup;


    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrName();
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {
        return "icon_group";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        return $this->getNumberOfMembers();
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        $objUsersources = new class_module_user_sourcefactory();
        if(count($objUsersources->getArrUsersources()) > 1) {
            return $this->getLang("user_list_source", "user")." ".$this->getStrSubsystem();
        }
        return "";
    }


    public function rightView() {
        return class_module_system_module::getModuleByName("user")->rightView();
    }

    public function rightEdit() {
        return class_module_system_module::getModuleByName("user")->rightEdit();
    }

    public function rightDelete() {
        return class_module_system_module::getModuleByName("user")->rightDelete();
    }


    /**
     * Initialises the current object, if a systemid was given

     */
    protected function initObjectInternal() {
        $strQuery = "SELECT * FROM "._dbprefix_."user_group WHERE group_id = ?";
        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));

        if(count($arrRow) > 0) {
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
    public function updateObjectToDb($strPrevId = false) {
        //mode-splitting
        if($this->getSystemid() == "") {
            class_logger::getInstance(class_logger::USERSOURCES)->addLogRow("saved new group subsystem ".$this->getStrSubsystem()." / ".$this->getStrSystemid(), class_logger::$levelInfo);
            $strGrId = generateSystemid();
            $this->setSystemid($strGrId);
            $strQuery = "INSERT INTO "._dbprefix_."user_group
                          (group_id, group_subsystem, group_name) VALUES
                          (?, ?, ?)";


            $bitReturn = $this->objDB->_pQuery($strQuery, array($strGrId, $this->getStrSubsystem(), $this->getStrName()));

            //create the new instance on the remote-system
            $objSources = new class_module_user_sourcefactory();
            $objProvider = $objSources->getUsersource($this->getStrSubsystem());
            $objTargetGroup = $objProvider->getNewGroup();
            $objTargetGroup->updateObjectToDb();
            $objTargetGroup->setNewRecordId($this->getSystemid());
            $this->objDB->flushQueryCache();

            return $bitReturn;
        }
        else {
            class_logger::getInstance(class_logger::USERSOURCES)->addLogRow("updated group ".$this->getStrName(), class_logger::$levelInfo);
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
    protected function updateStateToDb() {
        return true;
    }


    /**
     * Returns all groups from database
     *
     * @param string $strPrevid
     * @param bool|int $intStart
     * @param bool|int $intEnd
     *
     * @return class_module_user_group[]
     * @static
     */
    public static function getObjectList($strPrevid = "", $intStart = null, $intEnd = null) {
        $strQuery = "SELECT group_id FROM "._dbprefix_."user_group ORDER BY group_name";

        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array(), $intStart, $intEnd);
        $arrReturn = array();
        foreach($arrIds as $arrOneId)
            $arrReturn[] = new class_module_user_group($arrOneId["group_id"]);

        return $arrReturn;
    }

    /**
     * Fetches the number of groups available
     *
     * @param string $strPrevid
     *
     * @return int
     */
    public static function getObjectCount($strPrevid = "") {
        $strQuery = "SELECT COUNT(*) FROM "._dbprefix_."user_group";
        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array());
        return $arrRow["COUNT(*)"];
    }

    /**
     * Returns the number of members of the current group.
     *
     * @return int
     */
    public function getNumberOfMembers() {
        $this->loadSourceObject();
        return $this->objSourceGroup->getNumberOfMembers();
    }

    /**
     * Deletes the given group
     *
     * @return bool
     */
    public function deleteObject() {
        class_logger::getInstance(class_logger::USERSOURCES)->addLogRow("deleted group with id ".$this->getSystemid(). " (".$this->getStrName().")", class_logger::$levelWarning);

        //Delete related group
        $this->getObjSourceGroup()->deleteGroup();

        $strQuery = "DELETE FROM "._dbprefix_."user_group WHERE group_id=?";
        $bitReturn = $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
        //TODO: remove legacy call
        class_core_eventdispatcher::notifyRecordDeletedListeners($this->getSystemid(), get_class($this));
        class_core_eventdispatcher::getInstance()->notifyGenericListeners(class_system_eventidentifier::EVENT_SYSTEM_RECORDDELETED, array($this->getSystemid(), get_class($this)));
        return $bitReturn;
    }

    /**
     * Loads the mapped source-object
     */
    private function loadSourceObject() {
        if($this->objSourceGroup == null) {
            $objUsersources = new class_module_user_sourcefactory();
            $this->setObjSourceGroup($objUsersources->getSourceGroup($this));
        }
    }

    /**
     * Loads a group by its name, returns null of not found
     *
     * @param string $strName
     *
     * @return class_module_user_group
     */
    public static function getGroupByName($strName) {
        $objFactory = new class_module_user_sourcefactory();
        return $objFactory->getGroupByName($strName);
    }


    // --- GETTERS / SETTERS --------------------------------------------------------------------------------
    public function getStrSubsystem() {
        return $this->strSubsystem;
    }

    public function setStrSubsystem($strSubsystem) {
        $this->strSubsystem = $strSubsystem;
    }

    /**
     * @return interface_usersources_group
     */
    public function getObjSourceGroup() {
        $this->loadSourceObject();
        return $this->objSourceGroup;
    }

    public function setObjSourceGroup($objSourceGroup) {
        $this->objSourceGroup = $objSourceGroup;
    }

    public function getStrName() {
        return $this->strName;
    }

    public function setStrName($strName) {
        $this->strName = $strName;
    }

    public function getIntRecordStatus() {
        return 1;
    }

}
