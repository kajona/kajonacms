<?php
/*"******************************************************************************************************
*   (c) 2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


namespace Kajona\Installer\System;


use Kajona\Packagemanager\System\PackagemanagerMetadata;
use Kajona\System\System\Carrier;
use Kajona\System\System\Classloader;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\SamplecontentInstallerInterface;
use Kajona\System\System\StringUtil;
use Kajona\System\System\SystemModule;

class SamplecontentInstallerHelper
{

    /**
     * @return SamplecontentInstallerInterface[]
     */
    public static function getSamplecontentInstallers()
    {
        //search for installers available
        $arrTempInstaller = Resourceloader::getInstance()->getFolderContent("/installer", array(".php"), false, null, function (&$strFilename, $strPath) {
            /** @var SamplecontentInstallerInterface $objInstance */
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
                self::initInstaller($objInstance);
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


    private static function initInstaller(SamplecontentInstallerInterface $objInstaller)
    {
        $objInstaller->setObjDb(Carrier::getInstance()->getObjDB());
        $objInstaller->setStrContentlanguage(Carrier::getInstance()->getObjSession()->getAdminLanguage(true, true));
    }

    /**
     * @param PackagemanagerMetadata $objPackage
     *
     * @return SamplecontentInstallerInterface
     */
    public static function getSamplecontentInstallerForPackage(PackagemanagerMetadata $objPackage)
    {
        $arrTempInstaller = Resourceloader::getInstance()->getFolderContent("/installer", array(".php"));
        foreach($arrTempInstaller as $strPath => $strFilename) {
            if(StringUtil::indexOf($strPath, $objPackage->getStrPath()) !== false) {

                /** @var SamplecontentInstallerInterface $objInstance */
                $objInstance = Classloader::getInstance()->getInstanceFromFilename($strPath, null, "Kajona\\System\\System\\SamplecontentInstallerInterface");
                if($objInstance != null) {
                    self::initInstaller($objInstance);
                    return $objInstance;
                }
            }
        }

        return null;
    }


    public static function install(SamplecontentInstallerInterface $objInstaller)
    {

        $strReturn = "Installer found: ".get_class($objInstaller)."\n";
        if($objInstaller->isInstalled()) {
            return "\t... is already installed\n";
        }
        $strModule = $objInstaller->getCorrespondingModule();
        $strReturn .= "Module ".$strModule."...\n";
        $objModule = SystemModule::getModuleByName($strModule);


        if ($objModule == null) {
            $strReturn .= "\t... not installed!\n";
        }
        else {
            $strReturn .= "\t... installed.\n";
            $strReturn .= $objInstaller->install();
        }

        Carrier::getInstance()->getObjDB()->flushQueryCache();

        //TODO: move to another place
        if (!file_exists(_realpath_."favicon.ico")) {
            if (!copy(Resourceloader::getInstance()->getAbsolutePathForModule("module_samplecontent")."/favicon.ico.root", _realpath_."favicon.ico")) {
                $strReturn .= "<b>Copying the favicon.ico.root to top level failed!!!</b>";
            }
        }



        return $strReturn;
    }

}