<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * A simple content-provider used to upload archives to the local filesytem.
 *
 * @package module_packagemanager
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_module_packagemanager_contentprovider_local implements interface_packagemanager_contentprovider {



    /**
     * Returns the name of the current provider, in most cases used to select the provider.
     *
     * @return mixed
     */
    public function getDisplayTitle() {
        return class_carrier::getInstance()->getObjLang()->getLang("provider_local", "packagemanager");
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
    public function renderPackageList() {
        $objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
        $objLang = class_carrier::getInstance()->getObjLang();
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
    public function processPackageUpload() {

        //fetch the upload, validate a few settings and copy the package to /project/temp
        $arrSource = class_carrier::getInstance()->getParam("provider_local_file");

        $strTarget = "/project/temp/".$arrSource["name"];
        $objFilesystem = new class_filesystem();

        //Check file for correct filters
        $strSuffix = uniStrtolower(uniSubstr($arrSource["name"], uniStrrpos($arrSource["name"], ".")));
        if(in_array($strSuffix, array(".phar"))) {
            if($objFilesystem->copyUpload($strTarget, $arrSource["tmp_name"])) {
                class_logger::getInstance(class_logger::PACKAGEMANAGEMENT)->addLogRow("uploaded package ".$arrSource["name"]." to ".$strTarget, class_logger::$levelInfo);
                class_resourceloader::getInstance()->flushCache();
                class_classloader::getInstance()->flushCache();
                class_reflection::flushCache();

                return $strTarget;
            }
        }
        class_logger::getInstance(class_logger::PACKAGEMANAGEMENT)->addLogRow("error in uploaded package ".$arrSource["name"]." either wrong format or not writeable target folder", class_logger::$levelInfo);
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
    public function searchPackage($strTitle) {
        return array();
    }

    /**
     * Inits the update of the passed package, of given.
     * Therefore, the built-in method processPackgeUpload
     * should be used.
     *
     * @param $strTitle
     *
     * @throws class_exception
     * @return mixed
     */
    public function initPackageUpdate($strTitle) {
        throw new class_exception("method not supported", class_exception::$level_ERROR);
    }
}