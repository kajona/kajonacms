<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Formular\Installer;

use Kajona\Formular\Admin\Elements\ElementFormularAdmin;
use Kajona\Pages\Admin\Elements\ElementPlaintextAdmin;
use Kajona\Pages\Admin\Elements\ElementRichtextAdmin;
use Kajona\Pages\System\PagesElement;
use Kajona\Pages\System\PagesFolder;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\Samplecontent\System\SamplecontentContentHelper;
use Kajona\System\System\Database;
use Kajona\System\System\SamplecontentInstallerInterface;
use Kajona\System\System\SystemSetting;


/**
 * Installer of the form samplecontent
 *
 * @package element_formular
 */
class InstallerSamplecontentFormular implements SamplecontentInstallerInterface  {

    /**
     * @var Database
     */
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
        $arrFolder = PagesFolder::getFolderList();
        foreach($arrFolder as $objOneFolder)
            if($objOneFolder->getStrName() == "mainnavigation")
                $strNaviFolderId = $objOneFolder->getSystemid();

        $strReturn .= "Creating new page contact...\n";

        $objHelper = new SamplecontentContentHelper();

        $objPage = $objHelper->createPage("contact", "Contact", $strNaviFolderId);
        $strReturn .= "ID of new page: ".$objPage->getSystemid()."\n";

        $objBlocks = $objHelper->createBlocksElement("Headline", $objPage);
        $objBlock = $objHelper->createBlockElement("Headline", $objBlocks);

        $strReturn .= "Adding headline-element to new page\n";
        $objHeadline = $objHelper->createPageElement("headline_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText("Contact");
        $objHeadlineAdmin->updateForeignElement();


        $objBlocks = $objHelper->createBlocksElement("Special Content", $objPage);
        $objBlock = $objHelper->createBlockElement("Form", $objBlocks);

        $objFormEl = $objHelper->createPageElement("contact_form", $objBlock);
        /** @var ElementFormularAdmin $objFormAdmin */
        $objFormAdmin = $objFormEl->getConcreteAdminInstance();
        $objFormAdmin->setStrClass("FormularContact.php");;
        $objFormAdmin->setStrTemplate("contact.tpl");
        $objFormAdmin->setStrEmail(SystemSetting::getConfigValue("_system_admin_email_"));
        $objFormAdmin->updateForeignElement();




        $objBlocks = $objHelper->createBlocksElement("Footer Area", $objPage);
        $objBlock = $objHelper->createBlockElement("Footer Text", $objBlocks);

        $objTextEl = $objHelper->createPageElement("footer_richtext", $objBlock);
        /** @var ElementRichtextAdmin $objTextAdmin */
        $objTextAdmin = $objTextEl->getConcreteAdminInstance();
        if($this->strContentLanguage == "de") {
            $objTextAdmin->setStrText("Hinweis: Das Formular sendet per default die Anfragen an die E-Mail Adresse des Administrators.<br />
                                Um diese Adresse zu ändern öffnen Sie bitte die Seite in der Administration und bearbeiten das Seitenelement &quot;Formular&quot;.<br /><br />");
        }
        else {
            $objTextAdmin->setStrText("Note: By default, the form sends the messages to the administators email-address.<br />
                               To change this address, open the current page using the administration and edit the page-element &quot;form&quot;.<br /><br />");
        }
        $objTextAdmin->updateForeignElement();

        return $strReturn;
    }

    public function setObjDb($objDb) {
        $this->objDB = $objDb;
    }

    public function setStrContentlanguage($strContentlanguage) {
        $this->strContentLanguage = $strContentlanguage;
    }

    public function getCorrespondingModule() {
        return "pages";
    }

}
