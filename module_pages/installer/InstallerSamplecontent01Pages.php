<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
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
        $objIndexPage = $objHelper->createPage("index", $this->strContentLanguage == "de" ? "Willkommen" : "Welcome", SystemModule::getModuleIdByNr(_pages_modul_id_), "home.tpl");

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
            $objRichtextAdmin->setStrText("Diese Installation von Kajona war erfolgreich. Wir wünschen viel Spaß mit Kajona V5.<br />
                                Nehmen Sie sich die Zeit und betrachten Sie die einzelnen Seiten, die mit Beispielinhalten befüllt wurde. Sie gelangen jederzeit auf diese
                                Seite durch den Link &quot;Home&quot; zurück.<br/>
                                Um die Inhalte der Webseite zu verändern sollten Sie sich als erstes am Administrationsbereich <a href='_webpath_/admin'>anmelden</a>.
                                Für weitere Informationen und Support besuchen Sie unsere Webseite: <a href=\"http://www.kajona.de\">www.kajona.de</a><br/>
                                Das gesamte Kajona-Team wünscht viel Spa&szlig; beim Verwalten der Webseite mit Kajona!");

        }
        else {
            $objRichtextAdmin->setStrText("This installation of Kajona was successful. Have fun using Kajona V5!<br />
                                Take some time and watch the pages already created and have a look at the sample-contents assigned to those page.
                                You may return to this page any time by clicking the &quot;home&quot; link.<br/>
                                To modify the contents of this webpage you have to <a href='_webpath_/admin'>log in</a> at the administration-backend.
                                For further information, support or proposals, please visit our website: <a href=\"http://www.kajona.de\">www.kajona.de</a><br/>
                                The Kajona-Team hopes you'll enjoy managing your website with Kajona!");
        }
        $objRichtextAdmin->updateForeignElement();

        $objImage = $objHelper->createPageElement("imageright_image", $objBlock);
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
            $objRichtextAdmin->setStrText("Dieser Text-Absatz befindet sich am Platzhalter contentleft_richtext, der im Standard-Template links ausgerichtet ist. Sobald Sie sich am
                                System <a href='_webpath_/admin'>angemeldet</a> haben und das Portal erneut aufrufen, wird der Portal-Editor angezeigt. Nutzen Sie Drag n Drop
                                um diesen Text-Absatz an einen anderen Platzhalter in diesem Template zu verschieben. Einzige Voraussetzung hierfür ist, dass der Platzhalter
                                Elemente des Typs richtext zulässt.");

        }
        else {
            $objRichtextAdmin->setStrText("This paragraph is located at the placeholder contentleft_richtext. The default-template aligns this placeholder to the left.
                                As soon as you <a href='_webpath_/admin'>log in</a> at the administration-backend and reload the portal, the portal-editor is being shown.
                                Use drag n drop to rearrange this placeholder and move it to another placeholder.
                                The only limitation when dropping the element is, that the target-placeholder allows elements of the type richtext.");
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
            $objRichtextAdmin->setStrText("Der Platzhalter dieses Elementes lautet contentright_richtext. Daher ist er für alle anderen Richtext-Absätze auf dieser Seite ein gültiger Ziel-Platzhalter,
                                sobald ein Absatz per drag n drop verschoben wird. Verschieben Sie die Absätze auf dieser Seite, um ein erstes Gefühl hierfür zu bekommen.");

        }
        else {
            $objRichtextAdmin->setStrText("The placeholder of this paragraph is defined as contentright_richtext. Therefore it is a valid target-placeholder for other richtext paragraphs on the current
                                page. Try to move paragraph on this site in order so see how the possible drop-areas are being highlighted.");
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
            $objRichtextAdmin->setStrText("Der Platzhalter dieses Elementes lautet content_richtext. Daher ist er für alle anderen Richtext-Absätze auf dieser Seite ein gültiger Ziel-Platzhalter,
                                sobald ein Absatz per drag n drop verschoben wird. Verschieben Sie die Absätze auf dieser Seite, um ein erstes Gefühl hierfür zu bekommen.");

        }
        else {
            $objRichtextAdmin->setStrText("The placeholder of this paragraph is defined as content_richtext. Therefore it is a valid target-placeholder for other richtext paragraphs on the current
                                page. Try to move paragraph on this site in order so see how the possible drop-areas are being highlighted.");
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
            $objRichtextAdmin->setStrText("Maybe the requested page doesn\'t exist anymore.<br />Please try it again later.");
        }
        $objRichtextAdmin->updateForeignElement();


        $strReturn .= "Creating imprint-site...\n";
        $objImprintPage = $objHelper->createPage("imprint", $this->strContentLanguage == "de" ? "Impressum" : "Imprint", $strSystemFolderID, "standard.tpl");
        $strReturn .= "ID of new page: ".$objImprintPage->getSystemid()."\n";


        $objBlocks = $objHelper->createBlocksElement("Headline", $objImprintPage);
        $objBlock = $objHelper->createBlockElement("Headline", $objBlocks);

        $objHeadline = $objHelper->createPageElement("headline_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText($this->strContentLanguage == "de" ? "Impressum" : "Imprint");
        $objHeadlineAdmin->updateForeignElement();

        $objBlocks = $objHelper->createBlocksElement("Page Intro", $objErrorPage);
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

        $objPage1 = $objHelper->createPage("page_1", $this->strContentLanguage == "de" ? "Beispielseite" : "Sample page 1", $strMainnavigationFolderID);
        $strReturn .= "ID of new page: ".$objPage1->getSystemid()."\n";
        $strReturn .= "ID of new page: ".$objPage1->getSystemid()."\n";

        $objBlocks = $objHelper->createBlocksElement("Headline", $objPage1);
        $objBlock = $objHelper->createBlockElement("Headline", $objBlocks);

        $strReturn .= "Adding headline-element to new page\n";
        $objHeadline = $objHelper->createPageElement("headline_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText($this->strContentLanguage == "de" ? "Beispielseite 1" : "Sample page 1");
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

        $objImage = $objHelper->createPageElement("imageright_image", $objBlock);
        /** @var ElementImageAdmin $objImageAdmin */
        $objImageAdmin = $objImage->getConcreteAdminInstance();
        $objImageAdmin->setStrImage("/files/images/samples/IMG_3000.JPG");
        $objImageAdmin->updateForeignElement();

        $strReturn .= "Creating sample subpage...\n";


        $objSubPage1 = $objHelper->createPage("subpage_1", $this->strContentLanguage == "de" ? "Beispiel-Unterseite 1" : "Sample subpage 1", $objPage1->getSystemid());
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

        $objImage = $objHelper->createPageElement("imageright_image", $objBlock);
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

        //fetch navifolder-id
        $strNaviFolderId = "";
        $arrFolder = PagesFolder::getFolderList();
        foreach ($arrFolder as $objOneFolder) {
            if ($objOneFolder->getStrName() == "mainnavigation") {
                $strNaviFolderId = $objOneFolder->getSystemid();
            }
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

        $objPage = $objHelper->createPage("downloads", "Downloads", $strNaviFolderId);
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

        //fetch navifolder-id
        $strNaviFolderId = "";
        $arrFolder = PagesFolder::getFolderList();
        foreach ($arrFolder as $objOneFolder) {
            if ($objOneFolder->getStrName() == "mainnavigation") {
                $strNaviFolderId = $objOneFolder->getSystemid();
            }
        }


        $strReturn .= "Creating new gallery...\n";
        $objGallery = new MediamanagerRepo();
        $objGallery->setStrTitle("Sample Gallery");
        $objGallery->setStrPath(_filespath_."/images/samples");
        $objGallery->setStrUploadFilter(".jpg,.png,.gif,.jpeg");
        $objGallery->setStrViewFilter(".jpg,.png,.gif,.jpeg");
        $objGallery->updateObjectToDb();
        $objGallery->syncRepo();
        $strGalleryID = $objGallery->getSystemid();

        $strReturn .= "Modify rights to allow guests to rate images...\n";
        Carrier::getInstance()->getObjRights()->addGroupToRight(SystemSetting::getConfigValue("_guests_group_id_"), $objGallery->getSystemid(), "right3");


        $strReturn .= "Creating new gallery page...\n";

        $objHelper = new SamplecontentContentHelper();

        $objPage = $objHelper->createPage("gallery", "Gallery", $strNaviFolderId);
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


        $objBlocks = $objHelper->createBlocksElement("Footer", $objPage);
        $objBlock = $objHelper->createBlockElement("Footer", $objBlocks);

        $objRichtext = $objHelper->createPageElement("footer_plaintext", $objBlock);
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
