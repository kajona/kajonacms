<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * The lang-scriptlet may be used to embed language-file entries into a
 * template. The placeholder is replaced with the matching element from the lang-files.
 * Therefore, the following syntax may be used:
 *  [lang,title,module]
 *
 *
 * @package module_system
 * @since 4.0
 * @author sidler@mulchprod.de
 */
class class_scriptlet_lang implements interface_scriptlet {

    /**
     * Processes the content.
     * Make sure to return the string again, otherwise the output will remain blank.
     *
     * @param string $strContent
     *
     * @return string
     */
    public function processContent($strContent) {

        $objLang = class_carrier::getInstance()->getObjLang();

        $arrTemp = array();
        preg_match_all("#\[lang,([A-Za-z0-9_]+),([0-9A-Za-z_]+)\]#i", $strContent, $arrTemp);

        foreach($arrTemp[0] as $intKey => $strSearchString) {
            $strContent = uniStrReplace($strSearchString, $objLang->getLang($arrTemp[1][$intKey], $arrTemp[2][$intKey]), $strContent);
        }

        return $strContent;
    }

    /**
     * Define the context the scriptlet is applied to.
     * A combination of contexts is allowed using an or-concatenation.
     * Examples:
     *   return interface_scriptlet::BIT_CONTEXT_ADMIN
     *   return interface_scriptlet::BIT_CONTEXT_ADMIN | BIT_CONTEXT_ADMIN::BIT_CONTEXT_PORTAL_ELEMENT
     *
     * @return mixed
     */
    public function getProcessingContext() {
        return interface_scriptlet::BIT_CONTEXT_ADMIN | interface_scriptlet::BIT_CONTEXT_PORTAL_ELEMENT | interface_scriptlet::BIT_CONTEXT_PORTAL_PAGE;
    }

}
