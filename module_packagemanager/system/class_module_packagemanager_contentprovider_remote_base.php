<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                  *
********************************************************************************************************/

/**
 * A content-provider used to upload archives from remote repositories.
 * Provides both, a search and a download-part.
 *
 * This implementation allows usage for various remote repositories. The access details are specified
 * using the constructor.
 *
 * Remote repositories are expected to provide packages using the packageserver module.
 *
 * @package module_packagemanager
 * @author sidler@mulchprod.de
 * @author flo@mediaskills.org
 * @since 4.0
 */
abstract class class_module_packagemanager_contentprovider_remote_base implements interface_packagemanager_contentprovider {

    private static $STR_MODULE_NAME = "packagemanager";

    private $STR_BROWSE_HOST = "";
    private $STR_BROWSE_URL = "";
    private $STR_DOWNLOAD_URL = "";
    private $STR_PROTOCOL_HEADER = "";
    private $INT_BROWSE_HOST_PORT = "";
    private $STR_PROVIDER_NAME;
    private $CLASS_NAME;


    function __construct($strProviderName, $strBrowseHost, $strBrowseUrl, $strDownloadUrl, $strClassName, $strBrowseProtocol = "http://", $intBrowsePort = 80) {
        $this->STR_PROVIDER_NAME = $strProviderName;
        $this->STR_BROWSE_HOST = $strBrowseHost;
        $this->STR_BROWSE_URL = $strBrowseUrl;
        $this->STR_DOWNLOAD_URL = $strDownloadUrl;
        $this->CLASS_NAME = $strClassName;
        $this->STR_PROTOCOL_HEADER = $strBrowseProtocol;
        $this->INT_BROWSE_HOST_PORT = $intBrowsePort;
    }


    /**
     * Returns the name of the current provider, in most cases used to select the provider.
     *
     * @return mixed
     */
    public function getDisplayTitle() {
        return class_carrier::getInstance()->getObjLang()->getLang($this->STR_PROVIDER_NAME, self::$STR_MODULE_NAME);
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

        $objUser = new class_module_user_user(class_session::getInstance()->getUserID());
        $intStart = ($this->getPageNumber() - 1) * $objUser->getIntItemsPerPage();
        $intEnd = $intStart + $objUser->getIntItemsPerPage() - 1;

        $objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
        $objLang = class_carrier::getInstance()->getObjLang();
        $objManager = new class_module_packagemanager_manager();

        $objRemoteloader = $this->getRemoteloader();
        $objRemoteloader->setStrQueryParams($this->buildQueryParams($intStart, $intEnd));

        $strResponse = "";
        try {
            $strResponse = $objRemoteloader->getRemoteContent();
        }
        catch (class_exception $objEx) {
            return $objLang->getLang("package_remote_errorloading", self::$STR_MODULE_NAME);
        }

        $arrResponse = json_decode($strResponse, true);
        if($arrResponse === null)
            throw new class_exception("Error loading the remote package list. Got: <br />".htmlToString($strResponse, true), class_exception::$level_ERROR);

        $objRemoteParser = class_module_packagemanager_remoteparser_factory::getRemoteParser(
            $arrResponse, $this->getPageNumber(), $intStart, $intEnd, get_class($this), "&name=".urlencode($this->getParam("name"))."&type=".$this->getParam("type")
        );

        $arrPackages = $objRemoteParser->getArrPackages();

        $strReturn = $this->createFilterCriteria();

        $strReturn .= $objToolkit->listHeader();

        if(!$this->containsItems($arrPackages)) {
            $strReturn .= $objToolkit->getTextRow($objLang->getLang("commons_list_empty", null));
        } else {
            $intI = 0;
            foreach($arrPackages as $arrOnePackage) {

                //check if already installed locally
                if($objManager->getPackage($arrOnePackage["title"]) !== null) {
                    $strAction = $objToolkit->listButton(getImageAdmin("icon_installDisabled", $objLang->getLang("package_noinstall_installed", self::$STR_MODULE_NAME)));
                }
                else {

                    $strAction = $objToolkit->listButton(
                        getLinkAdmin(
                            self::$STR_MODULE_NAME,
                            "uploadPackage",
                            "provider=".get_class($this)."&systemid=".$arrOnePackage["systemid"],
                            $objLang->getLang("package_install", self::$STR_MODULE_NAME),
                            $objLang->getLang("package_install", self::$STR_MODULE_NAME),
                            "icon_install"
                        )
                    );
                }


                $strIcon = "icon_module";
                if($arrOnePackage["type"] == "TEMPLATE")
                    $strIcon = "icon_dot";


                $arrOnePackage["version"] = $objLang->getLang("type_".$arrOnePackage["type"], self::$STR_MODULE_NAME).", V ".$arrOnePackage["version"];


                $strReturn .= $objToolkit->genericAdminList($arrOnePackage["systemid"], $arrOnePackage["title"], getImageAdmin($strIcon), $strAction, $intI++, $arrOnePackage["version"], $arrOnePackage["description"]);
            }
        }

        $strReturn .= $objToolkit->listFooter();

        $strReturn .= $objRemoteParser->paginationFooter();

        return $strReturn;
    }

