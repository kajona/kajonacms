<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                     *
********************************************************************************************************/

/**
 * Portal-Part of the richtext element
 *
 * @package module_pages
 * @author jschroeter@kajona.de
 * @targetTable element_universal.content_id
 */
class class_element_richtext_portal extends class_element_portal implements interface_portal_element {

    /**
     * Does a little "make-up" to the contents
     *
     * @return string
     */
    public function loadData() {

        $strReturn = "";

        $strTemplate = $this->arrElementData["char1"];
        //fallback
        if($strTemplate == "") {
            $strTemplate = "richtext.tpl";
        }

        $objPageElement = new class_module_pages_pageelement($this->getSystemid());
        $objAdmin = $objPageElement->getConcreteAdminInstance();
        $objAdmin->loadElementData();

        $objMapper = new class_template_mapper($objAdmin);
        return $objMapper->writeToTemplate("/element_richtext/".$strTemplate, "richtext");
    }

}
