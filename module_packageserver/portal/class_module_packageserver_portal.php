<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/
/**
 * Portal-class of the packageserver. Processes requests and passes infos / download-links
 *
 * @package module_packageserver
 * @author sidler@mulchprod.de
 * @since 4.0
 *
 * @module packageserver
 * @moduleId _packageserver_module_id_
 */
class class_module_packageserver_portal extends class_portal_controller implements interface_portal {

    const PROTOCOL_VERSION = 4;

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
        $intNrOfFiles = 0;

        $intStart = $this->ensureNumericValue($this->getParam("start"), null);
        $intEnd = $this->ensureNumericValue($this->getParam("end"), null);
        $strTypeFilter = $this->isValidCategoryFilter($this->getParam("type")) ? $this->getParam("type") : false;

        $strNameFilter = trim($this->getParam("title")) != "" ? trim($this->getParam("title")) : false;

        if ($this->isValidPagingParameter($intStart) && $this->isValidPagingParameter($intEnd)) {

            if ($intEnd >= $intStart) {
                $intNrOfFiles = $this->getAllPackagesCount(class_module_system_setting::getConfigValue("_packageserver_repo_id_"), $strTypeFilter, $strNameFilter);
                $arrDBFiles = $this->getAllPackages(class_module_system_setting::getConfigValue("_packageserver_repo_id_"), $strTypeFilter, $intStart, $intEnd, $strNameFilter);

                //error-handling: a new filter and a offset is passed. but maybe the passed offset is no longer valid for the new filter criteria
                if(count($arrDBFiles) == 0 && $intNrOfFiles > 0) {
                    $arrDBFiles = $this->getAllPackages(class_module_system_setting::getConfigValue("_packageserver_repo_id_"), $strTypeFilter, 0, $intNrOfFiles, $strNameFilter);
                }

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

                class_module_packageserver_log::generateDlLog($strNameFilter !== false ? $strNameFilter : "", isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : "::1", urldecode($this->getParam("domain")));
                class_response_object::getInstance()->setStrResponseType(class_http_responsetypes::STR_TYPE_JSON);
            }
        }

        $result = array();
        $result['numberOfTotalItems'] = $intNrOfFiles;
        $result['items'] = $arrPackages;
        $result['protocolVersion'] = self::PROTOCOL_VERSION;

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

    private function isValidCategoryFilter($strParam) {
        $arrTypes = array(
            class_module_packagemanager_manager::STR_TYPE_ELEMENT,
            class_module_packagemanager_manager::STR_TYPE_MODULE,
            class_module_packagemanager_manager::STR_TYPE_TEMPLATE
        );
        return in_array($strParam, $arrTypes);
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
     * @param int|bool $strCategoryFilter
     * @param int $intStart
     * @param int $intEnd
     * @param bool $strNameFilter
     *
     * @return class_module_mediamanager_file[]
     */
    private function getAllPackages($strParentId, $strCategoryFilter = false, $intStart = null, $intEnd = null, $strNameFilter = false) {
        $arrReturn = array();

        if(validateSystemid($strParentId)) {

            $arrSubfiles = class_module_mediamanager_file::loadFilesDB($strParentId, false, true, null, null, true);

            foreach($arrSubfiles as $objOneFile) {
                if($objOneFile->getIntType() == class_module_mediamanager_file::$INT_TYPE_FILE) {

                    //filename based check if the file should be included
                    if($strNameFilter !== false) {
                        if(uniStrpos($strNameFilter, ",") !== false) {
                            if(in_array($objOneFile->getStrName(), explode(",", $strNameFilter)))
                                $arrReturn[] = $objOneFile;
                        }
                        else if(uniSubstr($objOneFile->getStrName(), 0, uniStrlen($strNameFilter)) == $strNameFilter)
                            $arrReturn[] = $objOneFile;
                    }
                    else
                        $arrReturn[] = $objOneFile;
                }
                else
                    $arrReturn = array_merge($arrReturn, $this->getAllPackages($objOneFile->getSystemid()));
            }

            if($intStart !== null && $intEnd !== null && $intStart > 0 && $intEnd > $intStart) {
                if($intEnd > count($arrReturn))
                    $intEnd = count($arrReturn);

                $arrTemp = array();
                for($intI = $intStart; $intI <= $intEnd; $intI++)
                    $arrTemp[] = $arrReturn[$intI];

                $arrReturn = $arrTemp;

            }

            //sort them by filename
            usort($arrReturn, function(class_module_mediamanager_file $objA, class_module_mediamanager_file $objB) {
                return strcmp($objA->getStrName(), $objB->getStrName());
            });
        }
        else {
            $arrReturn = class_module_mediamanager_file::getFlatPackageList($strCategoryFilter, true, $intStart, $intEnd, $strNameFilter);
        }

        return $arrReturn;
    }

    /**
     * Internal helper, triggers the counting of packages available for the current request
     *
     * @param $strParentId
     * @param bool $strCategoryFilterFilter
     * @param bool $strNameFilter
     *
     * @return int
     */
    private function getAllPackagesCount($strParentId, $strCategoryFilterFilter = false, $strNameFilter = false) {
        if(validateSystemid($strParentId))
            return count($this->getAllPackages($strParentId, $strCategoryFilterFilter, null, null, $strNameFilter));
        else
            return class_module_mediamanager_file::getFlatPackageListCount($strCategoryFilterFilter, true, $strNameFilter);
    }


}
