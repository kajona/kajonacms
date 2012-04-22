<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_tags_admin.php 4485 2012-02-07 12:48:04Z sidler $                                  *
********************************************************************************************************/

class class_module_packagemanager_packagemanager_module implements interface_packagemanager_packagemanager {

    /**
     * @var class_module_packagemanager_metadata
     */
    private $objMetadata;


    /**
     * Returns a list of installed packages, so a single metadata-entry
     * for each package.
     *
     * @return class_module_packagemanager_metadata[]
     */
    public function getInstalledPackages() {
        $arrReturn = array();

        //loop all modules
        $arrModules = class_resourceloader::getInstance()->getArrModules();

        foreach($arrModules as $strOneModule) {
            try {
                $objMetadata = new class_module_packagemanager_metadata();
                $objMetadata->autoInit("/core/".$strOneModule);
                $arrReturn[] = $objMetadata;
            }
            catch(class_exception $objEx) {

            }
        }

        return $arrReturn;
    }


    /**
     * Copies the extracted(!) package from the temp-folder
     * to the target-folder.
     * In most cases, this is either located at /core or at /templates.
     * The original should be deleted afterwards.
     */
    public function move2Filesystem() {
        $strSource = $this->objMetadata->getStrPath();
        $strTarget = $this->objMetadata->getStrTarget();

        $objFilesystem = new class_filesystem();
        $objFilesystem->folderCopyRecursive($strSource, "/core/".$strTarget);
        $this->objMetadata->setStrPath("/core/".$strTarget);

        $objFilesystem->folderDeleteRecursive($strSource);
    }

    /**
     * Invokes the installer, if given.
     * The installer itself is capable of detecting whether an update or a plain installation is required.
     *
     */
    public function installOrUpdate() {
        // TODO: Implement installOrUpdate() method.
    }


    public function setObjMetadata($objMetadata) {
        $this->objMetadata = $objMetadata;
    }

    public function getObjMetadata() {
        return $this->objMetadata;
    }

}