<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: installer_sc_faqs.php 3698 2011-03-25 18:57:34Z sidler $                                       *
********************************************************************************************************/


/**
 * Installer of the eventmanagers samplecontenht
 *
 * @package modul_eventmanager
 */
class class_installer_sc_eventmanager implements interface_sc_installer  {

    private $objDB;
    private $strContentLanguage;

    private $strIndexID = "";

    public function install() {
        $strReturn = "";

        //fetch navifolder-id
        $strNaviFolderId = "";
        $arrFolder = class_modul_pages_folder::getFolderList();
        foreach($arrFolder as $objOneFolder)
            if($objOneFolder->getStrName() == "mainnavigation")
                $strNaviFolderId = $objOneFolder->getSystemid();


        $strReturn .= "Creating event\n";
        $objEvent = new class_modul_eventmanager_event();
        
        $objEvent->setObjStartDate(new class_date());
        $objEvent->setObjEndDate(new class_date(time()+3600));
        $objEvent->setIntRegistrationRequired(1);

        if($this->strContentLanguage == "de") {
        	$objEvent->setStrTitle("Start der neuen Webseite mit Kajona");
        	$objEvent->setStrDescription("Die neue Webseite ist online!<br />Als Basis dafür kommt das freie Open Source Content Management System <a href=\"http://www.kajona.de\">Kajona</a>, zum Einsatz.");
        }
        else {
            $objEvent->setStrTitle("Launch of the new website");
        	$objEvent->setStrDescription("The new website is available!<br />The page is based on <a href=\"http://www.kajona.de\">Kajona</a>, a free open source content management system.");
        }

        $strReturn .= "Saving event...\n";
        $objEvent->updateObjectToDb();


        $strReturn .= "Creating events-page\n";
        $objPage = new class_modul_pages_page();
        $objPage->setStrName("events");
        $objPage->setStrBrowsername("Events");
        $objPage->setStrTemplate("kajona_demo.tpl");
        $objPage->updateObjectToDb($strNaviFolderId);

        $strEventsPageId = $objPage->getSystemid();

        $strReturn .= "ID of new page: ".$strEventsPageId."\n";
        $strReturn .= "Adding eventmanager-element to new page\n";
        if(class_modul_pages_element::getElement("eventmanager") != null) {
            $objPagelement = new class_modul_pages_pageelement();
            $objPagelement->setStrPlaceholder("mixed3_flash|mediaplayer|tags|eventmanager");
            $objPagelement->setStrName("mixed3");
            $objPagelement->setStrElement("eventmanager");
            $objPagelement->updateObjectToDb($strEventsPageId);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_universal
                            SET char1 = ?,
                                ".$this->objDB->encloseColumnName("int1")." = ?,
                                ".$this->objDB->encloseColumnName("int2")." = ?
                          WHERE content_id = ? ";
            if($this->objDB->_pQuery($strQuery, array("eventmanager.tpl", 0, 0, $strElementId)))
                $strReturn .= "eventmanger-element created.\n";
            else
                $strReturn .= "Error creating eventmanager-element.\n";
        }

        $strReturn .= "Adding headline-element to new page\n";
        
        if(class_modul_pages_element::getElement("row") != null) {
            $objPagelement = new class_modul_pages_pageelement();
            $objPagelement->setStrPlaceholder("headline_row");
            $objPagelement->setStrName("headline");
            $objPagelement->setStrElement("row");
            $objPagelement->updateObjectToDb($strEventsPageId);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                             SET paragraph_title = ?
                           WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, array("Events", $strElementId)))
                $strReturn .= "Headline element created.\n";
            else
                $strReturn .= "Error creating headline element.\n";
        }

        return $strReturn;
    }

    public function setObjDb($objDb) {
        $this->objDB = $objDb;
    }

    public function setStrContentlanguage($strContentlanguage) {
        $this->strContentLanguage = $strContentlanguage;
    }

    public function getCorrespondingModule() {
        return "eventmanager";
    }

}
?>