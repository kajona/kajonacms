<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                          *
********************************************************************************************************/


/**
 * Installer of the filemanager samplecontent
 *
 * @package modul_filemanager
 */
class class_installer_sc_filemanager implements interface_sc_installer  {

    private $objDB;
    private $strContentLanguage;

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     * @return string
     */
    public function install() {
		$strReturn = "";

        $strReturn .= "Creating picture upload folder\n";
        if(!is_dir(_realpath_._filespath_."/images/upload"))
            mkdir(_realpath_._filespath_."/images/upload", 0777, true);

        $strReturn .= "Creating new picture repository\n";
        $objRepo = new class_module_filemanager_repo();

        if($this->strContentLanguage == "de")
            $objRepo->setStrName("Hochgeladene Bilder");
        else
            $objRepo->setStrName("Picture uploads");

        $objRepo->setStrPath(_projectpath_."/pics/upload");
        $objRepo->setStrUploadFilter(".jpg,.png,.gif,.jpeg");
        $objRepo->setStrViewFilter(".jpg,.png,.gif,.jpeg");
        $objRepo->updateObjectToDb();
        $strReturn .= "ID of new repo: ".$objRepo->getSystemid()."\n";

        $strReturn .= "Setting the repository as the default images repository\n";
        $objSetting = class_module_system_setting::getConfigByName("_filemanager_default_imagesrepoid_");
        $objSetting->setStrValue($objRepo->getSystemid());
        $objSetting->updateObjectToDb();

        $strReturn .= "Creating file upload folder\n";
        if(!is_dir(_realpath_._filespath_."/public"))
            mkdir(_realpath_._filespath_."/public", 0777, true);

        $strReturn .= "Creating new file repository\n";
        $objRepo = new class_module_filemanager_repo();

        if($this->strContentLanguage == "de")
            $objRepo->setStrName("Hochgeladene Dateien");
        else
            $objRepo->setStrName("File uploads");

        $objRepo->setStrPath(_projectpath_."/downloads/public");
        $objRepo->setStrUploadFilter(".zip,.pdf,.txt");
        $objRepo->setStrViewFilter(".zip,.pdf,.txt");
        $objRepo->updateObjectToDb();
        $strReturn .= "ID of new repo: ".$objRepo->getSystemid()."\n";

        $strReturn .= "Setting the repository as the default files repository\n";
        $objSetting = class_module_system_setting::getConfigByName("_filemanager_default_filesrepoid_");
        $objSetting->setStrValue($objRepo->getSystemid());
        $objSetting->updateObjectToDb();


		return $strReturn;
    }

    public function setObjDb($objDb) {
        $this->objDB = $objDb;
    }

    public function setStrContentlanguage($strContentlanguage) {
        $this->strContentLanguage = $strContentlanguage;
    }

    public function getCorrespondingModule() {
        return "filemanager";
    }

}
?>