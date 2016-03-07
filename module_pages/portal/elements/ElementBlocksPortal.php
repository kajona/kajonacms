<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pages\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PagesPortaleditor;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;
use Kajona\System\System\Template;


/**
 * Portal-Part of the blocks-element
 *
 * @author sidler@mulchprod.de
 * @targetTable element_universal.content_id
 */
class ElementBlocksPortal extends ElementPortal implements PortalElementInterface
{


    private $arrBlocks = null;


    /**
     * Does a little "make-up" to the contents
     *
     * @return string
     */
    public function loadData()
    {
        $strReturn = "";
        foreach ($this->getBlockElements() as $objElement) {
            $strReturn .= $objElement->getRenderedElementOutput(PagesPortaleditor::isActive());
        }

        return $strReturn;
    }

    /**
     * @inheritDoc
     */
    public function getCachetimeInSeconds()
    {
        $intDefault = null;

        foreach($this->getBlockElements() as $objOneElement) {
            $intElCachetime = $objOneElement->getCachetimeInSeconds();
            if($intDefault === null || $intElCachetime < $intDefault) {
                $intDefault = (int)$objOneElement->getCachetimeInSeconds();
            }

            if($intDefault === 0) {
                break;
            }
        }


        return $intDefault !== null ? $intDefault : 0;
    }


    /**
     * @return ElementBlockPortal[]
     */
    private function getBlockElements()
    {

        if($this->arrBlocks == null) {
            $this->arrBlocks = array();

            //load elements below
            $arrElementsOnBlocks = PagesPageelement::getElementsOnPage($this->getSystemid(), !PagesPortaleditor::isActive(), $this->getStrPortalLanguage());

            if (count($arrElementsOnBlocks) == 0) {
                return array();
            }

            $objPageData = PagesPage::getPageByName($this->getPagename());

            $objPlaceholders = $this->objTemplate->parsePageTemplate("/module_pages/".$objPageData->getStrTemplate(), Template::INT_ELEMENT_MODE_REGULAR);

            foreach ($objPlaceholders->getArrBlocks() as $objOneBlock) {
                if ($objOneBlock->getStrName() == $this->arrElementData["page_element_ph_name"]) {

                    foreach ($arrElementsOnBlocks as $objOneElement) {
                        /** @var  ElementBlockPortal $objElement */
                        $objElement = $objOneElement->getConcretePortalInstance();
                        $this->arrBlocks[] = $objElement;
                    }

                }
            }


        }

        return $this->arrBlocks;

    }

    protected function addPortalEditorCode($strElementOutput)
    {
        return $strElementOutput;
    }


}
