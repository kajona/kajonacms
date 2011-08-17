<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                         *
********************************************************************************************************/

/**
 * Installer of the postacomment samplecontent
 *
 * @package modul_postacomment
 */
class class_installer_sc_postacomment implements interface_sc_installer  {

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


        $strReturn .= "Creating new postacomment page...\n";

        $objPage = new class_modul_pages_page();
        $objPage->setStrName("postacomment");
        $objPage->setStrBrowsername("Postacomment Sample");
        $objPage->setStrTemplate("kajona_demo.tpl");
        $objPage->updateObjectToDb($strNaviFolderId);

        $strPostacommentPageID = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strPostacommentPageID."\n";
        $strReturn .= "Adding pagelement to new page\n";
        
        if(class_modul_pages_element::getElement("postacomment") != null) {
            $objPagelement = new class_modul_pages_pageelement();
            $objPagelement->setStrPlaceholder("comments_postacomment");
            $objPagelement->setStrName("comments");
            $objPagelement->setStrElement("postacomment");
            $objPagelement->updateObjectToDb($strPostacommentPageID);
            $strElementId = $objPagelement->getSystemid();

            $strQuery = "UPDATE "._dbprefix_."element_universal
                                SET char1 = ?
                                WHERE content_id = ?";

            if($this->objDB->_pQuery($strQuery, array("postacomment_classic.tpl", $strElementId)))
                $strReturn .= "Postacomment element created.\n";
            else
                $strReturn .= "Error creating Postacomment element.\n";
        }

        $strReturn .= "Adding headline-element to new page\n";
        if(class_modul_pages_element::getElement("row") != null) {
            $objPagelement = new class_modul_pages_pageelement();
            $objPagelement->setStrPlaceholder("headline_row");
            $objPagelement->setStrName("headline");
            $objPagelement->setStrElement("row");
            $objPagelement->updateObjectToDb($strPostacommentPageID);
            $strElementId = $objPagelement->getSystemid();
            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                SET paragraph_title = ?
                                WHERE content_id = ?";
            if($this->objDB->_pQuery($strQuery, array("Postacomment Sample", $strElementId)))
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
            $objPagelement->updateObjectToDb($strPostacommentPageID);
            $strElementId = $objPagelement->getSystemid();

            $arrParams = array();
            if($this->strContentLanguage == "de") {
                $arrParams[] = "";
                $arrParams[] = "Über das unten stehende Formular kann dieser Seite ein Kommentar hinzugefügt werden. Um die Ajax-Funktionen dieses Moduls 
                                zu nutzen, kann über die Administration das Template des Postacomment-Seitenelements verändert werden.";
                $arrParams[] = $strElementId;
            }
            else {
                $arrParams[] = "";
                $arrParams[] = "By using the form below, comments may be added to the current page. To make use of the ajax-features of this module,
                                switch the template to be used by the postacomment-pageelement by using the admin-backend.";
                $arrParams[] = $strElementId;
            }

            $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                    SET paragraph_title = ?,
                                        paragraph_content = ?
                                  WHERE content_id = ? ";

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
        return "postacomment";
    }

}
?>