<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pages\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PagesPortaleditor;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\Pages\System\PagesPageelement;
use Kajona\Pages\System\PagesPortaleditorActionEnum;
use Kajona\Pages\System\PagesPortaleditorSystemidAction;
use Kajona\System\System\Exception;
use Kajona\System\System\LanguagesLanguage;
use Kajona\System\System\Link;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\Template;


/**
 * Portal-Part of the block-element
 *
 * @author sidler@mulchprod.de
 * @targetTable element_universal.content_id
 */
class ElementBlockPortal extends ElementPortal implements PortalElementInterface
{


    /**
     * Does a little "make-up" to the contents
     *
     * @return string
     */
    public function loadData()
    {

        $strReturn = "";

        //load elements below
        $arrElementsOnBlock = PagesPageelement::getElementsOnPage($this->getSystemid(), true, $this->getStrPortalLanguage());

        if (count($arrElementsOnBlock) == 0) {
            return "";
        }

        /** @var PagesPageelement $objBlocksElement */
        $objBlocksElement = Objectfactory::getInstance()->getObject($this->getPrevId());

        //fetch the matching page
        $objPageData = Objectfactory::getInstance()->getObject($objBlocksElement->getPrevId());

        $objPlaceholders = $this->objTemplate->parsePageTemplate("/module_pages/".$objPageData->getStrTemplate(), Template::INT_ELEMENT_MODE_REGULAR);


        foreach ($objPlaceholders->getArrBlocks() as $objOneBlocks) {

            if ($objOneBlocks->getStrName() == $objBlocksElement->getStrName()) {

                foreach ($objOneBlocks->getArrBlocks() as $objOneBlock) {

                    if ($objOneBlock->getStrName() == $this->arrElementData["page_element_ph_name"]) {

                        $arrTemplate = array();

                        foreach ($arrElementsOnBlock as $objOneElement) {

                            /** @var  ElementPortal $objElement */
                            $objElement = $objOneElement->getConcretePortalInstance();

                            $arrTemplate[$objOneElement->getStrPlaceholder()] = $objElement->getRenderedElementOutput(PagesPortaleditor::isActive());
                        }

                        $this->objTemplate->setTemplate($objOneBlock->getStrContent());
                        $strReturn .= $this->objTemplate->fillCurrentTemplate($arrTemplate, true);
                    }

                }
            }

        }

        return $strReturn;
    }

    /**
     * @inheritDoc
     */
    public function getCachetimeInSeconds()
    {
        $arrElementsOnBlock = PagesPageelement::getElementsOnPage($this->getSystemid(), true, $this->getStrPortalLanguage());
        $intCachetime = null;
        foreach($arrElementsOnBlock as $objOneElement) {
            $intElTime = $objOneElement->getConcretePortalInstance()->getCachetimeInSeconds();
            if($intCachetime === null || $intElTime < $intCachetime) {
                $intCachetime = (int)$intElTime;
            }

            if($intCachetime === 0) {
                break;
            }
        }

        return $intCachetime !== null ? $intCachetime : 0;
    }


    /**
     * @throws Exception
     */
    public function getPortalEditorActions()
    {

        $objPageelement = new PagesPageelement($this->getSystemid());
        if (!$objPageelement->rightEdit()) {
            return;
        }

        //fetch the language to set the correct admin-lang
        $objLanguages = new LanguagesLanguage();
        $strAdminLangParam = $objLanguages->getPortalLanguage();

        PagesPortaleditor::getInstance()->registerAction(
            new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::COPY(), Link::getLinkAdminHref("pages_content", "copyElement", "&systemid={$this->getSystemid()}&language={$strAdminLangParam}&pe=1"), $this->getSystemid())
        );
        PagesPortaleditor::getInstance()->registerAction(
            new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::DELETE(), Link::getLinkAdminHref("pages_content", "deleteElementFinal", "&systemid={$this->getSystemid()}&language={$strAdminLangParam}&pe=1"), $this->getSystemid())
        );
        PagesPortaleditor::getInstance()->registerAction(
            new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::MOVE(), "", $this->getSystemid())
        );


        PagesPortaleditor::getInstance()->registerAction(
            new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::SETINACTIVE(), Link::getLinkAdminHref("pages_content", "elementStatus", "&systemid={$this->getSystemid()}&language={$strAdminLangParam}&pe=1"), $this->getSystemid())
        );
        PagesPortaleditor::getInstance()->registerAction(
            new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::SETACTIVE(), Link::getLinkAdminHref("pages_content", "elementStatus", "&systemid={$this->getSystemid()}&language={$strAdminLangParam}&pe=1"), $this->getSystemid())
        );

    }

    /**
     * @inheritDoc
     */
    protected function removePortalEditorTags($strElementOutput)
    {
        return $strElementOutput;
    }

}
