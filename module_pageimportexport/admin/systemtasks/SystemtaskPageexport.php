<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pageimportexport\Admin\Systemtasks;

use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\System\Admin\Systemtasks\AdminSystemtaskInterface;
use Kajona\System\Admin\Systemtasks\SystemtaskBase;
use Kajona\System\System\Carrier;
use Kajona\System\System\Exception;
use Kajona\System\System\LanguagesLanguage;
use Kajona\System\System\SystemModule;
use XMLWriter;


/**
 * Exports a page into a xml-structure.
 *
 * @package module_pageimportexport
 * @author sidler@mulchprod.de
 */
class SystemtaskPageexport extends SystemtaskBase implements AdminSystemtaskInterface
{


    /**
     * contructor to call the base constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setStrTextBase("pages");
    }

    /**
     * @see AdminSystemtaskInterface::getGroupIdenitfier()
     * @return string
     */
    public function getGroupIdentifier()
    {
        return "pages";
    }

    /**
     * @see AdminSystemtaskInterface::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName()
    {
        return "pageexport";
    }

    /**
     * @see AdminSystemtaskInterface::getStrTaskName()
     * @return string
     */
    public function getStrTaskName()
    {
        return $this->getLang("systemtask_pageexport_name");
    }

    /**
     * @see AdminSystemtaskInterface::executeTask()
     * @throws Exception
     * @return string
     */
    public function executeTask()
    {

        if (!SystemModule::getModuleByName("pages")->rightEdit()) {
            return $this->getLang("commons_error_permissions");
        }

        //load the page itself
        $objPage = PagesPage::getPageByName($this->getParam("pageExport"));
        if ($objPage !== null) {

            $objSystem = SystemModule::getModuleByName("system");

            $objXmlWriter = new XMLWriter();


            $strExportFolder = $this->getParam("exportFolder");
            $strExportPrefix = $this->getParam("exportPrefix");

            if ($strExportFolder == "") {
                $strExportFolder = _realpath_._projectpath_."/temp";
            }
            else {
                $strExportFolder = _realpath_."/".$strExportFolder;
            }

            if ($strExportPrefix != "") {
                $strExportPrefix = "_".$strExportPrefix;
            }

            if (is_dir($strExportFolder)) {

                if (!$objXmlWriter->openUri($strExportFolder."/".$strExportPrefix.$objPage->getSystemid().".xml")) {
                    throw new Exception("failed to open export file ", Exception::$level_ERROR);
                }

                //$objXmlWriter->openMemory();

                $objXmlWriter->setIndent(true);

                $objXmlWriter->startDocument("1.0", "UTF-8");

                $objXmlWriter->startComment();

                $strComment = "\n   Kajona XML export\n";
                $strComment .= "   (c) Kajona, www.kajona.de\n";
                $strComment .= "   Kernel version:  ".$objSystem->getStrVersion()."\n";
                $strComment .= "   Schema version:  1.0\n";
                $strComment .= "   Export Date:     ".dateToString(new \Kajona\System\System\Date)."\n";

                $objXmlWriter->text($strComment);
                $objXmlWriter->endComment();

                $objXmlWriter->startElement("pages");

                $objXmlWriter->startElement("page");

                $objXmlWriter->startElement("metadata");

                $objXmlWriter->startElement("kernelVersion");
                $objXmlWriter->text($objSystem->getStrVersion());
                $objXmlWriter->endElement();

                $objXmlWriter->startElement("systemid");
                $objXmlWriter->text($objPage->getSystemid());
                $objXmlWriter->endElement();

                $objXmlWriter->startElement("previd");
                $objXmlWriter->text($objPage->getPrevId());
                $objXmlWriter->endElement();

                $objXmlWriter->startElement("pagename");
                $objXmlWriter->text($objPage->getStrName());
                $objXmlWriter->endElement();

                //try to load the parent page-name
                $strParentName = "";
                if (validateSystemid($objPage->getPrevId())) {
                    $objParentPage = new PagesPage($objPage->getPrevId());
                    $strParentName = $objParentPage->getStrName();
                }

                $objXmlWriter->startElement("prevname");
                $objXmlWriter->text($strParentName);
                $objXmlWriter->endElement();


                $objXmlWriter->startElement("pageproperties");
                $this->createPageMetadata($objPage->getSystemid(), $objXmlWriter);
                $objXmlWriter->endElement();

                //metadata
                $objXmlWriter->endElement();

                $objXmlWriter->startElement("elements");
                $this->createElementData($objPage->getSystemid(), $objXmlWriter);
                $objXmlWriter->endElement();

                //page
                $objXmlWriter->endElement();

                //pages
                $objXmlWriter->endElement();

                $objXmlWriter->flush();
                //return $objXmlWriter->outputMemory(true);
                return $this->getLang("systemtask_pageexport_success").$strExportFolder."/".$strExportPrefix.$objPage->getSystemid().".xml"."";
            }
            else {
                throw new Exception("writing XML: Folder ".$strExportFolder." does not exist! ", Exception::$level_ERROR);
            }

        }

        return $this->getLang("systemtask_pageexport_error");

    }

