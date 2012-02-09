<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_tags_tag.php 4471 2012-01-25 16:49:25Z sidler $                                    *
********************************************************************************************************/

/**
 * A model-class for template-packs.
 * Since not part of the regular system-table, it only acts as some kind of
 * wrapper.
 *
 * @package module_templatemanager
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_module_templatemanager_template extends class_model implements interface_model, interface_admin_listable  {

    private $strName = "";

    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {

        $this->setArrModuleEntry("modul", "templatemanager");
        $this->setArrModuleEntry("moduleId", _templatemanager_module_id_);

		parent::__construct($strSystemid);

    }

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
        return "icon_dot.gif";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     * @return string
     */
    public function getStrAdditionalInfo() {
        return "";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     * @return string
     */
    public function getStrLongDescription() {
        return "";
    }


    /**
     * @see class_model::getObjectTables();
     * @return array
     */
    protected function getObjectTables() {
        return array(_dbprefix_."templatepacks" => "templatepack_id");
    }

    /**
     * Initialises the current object, if a systemid was given
     *
     */
    protected function initObjectInternal() {
        $strQuery = "SELECT *
                      FROM "._dbprefix_."templatepacks,
                           "._dbprefix_."system,
                           "._dbprefix_."system_right
                     WHERE templatepack_id = system_id
                       AND system_id = right_id
                       AND templatepack_id = ?";

        $arrRow = $this->objDB->getPRow($strQuery, array($this->getSystemid()));
        $this->setArrInitRow($arrRow);

        $this->setStrName($arrRow["templatepack_name"]);
    }

    /**
     * saves the current object with all its params back to the database
     *
     * @return bool
     */
    protected function updateStateToDb() {
        $strQuery = "UPDATE "._dbprefix_."templatepacks
                        SET templatepack_name = ?
                      WHERE templatepack_id = ?";
        return $this->objDB->_pQuery($strQuery, array($this->getStrName(), $this->getSystemid()));
    }


    /**
     * Deletes the tag with the given systemid from the system
     *
     * @return bool
     */
    protected function deleteObjectInternal() {
        $strQuery = "DELETE FROM "._dbprefix_."templatepacks
                           WHERE templatepack_id = ?";
        return $this->objDB->_pQuery($strQuery, array($this->getSystemid()));
    }

    /**
     * Fetches the list of packs available
     *
     * @static
     * @param null|int $intStart
     * @param null|int $intEnd
     * @return class_module_templatemanager_template[]
     */
    public static function getAllTemplatepacks($intStart = null, $intEnd = null) {
        $strQuery = "SELECT templatepack_id
                       FROM "._dbprefix_."templatepacks
                   ORDER BY templatepack_name ASC ";

        if($intStart !== null && $intEnd !== null)
            $arrRows = class_carrier::getInstance()->getObjDB()->getPArraySection($strQuery, array(), $intStart, $intEnd);
        else
            $arrRows = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array());

        $arrReturn = array();
        foreach($arrRows as $arrOneRow)
            $arrReturn[] = new class_module_templatemanager_template($arrOneRow["templatepack_id"]);

        return $arrReturn;
    }

    /**
     * Fetches the list of packs available
     *
     * @static
     * @return int
     */
    public static function getAllTemplatepacksCount() {
        $strQuery = "SELECT COUNT(*)
                       FROM "._dbprefix_."templatepacks
                   ORDER BY templatepack_name ASC ";

        $arrRow = class_carrier::getInstance()->getObjDB()->getPRow($strQuery, array());
        return $arrRow["COUNT(*)"];
    }

    /**
     * Synchronized the list of template-packs available in the filesystem
     * with the list of packs stored at the database.
     * @static
     */
    public static function syncTemplatepacks() {
        //scan the list of packs available in the filesystem
        $objFilesystem = new class_filesystem();
        $arrFolders = $objFilesystem->getCompleteList("/templates");

        //scan packs installed
        $arrPacksInstalled = self::getAllTemplatepacks();

        foreach($arrFolders["folders"] as $strOneFolder) {
            $bitFolderFound = false;
            //search the pack in the list of available ones
            foreach($arrPacksInstalled as $objOnePack) {
                if($objOnePack->getStrName() == $strOneFolder) {
                    $bitFolderFound = true;
                    break;
                }
            }
            if(!$bitFolderFound) {
                $objPack = new class_module_templatemanager_template();
                $objPack->setStrName($strOneFolder);
                $objPack->updateObjectToDb();
            }
        }

        //scan folders not existing any more
        foreach($arrPacksInstalled as $objOnePack) {
            if(!in_array($objOnePack->getStrName(), $arrFolders["folders"]))
                $objOnePack->deleteObject();
        }
    }


    public function rightEdit() {
        return false;
    }



    public function setStrName($strName) {
        $this->strName = $strName;
    }

    public function getStrName() {
        return $this->strName;
    }
}
