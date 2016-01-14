<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


namespace Kajona\System\System\Scriptlets;

use class_resourceloader;
use interface_scriptlet;

/**
 * A scriptlet to replace script / image src within templates.
 * Provides a switch to check for content in /files/extract rather then the module-folders
 *  [webpath,module_name]
 *
 *
 * @since 5.0
 * @author sidler@mulchprod.de
 */
class ScriptletWebpath implements interface_scriptlet {

    /**
     * Processes the content.
     * Make sure to return the string again, otherwise the output will remain blank.
     *
     * @param string $strContent
     *
     * @return string
     */
    public function processContent($strContent) {

        if($strContent == "")
            return $strContent;

        $arrTemp = array();
        preg_match_all("#\[webpath,([A-Za-z0-9_]+)\]#i", $strContent, $arrTemp);

        foreach($arrTemp[0] as $intKey => $strSearchString) {
            $strContent = uniStrReplace($strSearchString, class_resourceloader::getInstance()->getWebPathForModule($arrTemp[1][$intKey]), $strContent);
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
