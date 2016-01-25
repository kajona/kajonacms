<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pages\Portal\Elements;

use class_link;
use class_module_languages_language;
use class_objectfactory;
use class_template;
use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PagesPortaleditor;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\Pages\System\PagesPortaleditorActionEnum;
use Kajona\Pages\System\PagesPortaleditorPlaceholderAction;
use Kajona\Pages\System\PagesPortaleditorSystemidAction;


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
    public function loadData() {

        $strReturn = "";

        //load elements below
        $arrElementsOnBlock = PagesPageelement::getElementsOnPage($this->getSystemid(), true, $this->getStrPortalLanguage());

        if(count($arrElementsOnBlock) == 0) {
            return "";
        }

        /** @var PagesPageelement $objBlocksElement */
        $objBlocksElement = class_objectfactory::getInstance()->getObject($this->getPrevId());

        //fetch the matching page
        $objPageData = class_objectfactory::getInstance()->getObject($objBlocksElement->getPrevId());

        $objPlaceholders = $this->objTemplate->parsePageTemplate("/module_pages/".$objPageData->getStrTemplate(), class_template::INT_ELEMENT_MODE_REGULAR);


        foreach($objPlaceholders->getArrBlocks() as $objOneBlocks) {

            if($objOneBlocks->getStrName() == $objBlocksElement->getStrName()) {

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
        return '<div data-element="block" data-systemid="' . $this->getSystemid() . '">' . $strReturn . '</div>';
    }


    /**
     * @throws \class_exception
     */
    public function getPortalEditorActions()
    {

        $objPageelement = new PagesPageelement($this->getSystemid());
        if (!$objPageelement->rightEdit()) {
            return;
        }

        //fetch the language to set the correct admin-lang
        $objLanguages = new class_module_languages_language();
        $strAdminLangParam = $objLanguages->getPortalLanguage();

        PagesPortaleditor::getInstance()->registerAction(
            new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::COPY(), class_link::getLinkAdminHref("pages_content", "copyElement", "&systemid={$this->getSystemid()}&language={$strAdminLangParam}&pe=1"), $this->getSystemid())
        );
        PagesPortaleditor::getInstance()->registerAction(
            new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::DELETE(), class_link::getLinkAdminHref("pages_content", "deleteElementFinal", "&systemid={$this->getSystemid()}&language={$strAdminLangParam}&pe=1"), $this->getSystemid())
        );
        PagesPortaleditor::getInstance()->registerAction(
            new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::MOVE(), "", $this->getSystemid())
        );


        PagesPortaleditor::getInstance()->registerAction(
            new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::SETINACTIVE(), class_link::getLinkAdminHref("pages_content", "elementStatus", "&systemid={$this->getSystemid()}&language={$strAdminLangParam}&pe=1"), $this->getSystemid())
        );
        PagesPortaleditor::getInstance()->registerAction(
            new PagesPortaleditorSystemidAction(PagesPortaleditorActionEnum::SETACTIVE(), class_link::getLinkAdminHref("pages_content", "elementStatus", "&systemid={$this->getSystemid()}&language={$strAdminLangParam}&pe=1"), $this->getSystemid())
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