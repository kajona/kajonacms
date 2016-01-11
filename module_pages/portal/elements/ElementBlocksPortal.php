<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pages\Portal\Elements;

use class_template;
use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PagesPortaleditor;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\Pages\System\PagesPage;
use Kajona\Pages\System\PagesPageelement;


/**
 * Portal-Part of the blocks-element
 *
 * @author sidler@mulchprod.de
 * @targetTable element_universal.content_id
 */
class ElementBlocksPortal extends ElementPortal implements PortalElementInterface
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
        $arrElementsOnBlocks = PagesPageelement::getElementsOnPage($this->getSystemid(), !PagesPortaleditor::isActive(), $this->getStrPortalLanguage());

        if(count($arrElementsOnBlocks) == 0) {
            return "";
        }

        $objPageData = PagesPage::getPageByName($this->getPagename());

        $objPlaceholders = $this->objTemplate->parsePageTemplate("/module_pages/".$objPageData->getStrTemplate(), class_template::INT_ELEMENT_MODE_REGULAR);

        foreach($objPlaceholders->getArrBlocks() as $objOneBlock) {
            if($objOneBlock->getStrName() == $this->arrElementData["page_element_ph_name"]) {

                foreach($arrElementsOnBlocks as $objOneElement) {
                    /** @var  ElementBlockPortal $objElement  */
                    $objElement = $objOneElement->getConcretePortalInstance();
                    $strReturn .= $objElement->getRenderedElementOutput(PagesPortaleditor::isActive());
//                    $strReturn .= PagesPortaleditor::getPlaceholderWrapper("block_".$objOneElement->getSystemid());
                }

            }
        }

        return $strReturn;
        return '<div data-element="blocks" data-name="content" data-systemid="' . $this->getSystemid() . '">' . $strReturn . '</div>';
    }

    protected function addPortalEditorCode($strElementOutput)
    {
        return $strElementOutput;
    }


}
