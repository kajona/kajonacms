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

        $strReturn = "blocks";


        return $strReturn;
    }

}