    private function buildQueryParams($intStart, $intEnd) {
        $strQuery = $this->STR_BROWSE_URL;
        if ($this->getParam("name") != "") {
            // build search query with filters for name + paging + type
            $strQuery .= "&title=".$this->getParam("name");
        }

        $arrTypes = array(
            class_module_packagemanager_manager::STR_TYPE_MODULE,
            class_module_packagemanager_manager::STR_TYPE_TEMPLATE
        );

        if ($this->getParam("type") != "") {
            if (in_array($this->getParam("type"), $arrTypes)) {
                $strQuery .= "&type=". $this->getParam("type");
            }
        }

        return $strQuery."&start=".$intStart."&end=".$intEnd."&domain=".urlencode(_webpath_);
    }



    private function getParam($strParamName) {
        return class_carrier::getInstance()->getParam($strParamName);
    }

    private function containsItems($arrResult) {
        return $arrResult != null && count($arrResult) > 0;
    }

    private function createFilterCriteria() {

        $objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
        $objLang = class_carrier::getInstance()->getObjLang();

        $strReturn = "";

        //And create the selector
        $strReturn .= $objToolkit->formHeader(
            getLinkAdminHref(self::$STR_MODULE_NAME, $this->getParam("action"), "&provider=".$this->CLASS_NAME)
        );
        $strReturn .= $objToolkit->formInputHidden("action", $this->getParam("action"));
        $strReturn .= $objToolkit->formInputHidden("filter", "true");
        $strReturn .= $objToolkit->formInputText("name", $objLang->getLang("name", self::$STR_MODULE_NAME), $this->getParam("name"));

        $arrTypeOption = array();
        $arrTypeOption[""] = $objLang->getLang("all", self::$STR_MODULE_NAME);
        $arrTypeOption[class_module_packagemanager_manager::STR_TYPE_TEMPLATE] = $objLang->getLang("template", self::$STR_MODULE_NAME);
        $arrTypeOption[class_module_packagemanager_manager::STR_TYPE_MODULE] = $objLang->getLang("module", self::$STR_MODULE_NAME);
        $strReturn .= $objToolkit->formInputDropdown("type", $arrTypeOption, $objLang->getLang("type", self::$STR_MODULE_NAME), $this->getParam("type"));

        $strReturn .= $objToolkit->formInputSubmit($objLang->getLang("filter", self::$STR_MODULE_NAME));
        $strReturn .= $objToolkit->formClose();

        return $strReturn;
    }

    /**
     * Returns the number of the current page.
     *
     * @return int
     */
    private function getPageNumber() {
        return (int)($this->getParam("pv") != "" ? $this->getParam("pv") : 1);
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
        $objRemoteloader = $this->getRemoteloader();
        $objRemoteloader->setBitCacheEnabled(false);
        $objRemoteloader->setStrQueryParams($this->STR_DOWNLOAD_URL."?systemid=".$this->getParam("systemid"));

        $strResponse = $objRemoteloader->getRemoteContent();
        file_put_contents(_realpath_._projectpath_."/temp/".$strFilename, $strResponse);

        class_resourceloader::getInstance()->flushCache();
        class_classloader::getInstance()->flushCache();
        class_reflection::flushCache();


        return _projectpath_."/temp/".$strFilename;
    }

    /**
     * Searches for a list of packages, the title may be a comma-separated list of package-names
     * If found, the packages' metadata is returned.
     * The basic array-syntax should be used, so
     * array("title", "version", "description", "systemid")
     *
     * @param $strTitle
     *
     * @return array
     */
    public function searchPackage($strTitle) {

        $objRemoteloader = $this->getRemoteloader();
        $objRemoteloader->setStrQueryParams($this->STR_BROWSE_URL."&title=".urlencode($strTitle)."&domain=".urlencode(_webpath_));

        try {
            $strPackages = $objRemoteloader->getRemoteContent();
        }
        catch(class_exception $objEx) {
            return array();
        }

        $arrPackages = json_decode($strPackages, true);
        return $arrPackages["items"];
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

        if(count($arrMetadata) == 1)
            $arrMetadata = $arrMetadata[0];

        if(isset($arrMetadata["systemid"])) {
            $strUrl = getLinkAdminHref(self::$STR_MODULE_NAME, "uploadPackage", "&provider=".get_class($this)."&systemid=".$arrMetadata["systemid"]);

            $strUrl = str_replace("_webpath_", _webpath_, $strUrl);
            $strUrl = str_replace("_indexpath_", _indexpath_, $strUrl);
            class_response_object::getInstance()->setStrRedirectUrl($strUrl);
        }

    }

    /**
     * Returns a fully set up remoteloader to start querying the package repo
     * @return class_remoteloader
     */
    private function getRemoteloader() {
        $objRemoteloader = new class_remoteloader();
        $objRemoteloader->setStrHost($this->STR_BROWSE_HOST);
        $objRemoteloader->setStrProtocolHeader($this->STR_PROTOCOL_HEADER);
        $objRemoteloader->setIntPort($this->INT_BROWSE_HOST_PORT);
        return $objRemoteloader;
    }
}