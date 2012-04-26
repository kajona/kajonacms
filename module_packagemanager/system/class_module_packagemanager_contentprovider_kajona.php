<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_tags_admin.php 4485 2012-02-07 12:48:04Z sidler $                                  *
********************************************************************************************************/

/**
 * A simple content-provider used to upload archives from the official kajona-repo.
 * Provides both, a search and a download-part.
 *
 * @module module_packagemanager
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_module_packagemanager_contentprovider_kajona implements interface_packagemanager_contentprovider {



    /**
     * Returns the name of the current provider, in most cases used to select the provider.
     *
     * @return mixed
     */
    public function getDisplayTitle() {
        return class_carrier::getInstance()->getObjLang()->getLang("provider_kajona", "packagemanager");
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
     * provider = class_name
     * The provider will be called using the processPackageUpload method.
     *
     * @return string
     */
    public function renderPackageList() {
        $objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
        $objLang = class_carrier::getInstance()->getObjLang();
        $strReturn = "";

        $strReturn .= $objToolkit->listHeader();

        $strAction = $objToolkit->listButton(
            getLinkAdmin("packagemanager", "uploadPackage", "provider=".__CLASS__."&file=test", $objLang->getLang("package_install", "packagemanager"), $objLang->getLang("package_install", "packagemanager"), "icon_downloads.gif")
        );

        $strReturn .= $objToolkit->genericAdminList(generateSystemid(), "test entry", getImageAdmin("icon_module.gif"), $strAction, 0);

        $strReturn .= $objToolkit->listFooter();

        return $strReturn;
    }

    /**
     * The real "download" or "upload" should be handled right here.
     * All packages have to be downloaded to /project/temp in order to be processed afterwards.
     *
     * @return string the filename of the package downloaded
     */
    public function processPackageUpload() {

        $strFilename = generateSystemid().".zip";

        //stream the original package
        $objRemoteloader = new class_remoteloader();
        $objRemoteloader->setBitCacheEnabled(false);

        $objRemoteloader->setStrHost("localhost");
        $objRemoteloader->setStrQueryParams("/pchart2.zip");

        $strResponse = $objRemoteloader->getRemoteContent();
        file_put_contents(_realpath_._projectpath_."/temp/".$strFilename, $strResponse);


        return _projectpath_."/temp/".$strFilename;
    }
}