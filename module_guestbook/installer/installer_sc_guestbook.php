<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/
use Kajona\Pages\System\PagesFolder;
use Kajona\Pages\System\PagesPage;


/**
 * Installer of the guestbook samplecontent
 *
 * @package module_guestbook
 */
class class_installer_sc_guestbook implements interface_sc_installer  {

    /**
     * @var class_db
     */
    private $objDB;
    private $strContentLanguage;

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     * @return string
     */
    public function install() {
        $strReturn = "";

        //fetch navifolder-id
        $strNaviFolderId = "";
        $arrFolder = PagesFolder::getFolderList();
        foreach($arrFolder as $objOneFolder)
            if($objOneFolder->getStrName() == "mainnavigation")
                $strNaviFolderId = $objOneFolder->getSystemid();

        $strReturn .= "Creating new guestbook...\n";
        $objGuestbook = new class_module_guestbook_guestbook();
        $objGuestbook->setStrGuestbookTitle("Guestbook");
        $objGuestbook->setIntGuestbookModerated(0);
        $objGuestbook->updateObjectToDb();
        $strGuestbookID = $objGuestbook->getSystemid();
        $strReturn .= "ID of new guestbook: ".$strGuestbookID."\n";


        $strReturn .= "Creating new guestbook page...\n";

        $objPage = new PagesPage();
        $objPage->setStrName("guestbook");
        $objPage->setStrBrowsername("Guestbook");
        $objPage->setStrTemplate("standard.tpl");
        $objPage->updateObjectToDb($strNaviFolderId);

        $strGuestbookpageID = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strGuestbookpageID."\n";
        $strReturn .= "Adding pagelement to new page\n";
        
        if(class_module_pages_element::getElement("guestbook") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("special_news|guestbook|downloads|gallery|galleryRandom|form|tellafriend|maps|search|navigation|faqs|postacomment|votings|userlist|rssfeed|tagto|portallogin|portalregistration|portalupload|directorybrowser|lastmodified|tagcloud|downloadstoplist|flash|mediaplayer|tags|eventmanager");
            $objPagelement->setStrName("special");
            $objPagelement->setStrElement("guestbook");
            $objPagelement->updateObjectToDb($strGuestbookpageID);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_guestbook
                            SET guestbook_id = ?,
                                guestbook_template = ?,
                                guestbook_amount = ?
                            WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, array($strGuestbookID, "guestbook.tpl", 5, $strElementId)))
                $strReturn .= "Guestbookelement created.\n";
            else
                $strReturn .= "Error creating Guestbookelement.\n";
        
        }

        $strReturn .= "Adding headline-element to new page\n";
        
        if(class_module_pages_element::getElement("row") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("headline_row");
            $objPagelement->setStrName("headline");
            $objPagelement->setStrElement("row");
            $objPagelement->updateObjectToDb($strGuestbookpageID);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                SET paragraph_title = ?
                                WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, array("Guestbook", $strElementId)))
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
        return "guestbook";
    }

}
