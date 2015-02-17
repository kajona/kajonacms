<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                     *
********************************************************************************************************/

/**
 * @package element_facebooklikebox
 * @author jschroeter@kajona.de
 * @targetTable element_universal.content_id
 */
class class_element_facebooklikebox_portal extends class_element_portal implements interface_portal_element {


    /**
     * Renders the template
     *
     * @return string the prepared html-output
     */
    public function loadData() {
        $strLanguage = $this->getStrPortalLanguage();
        //load the template
        $strTemplateID = $this->objTemplate->readTemplate("/element_facebooklikebox/".$this->arrElementData["char1"], "facebooklikebox");
        $strReturn = $this->fillTemplate(array("portallanguage" => $strLanguage), $strTemplateID);
        return "adfsdfs".$strReturn;
    }

}
