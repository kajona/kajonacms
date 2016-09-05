<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                *
********************************************************************************************************/
namespace Kajona\Pages\Installer;

use Kajona\Mediamanager\Admin\Elements\ElementDownloadsAdmin;
use Kajona\Mediamanager\Admin\Elements\ElementGalleryAdmin;
use Kajona\Mediamanager\System\MediamanagerRepo;
use Kajona\Pages\Admin\Elements\ElementImageAdmin;
use Kajona\Pages\Admin\Elements\ElementPlaintextAdmin;
use Kajona\Pages\Admin\Elements\ElementRichtextAdmin;
use Kajona\Pages\System\PagesFolder;
use Kajona\Pages\System\PagesPage;
use Kajona\Samplecontent\System\SamplecontentContentHelper;
use Kajona\System\System\Carrier;
use Kajona\System\System\Database;
use Kajona\System\System\SamplecontentInstallerInterface;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;

/**
 * Installer of the pages samplecontent
 *
 * @package module_pages
 */
class InstallerSamplecontent01Pages implements SamplecontentInstallerInterface
{

    /**
     * @var Database
     */
    private $objDB;
    private $strContentLanguage;


    /**
     * @inheritDoc
     */
    public function isInstalled()
    {
        return PagesPage::getPageByName("index") != null;
    }

