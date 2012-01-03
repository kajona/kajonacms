<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                               *
********************************************************************************************************/


/**
 * Interface of the tags samplecontent
 *
 * @package module_tags
 */
class class_installer_sc_tags implements interface_sc_installer  {

    private $objDB;
    private $strContentLanguage;

    private $strMasterID = "";

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

        //search the master page
        $objMaster = class_module_pages_page::getPageByName("master");
        if($objMaster != null)
            $this->strMasterID = $objMaster->getSystemid();

        $strReturn .= "Creating tags page\n";
            $objPage = new class_module_pages_page();
            $objPage->setStrName("tags");

            if($this->strContentLanguage == "de")
                $objPage->setStrBrowsername("Tags");
            else
                $objPage->setStrBrowsername("Tags");

            $objPage->setStrTemplate("kajona_demo.tpl");
            $objPage->updateObjectToDb($strNaviFolderId);
            $strSearchresultsId = $objPage->getSystemid();
            $strReturn .= "ID of new page: ".$strSearchresultsId."\n";
            $strReturn .= "Adding tags-element to new page\n";

            if(class_module_pages_element::getElement("tags") != null) {
                $objPagelement = new class_module_pages_pageelement();
                $objPagelement->setStrPlaceholder("mixed3_flash|mediaplayer|tags|eventmanager");
                $objPagelement->setStrName("mixed3");
                $objPagelement->setStrElement("tags");
                $objPagelement->updateObjectToDb($strSearchresultsId);
                $strElementId = $objPagelement->getSystemid();
                 $strQuery = "UPDATE "._dbprefix_."element_universal
                                    SET char1 = ?
                                    WHERE content_id = ?";
                    if($this->objDB->_pQuery($strQuery, array("tags.tpl", $strElementId)))
                        $strReturn .= "Tags element created.\n";
                    else
                        $strReturn .= "Error creating tags element.\n";

            }

            $strReturn .= "Adding headline-element to new page\n";
            if(class_module_pages_element::getElement("row") != null) {
                $objPagelement = new class_module_pages_pageelement();
                $objPagelement->setStrPlaceholder("headline_row");
                $objPagelement->setStrName("headline");
                $objPagelement->setStrElement("row");
                $objPagelement->updateObjectToDb($strSearchresultsId);
                $strElementId = $objPagelement->getSystemid();

                $strQuery = "UPDATE "._dbprefix_."element_paragraph
                                SET paragraph_title = ?
                                WHERE content_id = ?";

                if($this->objDB->_pQuery($strQuery, array("Tags", $strElementId)))
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
        return "tags";
    }
}
