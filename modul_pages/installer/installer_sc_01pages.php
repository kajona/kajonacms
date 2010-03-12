<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                *
********************************************************************************************************/

/**
 * Installer of the pages samplecontent
 *
 * @package modul_pages
 */
class class_installer_sc_01pages implements interface_sc_installer  {

    private $objDB;
    private $strContentLanguage;
    private $strIndexID;
    private $strMasterID;

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     */
    public function install() {

        $strReturn = "";


        $strReturn .= "Shifting pages to first position...\n";
        $objPagesModule = class_modul_system_module::getModuleByName("pages");
        $objCommon = new class_modul_system_common();
        $objCommon->setAbsolutePosition($objPagesModule->getSystemid(), 1);

        $strReturn .= "Setting default template...\n";
        $objConstant = class_modul_system_setting::getConfigByName("_pages_defaulttemplate_");
        $objConstant->setStrValue("kajona_demo.tpl");
        $objConstant->updateObjectToDb();

        $strReturn .= "Creating index-site...\n";
        $objPage = new class_modul_pages_page();
        $objPage->setStrName("index");

        if($this->strContentLanguage == "de") {
            $objPage->setStrBrowsername("Willkommen");
        }
        else {
            $objPage->setStrBrowsername("Welcome");
        }

        $objPage->setStrTemplate("kajona_demo.tpl");
        //set language to "" - being update by the languages sc installer later
        $objPage->setStrLanguage("");
        $objPage->updateObjectToDb();
        $this->strIndexID = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$this->strIndexID."\n";
        $strReturn .= "Adding headline-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("headline_row");
        $objPagelement->setStrName("headline");
        $objPagelement->setStrElement("row");
        $objPagelement->updateObjectToDb($this->strIndexID);
        $strElementId = $objPagelement->getSystemid();

        if($this->strContentLanguage == "de") {
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                            SET paragraph_title = 'Willkommen'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
        }
        else {
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                SET paragraph_title = 'Welcome'
                                WHERE content_id = '".dbsafeString($strElementId)."'";
        }

        if($this->objDB->_query($strQuery))
            $strReturn .= "Headline element created.\n";
        else
            $strReturn .= "Error creating headline element.\n";

        $strReturn .= "Adding paragraph-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("text_paragraph");
        $objPagelement->setStrName("text");
        $objPagelement->setStrElement("paragraph");
        $objPagelement->updateObjectToDb($this->strIndexID);
        $strElementId = $objPagelement->getSystemid();

        if($this->strContentLanguage == "de") {
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                            SET paragraph_title = 'Herzlichen Glückwunsch!',
                                paragraph_content ='Diese Installation von Kajona war erfolgreich. Wir wünschen viel Spaß mit Kajona V3.<br />
                                                Für weitere Informationen und Support besuchen Sie unsere Webseite: <a href=\"http://www.kajona.de\">www.kajona.de</a>'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
        }
        else {
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                SET paragraph_title = 'Congratulations!',
                                    paragraph_content ='This installation of Kajona was successful. Have fun using Kajona!<br />
                                                     For further information, support or proposals, please visit our website: <a href=\"http://www.kajona.de\">www.kajona.de</a>'
                                WHERE content_id = '".dbsafeString($strElementId)."'";
        }


        if($this->objDB->_query($strQuery))
            $strReturn .= "Paragraph element created.\n";
        else
            $strReturn .= "Error creating paragraph element.\n";

        /*
        $strReturn .= "Adding image-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("picture1_image");
        $objPagelement->setStrName("picture1");
        $objPagelement->setStrElement("image");
        $objPagelement->updateObjectToDb($this->strIndexID);
        $strElementId = $objPagelement->getSystemid();
         $strQuery = "UPDATE "._dbprefix_."element_bild
                            SET bild_bild = '/portal/pics/kajona/login_logo.gif'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
            if($this->objDB->_query($strQuery))
                $strReturn .= "Image element created.\n";
            else
                $strReturn .= "Error creating image element.\n";

        */

        $strReturn .= "Creating system folder...\n";
        $objFolder = new class_modul_pages_folder();
        $objFolder->setStrName("_system");
        $objFolder->updateObjectToDb(class_modul_system_module::getModuleByName("pages")->getSystemid());
        $strFolderID = $objFolder->getSystemid();
        $strReturn .= "ID of new folder: ".$strFolderID."\n";
        $strReturn .= "Creating master-page\n";
        $objPage = new class_modul_pages_page();
        $objPage->setStrName("master");
        $objPage->setStrTemplate("master.tpl");
        //set language to "" - being update by the languages sc installer later
        $objPage->setStrLanguage("");
        $objPage->updateObjectToDb($strFolderID);
        $this->strMasterID = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$this->strMasterID."\n";


        $strReturn .= "Creating error-site...\n";
        $objPage = new class_modul_pages_page();
        $objPage->setStrName("error");

        if($this->strContentLanguage == "de")
            $objPage->setStrBrowsername("Fehler");
        else
            $objPage->setStrBrowsername("Error");


        $objPage->setStrTemplate("kajona_demo.tpl");
        //set language to "" - being update by the languages sc installer later
        $objPage->setStrLanguage("");
        $objPage->updateObjectToDb($strFolderID);
        $strErrorPageId = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strErrorPageId."\n";

        $strReturn .= "Adding headline-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("headline_row");
        $objPagelement->setStrName("headline");
        $objPagelement->setStrElement("row");
        $objPagelement->updateObjectToDb($strErrorPageId);
        $strElementId = $objPagelement->getSystemid();

        if($this->strContentLanguage == "de") {
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                            SET paragraph_title = 'Fehler'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
        }
        else {
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                            SET paragraph_title = 'Error'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
        }

        if($this->objDB->_query($strQuery))
            $strReturn .= "Headline element created.\n";
        else
            $strReturn .= "Error creating headline element.\n";

        $strReturn .= "Adding paragraph-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("text_paragraph");
        $objPagelement->setStrName("text");
        $objPagelement->setStrElement("paragraph");
        $objPagelement->updateObjectToDb($strErrorPageId);
        $strElementId = $objPagelement->getSystemid();


        if($this->strContentLanguage == "de") {
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                        SET paragraph_title = 'Ein Fehler ist aufgetreten',
                           paragraph_content ='Während Ihre Anfrage ist leider ein Fehler aufgetreten.<br />
                                           Bitte versuchen Sie die letzte Aktion erneut.'
                      WHERE content_id = '".dbsafeString($strElementId)."'";
        }
        else {
             $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                SET paragraph_title = 'An error occured',
                                    paragraph_content ='Maybe the requested page doesn\'t exist anymore.<br />
                                                    Please try it again later.'
                                WHERE content_id = '".dbsafeString($strElementId)."'";
        }

        if($this->objDB->_query($strQuery))
            $strReturn .= "Paragraph element created.\n";
        else
            $strReturn .= "Error creating paragraph element.\n";





        $strReturn .= "Creating imprint-site...\n";
        $objPage = new class_modul_pages_page();
        $objPage->setStrName("imprint");
        if($this->strContentLanguage == "de")
            $objPage->setStrBrowsername("Impressum");
        else
            $objPage->setStrBrowsername("Imprint");
        $objPage->setStrTemplate("kajona_demo.tpl");
        //set language to "" - being update by the languages sc installer later
        $objPage->setStrLanguage("");
        $objPage->updateObjectToDb($strFolderID);
        $strImprintPageId = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strImprintPageId."\n";
        $strReturn .= "Adding headline-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("headline_row");
        $objPagelement->setStrName("headline");
        $objPagelement->setStrElement("row");
        $objPagelement->updateObjectToDb($strImprintPageId);
        $strElementId = $objPagelement->getSystemid();
        if($this->strContentLanguage == "de") {
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                            SET paragraph_title = 'Impressum'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
        }
        else {
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                            SET paragraph_title = 'Imprint'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
        }

        if($this->objDB->_query($strQuery))
            $strReturn .= "Headline element created.\n";
        else
            $strReturn .= "Error creating headline element.\n";

        $strReturn .= "Adding paragraph-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("text_paragraph");
        $objPagelement->setStrName("text");
        $objPagelement->setStrElement("paragraph");
        $objPagelement->updateObjectToDb($strImprintPageId);
        $strElementId = $objPagelement->getSystemid();


        if($this->strContentLanguage == "de") {
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                        SET paragraph_title = 'Impressum',
                           paragraph_content ='Bitte tragen Sie hier Ihre Kontaktdaten ein.<br />
                                           Nachname, Name<br />
                                           Straße und Hausnummer<br />
                                           PLZ, Ort<br />
                                           Telefon<br />
                                           E-Mail<br />
                                           <br />
                                           Site powered by <a href=\"http://www.kajona.de\" target=\"_blank\" title=\"Kajona³ CMS - empowering your content\">Kajona³</a><br /><a href=\"http://www.kajona.de\" target=\"_blank\" title=\"Kajona³ CMS - empowering your content\"><img src=\"portal/pics/kajona/kajona_poweredby.png\" alt=\"Kajona³\" /></a><br />
                                           '
                      WHERE content_id = '".dbsafeString($strElementId)."'";
        }
        else {
             $strQuery = "UPDATE "._dbprefix_."element_paragraph
                        SET paragraph_title = 'Imprint',
                           paragraph_content ='Please provide your contact details.<br />
                                           Name, Forename<br />
                                           Street<br />
                                           Zip, City<br />
                                           Phone<br />
                                           Mail<br />
                                           <br />
                                           Site powered by <a href=\"http://www.kajona.de\" target=\"_blank\" title=\"Kajona³ CMS - empowering your content\">Kajona³</a><br /><a href=\"http://www.kajona.de\" target=\"_blank\" title=\"Kajona³ CMS - empowering your content\"><img src=\"portal/pics/kajona/kajona_poweredby.png\" alt=\"Kajona³\" /></a><br />
                                           '
                      WHERE content_id = '".dbsafeString($strElementId)."'";
        }

        if($this->objDB->_query($strQuery))
            $strReturn .= "Paragraph element created.\n";
        else
            $strReturn .= "Error creating paragraph element.\n";




        $strReturn .= "Creating sample page...\n";
        $objPage = new class_modul_pages_page();
        $objPage->setStrName("page_1");
        if($this->strContentLanguage == "de")
            $objPage->setStrBrowsername("Beispielseite 1");
        else
            $objPage->setStrBrowsername("Sample page 1");
        $objPage->setStrTemplate("kajona_demo.tpl");
        //set language to "" - being update by the languages sc installer later
        $objPage->setStrLanguage("");
        $objPage->updateObjectToDb();
        $strSamplePageId = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strSamplePageId."\n";
        $strReturn .= "Adding headline-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("headline_row");
        $objPagelement->setStrName("headline");
        $objPagelement->setStrElement("row");
        $objPagelement->updateObjectToDb($strSamplePageId);
        $strElementId = $objPagelement->getSystemid();
        if($this->strContentLanguage == "de") {
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                            SET paragraph_title = 'Beispielseite 1'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
        }
        else {
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                            SET paragraph_title = 'Sample page 1'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
        }

        if($this->objDB->_query($strQuery))
            $strReturn .= "Headline element created.\n";
        else
            $strReturn .= "Error creating headline element.\n";

        $strReturn .= "Adding paragraph-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("text_paragraph");
        $objPagelement->setStrName("text");
        $objPagelement->setStrElement("paragraph");
        $objPagelement->updateObjectToDb($strSamplePageId);
        $strElementId = $objPagelement->getSystemid();


        if($this->strContentLanguage == "de") {
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
							SET paragraph_title = 'Standard-Absatz',
								paragraph_content ='Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.'
						 	WHERE content_id = '".dbsafeString($strElementId)."'";
        }
        else {
             $strQuery = "UPDATE "._dbprefix_."element_paragraph
							SET paragraph_title = 'Standard paragraph',
								paragraph_content ='Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.'
							WHERE content_id = '".dbsafeString($strElementId)."'";
        }

        if($this->objDB->_query($strQuery))
            $strReturn .= "Paragraph element created.\n";
        else
            $strReturn .= "Error creating paragraph element.\n";






       $strReturn .= "Creating sample subpage...\n";
        $objPage = new class_modul_pages_page();
        $objPage->setStrName("subpage_1");
        if($this->strContentLanguage == "de")
            $objPage->setStrBrowsername("Beispiel-Unterseite 1");
        else
            $objPage->setStrBrowsername("Sample subpage 1");
        $objPage->setStrTemplate("kajona_demo.tpl");
        //set language to "" - being update by the languages sc installer later
        $objPage->setStrLanguage("");
        $objPage->updateObjectToDb();
        $strSampleSubPageId = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strSampleSubPageId."\n";
        $strReturn .= "Adding headline-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("headline_row");
        $objPagelement->setStrName("headline");
        $objPagelement->setStrElement("row");
        $objPagelement->updateObjectToDb($strSampleSubPageId);
        $strElementId = $objPagelement->getSystemid();
        if($this->strContentLanguage == "de") {
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                            SET paragraph_title = 'Beispiel-Unterseite 1'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
        }
        else {
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                            SET paragraph_title = 'Sample subpage 1'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
        }

        if($this->objDB->_query($strQuery))
            $strReturn .= "Headline element created.\n";
        else
            $strReturn .= "Error creating headline element.\n";

        $strReturn .= "Adding paragraph-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("text_paragraph");
        $objPagelement->setStrName("text");
        $objPagelement->setStrElement("paragraph");
        $objPagelement->updateObjectToDb($strSampleSubPageId);
        $strElementId = $objPagelement->getSystemid();


        if($this->strContentLanguage == "de") {
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
							SET paragraph_title = 'Standard-Absatz auf Unterseite',
								paragraph_content ='Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.'
						 	WHERE content_id = '".dbsafeString($strElementId)."'";
        }
        else {
             $strQuery = "UPDATE "._dbprefix_."element_paragraph
							SET paragraph_title = 'Standard paragraph on subpage',
								paragraph_content ='Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.'
							WHERE content_id = '".dbsafeString($strElementId)."'";
        }

        if($this->objDB->_query($strQuery))
            $strReturn .= "Paragraph element created.\n";
        else
            $strReturn .= "Error creating paragraph element.\n";

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
?>