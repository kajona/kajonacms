<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*                                                                                                       *
*   class_installer_sc_postacomment.php                                                                 *
*   Interface of the postacomment samplecontent                                                         *
*                                                                                                       *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                         *
********************************************************************************************************/


include_once(_systempath_."/interface_sc_installer.php");

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


        $strReturn .= "Creating new postacomment page...\n";

        $objPage = new class_modul_pages_page();
        $objPage->setStrName("postacomment");
        $objPage->setStrBrowsername("Postacomment Sample");
        $objPage->setStrTemplate("kajona_demo.tpl");
        $objPage->saveObjectToDb();

        $strPostacommentPageID = $objPage->getSystemid();
        $strReturn .= "ID of new page: ".$strPostacommentPageID."\n";
        $strReturn .= "Adding pagelement to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("comments_postacomment");
        $objPagelement->setStrName("comments");
        $objPagelement->setStrElement("postacomment");
        $objPagelement->saveObjectToDb($strPostacommentPageID, "comments_postacomment", _dbprefix_."element_universal", "first");
        $strElementId = $objPagelement->getSystemid();
        
        if($this->strContentLanguage == "de") {
	        $strQuery = "UPDATE "._dbprefix_."element_universal
	                        SET char1 ='postacomment_classic_de.tpl'
	                        WHERE content_id = '".dbsafeString($strElementId)."'";
        }
        else {
	        $strQuery = "UPDATE "._dbprefix_."element_universal
	                        SET char1 ='postacomment_classic_en.tpl'
	                        WHERE content_id = '".dbsafeString($strElementId)."'";
        }
        if($this->objDB->_query($strQuery))
            $strReturn .= "Postacomment element created.\n";
        else
            $strReturn .= "Error creating Postacomment element.\n";

        $strReturn .= "Adding headline-element to new page\n";
        $objPagelement = new class_modul_pages_pageelement();
        $objPagelement->setStrPlaceholder("headline_row");
        $objPagelement->setStrName("headline");
        $objPagelement->setStrElement("row");
        $objPagelement->saveObjectToDb($strPostacommentPageID, "headline_row", _dbprefix_."element_absatz", "first");
        $strElementId = $objPagelement->getSystemid();
        $strQuery = "UPDATE "._dbprefix_."element_absatz
                            SET absatz_titel = 'Postacomment Sample'
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
        $objPagelement->saveObjectToDb($strPostacommentPageID, "text_paragraph", _dbprefix_."element_absatz", "first");
        $strElementId = $objPagelement->getSystemid();

        if($this->strContentLanguage == "de") {
            $strQuery = "UPDATE "._dbprefix_."element_absatz
                            SET absatz_titel = '',
                                absatz_inhalt ='Über das unten stehende Formular kann dieser Seite ein Kommentar hinzugefügt werden. Um die Ajax-Funktionen dieses Moduls
                                                zu nutzen, kann über die Administration das Template des Postacomment-Seitenelements verändert werden.'
                            WHERE content_id = '".dbsafeString($strElementId)."'";
        }
        else {
            $strQuery = "UPDATE "._dbprefix_."element_absatz
                                SET absatz_titel = '',
                                    absatz_inhalt ='By using the form below, comments may be added to the current page. To make use of the ajax-features of this module,
                                                    switch the template to be used by the postacomment-pageelement by using the admin-backend.'
                                WHERE content_id = '".dbsafeString($strElementId)."'";
        }        
        
        if($this->objDB->_query($strQuery))
            $strReturn .= "Paragraph element created.\n";
        else
            $strReturn .= "Error creating paragraph element.\n";

        $strReturn .= "Creating Navigation-Entry...\n";
        
        include_once(_systempath_."/class_modul_navigation_tree.php");
        include_once(_systempath_."/class_modul_navigation_point.php");
        $arrNavis = class_modul_navigation_tree::getAllNavis();
        if(count($arrNavis) > 0) {
            $objNavi = $arrNavis[0];
            $strTreeId = $objNavi->getSystemid();
        }
        $objNaviPoint = new class_modul_navigation_point();
        $objNaviPoint->setStrName("Postacomment");
        $objNaviPoint->setStrPageI("postacomment");
        $objNaviPoint->saveObjectToDb($strTreeId);
        $strReturn .= "ID of new navigation point: ".$objNaviPoint->getSystemid()."\n";

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
 
