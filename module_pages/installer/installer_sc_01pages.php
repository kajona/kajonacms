<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                *
********************************************************************************************************/

/**
 * Installer of the pages samplecontent
 *
 * @package module_pages
 */
class class_installer_sc_01pages implements interface_sc_installer  {

    /**
     * @var class_db
     */
    private $objDB;
    private $strContentLanguage;
    private $strIndexID;
    private $strMasterID;

    /**
     * Does the hard work: installs the module and registers needed constants
     * @return string
     */
    public function install() {

        $strReturn = "";


        $strReturn .= "Shifting pages to first position...\n";
        $objPagesModule = class_module_system_module::getModuleByName("pages");
        $objPagesModule->setAbsolutePosition(1);

        $strReturn .= "Setting default template...\n";
        $objConstant = class_module_system_setting::getConfigByName("_pages_defaulttemplate_");
        $objConstant->setStrValue("kajona_demo.tpl");
        $objConstant->updateObjectToDb();


        $strReturn .= "Creating system folder...\n";
        $objFolder = new class_module_pages_folder();
        $objFolder->setStrName("_system");
        $objFolder->updateObjectToDb(class_module_system_module::getModuleByName("pages")->getSystemid());
        $strSystemFolderID = $objFolder->getSystemid();
        $strReturn .= "ID of new folder: ".$strSystemFolderID."\n";


        $strReturn .= "Creating mainnavigation folder...\n";
        $objFolder = new class_module_pages_folder();
        $objFolder->setStrName("mainnavigation");
        $objFolder->updateObjectToDb(class_module_system_module::getModuleByName("pages")->getSystemid());
        $strMainnavigationFolderID = $objFolder->getSystemid();
        $strReturn .= "ID of new folder: ".$strSystemFolderID."\n";

        $strReturn .= "Creating index-site...\n";
        $objPage = new class_module_pages_page();
        $objPage->setStrName("index");

        if($this->strContentLanguage == "de") {
            $objPage->setStrBrowsername("Willkommen");
        }
        else {
            $objPage->setStrBrowsername("Welcome");
        }

        $objPage->setStrTemplate("kajona_demo.tpl");
        $objPage->updateObjectToDb();
        $this->strIndexID = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$this->strIndexID."\n";
        $strReturn .= "Adding headline-element to new page\n";
        if(class_module_pages_element::getElement("row") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("headline_row");
            $objPagelement->setStrName("headline");
            $objPagelement->setStrElement("row");
            $objPagelement->updateObjectToDb($this->strIndexID);
            $strElementId = $objPagelement->getSystemid();

            $arrParams = array();
            if($this->strContentLanguage == "de") {
                $arrParams[] = "Willkommen";
                $arrParams[] = $strElementId;
            }
            else {
                $arrParams[] = "Welcome";
                $arrParams[] = $strElementId;
            }

            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                SET paragraph_title = ?
                                WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, $arrParams))
                $strReturn .= "Headline element created.\n";
            else
                $strReturn .= "Error creating headline element.\n";
        }

        $strReturn .= "Adding paragraph-element to new page\n";
        if(class_module_pages_element::getElement("paragraph") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("text_paragraph");
            $objPagelement->setStrName("text");
            $objPagelement->setStrElement("paragraph");
            $objPagelement->updateObjectToDb($this->strIndexID);
            $strElementId = $objPagelement->getSystemid();

            $arrParams = array();
            if($this->strContentLanguage == "de") {
                $arrParams[] = "Herzlichen Glückwunsch!";
                $arrParams[] = "Diese Installation von Kajona war erfolgreich. Wir wünschen viel Spaß mit Kajona V3.<br />
                                Für weitere Informationen und Support besuchen Sie unsere Webseite: <a href=\"http://www.kajona.de\">www.kajona.de</a>";
                $arrParams[] = "/files/images/samples/P3197800.JPG";
                $arrParams[] = $strElementId;

            }
            else {
                $arrParams[] = "Congratulations!";
                $arrParams[] = "This installation of Kajona was successful. Have fun using Kajona!<br />
                                 For further information, support or proposals, please visit our website: <a href=\"http://www.kajona.de\">www.kajona.de</a>";
                $arrParams[] = "/files/images/samples/P3197800.JPG";
                $arrParams[] = $strElementId;
            }

            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                SET paragraph_title = ?,
                                    paragraph_content =  ?,
                                    paragraph_image = ?
                                WHERE content_id = ?";

            if($this->objDB->_pQuery($strQuery, $arrParams, array(true, false)))
                $strReturn .= "Paragraph element created.\n";
            else
                $strReturn .= "Error creating paragraph element.\n";
        }

        $strReturn .= "Creating master-page\n";
        $objPage = new class_module_pages_page();
        $objPage->setStrName("master");
        $objPage->setStrBrowsername("master");
        $objPage->setStrTemplate("master.tpl");
        $objPage->updateObjectToDb($strSystemFolderID);
        $this->strMasterID = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$this->strMasterID."\n";


        $strReturn .= "Creating error-site...\n";
        $objPage = new class_module_pages_page();
        $objPage->setStrName("error");

        if($this->strContentLanguage == "de")
            $objPage->setStrBrowsername("Fehler");
        else
            $objPage->setStrBrowsername("Error");


        $objPage->setStrTemplate("kajona_demo.tpl");
        $objPage->updateObjectToDb($strSystemFolderID);
        $strErrorPageId = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strErrorPageId."\n";

        $strReturn .= "Adding headline-element to new page\n";
        if(class_module_pages_element::getElement("row") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("headline_row");
            $objPagelement->setStrName("headline");
            $objPagelement->setStrElement("row");
            $objPagelement->updateObjectToDb($strErrorPageId);
            $strElementId = $objPagelement->getSystemid();

            $arrParams = array();
            if($this->strContentLanguage == "de") {
                $arrParams[] = "Fehler";
                $arrParams[] = $strElementId;
            }
            else {
                $arrParams[] = "Error";
                $arrParams[] = $strElementId;
            }

            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                            SET paragraph_title = ?
                            WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery,$arrParams))
                $strReturn .= "Headline element created.\n";
            else
                $strReturn .= "Error creating headline element.\n";

        }

        $strReturn .= "Adding paragraph-element to new page\n";
        if(class_module_pages_element::getElement("paragraph") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("text_paragraph");
            $objPagelement->setStrName("text");
            $objPagelement->setStrElement("paragraph");
            $objPagelement->updateObjectToDb($strErrorPageId);
            $strElementId = $objPagelement->getSystemid();

            $arrParams = array();
            if($this->strContentLanguage == "de") {
                $arrParams[] = "Ein Fehler ist aufgetreten";
                $arrParams[] = "Während Ihre Anfrage ist leider ein Fehler aufgetreten.<br />Bitte versuchen Sie die letzte Aktion erneut.";
                $arrParams[] = $strElementId;
            }
            else {
                $arrParams[] = "An error occured";
                $arrParams[] = "Maybe the requested page doesn\'t exist anymore.<br />Please try it again later.";
                $arrParams[] = $strElementId;
            }

            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                        SET paragraph_title = ?,
                            paragraph_content = ?
                        WHERE content_id = ?";

            if($this->objDB->_pQuery($strQuery, $arrParams, array(true, false)))
                $strReturn .= "Paragraph element created.\n";
            else
                $strReturn .= "Error creating paragraph element.\n";
        }


        $strReturn .= "Creating imprint-site...\n";
        $objPage = new class_module_pages_page();
        $objPage->setStrName("imprint");
        if($this->strContentLanguage == "de")
            $objPage->setStrBrowsername("Impressum");
        else
            $objPage->setStrBrowsername("Imprint");
        $objPage->setStrTemplate("kajona_demo.tpl");
        $objPage->updateObjectToDb($strSystemFolderID);
        $strImprintPageId = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strImprintPageId."\n";
        $strReturn .= "Adding headline-element to new page\n";
        if(class_module_pages_element::getElement("row") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("headline_row");
            $objPagelement->setStrName("headline");
            $objPagelement->setStrElement("row");
            $objPagelement->updateObjectToDb($strImprintPageId);
            $strElementId = $objPagelement->getSystemid();

            $arrParams = array();
            if($this->strContentLanguage == "de") {
                $arrParams[] = "Impressum";
                $arrParams[] = $strElementId;
            }
            else {
                $arrParams[] = "Imprint";
                $arrParams[] = $strElementId;
            }

            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                            SET paragraph_title = ?
                            WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, $arrParams))
                $strReturn .= "Headline element created.\n";
            else
            $strReturn .= "Error creating headline element.\n";

        }

        $strReturn .= "Adding paragraph-element to new page\n";
        if(class_module_pages_element::getElement("paragraph") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("text_paragraph");
            $objPagelement->setStrName("text");
            $objPagelement->setStrElement("paragraph");
            $objPagelement->updateObjectToDb($strImprintPageId);
            $strElementId = $objPagelement->getSystemid();

            $arrParams = array();
            if($this->strContentLanguage == "de") {
                $arrParams[] = "Impressum";
                $arrParams[] = "Bitte tragen Sie hier Ihre Kontaktdaten ein.<br />
                               Nachname, Name<br />
                               Straße und Hausnummer<br />
                               PLZ, Ort<br />
                               Telefon<br />
                               E-Mail<br />
                               <br />
                               Site powered by <a href=\"http://www.kajona.de\" target=\"_blank\" title=\"Kajona CMS - empowering your content\">Kajona</a><br /><a href=\"http://www.kajona.de\" target=\"_blank\" title=\"Kajona CMS - empowering your content\"><img src=\"portal/pics/kajona/kajona_poweredby.png\" alt=\"Kajona\" /></a><br />";
                $arrParams[] = $strElementId;

            }
            else {
                $arrParams[] = "Imprint";
                $arrParams[] = "Please provide your contact details.<br />
                               Name, Forename<br />
                               Street<br />
                               Zip, City<br />
                               Phone<br />
                               Mail<br />
                               <br />
                               Site powered by <a href=\"http://www.kajona.de\" target=\"_blank\" title=\"Kajona CMS - empowering your content\">Kajona</a><br /><a href=\"http://www.kajona.de\" target=\"_blank\" title=\"Kajona CMS - empowering your content\"><img src=\"portal/pics/kajona/kajona_poweredby.png\" alt=\"Kajona\" /></a><br />";
                $arrParams[] = $strElementId;
            }

            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                        SET paragraph_title = ?,
                           paragraph_content = ?
                      WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, $arrParams, array(true, false)))
                $strReturn .= "Paragraph element created.\n";
            else
                $strReturn .= "Error creating paragraph element.\n";

        }


        $strReturn .= "Creating sample page...\n";
        $objPage = new class_module_pages_page();
        $objPage->setStrName("page_1");
        if($this->strContentLanguage == "de")
            $objPage->setStrBrowsername("Beispielseite 1");
        else
            $objPage->setStrBrowsername("Sample page 1");
        $objPage->setStrTemplate("kajona_demo.tpl");
        $objPage->updateObjectToDb($strMainnavigationFolderID);
        $strSamplePageId = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strSamplePageId."\n";
        $strReturn .= "Adding headline-element to new page\n";

        if(class_module_pages_element::getElement("row") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("headline_row");
            $objPagelement->setStrName("headline");
            $objPagelement->setStrElement("row");
            $objPagelement->updateObjectToDb($strSamplePageId);
            $strElementId = $objPagelement->getSystemid();

            $arrParams = array();
            if($this->strContentLanguage == "de") {
                $arrParams[] = "Beispielseite 1";
                $arrParams[] = $strElementId;
            }
            else {
                $arrParams[] = "Sample page 1";
                $arrParams[] = $strElementId;
            }

            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                            SET paragraph_title = ?
                            WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, $arrParams))
                $strReturn .= "Headline element created.\n";
            else
                $strReturn .= "Error creating headline element.\n";

        }
        $strReturn .= "Adding paragraph-element to new page\n";
        if(class_module_pages_element::getElement("paragraph") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("text_paragraph");
            $objPagelement->setStrName("text");
            $objPagelement->setStrElement("paragraph");
            $objPagelement->updateObjectToDb($strSamplePageId);
            $strElementId = $objPagelement->getSystemid();

            $arrParams = array();
            if($this->strContentLanguage == "de") {
                $arrParams[] = "Standard-Absatz";
                $arrParams[] = "Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.";
                $arrParams[] = "/files/images/samples/IMG_3000.JPG";
                $arrParams[] = "http://www.kajona.de/";
                $arrParams[] = $strElementId;
            }
            else {
                $arrParams[] = "Standard paragraph";
                $arrParams[] = "Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.";
                $arrParams[] = "/files/images/samples/IMG_3000.JPG";
                $arrParams[] = "http://www.kajona.de/";
                $arrParams[] = $strElementId;
            }

            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                            SET paragraph_title = ?,
                                paragraph_content = ?,
                                paragraph_image = ?,
                                paragraph_link = ?
                            WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, $arrParams))
                $strReturn .= "Paragraph element created.\n";
            else
                $strReturn .= "Error creating paragraph element.\n";


        }



       $strReturn .= "Creating sample subpage...\n";
        $objPage = new class_module_pages_page();
        $objPage->setStrName("subpage_1");

        if($this->strContentLanguage == "de")
            $objPage->setStrBrowsername("Beispiel-Unterseite 1");
        else
            $objPage->setStrBrowsername("Sample subpage 1");
        $objPage->setStrTemplate("kajona_demo.tpl");
        $objPage->updateObjectToDb($strSamplePageId);
        $strSampleSubPageId = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strSampleSubPageId."\n";
        $strReturn .= "Adding headline-element to new page\n";

        if(class_module_pages_element::getElement("row") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("headline_row");
            $objPagelement->setStrName("headline");
            $objPagelement->setStrElement("row");
            $objPagelement->updateObjectToDb($strSampleSubPageId);
            $strElementId = $objPagelement->getSystemid();

            $arrParams = array();
            if($this->strContentLanguage == "de") {
                $arrParams[] = "Beispiel-Unterseite 1";
                $arrParams[] = $strElementId;
            }
            else {
                $arrParams[] = "Sample subpage 1";
                $arrParams[] = $strElementId;
            }

            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                            SET paragraph_title = ?
                            WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, $arrParams))
                $strReturn .= "Headline element created.\n";
            else
                $strReturn .= "Error creating headline element.\n";

        }

        $strReturn .= "Adding paragraph-element to new page\n";
        if(class_module_pages_element::getElement("paragraph") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("text_paragraph");
            $objPagelement->setStrName("text");
            $objPagelement->setStrElement("paragraph");
            $objPagelement->updateObjectToDb($strSampleSubPageId);
            $strElementId = $objPagelement->getSystemid();


            $arrParams = array();
            if($this->strContentLanguage == "de") {
                $arrParams[] = "Standard-Absatz auf Unterseite";
                $arrParams[] = "Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.";
                $arrParams[] = $strElementId;
            }
            else {
                $arrParams[] = "Standard paragraph on subpage";
                $arrParams[] = "Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.";
                $arrParams[] = $strElementId;
            }

            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                            SET paragraph_title = ?,
                                paragraph_content = ?
                            WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, $arrParams))
                $strReturn .= "Paragraph element created.\n";
            else
                $strReturn .= "Error creating paragraph element.\n";
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
        return "pages";
    }

}
