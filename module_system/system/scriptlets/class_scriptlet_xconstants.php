<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * General replacement of global constants such as the webpath
 *
 * @package module_system
 * @since 4.0
 * @author sidler@mulchprod.de
 */
class class_scriptlet_xconstants implements interface_scriptlet {

    /**
     * Processes the content.
     * Make sure to return the string again, otherwise the output will remain blank.
     *
     * @param string $strContent
     *
     * @return string
     */
    public function processContent($strContent) {

        if(!defined("_system_browser_cachebuster_"))
            define("_system_browser_cachebuster_", 0);

        $arrConstants = array(
            "_indexpath_",
            "_webpath_",
            "_system_browser_cachebuster_",
            "_gentime_"
        );
        $arrValues = array(
            _indexpath_,
            _webpath_,
            class_module_system_setting::getConfigValue("_system_browser_cachebuster_"),
            date("d.m.y H:i", time())
        );

        if(defined("_packagemanager_defaulttemplate_")) {
            $arrConstants[] = "_packagemanager_defaulttemplate_";
            $arrValues[] = class_module_system_setting::getConfigValue("_packagemanager_defaulttemplate_");
        }

        if(defined("_skinwebpath_")) {
            $arrConstants[] = "_skinwebpath_";
            $arrValues[] = _skinwebpath_;
        }


        return str_replace($arrConstants, $arrValues, $strContent);
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
        return interface_scriptlet::BIT_CONTEXT_PORTAL_PAGE | interface_scriptlet::BIT_CONTEXT_ADMIN;
    }

}
