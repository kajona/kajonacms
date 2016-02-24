<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/
namespace Kajona\System\Installer;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\System\System\Classloader;
use Kajona\System\System\Database;
use Kajona\System\System\LanguagesLanguage;
use Kajona\System\System\SamplecontentInstallerInterface;


/**
 * Installer of the navigation languages
 *
 */
class InstallerSamplecontentZZLanguages implements SamplecontentInstallerInterface  {

    /**
     * @var Database
     */
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

            $strReturn .= " Target language: de\n";

            if(class_exists("Kajona\\Pages\\System\\PagesPage", false) || Classloader::getInstance()->loadClass("Kajona\\Pages\\System\\PagesPage") !== false)
                PagesPage::assignNullProperties("de", true);
            if(class_exists("Kajona\\Pages\\System\\PagesPageelement", false) || Classloader::getInstance()->loadClass("Kajona\\Pages\\System\\PagesPageelement") !== false)
                PagesPageelement::assignNullElements("de");

            $objLang = new LanguagesLanguage();
            $objLang->setStrAdminLanguageToWorkOn("de");
        }
        else {

            $strReturn .= " Target language: en\n";

            if(class_exists("Kajona\\Pages\\System\\PagesPage", false) || Classloader::getInstance()->loadClass("Kajona\\Pages\\System\\PagesPage") !== false)
                PagesPage::assignNullProperties("en", true);
            if(class_exists("Kajona\\Pages\\System\\PagesPageelement", false) || Classloader::getInstance()->loadClass("Kajona\\Pages\\System\\PagesPageelement") !== false)
                PagesPageelement::assignNullElements("en");

            $objLang = new LanguagesLanguage();
            $objLang->setStrAdminLanguageToWorkOn("en");

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
