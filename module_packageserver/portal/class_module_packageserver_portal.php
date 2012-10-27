<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/
/**
 * Portal-class of the packageserver. Processes requests and passes infos / download-links
 *
 * @package module_packageserver
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_module_packageserver_portal extends class_portal implements interface_portal {

    /**
     * Constructor
     *
     * @param array $arrElementData
     */
    public function __construct($arrElementData) {
        $this->setArrModuleEntry("moduleId", _packageserver_module_id_);
        $this->setArrModuleEntry("modul", "packageserver");

        parent::__construct($arrElementData);
    }


    /**
     * Returns a list of all packages available.
     * By default a json-encoded array-like structure.
     *
     * @return string|json
     * @permissions view
     * @xml
     */
    protected function actionList() {
        $arrPackages = array();

        $intStart = $this->ensureNumericValue($this->getParam("start"), null);
        $intEnd = $this->ensureNumericValue($this->getParam("end"), null);
        $intTypeFilter = $this->ensureNumericValue($this->getParam("type"), false);

        if ($this->isValidPagingParameter($intStart)
            && $this->isValidPagingParameter($intEnd)) {

            if ($intEnd >= $intStart) {
                // TODO $arrUnpagedDBFiles is only required for total number of results - maybe use simple SQL count?
                $arrUnpagedDBFiles = $this->getAllPackages(_packageserver_repo_id_, $intTypeFilter);
                $arrDBFiles = $this->getAllPackages(_packageserver_repo_id_, $intTypeFilter, $intStart, $intEnd);
                $objManager = new class_module_packagemanager_manager();

                foreach($arrDBFiles as $objOneFile) {

                    try {

                        $objMetadata = $objManager->getPackageManagerForPath($objOneFile->getStrFilename());

                        $arrPackages[] = array(
                            "systemid"    => $objOneFile->getSystemid(),
                            "title"       => $objMetadata->getObjMetadata()->getStrTitle(),
                            "version"     => $objMetadata->getObjMetadata()->getStrVersion(),
                            "description" => $objMetadata->getObjMetadata()->getStrDescription(),
                            "type"        => $objMetadata->getObjMetadata()->getStrType()
                        );

                    }
                    catch(class_exception $objEx) {

                    }
                }


                class_module_packageserver_log::generateDlLog("", $_SERVER["REMOTE_ADDR"], urldecode($this->getParam("domain")));

                class_response_object::getInstance()->setStResponseType(class_http_responsetypes::STR_TYPE_JSON);
            }

        }

        $result = array();
        $result['numberOfTotalItems'] = count($arrUnpagedDBFiles);
        $result['items'] = $arrPackages;

        $strReturn = json_encode($result);
        return $strReturn;
    }

    private function ensureNumericValue($strParam, $objDefaultValue) {
        if ($strParam === null || trim($strParam) === "") {
            return $objDefaultValue;
        } elseif (!is_numeric($strParam)) {
            // type filter has unknown value
            return $objDefaultValue;
        }
        return $strParam;
    }

    private function isValidPagingParameter($parameter) {
        if ($parameter === null || (is_numeric($parameter) && (int) $parameter >= 0)) {
            return true;
        }
        return false;
    }

    /**
     * Internal helper, loads all files available including a traversal
     * of the nested folders.
     *
     * @param $strParentId
     * @param int|bool $intTypeFilter
     * @param int $intStart
     * @param int $intEnd
     *
     * @return class_module_mediamanager_file[]
     */
    private function getAllPackages($strParentId, $intTypeFilter = false, $intStart = null, $intEnd = null) {
        $arrReturn = array();

        $arrSubfiles = class_module_mediamanager_file::loadFilesDB($strParentId, $intTypeFilter, true, $intStart, $intEnd, true);

        foreach($arrSubfiles as $objOneFile) {
            if($objOneFile->getIntType() == class_module_mediamanager_file::$INT_TYPE_FILE)
                $arrReturn[] = $objOneFile;
            else
                $arrReturn = array_merge($arrReturn, $this->getAllPackages($objOneFile->getSystemid()));
        }

        return $arrReturn;
    }


    /**
     * Searches a list of packages and returns all of the infos found relating that packages.
     * Therefore, the package-names should be sent as a comma-separated list, e.g.:
     * xml.php?module=packageserver&action=searchPackages&title=system,pages,mediamanager
     *
     * @xml
     * @return string|json
     */
    protected function actionSearchPackages() {
        $arrReturn = array();
        $arrSearch = explode(",", $this->getParam("title"));

        $arrDBFiles = $this->getAllPackages(_packageserver_repo_id_);
        $objManager = new class_module_packagemanager_manager();

        foreach($arrDBFiles as $objOneFile) {

            try {

                $objMetadata = $objManager->getPackageManagerForPath($objOneFile->getStrFilename());

                if(in_array($objMetadata->getObjMetadata()->getStrTitle(), $arrSearch)) {

                    $arrReturn[] = array(
                        "systemid"    => $objOneFile->getSystemid(),
                        "title"       => $objMetadata->getObjMetadata()->getStrTitle(),
                        "version"     => $objMetadata->getObjMetadata()->getStrVersion(),
                        "description" => $objMetadata->getObjMetadata()->getStrDescription(),
                        "type"        => $objMetadata->getObjMetadata()->getStrType()
                    );

                }

            }
            catch(class_exception $objEx) {

            }
        }

        class_module_packageserver_log::generateDlLog($this->getParam("title"), $_SERVER["REMOTE_ADDR"], urldecode($this->getParam("domain")));
        class_response_object::getInstance()->setStResponseType(class_http_responsetypes::STR_TYPE_JSON);
        return json_encode($arrReturn);
    }

}
