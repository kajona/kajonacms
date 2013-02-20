<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
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
            0 => "_indexpath_",
            1 => "_webpath_",
            2 => "_system_browser_cachebuster_",
            3 => "_gentime_"
        );
        $arrValues = array(
            0 => _indexpath_,
            1 => _webpath_,
            2 => _system_browser_cachebuster_,
            3 => date("d.m.y H:i", time())
        );

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
