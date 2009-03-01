<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                              *
********************************************************************************************************/


include_once(_systempath_."/interface_sc_installer.php");

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


        $strReturn .= "Creating new gallery...\n";
        include_once(_systempath_."/class_modul_gallery_gallery.php");
        include_once(_systempath_."/class_modul_gallery_pic.php");
        $objGallery = new class_modul_gallery_gallery();
        $objGallery->setStrTitle("Sample Gallery");
        $objGallery->setStrPath("/portal/pics/upload");
        $objGallery->saveObjectToDb();
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
        //set language to "" - being update by the languages sc installer later
        $objPage->setStrLanguage("");
        $objPage->saveObjectToDb();

        $strGalleryPageId = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strGalleryPageId."\n";
        $strReturn .= "Adding pagelement to new page\n";

        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("bilder_gallery");
        $objPagelement->setStrName("bilder");
        $objPagelement->setStrElement("gallery");
        $objPagelement->saveObjectToDb($strGalleryPageId, "bilder_gallery", _dbprefix_."element_gallery", "first");
        $strElementId = $objPagelement->getSystemid();
        $strQuery = "UPDATE "._dbprefix_."element_gallery
                        SET gallery_id = '".dbsafeString($strGalleryID)."',
                            gallery_mode = 0,
                            gallery_template = 'gallery.tpl',
                            gallery_maxh_p = 110,
                            gallery_maxh_d = 600,
                            gallery_maxw_p = 150,
                            gallery_maxw_d = 600,
                            gallery_maxh_m = 45,
                            gallery_maxw_m = 70,
                            gallery_nrow = 3,
                            gallery_imagesperpage = 6,
                            gallery_text = '(c) kajona.de',
                            gallery_text_x = 5,
                            gallery_text_y = 15
                        WHERE content_id = '".dbsafeString($strElementId)."'";
        if($this->objDB->_query($strQuery))
            $strReturn .= "Gallery element created.\n";
        else
            $strReturn .= "Error creating Gallery element.\n";



        $strReturn .= "Adding headline-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("headline_row");
        $objPagelement->setStrName("headline");
        $objPagelement->setStrElement("row");
        $objPagelement->saveObjectToDb($strGalleryPageId, "headline_row", _dbprefix_."element_absatz", "first");
        $strElementId = $objPagelement->getSystemid();
        $strQuery = "UPDATE "._dbprefix_."element_absatz
                            SET absatz_titel = 'Gallery'
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
        $objPagelement->saveObjectToDb($strGalleryPageId, "text_paragraph", _dbprefix_."element_absatz", "first");
        $strElementId = $objPagelement->getSystemid();

        if($this->strContentLanguage == "de") {
            $strQuery = "UPDATE "._dbprefix_."element_absatz
                            SET absatz_titel = '',
                                absatz_inhalt ='Alle Beispielbilder &copy by kajona.de'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
        }
        else {
            $strQuery = "UPDATE "._dbprefix_."element_absatz
                                SET absatz_titel = '',
                                    absatz_inhalt ='All sample images &copy; by kajona.de'
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

	        include_once(_systempath_."/class_modul_navigation_tree.php");
	        include_once(_systempath_."/class_modul_navigation_point.php");
	        $objNavi = class_modul_navigation_tree::getNavigationByName("mainnavigation");
	        $strTreeId = $objNavi->getSystemid();

	        $objNaviPoint = new class_modul_navigation_point();
	        $objNaviPoint->setStrName("Gallery");
	        $objNaviPoint->setStrPageI("gallery");
	        $objNaviPoint->saveObjectToDb($strTreeId);
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
        return "gallery";
    }

}
?>