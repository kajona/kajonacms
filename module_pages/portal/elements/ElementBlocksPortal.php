<?php
/*"******************************************************************************************************
*   (c) 2015-2016 by Kajona, www.kajona.de                                                         *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pages\Portal\Elements;

use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PagesPortalController;
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



    private function getElementsOnBlocks()
    {
        if(PagesPortalController::$arrElementsOnPage != null) {
            $arrElementsOnBlocks = array();

            foreach(PagesPortalController::$arrElementsOnPage as $objOneElement) {
                if($objOneElement->getPrevId() == $this->getSystemid()) {
                    $arrElementsOnBlocks[] = $objOneElement;
                }
            }

        }
        else {
            //load elements below
            $arrElementsOnBlocks = PagesPageelement::getElementsOnPage($this->getSystemid(), !PagesPortaleditor::isActive(), $this->getStrPortalLanguage());
        }

        return $arrElementsOnBlocks;
    }


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
    public function getCacheHashSum()
    {
        $strSum = "";
        $arrElementsOnBlock = $this->getElementsOnBlocks();
        $intCachetime = null;
        foreach($arrElementsOnBlock as $objOneElement) {
            if($objOneElement->getConcretePortalInstance() !== null) {
                $strSum .= $objOneElement->getConcretePortalInstance()->getCacheHashSum();
            }
        }

        return sha1($strSum);
    }

    /**
     * @inheritDoc
     */
    public function getCachetimeInSeconds()
    {
        $intDefault = null;

        foreach($this->getBlockElements() as $objOneElement) {
            $intElCachetime = $objOneElement->getCachetimeInSeconds();
            if($intElCachetime !== null) {
                if ($intDefault === null || $intElCachetime < $intDefault) {
                    $intDefault = (int)$objOneElement->getCachetimeInSeconds();
                }
            }

            if($intDefault === 0) {
                break;
            }
        }


        return $intDefault;
    }


    /**
     * @return ElementBlockPortal[]
     */
    private function getBlockElements()
    {

        if($this->arrBlocks == null) {
            $this->arrBlocks = array();

            //load elements below
            $arrElementsOnBlocks = $this->getElementsOnBlocks();

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
                        
                        if($objElement !== null) {
                            $this->arrBlocks[] = $objElement;
                        }
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

    /**
     * @inheritDoc
     */
    protected function getAnchorTag()
    {
        return "";
    }


}
