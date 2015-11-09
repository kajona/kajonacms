<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pageimportexport\Admin\Systemtasks;

use class_carrier;
use class_filesystem;
use class_logger;
use class_module_languages_language;
use class_module_system_module;
use class_objectfactory;
use class_systemtask_base;
use class_xml_parser;
use interface_admin_systemtask;
use Kajona\Pages\System\PagesElement;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;


/**
 * Imports a xml-based page into the system. Tries to be as error-safe as possible.
 *
 * @package module_pageimportexport
 * @author sidler@mulchprod.de
 */
class SystemtaskPageimport extends class_systemtask_base implements interface_admin_systemtask
{


    /**
     * constructor to call the base constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setStrTextBase("pages");
        $this->setBitMultipartform(true);
    }

    /**
     * @see interface_admin_systemtask::getGroupIdenitfier()
     * @return string
     */
    public function getGroupIdentifier()
    {
        return "pages";
    }

    /**
     * @see interface_admin_systemtask::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName()
    {
        return "pageimport";
    }

    /**
     * @see interface_admin_systemtask::getStrTaskName()
     * @return string
     */
    public function getStrTaskName()
    {
        return $this->getLang("systemtask_pageimport_name");
    }

    /**
     * @see interface_admin_systemtask::executeTask()
     * @return string
     */
    public function executeTask()
    {

        if (!class_module_system_module::getModuleByName("pages")->rightEdit()) {
            return $this->getLang("commons_error_permissions");
        }

        $strReturn = "";

        $objFilesystem = new class_filesystem();
        $strTarget = $this->getParam("pageimport_file");
        $strError = $this->getParam("pageimport_error");
        $strTopFolderId = $this->getParam("topFolderId");

        $bitReplaceExisting = $this->getParam("pageimport_replace") != "";

        if ($strError != "suffix") {
            if ($strError != "upload") {

                //parse using the kajona xml parser
                $objXML = new class_xml_parser();
                $objXML->loadFile($strTarget);

                $arrXML = $objXML->xmlToArray();

                foreach ($arrXML as $arrOneXml) {
                    foreach ($arrOneXml as $arrNode) {
                        foreach ($arrNode as $strName => $arrSubnode) {
                            if ($strName == "page") {
                                $strReturn .= $this->processSinglePage($arrSubnode[0], $bitReplaceExisting, $strTopFolderId);
                            }
                        }
                    }
                }
                $strReturn = $this->getLang("systemtask_pageimport_success").$strReturn;

            }
            else {
                $strReturn .= $this->getLang("systemtask_pageimport_errorupload");
            }
        }
        else {
            $strReturn .= $this->getLang("systemtask_pageimport_errortype");
        }

        $objFilesystem->fileDelete($strTarget);

        return $strReturn;
    }

    /**
     * @see interface_admin_systemtask::getAdminForm()
     * @return string
     */
    public function getAdminForm()
    {
        $strReturn = "";
        $strReturn .= $this->objToolkit->formInputUpload("pageimport_file", $this->getLang("systemtask_pageimport_file"));
        $strReturn .= $this->objToolkit->formInputCheckbox("pageimport_replace", $this->getLang("systemtask_pageimport_replace"));
        return $strReturn;
    }

    /**
     * @see interface_admin_systemtask::getSubmitParams()
     * @return string
     */
    public function getSubmitParams()
    {
        $arrFile = $this->getParam("pageimport_file");
        $strError = "";

        $objFilesystem = new class_filesystem();
        $strTarget = "/import_".generateSystemid().".xml";

        $strSuffix = uniStrtolower(uniSubstr($arrFile["name"], uniStrrpos($arrFile["name"], ".")));
        if ($strSuffix == ".xml") {
            if ($objFilesystem->copyUpload($strTarget, $arrFile["tmp_name"])) {
                class_logger::getInstance()->addLogRow("uploaded file ".$strTarget, class_logger::$levelInfo);
            }
            else {
                $strError = "upload";
            }
        }
        else {
            $strError = "suffix";
        }


        return "&pageimport_file=".$strTarget."&pageimport_error=".$strError."&pageimport_replace=".$this->getParam("pageimport_replace");
    }

    //--- helpers ---------------------------------------------------------------------------------------

