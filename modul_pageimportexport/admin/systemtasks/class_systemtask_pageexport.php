<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: class_systemtask_dbimport.php 3714 2011-04-01 13:21:01Z sidler $                                        *
********************************************************************************************************/

/**
 * Exports a page into a xml-structure.
 *
 * @package modul_pages
 * @author sidler@mulchprod.de
 */
class class_systemtask_pageexport extends class_systemtask_base implements interface_admin_systemtask {


    /**
     * contructor to call the base constructor
     */
    public function __construct() {
        parent::__construct();
        
        $this->setStrTextBase("pages");
    }

    /**
     * @see interface_admin_systemtask::getGroupIdenitfier()
     * @return string
     */
    public function getGroupIdentifier() {
        return "pages";
    }
    
    /**
     * @see interface_admin_systemtask::getStrInternalTaskName()
     * @return string
     */
    public function getStrInternalTaskName() {
        return "pageexport";
    }
    
    /**
     * @see interface_admin_systemtask::getStrTaskName()
     * @return string
     */
    public function getStrTaskName() {
        return $this->getText("systemtask_pageexport_name");
    }
    
    /**
     * @see interface_admin_systemtask::executeTask()
     * @return string
     */
    public function executeTask() {
        
        //load the page itself
        $objPage = class_modul_pages_page::getPageByName($this->getParam("pageExport"));
        if(validateSystemid($objPage->getSystemid())) {
            
            $objSystem = class_modul_system_module::getModuleByName("system");
            
            $objXmlWriter = new XMLWriter();
            //$objXmlWriter->openMemory();
            
            
            $objXmlWriter->openUri(_realpath_."/".$objPage->getSystemid().".xml");
            
            $objXmlWriter->setIndent(true);
            
            $objXmlWriter->startDocument("1.0", "UTF-8");
            
            $objXmlWriter->startComment();
            
            $strComment = "\n   Kajona XML export\n";
            $strComment .=  "   (c) Kajona, www.kajona.de\n";
            $strComment .=  "   Kernel version:  ".$objSystem->getStrVersion()."\n";
            $strComment .=  "   Schema version:  1.0\n";
            $strComment .=  "   Export Date:     ".dateToString(new class_date)."\n";
            
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
            if(validateSystemid($objPage->getPrevId())) {
                $objParentPage = new class_modul_pages_page($objPage->getPrevId());
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
            
            return $this->getText("systemtask_pageexport_success")."<a href=\""._webpath_."/".$objPage->getSystemid().".xml\" target=\"_blank\">"._webpath_."/".$objPage->getSystemid().".xml"."</a>";
        }
        
        return $this->getText("systemtask_pageexport_error");
        
    }

    /**
     * @see interface_admin_systemtask::getAdminForm()
     * @return string 
     */
    public function getAdminForm() {
    	$strReturn = "";
        
        $strReturn .= $this->objToolkit->formInputPageSelector("pageexport_page", $this->getText("systemtask_pageexport_page"));
         
        return $strReturn;
    }
    
    /**
     * @see interface_admin_systemtask::getSubmitParams()
     * @return string 
     */
    public function getSubmitParams() {
        return "&pageExport=".$this->getParam("pageexport_page");
    }
    
    
    
    
    
    
    //helper-functions ----------------------------------------------------------------------------------
    
    
    private function createElementData($strPageId, XMLWriter $objWriter) {
        $arrElements = class_modul_pages_pageelement::getAllElementsOnPage($strPageId);
        
        foreach($arrElements as $objOneElement) {
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
            
            include_once(_adminpath_."/elemente/".$objOneElement->getStrClassAdmin());
            $strElementClass = str_replace(".php", "", $objOneElement->getStrClassAdmin());
            $objElement = new $strElementClass();
            //Fetch the table
            $strElementTable = $objElement->getTable();
            
            
            
            $objWriter->startElement("foreignTable");
            $objWriter->startAttribute("table");
            $objWriter->text(uniStrReplace(_dbprefix_, "",$strElementTable));
            $objWriter->endAttribute();
            
            
            //content-row
            $arrContentRow = class_carrier::getInstance()->getObjDB()->getPRow("SELECT * FROM ".$strElementTable." WHERE content_id = ? ", array($objOneElement->getSystemid()) );
            $arrColumns = class_carrier::getInstance()->getObjDB()->getColumnsOfTable($strElementTable);
            
            foreach($arrColumns as $arrOneCol) {
                
                $objWriter->startElement("column");
                $objWriter->startAttribute("name");
                $objWriter->text($arrOneCol["columnName"]);
                $objWriter->endAttribute();
                
                $objWriter->startCdata();
                $objWriter->text($arrContentRow[$arrOneCol["columnName"]]);
                $objWriter->endCdata();
                
                //column
                $objWriter->endElement();
            }
            
            //foreignTable
            $objWriter->endElement();
            
            //element
            $objWriter->endElement();
        }
    }
    
    
    private function createPageMetadata($strPageId, XMLWriter $objWriter) {
        //loop all languages if given
        $objLanguages = new class_modul_languages_language();
        $strCurrentLanguage = $objLanguages->getStrAdminLanguageToWorkOn();
        
        $arrLanguages = class_modul_languages_language::getAllLanguages();
        foreach($arrLanguages as $objOneLanguage) {
            $objLanguages->setStrAdminLanguageToWorkOn($objOneLanguage->getStrName());
            
            $objPage = new class_modul_pages_page($strPageId);
            
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
            
            //propertyset
            $objWriter->endElement();
        }
        
        
        $objLanguages->setStrAdminLanguageToWorkOn($strCurrentLanguage);
    }
    
}
?>