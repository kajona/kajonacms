<?php
/*"******************************************************************************************************
*   (c) 2015-2016 by Kajona, www.kajona.de                                                         *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pages\Event;

use Kajona\Packagemanager\System\PackagemanagerEventidentifier;
use Kajona\Packagemanager\System\PackagemanagerManager;
use Kajona\Packagemanager\System\PackagemanagerPackagemanagerModule;
use Kajona\Packagemanager\System\PackagemanagerPackagemanagerPharmodule;
use Kajona\Packagemanager\System\PackagemanagerPackagemanagerTemplate;
use Kajona\System\System\CoreEventdispatcher;
use Kajona\System\System\Filesystem;
use Kajona\System\System\GenericeventListenerInterface;
use Kajona\System\System\Logger;
use Kajona\System\System\PharModule;
use Kajona\System\System\StringUtil;

/**
 * Updates the default template in case of package updated
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 * @since 5.1
 *
 */
class PagesPackagemanagerUpdatedListener implements GenericeventListenerInterface
{


    /**
     * Searches for tags assigned to the systemid to be deleted.
     *
     * @param string $strEventName
     * @param array $arrArguments
     *
     * @return bool
     */
    public function handleEvent($strEventName, array $arrArguments)
    {

        //loop all installed modules, otherwise some modules may get lost
        $objPackagesManager = new PackagemanagerManager();

        foreach($objPackagesManager->getAvailablePackages() as $objOneMetadata) {
            $objHandler = $objPackagesManager->getPackageManagerForPath($objOneMetadata->getStrPath());

            if ($objHandler instanceof PackagemanagerPackagemanagerModule) {

                $objFilesystem = new Filesystem();
                Logger::getInstance(Logger::PAGES)->addLogRow("updating default template from /".$objHandler->getObjMetadata()->getStrPath(), Logger::$levelInfo);
                if (is_dir(_realpath_.$objHandler->getObjMetadata()->getStrPath()."/templates/default/js")) {
                    $objFilesystem->folderCopyRecursive($objHandler->getObjMetadata()->getStrPath()."/templates/default/js", "/templates/default/js", true);
                }

                if (is_dir(_realpath_.$objHandler->getObjMetadata()->getStrPath()."/templates/default/css")) {
                    $objFilesystem->folderCopyRecursive($objHandler->getObjMetadata()->getStrPath()."/templates/default/css", "/templates/default/css", true);
                }

                if (is_dir(_realpath_.$objHandler->getObjMetadata()->getStrPath()."/templates/default/pics")) {
                    $objFilesystem->folderCopyRecursive($objHandler->getObjMetadata()->getStrPath()."/templates/default/pics", "/templates/default/pics", true);
                }

            } elseif ($objHandler instanceof PackagemanagerPackagemanagerPharmodule) {

                //read the module and extract
                $objPharModule = new PharModule($objHandler->getObjMetadata()->getStrPath());
                $objFilesystem = new Filesystem();
                foreach ($objPharModule->getContentMap() as $strKey => $strFullPath) {

                    foreach (array("js", "css", "pics") as $strOneSubfolder) {

                        $intStrPos = StringUtil::indexOf($strFullPath, "templates/default/{$strOneSubfolder}", false);
                        if ($intStrPos !== false) {
                            $strTargetPath = _realpath_."templates/default/{$strOneSubfolder}/".StringUtil::substring($strFullPath, $intStrPos + StringUtil::length("templates/default/{$strOneSubfolder}"));
                            $objFilesystem->folderCreate(dirname($strTargetPath), true, true);
                            //copy
                            copy($strFullPath, $strTargetPath);
                        }

                    }
                }

            }
        }

        return true;
    }


    /**
     * Internal init to register the event listener, called on file-inclusion, e.g. by the class-loader
     *
     * @return void
     */
    public static function staticConstruct()
    {
        CoreEventdispatcher::getInstance()->removeAndAddListener(PackagemanagerEventidentifier::EVENT_PACKAGEMANAGER_PACKAGEUPDATED, new PagesPackagemanagerUpdatedListener());
    }


}

PagesPackagemanagerUpdatedListener::staticConstruct();