    public function install()
    {
        $strReturn = $this->installScPages();
        $strReturn .= $this->installScDownloads();
        $strReturn .= $this->installScGallery();
        return $strReturn;
    }

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     * @return string
     */
    public function installScPages()
    {

        $strReturn = "";


        $strReturn .= "Shifting pages to first position...\n";
        $objPagesModule = SystemModule::getModuleByName("pages");
        $objPagesModule->setAbsolutePosition(3);

        $strReturn .= "Setting default template...\n";
        $objConstant = SystemSetting::getConfigByName("_pages_defaulttemplate_");
        $objConstant->setStrValue("standard.tpl");
        $objConstant->updateObjectToDb();


        $strReturn .= "Creating system folder...\n";
        $objFolder = new PagesFolder();
        $objFolder->setStrName("_system");
        $objFolder->updateObjectToDb(SystemModule::getModuleByName("pages")->getSystemid());
        $strSystemFolderID = $objFolder->getSystemid();
        $strReturn .= "ID of new folder: ".$strSystemFolderID."\n";


        $strReturn .= "Creating mainnavigation folder...\n";
        $objFolder = new PagesFolder();
        $objFolder->setStrName("mainnavigation");
        $objFolder->updateObjectToDb(SystemModule::getModuleByName("pages")->getSystemid());
        $strMainnavigationFolderID = $objFolder->getSystemid();
        $strReturn .= "ID of new folder: ".$strSystemFolderID."\n";


        $objHelper = new SamplecontentContentHelper();
        $strReturn .= "Creating index-site...\n";
        $objIndexPage = $objHelper->createPage("index", $this->strContentLanguage == "de" ? "Willkommen" : "Welcome", $strMainnavigationFolderID, "home.tpl");

        $objBlocks = $objHelper->createBlocksElement("Headline", $objIndexPage);
        $objBlock = $objHelper->createBlockElement("Headline", $objBlocks);

        $objHeadline = $objHelper->createPageElement("headline_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText($this->strContentLanguage == "de" ? "Willkommen" : "Welcome");
        $objHeadlineAdmin->updateForeignElement();

        $objBlocks = $objHelper->createBlocksElement("Page Intro", $objIndexPage);
        $objBlock = $objHelper->createBlockElement("Two Columns Large Text and Image", $objBlocks);

        $objHeadline = $objHelper->createPageElement("headlineleft_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText($this->strContentLanguage == "de" ? "Herzlichen Glückwunsch!" : "Congratulations!");
        $objHeadlineAdmin->updateForeignElement();

        $objRichtext = $objHelper->createPageElement("contentleft_richtext", $objBlock);
        /** @var ElementRichtextAdmin $objRichtextAdmin */
        $objRichtextAdmin = $objRichtext->getConcreteAdminInstance();

        if ($this->strContentLanguage == "de") {
            $objRichtextAdmin->setStrText("Diese Installation von Kajona war erfolgreich.<br />
                                Nehmen Sie sich die Zeit und betrachten Sie die einzelnen Seiten, die mit Beispielinhalten befüllt wurde. Sie gelangen jederzeit auf diese
                                Seite durch den Link &quot;Home&quot; zurück.<br/>
                                Um die Inhalte der Webseite zu verändern sollten Sie sich als erstes am Administrationsbereich <a href='_webpath_/admin'>anmelden</a>.
                                Für weitere Informationen und Support besuchen Sie unsere Webseite: <a href=\"https://www.kajona.de\">www.kajona.de</a><br/>
                                Das gesamte Kajona-Team wünscht viel Spa&szlig; beim Verwalten der Webseite mit Kajona!");

        }
        else {
            $objRichtextAdmin->setStrText("This installation of Kajona was successful. <br />
                                Take some time and have a look at the pages already created and the sample-contents assigned to those page.
                                You may return to this page any time by clicking the &quot;home&quot; link.<br/>
                                To modify the contents of this webpage you have to <a href='_webpath_/admin'>log in</a> at the administration-backend.
                                For further information, support or proposals, please visit our website: <a href=\"https://www.kajona.de\">www.kajona.de</a><br/>
                                The Kajona-Team hopes you'll enjoy managing your website with Kajona!");
        }
        $objRichtextAdmin->updateForeignElement();

        $objImage = $objHelper->createPageElement("imageright_imagesrc", $objBlock);
        /** @var ElementImageAdmin $objImageAdmin */
        $objImageAdmin = $objImage->getConcreteAdminInstance();
        $objImageAdmin->setStrImage("/files/images/upload/teaser.jpg");
        $objImageAdmin->updateForeignElement();


        $objBlock = $objHelper->createBlockElement("Two Columns Header and Text", $objBlocks);

        $objHeadline = $objHelper->createPageElement("headlineleft_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText("Teaser 1");
        $objHeadlineAdmin->updateForeignElement();

        $objRichtext = $objHelper->createPageElement("contentleft_richtext", $objBlock);
        /** @var ElementRichtextAdmin $objRichtextAdmin */
        $objRichtextAdmin = $objRichtext->getConcreteAdminInstance();

        if ($this->strContentLanguage == "de") {
            $objRichtextAdmin->setStrText("Inhalte werden in Kajona in Form von Blöcken organisiert. Dieser Block 'Two Columns Header and Text' beinahltet zwei Spalten, dies ist die linke Inhaltsspalte.
                                Sobald Sie sich am System <a href='_webpath_/admin'>angemeldet</a> haben und das Portal erneut aufrufen, wird der Portal-Editor angezeigt. <br />
                                Nutzen Sie Drag n Drop um den gesamten Block zu verschieben.");

        }
        else {
            $objRichtextAdmin->setStrText("Content is organized in blocks. This block 'Two Columns Header and Text' is made of two columnns, this is the left column.
                                As soon as you <a href='_webpath_/admin'>log in</a> at the administration-backend and reload the portal, the portal-editor is being shown.
                                Use drag n drop to rearrange and organize blocks.");
        }
        $objRichtextAdmin->updateForeignElement();


        $objHeadline = $objHelper->createPageElement("headlineright_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText("Teaser 2");
        $objHeadlineAdmin->updateForeignElement();

        $objRichtext = $objHelper->createPageElement("contentright_richtext", $objBlock);
        /** @var ElementRichtextAdmin $objRichtextAdmin */
        $objRichtextAdmin = $objRichtext->getConcreteAdminInstance();

        if ($this->strContentLanguage == "de") {
            $objRichtextAdmin->setStrText("Das ist die rechte Spalte des Blocks 'Two Columns Header and Text'. Blöcke lassen sich immer nur als ganzes befüllen oder löschen. Somit wird sichergestellt, dass ein 
                                einheitliches Layout aufgebaut werden kann. Über den Portal-Editor lassen sich alle Texte direkt in der Seite bearbeiten.");

        }
        else {
            $objRichtextAdmin->setStrText("This is the right column of the block 'Two Columns Header and Text'. Blocks are creatable only as a whole, ensuring a consistent layout and look and feel of the page.
                                The portaleditor allows to change the contents directly on the page.");
        }
        $objRichtextAdmin->updateForeignElement();


        $objBlock = $objHelper->createBlockElement("Header and Text", $objBlocks);
        $objHeadline = $objHelper->createPageElement("headline_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText("Teaser 3");
        $objHeadlineAdmin->updateForeignElement();

        $objRichtext = $objHelper->createPageElement("content_richtext", $objBlock);
        /** @var ElementRichtextAdmin $objRichtextAdmin */
        $objRichtextAdmin = $objRichtext->getConcreteAdminInstance();

        if ($this->strContentLanguage == "de") {
            $objRichtextAdmin->setStrText("Im Gegensatz zum voherigen, zweispaltigen Block basiert dieser Block 'Headline and Text' nur auf einer Überschrift und einem einspaltigen Text.
                                Dieser darf dafür aber die gesamte Breite des Layouts nutzen. Verwenden Sie Drag n Drop um die Reihenfolge der Blöcke untereinander zu verändern.");

        }
        else {
            $objRichtextAdmin->setStrText("In contrast to the previous block made of two columns, the block 'Headline and Text' is made of a headline and a single text-column only.
                                Therefore the text may use the full width of the layout. Use drag n drop to change the order of the blocks.");
        }
        $objRichtextAdmin->updateForeignElement();


        $strReturn .= "Creating master-page\n";
        $objMasterPage = $objHelper->createPage("master", "master", $strSystemFolderID, "master.tpl");
        $strReturn .= "ID of new page: ".$objMasterPage->getSystemid()."\n";


        $strReturn .= "Creating error-site...\n";
        $objErrorPage = $objHelper->createPage("error", $this->strContentLanguage == "de" ? "Fehler" : "Error", $strSystemFolderID, "standard.tpl");
        $strReturn .= "ID of new page: ".$objErrorPage->getSystemid()."\n";

        $objBlocks = $objHelper->createBlocksElement("Headline", $objErrorPage);
        $objBlock = $objHelper->createBlockElement("Headline", $objBlocks);

        $objHeadline = $objHelper->createPageElement("headline_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText($this->strContentLanguage == "de" ? "Fehler" : "Error");
        $objHeadlineAdmin->updateForeignElement();

        $objBlocks = $objHelper->createBlocksElement("Page Intro", $objErrorPage);
        $objBlock = $objHelper->createBlockElement("Header and Text", $objBlocks);
        $objHeadline = $objHelper->createPageElement("headline_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText($this->strContentLanguage == "de" ? "Ein Fehler ist aufgetreten" : "An error occurred");
        $objHeadlineAdmin->updateForeignElement();

        $objRichtext = $objHelper->createPageElement("content_richtext", $objBlock);
        /** @var ElementRichtextAdmin $objRichtextAdmin */
        $objRichtextAdmin = $objRichtext->getConcreteAdminInstance();

        if ($this->strContentLanguage == "de") {
            $objRichtextAdmin->setStrText("Während Ihre Anfrage ist leider ein Fehler aufgetreten.<br />Bitte versuchen Sie die letzte Aktion erneut.");

        }
        else {
            $objRichtextAdmin->setStrText("Maybe the requested page doesn't exist anymore.<br />Please try it again later.");
        }
        $objRichtextAdmin->updateForeignElement();


        $strReturn .= "Creating imprint-site...\n";
        $objImprintPage = $objHelper->createPage("imprint", $this->strContentLanguage == "de" ? "Impressum" : "Imprint", PagesPage::getPageByName("index")->getSystemid());
        $strReturn .= "ID of new page: ".$objImprintPage->getSystemid()."\n";


        $objBlocks = $objHelper->createBlocksElement("Headline", $objImprintPage);
        $objBlock = $objHelper->createBlockElement("Headline", $objBlocks);

        $objHeadline = $objHelper->createPageElement("headline_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText($this->strContentLanguage == "de" ? "Impressum" : "Imprint");
        $objHeadlineAdmin->updateForeignElement();

        $objBlocks = $objHelper->createBlocksElement("Page Intro", $objImprintPage);
        $objBlock = $objHelper->createBlockElement("Text Only", $objBlocks);
        $objRichtext = $objHelper->createPageElement("content_richtext", $objBlock);
        /** @var ElementRichtextAdmin $objRichtextAdmin */
        $objRichtextAdmin = $objRichtext->getConcreteAdminInstance();

        if ($this->strContentLanguage == "de") {
            $objRichtextAdmin->setStrText("Bitte tragen Sie hier Ihre Kontaktdaten ein.<br />
                               Nachname, Name<br />
                               Straße und Hausnummer<br />
                               PLZ, Ort<br />
                               Telefon<br />
                               E-Mail<br />
                               <br />
                               Site powered by <a href=\"http://www.kajona.de\" target=\"_blank\" title=\"Kajona CMS - empowering your content\">Kajona</a><br /><a href=\"http://www.kajona.de\" target=\"_blank\" title=\"Kajona CMS - empowering your content\"><img src=\"_webpath_/templates/default/pics/default/kajona_poweredby.png\" alt=\"Kajona\" /></a><br />");

        }
        else {
            $objRichtextAdmin->setStrText("Please provide your contact details.<br />
                               Name, Forename<br />
                               Street<br />
                               Zip, City<br />
                               Phone<br />
                               Mail<br />
                               <br />
                               Site powered by <a href=\"http://www.kajona.de\" target=\"_blank\" title=\"Kajona CMS - empowering your content\">Kajona</a><br /><a href=\"http://www.kajona.de\" target=\"_blank\" title=\"Kajona CMS - empowering your content\"><img src=\"_webpath_/templates/default/pics/default/kajona_poweredby.png\" alt=\"Kajona\" /></a><br />");
        }
        $objRichtextAdmin->updateForeignElement();


        $strReturn .= "Creating sample page...\n";

        $objPage1 = $objHelper->createPage("samplepages", $this->strContentLanguage == "de" ? "Beispielseiten" : "Sample pages", PagesPage::getPageByName("index")->getSystemid());
        $strReturn .= "ID of new page: ".$objPage1->getSystemid()."\n";
        $strReturn .= "ID of new page: ".$objPage1->getSystemid()."\n";

        $objBlocks = $objHelper->createBlocksElement("Headline", $objPage1);
        $objBlock = $objHelper->createBlockElement("Headline", $objBlocks);

        $strReturn .= "Adding headline-element to new page\n";
        $objHeadline = $objHelper->createPageElement("headline_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText($this->strContentLanguage == "de" ? "Beispielseiten" : "Sample pages");
        $objHeadlineAdmin->updateForeignElement();


        $objBlocks = $objHelper->createBlocksElement("Page Intro", $objPage1);
        $objBlock = $objHelper->createBlockElement("Two Columns Large Text and Image", $objBlocks);

        $objHeadline = $objHelper->createPageElement("headlineleft_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText($this->strContentLanguage == "de" ? "Standard-Absatz" : "Default paragraph");
        $objHeadlineAdmin->updateForeignElement();

        $objRichtext = $objHelper->createPageElement("contentleft_richtext", $objBlock);
        /** @var ElementRichtextAdmin $objRichtextAdmin */
        $objRichtextAdmin = $objRichtext->getConcreteAdminInstance();

        if ($this->strContentLanguage == "de") {
            $objRichtextAdmin->setStrText("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.");

        }
        else {
            $objRichtextAdmin->setStrText("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.");
        }
        $objRichtextAdmin->updateForeignElement();

        $objImage = $objHelper->createPageElement("imageright_imagesrc", $objBlock);
        /** @var ElementImageAdmin $objImageAdmin */
        $objImageAdmin = $objImage->getConcreteAdminInstance();
        $objImageAdmin->setStrImage("/files/images/samples/IMG_3000.JPG");
        $objImageAdmin->updateForeignElement();

        $strReturn .= "Creating sample subpage...\n";


        $objSubPage1 = $objHelper->createPage("subpage_1", $this->strContentLanguage == "de" ? "Beispiel-Unterseite 1" : "Sample subpage 1", PagesPage::getPageByName("samplepages")->getSystemid());
        $strReturn .= "ID of new page: ".$objSubPage1->getSystemid()."\n";

        $objBlocks = $objHelper->createBlocksElement("Headline", $objSubPage1);
        $objBlock = $objHelper->createBlockElement("Headline", $objBlocks);

        $strReturn .= "Adding headline-element to new page\n";
        $objHeadline = $objHelper->createPageElement("headline_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText($this->strContentLanguage == "de" ? "Beispiel-Unterseite 1" : "Sample subpage 1");
        $objHeadlineAdmin->updateForeignElement();


        $objBlocks = $objHelper->createBlocksElement("Page Intro", $objSubPage1);
        $objBlock = $objHelper->createBlockElement("Header and Text", $objBlocks);

        $objHeadline = $objHelper->createPageElement("headline_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText($this->strContentLanguage == "de" ? "Standard-Absatz auf Unterseite" : "Default paragraph on subpage");
        $objHeadlineAdmin->updateForeignElement();

        $objRichtext = $objHelper->createPageElement("content_richtext", $objBlock);
        /** @var ElementRichtextAdmin $objRichtextAdmin */
        $objRichtextAdmin = $objRichtext->getConcreteAdminInstance();

        if ($this->strContentLanguage == "de") {
            $objRichtextAdmin->setStrText("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.");

        }
        else {
            $objRichtextAdmin->setStrText("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.");
        }
        $objRichtextAdmin->updateForeignElement();

        $objImage = $objHelper->createPageElement("imageright_imagesrc", $objBlock);
        /** @var ElementImageAdmin $objImageAdmin */
        $objImageAdmin = $objImage->getConcreteAdminInstance();
        $objImageAdmin->setStrImage("/files/images/samples/IMG_3000.JPG");
        $objImageAdmin->updateForeignElement();


        return $strReturn;
    }


    public function installScDownloads()
    {
        $strReturn = "";

        if (SystemModule::getModuleByName("mediamanager") == null) {
            return "Mediamanger not installed, skipping element\n";
        }


        $strReturn .= "Creating new downloads...\n";
        $objDownloads = new MediamanagerRepo();
        $objDownloads->setStrTitle("Sample downloads");
        $objDownloads->setStrPath("/files/downloads");
        $objDownloads->updateObjectToDb();
        $objDownloads->syncRepo();

        $strReturn .= "Adding download-permissions for guests...\n";
        Carrier::getInstance()->getObjRights()->addGroupToRight(SystemSetting::getConfigValue("_guests_group_id_"), $objDownloads->getSystemid(), "right2");

        $strReturn .= "Adding rating-permissions for guests...\n";
        Carrier::getInstance()->getObjRights()->addGroupToRight(SystemSetting::getConfigValue("_guests_group_id_"), $objDownloads->getSystemid(), "right3");

        $strReturn .= "Creating new downloads page...\n";

        $objHelper = new SamplecontentContentHelper();

        $objPage = $objHelper->createPage("downloads", "Downloads", PagesPage::getPageByName("samplepages")->getSystemid());
        $strReturn .= "ID of new page: ".$objPage->getSystemid()."\n";

        $objBlocks = $objHelper->createBlocksElement("Headline", $objPage);
        $objBlock = $objHelper->createBlockElement("Headline", $objBlocks);

        $strReturn .= "Adding headline-element to new page\n";
        $objHeadline = $objHelper->createPageElement("headline_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText("Downloads");
        $objHeadlineAdmin->updateForeignElement();


        $objBlocks = $objHelper->createBlocksElement("Special Content", $objPage);
        $objBlock = $objHelper->createBlockElement("Downloads", $objBlocks);

        $objMediamanager = $objHelper->createPageElement("downloads_downloads", $objBlock);
        /** @var ElementDownloadsAdmin $objMediamangerAdmin */
        $objMediamangerAdmin = $objMediamanager->getConcreteAdminInstance();
        $objMediamangerAdmin->setStrRepo($objDownloads->getSystemid());
        $objMediamangerAdmin->setStrTemplate("downloads.tpl");
        $objMediamangerAdmin->updateForeignElement();


        return $strReturn;
    }


    public function installScGallery()
    {

        if (SystemModule::getModuleByName("mediamanager") == null) {
            return "Mediamanger not installed, skipping element\n";
        }


        $strReturn = "";

        $strReturn .= "Creating new gallery...\n";
        $objGallery = new MediamanagerRepo();
        $objGallery->setStrTitle("Sample Gallery");
        $objGallery->setStrPath(_filespath_."/images/samples");
        $objGallery->setStrUploadFilter(".jpg,.png,.gif,.jpeg");
        $objGallery->setStrViewFilter(".jpg,.png,.gif,.jpeg");
        $objGallery->updateObjectToDb();
        $objGallery->syncRepo();

        $strReturn .= "Modify rights to allow guests to rate images...\n";
        Carrier::getInstance()->getObjRights()->addGroupToRight(SystemSetting::getConfigValue("_guests_group_id_"), $objGallery->getSystemid(), "right3");


        $strReturn .= "Creating new gallery page...\n";

        $objHelper = new SamplecontentContentHelper();

        $objPage = $objHelper->createPage("gallery", "Gallery", PagesPage::getPageByName("samplepages")->getSystemid());
        $strReturn .= "ID of new page: ".$objPage->getSystemid()."\n";

        $objBlocks = $objHelper->createBlocksElement("Headline", $objPage);
        $objBlock = $objHelper->createBlockElement("Headline", $objBlocks);

        $strReturn .= "Adding headline-element to new page\n";
        $objHeadline = $objHelper->createPageElement("headline_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText("Gallery");
        $objHeadlineAdmin->updateForeignElement();


        $objBlocks = $objHelper->createBlocksElement("Special Content", $objPage);
        $objBlock = $objHelper->createBlockElement("Gallery", $objBlocks);

        $objMediamanager = $objHelper->createPageElement("gallery_gallery", $objBlock);
        /** @var ElementGalleryAdmin $objMediamangerAdmin */
        $objMediamangerAdmin = $objMediamanager->getConcreteAdminInstance();
        $objMediamangerAdmin->setStrRepo($objGallery->getSystemid());
        $objMediamangerAdmin->setStrTemplate("gallery_imagelightbox.tpl");
        $objMediamangerAdmin->setIntGalleryMode(0);
        $objMediamangerAdmin->setIntMaxHD(600);
        $objMediamangerAdmin->setIntMaxWD(600);
        $objMediamangerAdmin->setStrText("(c) kajona.de");
        $objMediamangerAdmin->setIntTextX(15);
        $objMediamangerAdmin->setIntTextY(15);
        $objMediamangerAdmin->updateForeignElement();


        $objBlocks = $objHelper->createBlocksElement("Footer Area", $objPage);
        $objBlock = $objHelper->createBlockElement("Footer Text", $objBlocks);

        $objRichtext = $objHelper->createPageElement("footer_richtext", $objBlock);
        /** @var ElementRichtextAdmin $objRichtextAdmin */
        $objRichtextAdmin = $objRichtext->getConcreteAdminInstance();
        $objRichtextAdmin->setStrText($this->strContentLanguage == "de" ? "Alle Beispielbilder &copy; by kajona.de" : "All sample images &copy; by kajona.de");
        $objRichtextAdmin->updateForeignElement();


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
        return "pages";
    }

}
