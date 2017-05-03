<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                         *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * The scriptlet helper is the central place to trigger scriptlets or read meta-infos about the scriptlets currently installed.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.0
 */
class ScriptletHelper
{


    /**
     * Calls the scriptlets in order to process additional tags and in order to enrich the content.
     *
     * @param string $strContent the content to process
     * @param int $intContext context-selector used to find the matching scriptlets to apply. if not given, all contexts are applied - worst case!
     *
     * @return string
     * @see ScriptletInterface
     */
    public function processString($strContent, $intContext = null)
    {
        $arrScriptletFiles = Resourceloader::getInstance()->getFolderContent("/system/scriptlets", array(".php"));

        foreach ($arrScriptletFiles as $strPath => $strOneScriptlet) {

            /** @var $objScriptlet ScriptletInterface */
            $objScriptlet = Classloader::getInstance()->getInstanceFromFilename($strPath, "", "Kajona\\System\\System\\ScriptletInterface");

            if ($objScriptlet != null && ($intContext == null || ($intContext & $objScriptlet->getProcessingContext()))) {
                $strContent = $objScriptlet->processContent($strContent);
                Logger::getInstance("scriptlets.log")->info("processing call to ".$strOneScriptlet.", filter: ".$intContext);
            }

        }

        return $strContent;
    }


}

