<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Faqs\Installer;

use Kajona\Faqs\Admin\Elements\ElementFaqsAdmin;
use Kajona\Faqs\System\FaqsFaq;
use Kajona\Pages\Admin\Elements\ElementPlaintextAdmin;
use Kajona\Pages\System\PagesFolder;
use Kajona\Pages\System\PagesPage;
use Kajona\Samplecontent\System\SamplecontentContentHelper;
use Kajona\System\System\SamplecontentInstallerInterface;

/**
 * Installer of the faqs samplecontent
 *
 */
class InstallerSamplecontentFaqs implements SamplecontentInstallerInterface
{

    /**
     * @var \Kajona\System\System\Database
     */
    private $objDB;
    private $strContentLanguage;

    private $strIndexID = "";

    public function install()
    {
        $strReturn = "";

        //search the index page
        $objIndex = PagesPage::getPageByName("index");
        if ($objIndex != null) {
            $this->strIndexID = $objIndex->getSystemid();
        }

        $strReturn .= "Creating faqs\n";
        $objFaq1 = new FaqsFaq();
        $objFaq2 = new FaqsFaq();

        if ($this->strContentLanguage == "de") {
            $objFaq1->setStrQuestion("Was ist Kajona?");
            $objFaq1->setStrAnswer("Kajona ist ein Open Source Content Management System basierend auf PHP und einer Datenbank. Dank der modularen Bauweise ist Kajona einfach erweiter- und anpassbar.");

            $objFaq2->setStrQuestion("Wer entwickelt Kajona, wo gibt es weitere Infos?");
            $objFaq2->setStrAnswer("Kajona wird von einer Open Source Community entwickelt. Da Kajona ständig weiterentwickelt wird, sind wir jederzeit auf der Suche nach Helfern, seien es Programmierer, Grafiker, Betatester und auch Anwender. Weitere Informationen hierzu finden Sie auf <a href=\"http://www.kajona.de\">www.kajona.de</a>.");
        }
        else {
            $objFaq1->setStrQuestion("What is Kajona?");
            $objFaq1->setStrAnswer("Kajona is an open source content management system based on PHP and a database. Due to it's modular design, it can be extended and adopted very easily.");

            $objFaq2->setStrQuestion("Who develops Kajona, where can I find more infos?");
            $objFaq2->setStrAnswer("Kajona is being developed by an open source community. Since Kajona is still being developed, we are searching for contributors. Further information can be found at <a href=\"http://www.kajona.de\">www.kajona.de</a>.");
        }

        $strReturn .= "Saving faqs...\n";
        $objFaq1->updateObjectToDb();
        $objFaq2->updateObjectToDb();


        $strReturn .= "Creating faqs-page\n";
        $objHelper = new SamplecontentContentHelper();

        $objPage = $objHelper->createPage("faqs", "FAQs", PagesPage::getPageByName("samplepages")->getSystemid());
        $strReturn .= "ID of new page: ".$objPage->getSystemid()."\n";

        $objBlocks = $objHelper->createBlocksElement("Headline", $objPage);
        $objBlock = $objHelper->createBlockElement("Headline", $objBlocks);

        $strReturn .= "Adding headline-element to new page\n";
        $objHeadline = $objHelper->createPageElement("headline_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText("FAQs");
        $objHeadlineAdmin->updateForeignElement();


        $objBlocks = $objHelper->createBlocksElement("Special Content", $objPage);
        $objBlock = $objHelper->createBlockElement("Faqs", $objBlocks);

        $objFaqElement = $objHelper->createPageElement("faqs_faqs", $objBlock);
        /** @var ElementFaqsAdmin $objFaqElementAdmin */
        $objFaqElementAdmin = $objFaqElement->getConcreteAdminInstance();
        $objFaqElementAdmin->setStrCategory(0);
        $objFaqElementAdmin->setStrTemplate("demo_foldable.tpl");
        $objFaqElementAdmin->updateForeignElement();

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
        return "faqs";
    }

}
