<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pages\Portal\Elements;

use class_objectfactory;
use class_template;
use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PagesPortaleditor;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;


/**
 * Portal-Part of the block-element
 *
 * @author sidler@mulchprod.de
 * @targetTable element_universal.content_id
 */
class ElementBlockPortal extends ElementPortal implements PortalElementInterface {


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

        $objPageData = PagesPage::getPageByName($this->getPagename());

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
    }

}
