<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                              *
********************************************************************************************************/


/**
 * Installer of the form samplecontent
 *
 * @package element_formular
 */
class class_installer_sc_formular implements interface_sc_installer  {

    /**
     * @var class_db
     */
    private $objDB;
    private $strContentLanguage;

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     */
    public function install() {
        $strReturn = "";

        //fetch navifolder-id
        $strNaviFolderId = "";
        $arrFolder = class_module_pages_folder::getFolderList();
        foreach($arrFolder as $objOneFolder)
            if($objOneFolder->getStrName() == "mainnavigation")
                $strNaviFolderId = $objOneFolder->getSystemid();

        $strReturn .= "Creating new page contact...\n";

        $objPage = new class_module_pages_page();
        $objPage->setStrName("contact");
        $objPage->setStrBrowsername("Contact");
        $objPage->setStrTemplate("standard.tpl");
        $objPage->updateObjectToDb($strNaviFolderId);

        $strPageId = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strPageId."\n";
        $strReturn .= "Adding pagelement to new page\n";

        $objPagelement = new class_module_pages_pageelement();
        if(class_module_pages_element::getElement("form") != null) {
            $objPagelement->setStrPlaceholder("special_news|guestbook|downloads|gallery|galleryRandom|form|tellafriend|maps|search|navigation|faqs|postacomment|votings|userlist|rssfeed|tagto|portallogin|portalregistration|portalupload|directorybrowser|lastmodified|tagcloud|downloadstoplist|flash|mediaplayer|tags|eventmanager");
            $objPagelement->setStrName("special");
            $objPagelement->setStrElement("form");
            $objPagelement->updateObjectToDb($strPageId);
            $strElementId = $objPagelement->getSystemid();

            $arrParams = array();
            if($this->strContentLanguage == "de") {
                $arrParams[] = "class_formular_contact.php";
                $arrParams[] = class_module_system_setting::getConfigValue("_system_admin_email_");
                $arrParams[] = "contact.tpl";
                $arrParams[] = $strElementId;
            }
            else {
                $arrParams[] = "class_formular_contact.php";
                $arrParams[] = class_module_system_setting::getConfigValue("_system_admin_email_");
                $arrParams[] = "contact.tpl";
                $arrParams[] = $strElementId;
            }

            $strQuery = "UPDATE "._dbprefix_."element_formular
                        SET formular_class = ?,
                            formular_email = ?,
                            formular_template = ?
                        WHERE content_id = ?";

            if($this->objDB->_pQuery($strQuery, $arrParams))
                $strReturn .= "Contact element created.\n";
            else
                $strReturn .= "Error creating Contact element.\n";

        }


        $strReturn .= "Adding headline-element to new page\n";
        if(class_module_pages_element::getElement("row") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("headline_row");
            $objPagelement->setStrName("headline");
            $objPagelement->setStrElement("row");
            $objPagelement->updateObjectToDb($strPageId);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                SET paragraph_title = ?
                                WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, array("Contact", $strElementId)))
                $strReturn .= "Headline element created.\n";
            else
                $strReturn .= "Error creating headline element.\n";

        }

        $strReturn .= "Adding paragraph-element to new page\n";
        if(class_module_pages_element::getElement("paragraph") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("content_paragraph");
            $objPagelement->setStrName("content");
            $objPagelement->setStrElement("paragraph");
            $objPagelement->updateObjectToDb($strPageId);
            $strElementId = $objPagelement->getSystemid();

            $arrParams = array();
            if($this->strContentLanguage == "de") {
                $arrParams[] = "";
                $arrParams[] = "Hinweis: Das Formular sendet per default die Anfragen an die E-Mail Adresse des Administrators.<br />
                                Um diese Adresse zu ändern öffnen Sie bitte die Seite in der Administration und bearbeiten das Seitenelement &quot;Formular&quot;.<br /><br />";
                $arrParams[] = "";
                $arrParams[] = $strElementId;
            }
            else {
                $arrParams[] = "";
                $arrParams[] = "Note: By default, the form sends the messages to the administators email-address.<br />
                               To change this address, open the current page using the administration and edit the page-element &quot;form&quot;.<br /><br />";
                $arrParams[] = "";
                $arrParams[] = $strElementId;
            }

            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                SET paragraph_title = ?,
                                    paragraph_content = ?,
                                    paragraph_image = ?
                                WHERE content_id = ?";

            if($this->objDB->_pQuery($strQuery, $arrParams, array(true, false)))
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
