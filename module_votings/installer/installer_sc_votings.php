<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                       *
********************************************************************************************************/
use Kajona\Pages\System\PagesFolder;
use Kajona\Pages\System\PagesPage;


/**
 * Installer of the faqs samplecontent
 *
 * @package module_votings
 */
class class_installer_sc_votings implements interface_sc_installer  {

    /**
     * @var class_db
     */
    private $objDB;
    private $strContentLanguage;


    public function install() {
        $strReturn = "";

        //fetch navifolder-id
        $strNaviFolderId = "";
        $arrFolder = PagesFolder::getFolderList();
        foreach($arrFolder as $objOneFolder)
            if($objOneFolder->getStrName() == "mainnavigation")
                $strNaviFolderId = $objOneFolder->getSystemid();


        $strReturn .= "Creating voting\n";

        $objVoting = new class_module_votings_voting();
        if($this->strContentLanguage == "de")
            $objVoting->setStrTitle("Wie gefällt Ihnen unsere neue Webseite?");
        else
            $objVoting->setStrTitle("How do you like our new website?");

        $objVoting->updateObjectToDb();
        $objAnswer1 = new class_module_votings_answer();
        $objAnswer2 = new class_module_votings_answer();
        $objAnswer3 = new class_module_votings_answer();
        if($this->strContentLanguage == "de") {
            $objAnswer1->setStrText("Gefällt mir sehr gut!");
            $objAnswer2->setStrText("Ausbaufähig...");
            $objAnswer3->setStrText("Brennt im Kühlschrank immer Licht?");
        }
        else {
            $objAnswer1->setStrText("I like it!");
            $objAnswer2->setStrText("Well, work on it");
            $objAnswer3->setStrText("Sunglasses at night");
        }

        $objAnswer1->updateObjectToDb($objVoting->getSystemid());
        $objAnswer2->updateObjectToDb($objVoting->getSystemid());
        $objAnswer3->updateObjectToDb($objVoting->getSystemid());



        $strReturn .= "Creating voting-page\n";
        $objPage = new PagesPage();
        $objPage->setStrName("votings");
        $objPage->setStrBrowsername("Votings");
        $objPage->setStrTemplate("standard.tpl");
        $objPage->updateObjectToDb($strNaviFolderId);

        $strFaqsPageId = $objPage->getSystemid();

        $strReturn .= "ID of new page: ".$strFaqsPageId."\n";
        $strReturn .= "Adding votings-element 1 to new page\n";
        if(class_module_pages_element::getElement("faqs") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("special_news|guestbook|downloads|gallery|galleryRandom|form|tellafriend|maps|search|navigation|faqs|postacomment|votings|userlist|rssfeed|tagto|portallogin|portalregistration|portalupload|directorybrowser|lastmodified|tagcloud|downloadstoplist|flash|mediaplayer|tags|eventmanager");
            $objPagelement->setStrName("special");
            $objPagelement->setStrElement("votings");
            $objPagelement->updateObjectToDb($strFaqsPageId);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_universal
                            SET ".$this->objDB->encloseColumnName("int1")." = ?,
                                ".$this->objDB->encloseColumnName("char1")." = ?,
                                ".$this->objDB->encloseColumnName("char2")." = ?
                          WHERE content_id = ? ";
            if($this->objDB->_pQuery($strQuery, array(0, $objVoting->getSystemid(), "votings.tpl", $strElementId)))
                $strReturn .= "faqselement created.\n";
            else
                $strReturn .= "Error creating faqselement.\n";
        }

        $strReturn .= "Adding votings-element 2 to new page\n";
        if(class_module_pages_element::getElement("faqs") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("special_news|guestbook|downloads|gallery|galleryRandom|form|tellafriend|maps|search|navigation|faqs|postacomment|votings|userlist|rssfeed|tagto|portallogin|portalregistration|portalupload|directorybrowser|lastmodified|tagcloud|downloadstoplist|flash|mediaplayer|tags|eventmanager");
            $objPagelement->setStrName("special");
            $objPagelement->setStrElement("votings");
            $objPagelement->updateObjectToDb($strFaqsPageId);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_universal
                            SET ".$this->objDB->encloseColumnName("int1")." = ?,
                                ".$this->objDB->encloseColumnName("char1")." = ?,
                                ".$this->objDB->encloseColumnName("char2")." = ?
                          WHERE content_id = ? ";
            if($this->objDB->_pQuery($strQuery, array(1, $objVoting->getSystemid(), "votings.tpl", $strElementId)))
                $strReturn .= "faqselement created.\n";
            else
                $strReturn .= "Error creating faqselement.\n";
        }

        $strReturn .= "Adding headline-element to new page\n";
        
        if(class_module_pages_element::getElement("row") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("headline_row");
            $objPagelement->setStrName("headline");
            $objPagelement->setStrElement("row");
            $objPagelement->updateObjectToDb($strFaqsPageId);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                             SET paragraph_title = ?
                           WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, array("Votings", $strElementId)))
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
        return "faqs";
    }

}