    private function processSinglePage($arrPage, $bitReplaceExisting, $strTopFolderId)
    {

        $strReturn = "";

        $arrMetadata = $arrPage["metadata"][0];
        $arrElements = $arrPage["elements"][0]["element"];

        //create page itself

        $strPagename = $arrMetadata["pagename"][0]["value"];

        $strPrevId = "";

        //check if the exported prev-values may be used
        $strImportPrevName = $arrMetadata["prevname"][0]["value"];
        if ($strImportPrevName != "") {
            $objPage = PagesPage::getPageByName($strImportPrevName);
            if ($objPage !== null) {
                $strPrevId = $objPage->getSystemid();
            }
        }
        elseif (validateSystemid($arrMetadata["previd"][0]["value"])) {
            $objRoot = class_objectfactory::getInstance()->getObject($arrMetadata["previd"][0]["value"]);
            if ($objRoot !== null) {
                $strPrevId = $arrMetadata["previd"][0]["value"];
            }

        }

        if ($strPrevId == "") {
            $strPrevId = $strTopFolderId;
        }

        //check if an existing page should be replaced
        if ($bitReplaceExisting) {
            $objPage = PagesPage::getPageByName($strPagename);
            if ($objPage !== null) {
                $strPrevId = $objPage->getPrevId();
                $objPage->deleteObject();
            }
            class_carrier::getInstance()->getObjDB()->flushQueryCache();
        }


        $objPage = new PagesPage();
        $objPage->setStrName($strPagename);

        $objPage->updateObjectToDb($strPrevId);
        $strPageId = $objPage->getSystemid();

        $strReturn .= "created page ".$objPage->getStrName()."\n";

        //save propertysets
        $objLanguages = new class_module_languages_language();
        $strCurrentLanguage = $objLanguages->getStrAdminLanguageToWorkOn();

        $arrPropertysets = $arrMetadata["pageproperties"][0]["propertyset"];

        foreach ($arrPropertysets as $arrOnePropSet) {

            class_carrier::getInstance()->getObjDB()->flushQueryCache();

            $objLanguages->setStrAdminLanguageToWorkOn($arrOnePropSet["attributes"]["language"]);

            //reload page to save correct prop-sets
            $objPage = new PagesPage($strPageId);
            $objPage->setStrLanguage($arrOnePropSet["language"][0]["value"]);
            $objPage->setStrBrowsername($arrOnePropSet["browsername"][0]["value"]);
            $objPage->setStrDesc($arrOnePropSet["description"][0]["value"]);
            $objPage->setStrKeywords($arrOnePropSet["keywords"][0]["value"]);
            $objPage->setStrTemplate($arrOnePropSet["template"][0]["value"]);
            $objPage->setStrSeostring($arrOnePropSet["seostring"][0]["value"]);
            $objPage->setStrLanguage($arrOnePropSet["language"][0]["value"]);
            $objPage->setStrAlias($arrOnePropSet["alias"][0]["value"]);
            $objPage->setStrTarget($arrOnePropSet["target"][0]["value"]);
            $objPage->setStrPath($arrOnePropSet["path"][0]["value"]);

            $objPage->updateObjectToDb();

            $strReturn .= "saved propertyset for language ".$objPage->getStrLanguage()."\n";
        }


        $objLanguages->setStrAdminLanguageToWorkOn($strCurrentLanguage);


        //and import each element
        foreach ($arrElements as $arrSingleElement) {

            //validate if element exists
            $strElementName = $arrSingleElement["metadata"][0]["element"][0]["value"];
            if (PagesElement::getElement($strElementName) !== null) {

                $objElement = new PagesPageelement();
                $objElement->setStrPlaceholder($arrSingleElement["metadata"][0]["placeholder"][0]["value"]);
                $objElement->setStrName($arrSingleElement["metadata"][0]["name"][0]["value"]);
                $objElement->setStrElement($arrSingleElement["metadata"][0]["element"][0]["value"]);
                $objElement->setStrTitle($arrSingleElement["metadata"][0]["title"][0]["value"]);
                $objElement->setStrLanguage($arrSingleElement["metadata"][0]["language"][0]["value"]);

                $objElement->updateObjectToDb($strPageId);

                //and the foreign table
                $strTable = $arrSingleElement["foreignTable"][0]["attributes"]["table"];


                $arrValues = array();
                foreach ($arrSingleElement["foreignTable"][0]["column"] as $arrColumn) {
                    $arrValues[$arrColumn["attributes"]["name"]] = isset($arrColumn["value"]) ? $arrColumn["value"] : "";
                }

                unset($arrValues["content_id"]);

                //and build the query itself
                $strQuery = "UPDATE ".class_carrier::getInstance()->getObjDB()->encloseTableName(_dbprefix_.$strTable)." SET ";

                $arrInsertValues = array();
                $arrEscapes = array();

                $arrColumns = class_carrier::getInstance()->getObjDB()->getColumnsOfTable(_dbprefix_.$strTable);
                $arrColumns = array_map(function ($arrColumn) {
                    return $arrColumn["columnName"];
                }, $arrColumns);

                foreach ($arrValues as $strColumn => $strValue) {
                    if(!in_array($strColumn, $arrColumns)) {
                        continue;
                    }
                    $strQuery .= class_carrier::getInstance()->getObjDB()->encloseColumnName($strColumn)." = ? ,";

                    $arrInsertValues[] = $strValue;
                    $arrEscapes[] = false;
                }

                $strQuery = uniSubstr($strQuery, 0, -1);
                $strQuery .= " WHERE content_id = ?";
                $arrInsertValues[] = $objElement->getSystemid();

                class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, $arrInsertValues, $arrEscapes);
                $strReturn .= "created element ".$objElement->getStrName()."\n";

            }
            else {
                $strReturn .= "error: element ".$strElementName." not existing";
            }

        }


        return $this->objToolkit->getPreformatted(array($strReturn));
    }
}
