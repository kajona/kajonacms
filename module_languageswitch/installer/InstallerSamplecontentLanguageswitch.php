<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Languageswitch\Installer;

use Kajona\Languageswitch\Admin\Elements\ElementLanguageswitchAdmin;
use Kajona\Pages\System\PagesElement;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\System\System\Carrier;
use Kajona\System\System\Database;
use Kajona\System\System\SamplecontentInstallerInterface;


/**
 * Installer of the navigation languages
 *
 * @package element_languageswitch
 */
class InstallerSamplecontentLanguageswitch implements SamplecontentInstallerInterface
{

    /**
     * @var Database
     */
    private $objDB;
    private $strContentLanguage;

    private $strMasterID = "";

    /**
     * @inheritDoc
     */
    public function isInstalled()
    {
        $arrRow = Carrier::getInstance()->getObjDB()->getPRow("SELECT COUNT(*) FROM "._dbprefix_."page_element WHERE page_element_ph_element = ?", array("languageswitch"));
        return $arrRow["COUNT(*)"] > 0;
    }

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     * @return string
     */
    public function install()
    {
        $strReturn = "";

        //search the master page
        $objMaster = PagesPage::getPageByName("master");
        if ($objMaster != null) {
            $this->strMasterID = $objMaster->getSystemid();
        }

        if ($this->strMasterID != "") {
            $strReturn .= "Adding languageswitch to master page\n";
            $strReturn .= "ID of master page: ".$this->strMasterID."\n";

            if (PagesElement::getElement("languageswitch") != null) {
                $objPagelement = new PagesPageelement();
                $objPagelement->setStrPlaceholder("masterlanguageswitch_languageswitch");
                $objPagelement->setStrName("masterlanguageswitch");
                $objPagelement->setStrElement("languageswitch");
                $objPagelement->updateObjectToDb($this->strMasterID);
                /** @var ElementLanguageswitchAdmin $objLangSwitchAdmin */
                $objLangSwitchAdmin = $objPagelement->getConcreteAdminInstance();
                $objLangSwitchAdmin->setStrChar1("languageswitch.tpl");
                $objLangSwitchAdmin->updateForeignElement();
            }
        }

        return $strReturn;
    }

    public function setObjDb($objDb)
    {
        $this->objDB = $objDb;
    }

    public function setStrContentlanguage($strContentlanguage)
    {
        $this->strContentLanguage = $strContentlanguage;
    }

    public function getCorrespondingModule()
    {
        return "languages";
    }
}
