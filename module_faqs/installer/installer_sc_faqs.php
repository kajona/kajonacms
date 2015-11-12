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
 * @package module_faqs
 */
class class_installer_sc_faqs implements interface_sc_installer  {

    /**
     * @var class_db
     */
    private $objDB;
    private $strContentLanguage;

    private $strIndexID = "";

    public function install() {
        $strReturn = "";

        //fetch navifolder-id
        $strNaviFolderId = "";
        $arrFolder = PagesFolder::getFolderList();
        foreach($arrFolder as $objOneFolder)
            if($objOneFolder->getStrName() == "mainnavigation")
                $strNaviFolderId = $objOneFolder->getSystemid();


        //search the index page
        $objIndex = PagesPage::getPageByName("index");
        if($objIndex != null)
            $this->strIndexID = $objIndex->getSystemid();

        $strReturn .= "Creating faqs\n";
        $objFaq1 = new class_module_faqs_faq();
        $objFaq2 = new class_module_faqs_faq();

        if($this->strContentLanguage == "de") {
        	$objFaq1->setStrQuestion("Was ist Kajona?");
        	$objFaq1->setStrAnswer("Kajona ist ein Open Source Content Management System basierend auf PHP und einer Datenbank. Dank der modularen Bauweise ist Kajona einfach erweiter- und anpassbar.");

        	$objFaq2->setStrQuestion("Wer entwickelt Kajona, wo gibt es weitere Infos?");
        	$objFaq2->setStrAnswer("Kajona wird von einer Open Source Community entwickelt. Da Kajona ständig weiterentwickelt wird, sind wir jederzeit auf der Suche nach Helfern, seien es Programmierer, Grafiker, Betatester und auch Anwender. Weitere Informationen hierzu finden Sie auf <a href=\"http://www.kajona.de\">www.kajona.de</a>.");
        }
        else {
        	$objFaq1->setStrQuestion("What is Kajona?");
            $objFaq1->setStrAnswer("Kajona is an open source content management system based on PHP and a database. Due to it's modular design, it can be extended and adopted very easily.");

            $objFaq2->setStrQuestion("Who develops Kajona, where can I find more infos?");
            $objFaq2->setStrAnswer("Kajona is being developed by an open source community. Since Kajona is still being developed, we are searching for contributors. Further information can be found at <a href=\"http://www.kajona.de\">www.kajona.de</a>.");
        }

        $strReturn .= "Saving faqs...\n";
        $objFaq1->updateObjectToDb();
        $objFaq2->updateObjectToDb();


        $strReturn .= "Creating faqs-page\n";
        $objPage = new PagesPage();
        $objPage->setStrName("faqs");
        $objPage->setStrBrowsername("FAQs");
        $objPage->setStrTemplate("standard.tpl");
        $objPage->updateObjectToDb($strNaviFolderId);

        $strFaqsPageId = $objPage->getSystemid();

        $strReturn .= "ID of new page: ".$strFaqsPageId."\n";
        $strReturn .= "Adding faqs-element to new page\n";
        if(class_module_pages_element::getElement("faqs") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("special_news|guestbook|downloads|gallery|galleryRandom|form|tellafriend|maps|search|navigation|faqs|postacomment|votings|userlist|rssfeed|tagto|portallogin|portalregistration|portalupload|directorybrowser|lastmodified|tagcloud|downloadstoplist|flash|mediaplayer|tags|eventmanager");
            $objPagelement->setStrName("special");
            $objPagelement->setStrElement("faqs");
            $objPagelement->updateObjectToDb($strFaqsPageId);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_faqs
                            SET faqs_category= ?,
                                faqs_template = ?
                          WHERE content_id = ? ";
            if($this->objDB->_pQuery($strQuery, array(0, "demo_foldable.tpl", $strElementId)))
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
            if($this->objDB->_pQuery($strQuery, array("FAQs", $strElementId)))
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
