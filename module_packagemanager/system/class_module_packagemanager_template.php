<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                    *
********************************************************************************************************/

/**
 * A model-class for template-packs.
 *
 * @package module_packagemanager
 * @author sidler@mulchprod.de
 * @since 4.0
 * @targetTable templatepacks.templatepack_id
 *
 * @module packagemanager
 * @moduleId _packagemanager_module_id_
 */
class class_module_packagemanager_template extends class_model implements interface_model, interface_admin_listable {

    /**
     * @var string
     * @tableColumn templatepacks.templatepack_name
     * @tableColumnDatatype char254
     * @listOrder
     *
     * @addSearchIndex
     */
    private $strName = "";

    /**
     * @var class_module_packagemanager_metadata
     */
    private $objMetadata = null;

    /**
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
        return "icon_dot";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        $strReturn = "";
        if($this->objMetadata == null)
            return "";

        if($this->objMetadata->getStrVersion() != "")
            $strReturn .= $this->getLang("pack_version")." ".$this->objMetadata->getStrVersion();

        if($this->objMetadata->getStrAuthor() != "")
            $strReturn .= " ".$this->getLang("pack_author")." ".$this->objMetadata->getStrAuthor();

        return $strReturn;
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        if($this->objMetadata == null)
            return "";
        return $this->objMetadata->getStrDescription();
    }

    /**
     * Initialises the current object, if a systemid was given
     * @return void
     */
    protected function initObjectInternal() {
        parent::initObjectInternal();
        $this->objMetadata = $this->getMetadata();
    }

    /**
     * Deletes the tag with the given systemid from the system
     *
     * @return bool
     */
    protected function deleteObjectInternal() {

        //delete all files from the filesystem
        $objFilesystem = new class_filesystem();
        $objFilesystem->folderDeleteRecursive(_templatepath_."/".$this->getStrName());

        return parent::deleteObjectInternal();
    }

    /**
     * Synchronized the list of template-packs available in the filesystem
     * with the list of packs stored at the database.
     *
     * @return void
     * @static
     */
    public static function syncTemplatepacks() {
        //scan the list of packs available in the filesystem
        $objFilesystem = new class_filesystem();
        $arrFolders = $objFilesystem->getCompleteList("/templates");

        //scan packs installed
        /** @var class_module_packagemanager_template[] $arrPacksInstalled */
        $arrPacksInstalled = self::getObjectList();

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
                $objPack = new class_module_packagemanager_template();
                $objPack->setStrName($strOneFolder);
                $objPack->setIntRecordStatus(0);
                $objPack->updateObjectToDb();
            }
        }

        //scan folders not existing any more
        foreach($arrPacksInstalled as $objOnePack) {
            if(!in_array($objOnePack->getStrName(), $arrFolders["folders"]))
                $objOnePack->deleteObject();
        }
    }

    /**
     * @param int $intRecordStatus
     * @todo move this to an eventhandler
     */
    public function setIntRecordStatus($intRecordStatus) {
        if($intRecordStatus == 1) {
            //if set to active, mark all other packs as invalid
            $strQuery = "SELECT templatepack_id
                          FROM "._dbprefix_."templatepacks,
                               "._dbprefix_."system
                         WHERE system_id = templatepack_id
                           AND system_status = 1";
            $arrRows = $this->objDB->getPArray($strQuery, array());
            foreach($arrRows as $arrSingleRow) {
                $objPack = new class_module_packagemanager_template($arrSingleRow["templatepack_id"]);
                $objPack->setIntRecordStatus(0);
                $objPack->updateObjectToDb();
            }

            //update the active-pack constant
            $objSetting = class_module_system_setting::getConfigByName("_packagemanager_defaulttemplate_");
            $objSetting->setStrValue($this->getStrName());
            $objSetting->updateObjectToDb();
            $this->flushCompletePagesCache();
        }

        parent::setIntRecordStatus($intRecordStatus);
    }


    /**
     * @return class_module_packagemanager_metadata|null
     */
    private function getMetadata() {

        $objMetadata = new class_module_packagemanager_metadata();
        try {
            $objMetadata->autoInit(_templatepath_."/".$this->strName);
            return $objMetadata;
        }
        catch(class_exception $objEx) {

        }

        return null;
    }

    /**
     * @param string $strName
     * @return void
     */
    public function setStrName($strName) {
        $this->strName = $strName;
    }

    /**
     * @return string
     */
    public function getStrName() {
        return $this->strName;
    }
}
