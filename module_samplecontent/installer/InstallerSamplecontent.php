<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

namespace Kajona\Samplecontent\Installer;

use Kajona\System\System\Classloader;
use Kajona\System\System\InstallerBase;
use Kajona\System\System\InstallerRemovableInterface;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\SamplecontentInstallerInterface;
use Kajona\System\System\SystemModule;

/**
 * Class providing an installer for the samplecontent.
 * Samplecontent is not installed as a module, it just creates a few default entries
 * for other modules and installes a few sample-templates
 *
 * @package module_samplecontent
 * @moduleId _samplecontent_modul_id_
 */
class InstallerSamplecontent extends InstallerBase implements InstallerRemovableInterface {


    private $strContentLanguage;


	public function __construct() {
		parent::__construct();

		//set the correct language
        $this->strContentLanguage = $this->objSession->getAdminLanguage();
	}

    /**
     * @return array
     */
    public static function getSamplecontentInstallers()
    {
        //search for installers available
        $arrTempInstaller = Resourceloader::getInstance()->getFolderContent("/installer", array(".php"), false, null, function (&$strFilename, $strPath) {
            $objInstance = Classloader::getInstance()->getInstanceFromFilename($strPath, "Kajona\\System\\System\\SamplecontentInstallerInterface");

            //See if a legacy class was stored in the file
            if ($objInstance == null) {
                $strClass = uniSubstr($strFilename, 0, -4);
                $strClass = "class_".$strClass;

                if (in_array($strClass, get_declared_classes())) {
                    $strFilename = new $strClass();
                }
                else {
                    $strFilename = null;
                }
            }
            else {
                $strFilename = $objInstance;
            }
        });

        $arrInstaller = array();
        foreach ($arrTempInstaller as $objInstaller) {
            if ($objInstaller !== null) {
                $arrInstaller[uniStrReplace("class_", "", get_class($objInstaller))] = $objInstaller;
            }
        }


        uksort($arrInstaller, function ($strA, $strB) {

            $strNameA = uniStrrpos($strA, "\\") !== false ? uniSubstr($strA, uniStrrpos($strA, "\\") + 1) : $strA;
            $strNameB = uniStrrpos($strB, "\\") !== false ? uniSubstr($strB, uniStrrpos($strB, "\\") + 1) : $strB;

            return strcmp(strtolower($strNameA), strtolower($strNameB));
        });
        return $arrInstaller;
    }

    public function install() {
		$strReturn = "Installing ".$this->objMetadata->getStrTitle()."...\n";

		//Register the module
        $strReturn .= "\nRegistering module\n";
        $this->registerModule($this->objMetadata->getStrTitle(), _samplecontent_modul_id_, "", "", $this->objMetadata->getStrVersion() , false);

        $arrInstaller = self::getSamplecontentInstallers();


        $strReturn .= "Loading installers...\n";
        /** @var $objInstaller SamplecontentInstallerInterface|InstallerBase */
        foreach ($arrInstaller as $objInstaller) {

            if($objInstaller == null)
                continue;

            $strReturn .= "\n\nInstaller found: ".get_class($objInstaller)."\n";
            $strModule = $objInstaller->getCorrespondingModule();
            $strReturn .= "Module ".$strModule."...\n";
            $objModule = SystemModule::getModuleByName($strModule);
            if($objModule == null) {
                $strReturn .= "\t... not installed!\n";
            }
            else {
                $strReturn .= "\t... installed.\n";
                $objInstaller->setObjDb($this->objDB);
                $objInstaller->setStrContentlanguage($this->strContentLanguage);
                $strReturn .= $objInstaller->install();
            }
        }
        $this->objDB->flushQueryCache();

        if(!file_exists(_realpath_."/favicon.ico")) {
            if(!copy(Resourceloader::getInstance()->getAbsolutePathForModule("module_samplecontent")."/favicon.ico.root", _realpath_."/favicon.ico"))
                $strReturn .= "<b>Copying the favicon.ico.root to top level failed!!!</b>";
        }


		return $strReturn;
	}

    /**
     * Validates whether the current module/element is removable or not.
     * This is the place to trigger special validations and consistency checks going
     * beyond the common metadata-dependencies.
     *
     * @return bool
     */
    public function isRemovable() {
        return true;
    }

    /**
     * Removes the elements / modules handled by the current installer.
     * Use the reference param to add a human readable logging.
     *
     * @param string &$strReturn
     *
     * @return bool
     */
    public function remove(&$strReturn) {

        $strReturn .= "Removing the samplecontent module will NOT removed the contents created during installation! Only the module itself will be removed!\n";

        //delete the module-node
        $strReturn .= "Deleting the module-registration...\n";
        $objModule = SystemModule::getModuleByName($this->objMetadata->getStrTitle(), true);
        if(!$objModule->deleteObjectFromDatabase()) {
            $strReturn .= "Error deleting module, aborting.\n";
            return false;
        }


        return true;
    }


    public function update() {
	    $strReturn = "";
        //check installed version and to which version we can update
        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);

        $strReturn .= "Version found:\n\t Module: ".$arrModule["module_name"].", Version: ".$arrModule["module_version"]."\n\n";

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.5") {
            $strReturn .= "Updating 4.5 to 4.6...\n";
            $this->updateModuleVersion("samplecontent", "4.6");
        }

        $arrModule = SystemModule::getPlainModuleData($this->objMetadata->getStrTitle(), false);
        if($arrModule["module_version"] == "4.6") {
            $strReturn .= "Updating to 4.7...\n";
            $this->updateModuleVersion("samplecontent", "4.7");
        }

        return $strReturn;
	}




}
