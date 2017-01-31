<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pageimportexport\Admin\Systemtasks;

use Kajona\Pages\System\PagesElement;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\System\Admin\Systemtasks\AdminSystemtaskInterface;
use Kajona\System\Admin\Systemtasks\SystemtaskBase;
use Kajona\System\System\Carrier;
use Kajona\System\System\Filesystem;
use Kajona\System\System\LanguagesLanguage;
use Kajona\System\System\Logger;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\StringUtil;
use Kajona\System\System\SystemModule;
use SimpleXMLElement;


/**
 * Imports a xml-based page into the system. Tries to be as error-safe as possible.
 *
 * @package module_pageimportexport
 * @author sidler@mulchprod.de
 */
class SystemtaskPageimport extends SystemtaskBase implements AdminSystemtaskInterface
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
     * @inheritdoc
     */
    public function getGroupIdentifier()
    {
        return "pages";
    }

    /**
     * @inheritdoc
     */
    public function getStrInternalTaskName()
    {
        return "pageimport";
    }

    /**
     * @inheritdoc
     */
    public function getStrTaskName()
    {
        return $this->getLang("systemtask_pageimport_name");
    }

    /**
     * @inheritdoc
     */
    public function executeTask()
    {

        if (!SystemModule::getModuleByName("pages")->rightEdit()) {
            return $this->getLang("commons_error_permissions");
        }

        $strReturn = "";

        $objFilesystem = new Filesystem();
        $strTarget = $this->getParam("pageimport_file");
        $strError = $this->getParam("pageimport_error");
        $strTopFolderId = $this->getParam("topFolderId");

        $bitReplaceExisting = $this->getParam("pageimport_replace") != "";

        if ($strError != "suffix") {
            if ($strError != "upload") {

                $objXml = new SimpleXMLElement(file_get_contents(_realpath_.$strTarget));


                foreach ($objXml->page as $objOnePage) {
                    $strReturn .= $this->processSinglePage($objOnePage, $bitReplaceExisting, $strTopFolderId);
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
     * @inheritdoc
     */
    public function getAdminForm()
    {
        $strReturn = "";
        $strReturn .= $this->objToolkit->formInputUpload("pageimport_file", $this->getLang("systemtask_pageimport_file"));
        $strReturn .= $this->objToolkit->formInputCheckbox("pageimport_replace", $this->getLang("systemtask_pageimport_replace"));
        return $strReturn;
    }

    /**
     * @inheritdoc
     */
    public function getSubmitParams()
    {
        $arrFile = $this->getParam("pageimport_file");
        $strError = "";

        $objFilesystem = new Filesystem();
        $strTarget = "/import_".generateSystemid().".xml";

        $strSuffix = StringUtil::toLowerCase(StringUtil::substring($arrFile["name"], StringUtil::lastIndexOf($arrFile["name"], ".")));
        if ($strSuffix == ".xml") {
            if ($objFilesystem->copyUpload($strTarget, $arrFile["tmp_name"])) {
                Logger::getInstance()->addLogRow("uploaded file ".$strTarget, Logger::$levelInfo);
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

    private function processSinglePage(SimpleXMLElement $objXMlElement, $bitReplaceExisting, $strTopFolderId)
    {

        $strReturn = "";

        /** @var SimpleXMLElement $objMetadata */
        $objMetadata = $objXMlElement->metadata;

        //create page itself

        $strPagename = $objMetadata->pagename."";

        $strPrevId = "";

        //check if the exported prev-values may be used
        $strImportPrevName = $objMetadata->prevname."";
        if ($strImportPrevName != "") {
            $objPage = PagesPage::getPageByName($strImportPrevName);
            if ($objPage !== null) {
                $strPrevId = $objPage->getSystemid();
            }
        }
        elseif (validateSystemid($objMetadata->previd."")) {
            $objRoot = Objectfactory::getInstance()->getObject($objMetadata->previd."");
            if ($objRoot !== null) {
                $strPrevId = $objMetadata->previd."";
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
            Carrier::getInstance()->getObjDB()->flushQueryCache();
        }


        $objPage = new PagesPage();
        $objPage->setStrName($strPagename);

        $objPage->updateObjectToDb($strPrevId);
        $strPageId = $objPage->getSystemid();

        $strReturn .= "created page ".$objPage->getStrName()."\n";

        //save propertysets
        $objLanguages = new LanguagesLanguage();
        $strCurrentLanguage = $objLanguages->getStrAdminLanguageToWorkOn();


        foreach ($objMetadata->pageproperties->propertyset as $objOnePropSet) {

            Carrier::getInstance()->getObjDB()->flushQueryCache();

            $objLanguages->setStrAdminLanguageToWorkOn($objOnePropSet->language."");

            //reload page to save correct prop-sets
            $objPage = new PagesPage($strPageId);
            $objPage->setStrLanguage($objOnePropSet->language."");
            $objPage->setStrBrowsername($objOnePropSet->browsername."");
            $objPage->setStrDesc($objOnePropSet->description."");
            $objPage->setStrKeywords($objOnePropSet->keywords."");
            $objPage->setStrTemplate($objOnePropSet->template."");
            $objPage->setStrSeostring($objOnePropSet->seostring."");
            $objPage->setStrLanguage($objOnePropSet->language."");
            $objPage->setStrAlias($objOnePropSet->alias."");
            $objPage->setStrTarget($objOnePropSet->target."");
            $objPage->setStrPath($objOnePropSet->path."");

            $objPage->updateObjectToDb();

            $strReturn .= "saved propertyset for language ".$objPage->getStrLanguage()."\n";
        }


        $objLanguages->setStrAdminLanguageToWorkOn($strCurrentLanguage);


        //and import each element
        /** @var SimpleXMLElement $objSingleElement */
        foreach ($objXMlElement->elements->element as $objSingleElement) {

            //validate if element exists
            $strElementName = $objSingleElement->metadata->element."";
            if (PagesElement::getElement($strElementName) !== null) {

                $objElement = new PagesPageelement();
                $objElement->setStrPlaceholder($objSingleElement->metadata->placeholder."");
                $objElement->setStrName($objSingleElement->metadata->name."");
                $objElement->setStrElement($objSingleElement->metadata->element."");
                $objElement->setStrTitle($objSingleElement->metadata->title."");
                $objElement->setStrLanguage($objSingleElement->metadata->language."");

                $objElement->updateObjectToDb($strPageId);

                //and the foreign table
                $strTable = $objSingleElement->foreignTable["table"]."";


                $arrValues = array();
                foreach ($objSingleElement->foreignTable->column as $objColumn) {
                    $arrValues[$objColumn["name"].""] = $objColumn."" != "" ? $objColumn."" : null;
                }

                unset($arrValues["content_id"]);

                //and build the query itself
                $strQuery = "UPDATE ".Carrier::getInstance()->getObjDB()->encloseTableName(_dbprefix_.$strTable)." SET ";

                $arrInsertValues = array();
                $arrEscapes = array();

                $arrColumns = Carrier::getInstance()->getObjDB()->getColumnsOfTable(_dbprefix_.$strTable);
                $arrColumns = array_map(function ($arrColumn) {
                    return $arrColumn["columnName"];
                }, $arrColumns);

                foreach ($arrValues as $strColumn => $strValue) {
                    if(!in_array($strColumn, $arrColumns)) {
                        continue;
                    }
                    $strQuery .= Carrier::getInstance()->getObjDB()->encloseColumnName($strColumn)." = ? ,";

                    $arrInsertValues[] = $strValue;
                    $arrEscapes[] = false;
                }

                $strQuery = StringUtil::substring($strQuery, 0, -1);
                $strQuery .= " WHERE content_id = ?";
                $arrInsertValues[] = $objElement->getSystemid();

                Carrier::getInstance()->getObjDB()->_pQuery($strQuery, $arrInsertValues, $arrEscapes);
                $strReturn .= "created element ".$objElement->getStrName()."\n";

            }
            else {
                $strReturn .= "error: element ".$strElementName." not existing";
            }

        }


        return $this->objToolkit->getPreformatted(array($strReturn));
    }
}
