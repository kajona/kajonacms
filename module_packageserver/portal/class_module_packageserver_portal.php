<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_module_navigation_tree.php 4582 2012-04-11 18:27:04Z sidler $                              *
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
     *
     * @xml
	 */
	protected function actionList() {

        $arrPackages = array();

        $arrDBFiles = $this->getAllPackages(_packageserver_repo_id_);
        $objManager = new class_module_packagemanager_manager();

        foreach($arrDBFiles as $objOneFile) {

            try {

                $objMetadata = $objManager->getPackageManagerForPath($objOneFile->getStrFilename());

                $arrPackages[] = array(
                    "systemid" => $objOneFile->getSystemid(),
                    "title" => $objMetadata->getObjMetadata()->getStrTitle(),
                    "version" => $objMetadata->getObjMetadata()->getStrVersion(),
                    "description" => $objMetadata->getObjMetadata()->getStrDescription(),
                    "type" => $objMetadata->getObjMetadata()->getStrType()
                );

            }
            catch(class_exception $objEx) {

            }
        }


        class_module_packageserver_log::generateDlLog("", $_SERVER["REMOTE_ADDR"], urldecode($this->getParam("domain")));



        class_xml::setBitSuppressXmlHeader(true);
        $strReturn = json_encode($arrPackages);
        header("Content-type: application/json");
        return $strReturn;
    }

    /**
     * Internal helper, loads all files available including a traversal
     * of the nested folders.
     *
     * @param $strParentId
     * @return class_module_mediamanager_file[]
     */
    private function getAllPackages($strParentId) {
        $arrReturn = array();

        $arrSubfiles = class_module_mediamanager_file::loadFilesDB($strParentId, false, true);

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
     *
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
                        "systemid" => $objOneFile->getSystemid(),
                        "title" => $objMetadata->getObjMetadata()->getStrTitle(),
                        "version" => $objMetadata->getObjMetadata()->getStrVersion(),
                        "description" => $objMetadata->getObjMetadata()->getStrDescription(),
                        "type" => $objMetadata->getObjMetadata()->getStrType()
                    );

                }

            }
            catch(class_exception $objEx) {

            }
        }

        class_module_packageserver_log::generateDlLog($this->getParam("title"), $_SERVER["REMOTE_ADDR"], urldecode($this->getParam("domain")));


        class_xml::setBitSuppressXmlHeader(true);
        $strReturn = json_encode($arrReturn);
        header("Content-type: application/json");
        return $strReturn;

    }

}
