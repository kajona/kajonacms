<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id: class_element_downloads_toplist_admin.php 3530 2011-01-06 12:30:26Z sidler $                         *
********************************************************************************************************/

/**
 *
 * @package element_languageredirect
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class class_element_languageredirect_portal extends class_element_portal implements interface_portal_element {

       /**
     * Loads the files, sorts them and generates the output
     *
     * @return string
     */
    public function loadData() {

        $objTargetLang = new class_module_languages_language($this->arrElementData["char1"]);
        if($this->getStrPortalLanguage() != $objTargetLang->getStrName()) {
            $strTemplateID = $this->objTemplate->readTemplate("/element_languageredirect/" . $this->arrElementData["char2"], "languageredirect_wrapper");

            $strTargetHref = getLinkPortalHref($this->getPagename(), "", "", "", "", $objTargetLang->getStrName());
            class_response_object::getInstance()->setStrStatusCode(class_http_statuscodes::SC_REDIRECT);
            $this->portalReload($strTargetHref);
            return $this->fillTemplate(array("redirect_href" => $strTargetHref), $strTemplateID);
        }


        return "";
    }


}
