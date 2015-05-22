<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/


/**
 * Installer of the gallery samplecontent
 *
 * @package module_mediamanager
 */
class class_installer_sc_gallery implements interface_sc_installer  {

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

        if(class_module_system_module::getModuleByName("mediamanager") == null)
            return "Mediamanger not installed, skipping element\n";


        $strReturn = "";

        //fetch navifolder-id
        $strNaviFolderId = "";
        $arrFolder = class_module_pages_folder::getFolderList();
        foreach($arrFolder as $objOneFolder)
            if($objOneFolder->getStrName() == "mainnavigation")
                $strNaviFolderId = $objOneFolder->getSystemid();


        $strReturn .= "Creating new gallery...\n";
        $objGallery = new class_module_mediamanager_repo();
        $objGallery->setStrTitle("Sample Gallery");
        $objGallery->setStrPath(_filespath_."/images/samples");
        $objGallery->setStrUploadFilter(".jpg,.png,.gif,.jpeg");
        $objGallery->setStrViewFilter(".jpg,.png,.gif,.jpeg");
        $objGallery->updateObjectToDb();
        $objGallery->syncRepo();
        $strGalleryID = $objGallery->getSystemid();

        $strReturn .= "Modify rights to allow guests to rate images...\n";
        class_carrier::getInstance()->getObjRights()->addGroupToRight(class_module_system_setting::getConfigValue("_guests_group_id_"), $objGallery->getSystemid(), "right3");


        $strReturn .= "Creating new gallery page...\n";

        $objPage = new class_module_pages_page();
        $objPage->setStrName("gallery");
        $objPage->setStrBrowsername("Gallery");
        $objPage->setStrTemplate("standard.tpl");
        $objPage->updateObjectToDb($strNaviFolderId);

        $strGalleryPageId = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strGalleryPageId."\n";
        $strReturn .= "Adding pagelement to new page\n";

        if(class_module_pages_element::getElement("gallery") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("special_news|guestbook|downloads|gallery|galleryRandom|form|tellafriend|maps|search|navigation|faqs|postacomment|votings|userlist|rssfeed|tagto|portallogin|portalregistration|portalupload|directorybrowser|lastmodified|tagcloud|downloadstoplist|flash|mediaplayer|tags|eventmanager");
            $objPagelement->setStrName("special");
            $objPagelement->setStrElement("gallery");
            $objPagelement->updateObjectToDb($strGalleryPageId);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_gallery
                            SET gallery_id = ?,
                                gallery_mode = ?,
                                gallery_template = ?,
                                gallery_maxh_d = ?,
                                gallery_maxw_d = ?,
                                gallery_imagesperpage = ?,
                                gallery_text = ?,
                                gallery_text_x = ?,
                                gallery_text_y = ?
                            WHERE content_id = ? ";
            if($this->objDB->_pQuery($strQuery, array($strGalleryID, 0, "gallery_imagelightbox.tpl", 600, 600, 0, "(c) kajona.de", 5, 15, $strElementId)))
                $strReturn .= "Gallery element created.\n";
            else
                $strReturn .= "Error creating Gallery element.\n";
        }


        $strReturn .= "Adding headline-element to new page\n";

        if(class_module_pages_element::getElement("row") != null) {
            $objPagelement = new class_module_pages_pageelement();
            $objPagelement->setStrPlaceholder("headline_row");
            $objPagelement->setStrName("headline");
            $objPagelement->setStrElement("row");
            $objPagelement->updateObjectToDb($strGalleryPageId);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                SET paragraph_title = ?
                                WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, array("Gallery", $strElementId)))
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
            $objPagelement->updateObjectToDb($strGalleryPageId);
            $strElementId = $objPagelement->getSystemid();

            $arrParams = array();
            if($this->strContentLanguage == "de") {
                $arrParams[] = "";
                $arrParams[] = "Alle Beispielbilder &copy; by kajona.de";
                $arrParams[] = $strElementId;
            }
            else {
                $arrParams[] = "";
                $arrParams[] = "All sample images &copy; by kajona.de";
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
        return "mediamanager";
    }

}
