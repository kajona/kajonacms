<?php
/*"******************************************************************************************************
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                               *
********************************************************************************************************/

/**
 * Searches for non-replaces languag-entries in the generated content
 *
 * @package module_testing
 * @since 4.1
 * @author sidler@mulchprod.de
 */
class class_scriptlet_langsniffer implements interface_scriptlet {

    /**
     * Processes the content.
     * Make sure to return the string again, otherwise the output will remain blank.
     *
     * @param string $strContent
     *
     * @return string
     */
    public function processContent($strContent) {

        $arrMatches = array();
        if(preg_match_all("/(\![A-Za-z0-9_\-]*)\!/", $strContent, $arrMatches) != 0) {

            foreach($arrMatches[0] as $strOneHit) {
                if($strOneHit != "!!")
                    class_logger::getInstance("langentries.log")->addLogRow("missing lang-entry >".$strOneHit."< found!", class_logger::$levelWarning);
            }
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
        return interface_scriptlet::BIT_CONTEXT_PORTAL_PAGE | interface_scriptlet::BIT_CONTEXT_ADMIN | interface_scriptlet::BIT_CONTEXT_PORTAL_ELEMENT;
    }

}
