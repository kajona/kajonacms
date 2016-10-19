<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

namespace Kajona\Packagemanager\System;

use Kajona\System\System\Carrier;
use Kajona\System\System\Classloader;
use Kajona\System\System\Exception;
use Kajona\System\System\Filesystem;
use Kajona\System\System\Logger;
use Kajona\System\System\Reflection;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\StringUtil;


/**
 * A simple content-provider used to upload archives to the local filesytem.
 *
 * @package module_packagemanager
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class PackagemanagerContentproviderLocal implements PackagemanagerContentproviderInterface
{


    /**
     * Returns the name of the current provider, in most cases used to select the provider.
     *
     * @return mixed
     */
    public function getDisplayTitle()
    {
        return Carrier::getInstance()->getObjLang()->getLang("provider_local", "packagemanager");
    }

    /**
     * Renders the list of available packages or any other kind of gui-representation
     * of the packageprovider.
     *
     * Whenever the provider is capable of uploading new packages, the copy & n upload process
     * should be triggered by the admin-class again.
     * So make sure links or forms point to
     * module = packagemanager
     * action = uploadPackage
     * provider = class_na,e
     * The provider will be called using the processPackageUpload method.
     *
     * @return string
     */
    public function renderPackageList()
    {
        $objToolkit = Carrier::getInstance()->getObjToolkit("admin");
        $objLang = Carrier::getInstance()->getObjLang();
        $strReturn = "";

        $strReturn .= $objToolkit->getTextRow($objLang->getLang("provider_local_uploadhint", "packagemanager"));
        $strReturn .= $objToolkit->divider();

        $strReturn .= $objToolkit->formHeader(getLinkAdminHref("packagemanager", "uploadPackage"), generateSystemid(), "multipart/form-data");
        $strReturn .= $objToolkit->formInputUpload("provider_local_file", $objLang->getLang("provider_local_file", "packagemanager"));
        $strReturn .= $objToolkit->formInputHidden("provider", __CLASS__);
        $strReturn .= $objToolkit->formInputSubmit();
        $strReturn .= $objToolkit->formClose();

        return $strReturn;
    }

    /**
     * The real "download" or "upload" should be handled right here.
     * All packages have to be downloaded to /project/temp in order to be processed afterwards.
     *
     * @return string the filename of the package downloaded
     */
    public function processPackageUpload()
    {

        //fetch the upload, validate a few settings and copy the package to /project/temp
        $arrSource = Carrier::getInstance()->getParam("provider_local_file");

        $strTarget = "/project/temp/".$arrSource["name"];
        $objFilesystem = new Filesystem();

        //Check file for correct filters
        $strSuffix = uniStrtolower(uniSubstr($arrSource["name"], StringUtil::lastIndexOf($arrSource["name"], ".")));
        if (in_array($strSuffix, array(".phar"))) {
            if ($objFilesystem->copyUpload($strTarget, $arrSource["tmp_name"])) {
                Logger::getInstance(Logger::PACKAGEMANAGEMENT)->addLogRow("uploaded package ".$arrSource["name"]." to ".$strTarget, Logger::$levelInfo);
                Resourceloader::getInstance()->flushCache();
                Classloader::getInstance()->flushCache();
                Reflection::flushCache();

                return $strTarget;
            }
        }
        Logger::getInstance(Logger::PACKAGEMANAGEMENT)->addLogRow("error in uploaded package ".$arrSource["name"]." either wrong format or not writeable target folder", Logger::$levelInfo);
        @unlink($arrSource["tmp_name"]);

        return null;
    }

    /**
     * Searches for a single, given package.
     * If found, the packages' metadata is returned.
     * The basic array-syntax should be used, so
     * array("title", "version", "description", "systemid")
     *
     * @param $strTitle
     *
     * @return array
     */
    public function searchPackage($strTitle)
    {
        return array();
    }

    /**
     * Inits the update of the passed package, of given.
     * Therefore, the built-in method processPackgeUpload
     * should be used.
     *
     * @param $strTitle
     *
     * @throws Exception
     * @return mixed
     */
    public function initPackageUpdate($strTitle)
    {
        throw new Exception("method not supported", Exception::$level_ERROR);
    }
}