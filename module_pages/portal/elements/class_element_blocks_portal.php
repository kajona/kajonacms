<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Portal-Part of the blocks-element
 *
 * @package module_pages
 * @author sidler@mulchprod.de
 * @targetTable element_universal.content_id
 */
class class_element_blocks_portal extends class_element_portal implements interface_portal_element {


    /**
     * Does a little "make-up" to the contents
     *
     * @return string
     */
    public function loadData() {

        $strReturn = "";

        //load elements below
        $arrElementsOnBlocks = class_module_pages_pageelement::getElementsOnPage($this->getSystemid(), true, $this->getStrPortalLanguage());

        if(count($arrElementsOnBlocks) == 0) {
            return "";
        }

        $objPageData = class_module_pages_page::getPageByName($this->getPagename());

        $objPlaceholders = $this->objTemplate->parsePageTemplate("/module_pages/".$objPageData->getStrTemplate(), class_template::INT_ELEMENT_MODE_REGULAR);

        foreach($objPlaceholders->getArrBlocks() as $objOneBlock) {
            if($objOneBlock->getStrName() == $this->arrElementData["page_element_ph_name"]) {

                foreach($arrElementsOnBlocks as $objOneElement) {
                    /** @var  class_element_block_portal $objElement  */
                    $objElement = $objOneElement->getConcretePortalInstance();
                    $strReturn .= $objElement->getElementOutput();
                }

            }
        }

//        $this->objTemplate->deleteBlocksFromTemplate($strReturn, class_template_kajona_sections::BLOCKS);
        return $strReturn;
    }

}
