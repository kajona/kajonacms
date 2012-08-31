<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                         *
********************************************************************************************************/

/**
 * The scriptlet helper is the central place to trigger scriptlets or read meta-infos about the scriptlets currently installed.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class class_scriptlet_helper {


    /**
     * Calls the scriptlets in order to process additional tags and in order to enrich the content.
     *
     * @param $strContent
     * @return string
     */
    public function processString($strContent) {
        $arrScriptletFiles = class_resourceloader::getInstance()->getFolderContent("/system/scriptlets", array(".php"));

        foreach($arrScriptletFiles as $strOneScriptlet) {
            $strOneScriptlet = uniSubstr($strOneScriptlet, 0, -4);
            /** @var $objScriptlet interface_scriptlet */
            $objScriptlet = new $strOneScriptlet();

            if($objScriptlet instanceof interface_scriptlet)
                $strContent = $objScriptlet->processContent($strContent);
        }

        return $strContent;
    }


}

