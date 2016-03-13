<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Postacomment\Installer;

use Kajona\Pages\Admin\Elements\ElementPlaintextAdmin;
use Kajona\Pages\Admin\Elements\ElementRichtextAdmin;
use Kajona\Pages\System\PagesFolder;
use Kajona\Pages\System\PagesPage;
use Kajona\Postacomment\Admin\Elements\ElementPostacommentAdmin;
use Kajona\Samplecontent\System\SamplecontentContentHelper;
use Kajona\System\System\Database;
use Kajona\System\System\SamplecontentInstallerInterface;

/**
 * Installer of the postacomment samplecontent
 *
 */
class InstallerSamplecontentPostacomment implements SamplecontentInstallerInterface
{

    /**
     * @var Database
     */
    private $objDB;
    private $strContentLanguage;

    /**
     * Does the hard work: installs the module and registers needed constants
     *
     */
    public function install()
    {
        $strReturn = "";

        $strReturn .= "Creating new postacomment page...\n";
        $objHelper = new SamplecontentContentHelper();

        $objPage = $objHelper->createPage("postacomment", "Postacomment", PagesPage::getPageByName("samplepages")->getSystemid());
        $strReturn .= "ID of new page: ".$objPage->getSystemid()."\n";

        $objBlocks = $objHelper->createBlocksElement("Headline", $objPage);
        $objBlock = $objHelper->createBlockElement("Headline", $objBlocks);

        $strReturn .= "Adding headline-element to new page\n";
        $objHeadline = $objHelper->createPageElement("headline_plaintext", $objBlock);
        /** @var ElementPlaintextAdmin $objHeadlineAdmin */
        $objHeadlineAdmin = $objHeadline->getConcreteAdminInstance();
        $objHeadlineAdmin->setStrText("Postacomment");
        $objHeadlineAdmin->updateForeignElement();


        $objBlocks = $objHelper->createBlocksElement("Footer Area", $objPage);
        $objBlock = $objHelper->createBlockElement("Postacomment", $objBlocks);

        $objCommentEl = $objHelper->createPageElement("postacomment_postacomment", $objBlock);
        /** @var ElementPostacommentAdmin $objCommentElAdmin */
        $objCommentElAdmin = $objCommentEl->getConcreteAdminInstance();
        $objCommentElAdmin->setStrChar1("postacomment_ajax.tpl");
        $objCommentElAdmin->updateForeignElement();


        $strReturn .= "Adding paragraph-element to new page\n";
        $objBlocks = $objHelper->createBlocksElement("Page Intro", $objPage);
        $objBlock = $objHelper->createBlockElement("Text Only", $objBlocks);

        $objRichtextEl = $objHelper->createPageElement("content_richtext", $objBlock);
        /** @var ElementRichtextAdmin $objRichtextElAdmin */
        $objRichtextElAdmin = $objRichtextEl->getConcreteAdminInstance();
        if ($this->strContentLanguage == "de") {
            $objRichtextElAdmin->setStrText("Über das unten stehende Formular kann dieser Seite ein Kommentar hinzugefügt werden.");
        }
        else {
            $objRichtextElAdmin->setStrText("By using the form below, comments may be added to the current page. ");
        }
        $objRichtextElAdmin->updateForeignElement();

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
        return "postacomment";
    }

}
