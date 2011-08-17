<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                              *
********************************************************************************************************/


/**
 * Installer of the gallery samplecontent
 *
 * @package modul_gallery
 */
class class_installer_sc_gallery implements interface_sc_installer  {

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
        $arrFolder = class_modul_pages_folder::getFolderList();
        foreach($arrFolder as $objOneFolder)
            if($objOneFolder->getStrName() == "mainnavigation")
                $strNaviFolderId = $objOneFolder->getSystemid();


        $strReturn .= "Creating new gallery...\n";
        $objGallery = new class_modul_gallery_gallery();
        $objGallery->setStrTitle("Sample Gallery");
        $objGallery->setStrPath("/portal/pics/upload");
        $objGallery->updateObjectToDb();
        $strGalleryID = $objGallery->getSystemid();

        $strReturn .= "Sync gallery..\n";
        class_modul_gallery_pic::syncRecursive($objGallery->getSystemid(), $objGallery->getStrPath());

        $strReturn .= "Modify rights to allow guests to rate images...\n";
        class_carrier::getInstance()->getObjRights()->addGroupToRight(_guests_group_id_, $objGallery->getSystemid(), "right2");


        $strReturn .= "Creating new gallery page...\n";

        $objPage = new class_modul_pages_page();
        $objPage->setStrName("gallery");
        $objPage->setStrBrowsername("Gallery");
        $objPage->setStrTemplate("kajona_demo.tpl");
        $objPage->updateObjectToDb($strNaviFolderId);

        $strGalleryPageId = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strGalleryPageId."\n";
        $strReturn .= "Adding pagelement to new page\n";

        if(class_modul_pages_element::getElement("gallery") != null) {
            $objPagelement = new class_modul_pages_pageelement();
            $objPagelement->setStrPlaceholder("bilder_gallery");
            $objPagelement->setStrName("bilder");
            $objPagelement->setStrElement("gallery");
            $objPagelement->updateObjectToDb($strGalleryPageId);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_gallery
                            SET gallery_id = ?,
                                gallery_mode = ?,
                                gallery_template = ?,
                                gallery_maxh_p = ?,
                                gallery_maxh_d = ?,
                                gallery_maxw_p = ?,
                                gallery_maxw_d = ?,
                                gallery_maxh_m = ?,
                                gallery_maxw_m = ?,
                                gallery_imagesperpage = ?,
                                gallery_text = ?,
                                gallery_text_x = ?,
                                gallery_text_y = ?
                            WHERE content_id = ? ";
            if($this->objDB->_pQuery($strQuery, array($strGalleryID, 0, "gallery_imagelightbox.tpl", 110, 600, 150, 600, 45, 70, 0, "(c) kajona.de", 5, 15, $strElementId)))
                $strReturn .= "Gallery element created.\n";
            else
                $strReturn .= "Error creating Gallery element.\n";
        }


        $strReturn .= "Adding headline-element to new page\n";
        
        if(class_modul_pages_element::getElement("row") != null) {
            $objPagelement = new class_modul_pages_pageelement();
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
        if(class_modul_pages_element::getElement("paragraph") != null) {
            $objPagelement = new class_modul_pages_pageelement();
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
        return "gallery";
    }

}
?>