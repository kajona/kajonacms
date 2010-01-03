<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2010 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                            *
********************************************************************************************************/


/**
 * Installer of the navigation languages
 *
 * @package modul_languages
 */
class class_installer_sc_zzlanguages implements interface_sc_installer  {

    private $objDB;
    private $strContentLanguage;
    
    private $strMasterID = "";

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     */
    public function install() {
        $strReturn = "";
        
        //search the master page
        $objMaster = class_modul_pages_page::getPageByName("master");
        if($objMaster != null)
            $this->strMasterID = $objMaster->getSystemid();
            
        if($this->strMasterID != "") {
                $strReturn .= "Adding languageswitch to master page\n";
                $strReturn .= "ID of master page: ".$this->strMasterID."\n";
    
                $objPagelement = new class_modul_pages_pageelement();
                $objPagelement->setStrPlaceholder("masterswitch_languageswitch");
                $objPagelement->setStrName("masterswitch");
                $objPagelement->setStrElement("languageswitch");
                $objPagelement->updateObjectToDb($this->strMasterID);
                $strElementId = $objPagelement->getSystemid();
                $strReturn .= "ID of element: ".$strElementId."\n";
                $strReturn .= "Element created.\n";

                $strReturn .= "Setting languageswitch template...\n";
                $strQuery = "UPDATE "._dbprefix_."element_universal
	                        SET char1 = 'languageswitch.tpl'
	                        WHERE content_id = '".dbsafeString($strElementId)."'";
                $this->objDB->_query($strQuery);
         }    

        
        $strReturn .= "Assigning null-properties and elements to the default language.\n";
        if($this->strContentLanguage == "de") {
        
            if(include_once(_systempath_."/class_modul_pages_page.php"))
                class_modul_pages_page::assignNullProperties("de");
            if(include_once(_systempath_."/class_modul_pages_pageelement.php"))
                class_modul_pages_pageelement::assignNullElements("de");
        }
        else {
        	
            if(include_once(_systempath_."/class_modul_pages_page.php"))
                class_modul_pages_page::assignNullProperties("en");
            if(include_once(_systempath_."/class_modul_pages_pageelement.php"))
                class_modul_pages_pageelement::assignNullElements("en");
                
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
        return "languages";
    }
}
?>