<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * A simple content-provider used to upload archives from the kajonabase-repo.
 * Provides both, a search and a download-part.
 *
 * @module module_packagemanager
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_module_packagemanager_contentprovider_kajonabase implements interface_packagemanager_contentprovider {

    private $STR_BROWSE_HOST = "v4.kajonabase.net";
    private $STR_BROWSE_URL = "/xml.php?module=packageserver&action=list";
    private $STR_SEARCH_URL = "/xml.php?module=packageserver&action=searchPackages&title=";
    private $STR_DOWNLOAD_URL = "/download.php";


    /**
     * Returns the name of the current provider, in most cases used to select the provider.
     *
     * @return mixed
     */
    public function getDisplayTitle() {
        return class_carrier::getInstance()->getObjLang()->getLang("provider_kajonabase", "packagemanager");
    }

    /**
     * Renders the list of available packages or any other kind of gui-representation
     * of the packageprovider.
     *
     * Whenever the provider is capable of uploading new packages, the copy & and upload process
     * should be triggered by the admin-class again.
     * So make sure links or forms point to
     * module = packagemanager
     * action = uploadPackage
     * provider = class_name
     * The provider will be called using the processPackageUpload method.
     *
     * @throws class_exception
     * @return string
     */
    public function renderPackageList() {
        $objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
        $objLang = class_carrier::getInstance()->getObjLang();
        $strReturn = "";

        $strReturn .= $objToolkit->listHeader();

        $objRemoteloader = new class_remoteloader();
        $objRemoteloader->setStrHost($this->STR_BROWSE_HOST);
        $objRemoteloader->setStrQueryParams($this->STR_BROWSE_URL."&domain=".urlencode(_webpath_));

        $strPackages = "";
        try {
            $strPackages = $objRemoteloader->getRemoteContent();
        }
        catch (class_exception $objEx) {
            return $objLang->getLang("package_remote_errorloading", "packagemanager");
        }

        $arrPackages = json_decode($strPackages, true);

        if($arrPackages == null)
            throw new class_exception("Failed to load remote-packages:  ".strip_tags($strPackages), class_exception::$level_ERROR);

        $intI = 0;
        foreach($arrPackages as $arrOnePackage) {
            $strAction = $objToolkit->listButton(
                getLinkAdmin(
                    "packagemanager",
                    "uploadPackage",
                    "provider=".__CLASS__."&systemid=".$arrOnePackage["systemid"],
                    $objLang->getLang("package_install", "packagemanager"),
                    $objLang->getLang("package_install", "packagemanager"),
                    "icon_downloads.png"
                )
            );


            $strIcon = "icon_module.png";
            if($arrOnePackage["type"] == "TEMPLATE")
                $strIcon = "icon_dot.png";


            $arrOnePackage["version"] = $objLang->getLang("type_".$arrOnePackage["type"], "packagemanager").", V ".$arrOnePackage["version"];


            $strReturn .= $objToolkit->genericAdminList($arrOnePackage["systemid"], $arrOnePackage["title"], getImageAdmin($strIcon), $strAction, $intI++, $arrOnePackage["version"], $arrOnePackage["description"]);
        }


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

        $objRemoteloader->setStrHost($this->STR_BROWSE_HOST);
        $objRemoteloader->setStrQueryParams($this->STR_DOWNLOAD_URL."?systemid=".class_carrier::getInstance()->getParam("systemid"));

        $strResponse = $objRemoteloader->getRemoteContent();
        file_put_contents(_realpath_._projectpath_."/temp/".$strFilename, $strResponse, LOCK_EX);


        return _projectpath_."/temp/".$strFilename;
    }

    /**
     * Searches for a single, given package.
     * If found, the packages' metadata is returned.
     * The basic array-syntax should be used, so
     * array("title", "version", "description", "systemid")
     *
     * @param $strTitle
     *
     * @return string|null
     */
    public function searchPackage($strTitle) {

        $objRemoteloader = new class_remoteloader();
        $objRemoteloader->setStrHost($this->STR_BROWSE_HOST);
        $objRemoteloader->setStrQueryParams($this->STR_SEARCH_URL.$strTitle."&domain=".urlencode(_webpath_));

        $strPackages = "";
        try {
            $strPackages = $objRemoteloader->getRemoteContent();
        }
        catch(class_exception $objEx) {
            return null;
        }

        $arrPackages = json_decode($strPackages, true);

        if(count($arrPackages) > 0)
            return $arrPackages[0];

        return null;

    }


    /**
     * Inits the update of the passed package, of given.
     * Therefore, the built-in method processPackgeUpload
     * should be used.
     *
     * @param $strTitle
     *
     * @return mixed
     */
    public function initPackageUpdate($strTitle) {
        $arrMetadata = $this->searchPackage($strTitle);

        if(isset($arrMetadata["systemid"])) {
            $strUrl = getLinkAdminHref("packagemanager", "uploadPackage", "&provider=".__CLASS__."&systemid=".$arrMetadata["systemid"]);

            $strUrl = str_replace("_webpath_", _webpath_, $strUrl);
            $strUrl = str_replace("_indexpath_", _indexpath_, $strUrl);
            class_response_object::getInstance()->setStrRedirectUrl($strUrl);
        }

    }
}