<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
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
        $objPagesModule->setAbsolutePosition(3);

        $strReturn .= "Setting default template...\n";
        $objConstant = class_module_system_setting::getConfigByName("_pages_defaulttemplate_");
        $objConstant->setStrValue("standard.tpl");
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

        $objPage->setStrTemplate("home.tpl");
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

        $strReturn .= "Adding paragraph-elements to new page\n";
        if(class_module_pages_element::getElement("paragraph") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("content_paragraph|image");
            $objPagelement->setStrName("text");
            $objPagelement->setStrElement("paragraph");
            $objPagelement->updateObjectToDb($this->strIndexID);
            $strElementId = $objPagelement->getSystemid();

            $arrParams = array();
            if($this->strContentLanguage == "de") {
                $arrParams[] = "Herzlichen Glückwunsch!";
                $arrParams[] = "Diese Installation von Kajona war erfolgreich. Wir wünschen viel Spaß mit Kajona V4.<br />
                                Nehmen Sie sich die Zeit und betrachten Sie die einzelnen Seiten, die mit Beispielinhalten befüllt wurde. Sie gelangen jederzeit auf diese
                                Seite durch den Link &quot;Home&quot; zurück.<br/>
                                Um die Inhalte der Webseite zu verändern sollten Sie sich als erstes am Administrationsbereich <a href='_webpath_/admin'>anmelden</a>.
                                Für weitere Informationen und Support besuchen Sie unsere Webseite: <a href=\"http://www.kajona.de\">www.kajona.de</a><br/>
                                Das gesamte Kajona-Team wünscht viel Spa&szlig; beim Verwalten der Webseite mit Kajona!";
                $arrParams[] = "/files/images/upload/teaser.jpg";
                $arrParams[] = $strElementId;

            }
            else {
                $arrParams[] = "Congratulations!";
                $arrParams[] = "This installation of Kajona was successful. Have fun using Kajona V4!<br />
                                Take some time and watch the pages already created and have a look at the sample-contents assigned to those page.
                                You may return to this page any time by clicking the &quot;home&quot; link.<br/>
                                To modify the contents of this webpage you have to <a href='_webpath_/admin'>log in</a> at the administration-backend.
                                For further information, support or proposals, please visit our website: <a href=\"http://www.kajona.de\">www.kajona.de</a><br/>
                                The Kajona-Team hopes you'll enjoy managing your website with Kajona!";
                $arrParams[] = "/files/images/upload/teaser.jpg";
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



            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("column1_paragraph|image");
            $objPagelement->setStrName("column1");
            $objPagelement->setStrElement("paragraph");
            $objPagelement->updateObjectToDb($this->strIndexID);
            $strElementId = $objPagelement->getSystemid();

            $arrParams = array();
            if($this->strContentLanguage == "de") {
                $arrParams[] = "Teaser 1";
                $arrParams[] = "Dieser Text-Absatz befindet sich am Platzhalter column1_paragraph|image, der im Standard-Template links ausgerichtet ist. Sobald Sie sich am
                                System <a href='_webpath_/admin'>angemeldet</a> haben und das Portal erneut aufrufen, wird der Portal-Editor angezeigt. Nutzen Sie Drag n Drop
                                um diesen Text-Absatz an einen anderen Platzhalter in diesem Template zu verschieben. Einzige Voraussetzung hierfür ist, dass der Platzhalter
                                Elemente des Typs paragraph zulässt.";
                $arrParams[] = $strElementId;

            }
            else {
                $arrParams[] = "Teaser 1";
                $arrParams[] = "This paragraph is located at the placeholder column1_paragraph|image. The default-template aligns this placeholder to the left.
                                As soon as you <a href='_webpath_/admin'>log in</a> at the administration-backend and reload the portal, the portal-editor is being shown.
                                Use drag n drop to rearrange this placeholder and move it to another placeholder.
                                The only limitation when dropping the element is, that the target-placeholder allows elements of the type paragraph.";
                $arrParams[] = $strElementId;
            }

            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                SET paragraph_title = ?,
                                    paragraph_content =  ?
                                WHERE content_id = ?";

            if($this->objDB->_pQuery($strQuery, $arrParams, array(true, false)))
                $strReturn .= "Paragraph element created.\n";
            else
                $strReturn .= "Error creating paragraph element.\n";



            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("column2_paragraph|image");
            $objPagelement->setStrName("column2");
            $objPagelement->setStrElement("paragraph");
            $objPagelement->updateObjectToDb($this->strIndexID);
            $strElementId = $objPagelement->getSystemid();

            $arrParams = array();
            if($this->strContentLanguage == "de") {
                $arrParams[] = "Teaser 2";
                $arrParams[] = "Der Platzhalter dieses Elementes lautet column2_paragraph|image. Daher ist er für alle anderen Absätze auf dieser Seite ein gültiger Ziel-Platzhalter,
                                sobald ein Absatz per drag n drop verschoben wird. Verschieben Sie die Absätze auf dieser Seite, um ein erstes Gefühl hierfür zu bekommen.";
                $arrParams[] = $strElementId;

            }
            else {
                $arrParams[] = "Teaser 2";
                $arrParams[] = "The placeholder of this paragraph is defined as column2_paragraph|image. Therefore it is a valid target-placeholder for other paragraphs on the current
                                page. Try to move paragraph on this site in order so see how the possible drop-areas are being highlighted.";
                $arrParams[] = $strElementId;
            }

            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                SET paragraph_title = ?,
                                    paragraph_content =  ?
                                WHERE content_id = ?";

            if($this->objDB->_pQuery($strQuery, $arrParams, array(true, false)))
                $strReturn .= "Paragraph element created.\n";
            else
                $strReturn .= "Error creating paragraph element.\n";
            
           
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("column3_paragraph|image");
            $objPagelement->setStrName("column3");
            $objPagelement->setStrElement("paragraph");
            $objPagelement->updateObjectToDb($this->strIndexID);
            $strElementId = $objPagelement->getSystemid();

            $arrParams = array();
            if($this->strContentLanguage == "de") {
                $arrParams[] = "Teaser 3";
                $arrParams[] = "Der Platzhalter dieses Elementes lautet column3_paragraph|image. Daher ist er für alle anderen Absätze auf dieser Seite ein gültiger Ziel-Platzhalter,
                                sobald ein Absatz per drag n drop verschoben wird. Verschieben Sie die Absätze auf dieser Seite, um ein erstes Gefühl hierfür zu bekommen.";
                $arrParams[] = $strElementId;

            }
            else {
                $arrParams[] = "Teaser 3";
                $arrParams[] = "The placeholder of this paragraph is defined as column3_paragraph|image. Therefore it is a valid target-placeholder for other paragraphs on the current
                                page. Try to move paragraph on this site in order so see how the possible drop-areas are being highlighted.";
                $arrParams[] = $strElementId;
            }

            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                SET paragraph_title = ?,
                                    paragraph_content =  ?
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


        $objPage->setStrTemplate("standard.tpl");
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
            $objPagelement->setStrPlaceholder("content_paragraph|image");
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
                $arrParams[] = "An error occurred";
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
        $objPage->setStrTemplate("standard.tpl");
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
            $objPagelement->setStrPlaceholder("content_paragraph|image");
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
                               Site powered by <a href=\"http://www.kajona.de\" target=\"_blank\" title=\"Kajona CMS - empowering your content\">Kajona</a><br /><a href=\"http://www.kajona.de\" target=\"_blank\" title=\"Kajona CMS - empowering your content\"><img src=\"_webpath_/templates/default/pics/default/kajona_poweredby.png\" alt=\"Kajona\" /></a><br />";
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
                               Site powered by <a href=\"http://www.kajona.de\" target=\"_blank\" title=\"Kajona CMS - empowering your content\">Kajona</a><br /><a href=\"http://www.kajona.de\" target=\"_blank\" title=\"Kajona CMS - empowering your content\"><img src=\"_webpath_/templates/default/pics/default/kajona_poweredby.png\" alt=\"Kajona\" /></a><br />";
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
        $objPage->setStrTemplate("standard.tpl");
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
            $objPagelement->setStrPlaceholder("content_paragraph|image");
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
        $objPage->setStrTemplate("standard.tpl");
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
            $objPagelement->setStrPlaceholder("content_paragraph|image");
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
