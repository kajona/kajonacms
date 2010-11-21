<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                              *
********************************************************************************************************/


/**
 * Installer of the form samplecontent
 *
 * @package modul_pages
 */
class class_installer_sc_formular implements interface_sc_installer  {

    private $objDB;
    private $strContentLanguage;

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     */
    public function install() {
        $strReturn = "";

        $strReturn .= "Creating new page contact...\n";

        $objPage = new class_modul_pages_page();
        $objPage->setStrName("contact");
        $objPage->setStrBrowsername("Contact");
        $objPage->setStrTemplate("kajona_demo.tpl");
        //set language to "" - being update by the languages sc installer later
        $objPage->setStrLanguage("");
        $objPage->updateObjectToDb();

        $strPageId = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strPageId."\n";
        $strReturn .= "Adding pagelement to new page\n";

        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("formular_form|tellafriend");
        $objPagelement->setStrName("formular");
        $objPagelement->setStrElement("form");
        $objPagelement->updateObjectToDb($strPageId);
        $strElementId = $objPagelement->getSystemid();

        if($this->strContentLanguage == "de") {
            $strQuery = "UPDATE "._dbprefix_."element_formular
                        SET formular_class = 'class_formular_kontakt.php',
                            formular_email = '"._system_admin_email_."',
                            formular_template = 'contact.tpl',
                            formular_error = 'Es ist ein Fehler aufgetreten.',
                            formular_success = 'Vielen Dank für die Nachricht!'
                        WHERE content_id = '".dbsafeString($strElementId)."'";
        }
        else {
            $strQuery = "UPDATE "._dbprefix_."element_formular
                        SET formular_class = 'class_formular_kontakt.php',
                            formular_email = '"._system_admin_email_."',
                            formular_template = 'contact.tpl',
                            formular_error = 'An error occured.',
                            formular_success = 'Thank you for your message.'
                        WHERE content_id = '".dbsafeString($strElementId)."'";
        }

        
        if($this->objDB->_query($strQuery))
            $strReturn .= "Contact element created.\n";
        else
            $strReturn .= "Error creating Contact element.\n";



        $strReturn .= "Adding headline-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("headline_row");
        $objPagelement->setStrName("headline");
        $objPagelement->setStrElement("row");
        $objPagelement->updateObjectToDb($strPageId);
        $strElementId = $objPagelement->getSystemid();
        $strQuery = "UPDATE "._dbprefix_."element_paragraph
                            SET paragraph_title = 'Contact'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
        if($this->objDB->_query($strQuery))
            $strReturn .= "Headline element created.\n";
        else
            $strReturn .= "Error creating headline element.\n";




        $strReturn .= "Adding paragraph-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("text_paragraph");
        $objPagelement->setStrName("text");
        $objPagelement->setStrElement("paragraph");
        $objPagelement->updateObjectToDb($strPageId);
        $strElementId = $objPagelement->getSystemid();

        if($this->strContentLanguage == "de") {
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                            SET paragraph_title = '',
                                paragraph_content ='Hinweis: Das Formular sendet per default die Anfragen an die E-Mail Adresse des Administrators.<br />
                                                    Um diese Adresse zu ändern öffnen Sie bitte die Seite in der Administration und bearbeiten das Seitenelement &quot;Formular&quot;.<br /><br />',
                                paragraph_image = ''
                            WHERE content_id = '".dbsafeString($strElementId)."'";
        }
        else {
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                SET paragraph_title = '',
                                    paragraph_content ='Note: By default, the form sends the messages to the administators email-address.<br />
                                                              To change this address, open the current page using the administration and edit the page-element &quot;form&quot;.<br /><br />',
                                    paragraph_image = ''
                                WHERE content_id = '".dbsafeString($strElementId)."'";
        }


        if($this->objDB->_query($strQuery))
            $strReturn .= "Paragraph element created.\n";
        else
            $strReturn .= "Error creating paragraph element.\n";



        $strReturn .= "Creating Navigation-Entry...\n";
        //navigations installed?
        try {
            $objModule = class_modul_system_module::getModuleByName("navigation", true);
        }
        catch (class_exception $objException) {
            $objModule = null;
        }
        if($objModule != null) {

	        $objNavi = class_modul_navigation_tree::getNavigationByName("mainnavigation");
	        $strTreeId = $objNavi->getSystemid();

	        $objNaviPoint = new class_modul_navigation_point();
	        $objNaviPoint->setStrName("Contact");
	        $objNaviPoint->setStrPageI("contact");
	        $objNaviPoint->updateObjectToDb($strTreeId);
	        $strReturn .= "ID of new navigation point: ".$objNaviPoint->getSystemid()."\n";
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
?>