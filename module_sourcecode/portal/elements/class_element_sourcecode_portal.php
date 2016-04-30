<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

/**
 * Loads the sourcecode specified in the element-settings and prepares the output
 *
 * @package element_sourcecode
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class class_element_sourcecode_portal extends class_element_portal implements interface_portal_element {

    /**
     * Loads the feed and displays it
     *
     * @return string the prepared html-output
     */
    public function loadData() {
        $strTemplateID = $this->objTemplate->readTemplate("/element_sourcecode/" . $this->arrElementData["char1"], "sourcecode");
        return $this->objTemplate->fillTemplate(array("content_id" => $this->arrElementData["content_id"], "code" => nl2br($this->arrElementData["text"])), $strTemplateID);
    }

}
