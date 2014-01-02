<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * The imagehelper converts image-placeholders to real urls.
 * The syntax is
 *  [img,path_to_file,maxWidth,maxHeight]
 *
 *
 * @package module_stats
 * @since 4.1
 * @author sidler@mulchprod.de
 */
class class_scriptlet_statscollector implements interface_scriptlet {

    /**
     * Processes the content.
     * Make sure to return the string again, otherwise the output will remain blank.
     *
     * @param string $strContent
     *
     * @return string
     */
    public function processContent($strContent) {

        //process stats request
        $objStats = class_module_system_module::getModuleByName("stats");
        if($objStats != null) {

            //Collect Data
            $objLanguage = new class_module_languages_language();
            $objStats = new class_module_stats_worker();
            $objStats->createStatsEntry(
                getServer("REMOTE_ADDR"), time(), class_carrier::getInstance()->getParam("page"), rtrim(getServer("HTTP_REFERER"), "/"), getServer("HTTP_USER_AGENT"), $objLanguage->getPortalLanguage()
            );


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
        return interface_scriptlet::BIT_CONTEXT_PORTAL_PAGE;
    }

}
