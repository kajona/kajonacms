<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007 by Kajona, www.kajona.de                                                                   *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*                                                                                                       *
*   class_installer_sc_languages.php                                                                    *
*   Interface of the languages samplecontent                                                            *
*                                                                                                       *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                            *
********************************************************************************************************/


include_once(_systempath_."/interface_sc_installer.php");

/**
 * Installer of the navigation languages
 *
 * @package modul_languages
 */
class class_installer_sc_zzlanguages implements interface_sc_installer  {

    private $objDB;
    private $strContentLanguage;

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     */
    public function install() {
        $strReturn = "";

        $strReturn .= "Creating new default-language\n";
            include_once(_systempath_."/class_modul_languages_language.php");
            $objLanguage = new class_modul_languages_language();

            if($this->strContentLanguage == "de")
                $objLanguage->setStrName("de");
            else
               $objLanguage->setStrName("en");

            $objLanguage->setBitDefault(true);
            $objLanguage->saveObjectToDb();
            $strReturn .= "ID of new language: ".$objLanguage->getSystemid()."\n";
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
 
