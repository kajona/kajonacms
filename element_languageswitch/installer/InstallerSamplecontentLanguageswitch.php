<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Languageswitch\Installer;
use class_db;
use interface_sc_installer;
use Kajona\Pages\System\PagesElement;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;


/**
 * Installer of the navigation languages
 *
 * @package element_languageswitch
 */
class InstallerSamplecontentLanguageswitch implements interface_sc_installer  {

    /**
     * @var class_db
     */
    private $objDB;
    private $strContentLanguage;

    private $strMasterID = "";

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     * @return string
     */
    public function install() {
        $strReturn = "";

        //search the master page
        $objMaster = PagesPage::getPageByName("master");
        if($objMaster != null)
            $this->strMasterID = $objMaster->getSystemid();

        if($this->strMasterID != "") {
            $strReturn .= "Adding languageswitch to master page\n";
            $strReturn .= "ID of master page: ".$this->strMasterID."\n";

            if(PagesElement::getElement("languageswitch") != null) {
                $objPagelement = new PagesPageelement();
                $objPagelement->setStrPlaceholder("masterlanguageswitch_languageswitch");
                $objPagelement->setStrName("masterswitch");
                $objPagelement->setStrElement("languageswitch");
                $objPagelement->updateObjectToDb($this->strMasterID);
                $strElementId = $objPagelement->getSystemid();
                $strReturn .= "ID of element: ".$strElementId."\n";
                $strReturn .= "Element created.\n";

                $strReturn .= "Setting languageswitch template...\n";
                $strQuery = "UPDATE "._dbprefix_."element_universal
                            SET char1 = ?
                            WHERE content_id = ? ";
                $this->objDB->_pQuery($strQuery, array("languageswitch.tpl", $strElementId));
            }
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
