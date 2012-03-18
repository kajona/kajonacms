<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                            *
********************************************************************************************************/


/**
 * Installer of the navigation languages
 *
 * @package module_languages
 */
class class_installer_sc_zzlanguages implements interface_sc_installer  {

    private $objDB;
    private $strContentLanguage;


    /**
     *
     * Does the hard work: installs the module and registers needed constants
     *
     * @return string
     */
    public function install() {
        $strReturn = "";

        $strReturn .= "Assigning null-properties and elements to the default language.\n";
        if($this->strContentLanguage == "de") {

            if(class_exists("class_module_pages_page", false) || class_classloader::getInstance()->loadClass("class_module_pages_page") !== false)
                class_module_pages_page::assignNullProperties("de");
            if(class_exists("class_module_pages_pageelement", false) || class_classloader::getInstance()->loadClass("class_module_pages_pageelement") !== false)
                class_module_pages_pageelement::assignNullElements("de");
        }
        else {

            if(class_exists("class_module_pages_page", false) || class_classloader::getInstance()->loadClass("class_module_pages_page") !== false)
                class_module_pages_page::assignNullProperties("en");
            if(class_exists("class_module_pages_pageelement", false) || class_classloader::getInstance()->loadClass("class_module_pages_pageelement") !== false)
                class_module_pages_pageelement::assignNullElements("en");

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