    /**
     * @see AdminSystemtaskInterface::getAdminForm()
     * @return string
     */
    public function getAdminForm()
    {
        return $this->objToolkit->formInputPageSelector("pageexport_page", $this->getLang("systemtask_pageexport_page"));
    }

    /**
     * @see AdminSystemtaskInterface::getSubmitParams()
     * @return string
     */
    public function getSubmitParams()
    {
        return "&pageExport=".$this->getParam("pageexport_page");
    }


    //helper-functions ----------------------------------------------------------------------------------


    private function createElementData($strPageId, XMLWriter $objWriter)
    {
        $arrElements = PagesPageelement::getAllElementsOnPage($strPageId);

        foreach ($arrElements as $objOneElement) {
            $objWriter->startElement("element");
            //elements metadata

            $objWriter->startElement("metadata");

            $objWriter->startElement("systemid");
            $objWriter->text($objOneElement->getSystemid());
            $objWriter->endElement();

            $objWriter->startElement("placeholder");
            $objWriter->text($objOneElement->getStrPlaceholder());
            $objWriter->endElement();

            $objWriter->startElement("name");
            $objWriter->text($objOneElement->getStrName());
            $objWriter->endElement();

            $objWriter->startElement("element");
            $objWriter->text($objOneElement->getStrElement());
            $objWriter->endElement();

            $objWriter->startElement("title");
            $objWriter->text($objOneElement->getStrTitle(false));
            $objWriter->endElement();

            $objWriter->startElement("language");
            $objWriter->text($objOneElement->getStrLanguage());
            $objWriter->endElement();

            $objWriter->endElement();


            //the elements-content itself
            $objElement = $objOneElement->getConcreteAdminInstance();
            //Fetch the table
            $strElementTable = $objElement->getTable();


            $objWriter->startElement("foreignTable");
            $objWriter->startAttribute("table");
            $objWriter->text(uniStrReplace(_dbprefix_, "", $strElementTable));
            $objWriter->endAttribute();


            //content-row
            $arrContentRow = Carrier::getInstance()->getObjDB()->getPRow("SELECT * FROM ".$strElementTable." WHERE content_id = ? ", array($objOneElement->getSystemid()));
            $arrColumns = Carrier::getInstance()->getObjDB()->getColumnsOfTable($strElementTable);

            foreach ($arrColumns as $arrOneCol) {

                $objWriter->startElement("column");
                $objWriter->startAttribute("name");
                $objWriter->text($arrOneCol["columnName"]);
                $objWriter->endAttribute();

                if($arrContentRow[$arrOneCol["columnName"]] !== null) {
                    $objWriter->startCdata();
                    $objWriter->text($arrContentRow[$arrOneCol["columnName"]]);
                    $objWriter->endCdata();
                }

                //column
                $objWriter->endElement();
            }

            //foreignTable
            $objWriter->endElement();

            //element
            $objWriter->endElement();
        }
    }


    private function createPageMetadata($strPageId, XMLWriter $objWriter)
    {
        //loop all languages if given
        $objLanguages = new LanguagesLanguage();
        $strCurrentLanguage = $objLanguages->getStrAdminLanguageToWorkOn();

        $arrLanguages = LanguagesLanguage::getObjectList();
        foreach ($arrLanguages as $objOneLanguage) {
            $objLanguages->setStrAdminLanguageToWorkOn($objOneLanguage->getStrName());

            $objPage = new PagesPage($strPageId);

            $objWriter->startElement("propertyset");
            $objWriter->startAttribute("language");
            $objWriter->text($objOneLanguage->getStrName());
            $objWriter->endAttribute();

            $objWriter->startElement("browsername");
            $objWriter->text($objPage->getStrBrowsername());
            $objWriter->endElement();

            $objWriter->startElement("description");
            $objWriter->text($objPage->getStrDesc());
            $objWriter->endElement();

            $objWriter->startElement("keywords");
            $objWriter->text($objPage->getStrKeywords());
            $objWriter->endElement();

            $objWriter->startElement("template");
            $objWriter->text($objPage->getStrTemplate());
            $objWriter->endElement();

            $objWriter->startElement("seostring");
            $objWriter->text($objPage->getStrSeostring());
            $objWriter->endElement();

            $objWriter->startElement("language");
            $objWriter->text($objPage->getStrLanguage());
            $objWriter->endElement();

            $objWriter->startElement("alias");
            $objWriter->text($objPage->getStrAlias());
            $objWriter->endElement();

            $objWriter->startElement("path");
            $objWriter->text($objPage->getStrPath());
            $objWriter->endElement();

            $objWriter->startElement("target");
            $objWriter->text($objPage->getStrTarget());
            $objWriter->endElement();

            //propertyset
            $objWriter->endElement();
        }


        $objLanguages->setStrAdminLanguageToWorkOn($strCurrentLanguage);
    }

}
