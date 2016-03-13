<?php
/*"******************************************************************************************************
*   (c) 2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


namespace Kajona\Samplecontent\System;



use Kajona\Pages\System\PagesElement;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\System\System\Exception;

class SamplecontentContentHelper
{

    public function createPage($strName, $strBrowsername, $strPrevId, $strTemplate = "standard.tpl")
    {
        $objPage = new PagesPage();
        $objPage->setStrName($strName);
        $objPage->setStrBrowsername($strBrowsername);
        $objPage->setStrTemplate($strTemplate);
        $objPage->updateObjectToDb($strPrevId);

        return $objPage;
    }

    public function createBlocksElement($strBlocksName, PagesPage $objPage, $strLanguage = "")
    {
        if (PagesElement::getElement("blocks") == null || PagesElement::getElement("block") == null) {
            throw new Exception("block/blocks element not existing", Exception::$level_ERROR);
        }

        $objBlocks = new PagesPageelement();
        $objBlocks->setStrPlaceholder("blocks");
        $objBlocks->setStrName($strBlocksName);
        $objBlocks->setStrElement("blocks");
        if($strLanguage != "") {
            $objBlocks->setStrLanguage($strLanguage);
        }
        $objBlocks->updateObjectToDb($objPage->getSystemid());
        return $objBlocks;
    }

    public function createBlockElement($strBlockName, PagesPageelement $objBlocks, $strLanguage = "")
    {
        if ($objBlocks->getStrElement() != "blocks" || PagesElement::getElement("block") == null) {
            throw new Exception("block/blocks element not existing / wrong parent reference", Exception::$level_ERROR);
        }

        $objBlock = new PagesPageelement();
        $objBlock->setStrPlaceholder("block");
        $objBlock->setStrName($strBlockName);
        $objBlock->setStrElement("block");
        if($strLanguage != "") {
            $objBlock->setStrLanguage($strLanguage);
        }
        $objBlock->updateObjectToDb($objBlocks->getSystemid());
        return $objBlock;
    }


    public function createPageElement($strPlaceholder, PagesPageelement $objBlock, $strLanguage = "")
    {
        $arrPlaceholder = explode("_", $strPlaceholder);

        if ($objBlock->getStrElement() != "block") {
            throw new Exception("wrong parent reference", Exception::$level_ERROR);
        }

        if (PagesElement::getElement($arrPlaceholder[1]) == null) {
            throw new Exception($arrPlaceholder[1]. "element not existing", Exception::$level_ERROR);
        }


        $objPageelement = new PagesPageelement();
        $objPageelement->setStrPlaceholder($strPlaceholder);
        $objPageelement->setStrName($arrPlaceholder[0]);
        $objPageelement->setStrElement($arrPlaceholder[1]);
        if($strLanguage != "") {
            $objPageelement->setStrLanguage($strLanguage);
        }
        $objPageelement->updateObjectToDb($objBlock->getSystemid());
        return $objPageelement;
    }
}