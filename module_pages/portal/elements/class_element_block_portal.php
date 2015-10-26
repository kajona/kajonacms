<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Portal-Part of the block-element
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 * @targetTable element_universal.content_id
 */
class class_element_block_portal extends class_element_portal implements interface_portal_element {


    /**
     * Does a little "make-up" to the contents
     *
     * @return string
     */
    public function loadData() {

        $strReturn = "";

        //load elements below
        $arrElementsOnBlock = class_module_pages_pageelement::getElementsOnPage($this->getSystemid(), true, $this->getStrPortalLanguage());

        if(count($arrElementsOnBlock) == 0) {
            return "";
        }

        /** @var class_module_pages_pageelement $objBlocksElement */
        $objBlocksElement = class_objectfactory::getInstance()->getObject($this->getPrevId());

        $objPageData = class_module_pages_page::getPageByName($this->getPagename());

        $objPlaceholders = $this->objTemplate->parsePageTemplate("/module_pages/".$objPageData->getStrTemplate(), class_template::INT_ELEMENT_MODE_REGULAR);

        foreach($objPlaceholders->getArrBlocks() as $objOneBlocks) {

            if($objOneBlocks->getStrName() == $objBlocksElement->getStrName()) {

                foreach ($objOneBlocks->getArrBlocks() as $objOneBlock) {


                    if ($objOneBlock->getStrName() == $this->arrElementData["page_element_ph_name"]) {

                        $arrTemplate = array();

                        foreach ($arrElementsOnBlock as $objOneElement) {



                            /** @var  class_element_portal $objElement */
                            $objElement = $objOneElement->getConcretePortalInstance();

                            $arrTemplate[$objOneElement->getStrPlaceholder()] = $objElement->getElementOutput();
                        }

                        $this->objTemplate->setTemplate($objOneBlock->getStrContent());
                        $strReturn .= $this->objTemplate->fillCurrentTemplate($arrTemplate, false);
                    }


                }
            }
        }

        //TODO: block template replacement

        return $strReturn;
    }

}
