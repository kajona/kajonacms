<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System\Scriptlets;

use interface_scriptlet;


/**
 * The imagehelper converts image-placeholders to real urls.
 * The syntax is
 *  [img,path_to_file,maxWidth,maxHeight]
 *
 *
 * @package module_system
 * @since 4.0
 * @author sidler@mulchprod.de
 */
class ScriptletImagehelper implements interface_scriptlet {

    /**
     * Processes the content.
     * Make sure to return the string again, otherwise the output will remain blank.
     *
     * @param string $strContent
     *
     * @return string
     */
    public function processContent($strContent) {

        $arrTemp = array();
        preg_match_all("#\[img,([ \-+%A-Za-z0-9_\./\\\\(\)]+),([0-9]+),([0-9]+)(,fixed|,max|)\]#i", $strContent, $arrTemp);

        foreach($arrTemp[0] as $intKey => $strSearchString) {

            if(isset($arrTemp[4][$intKey]) && $arrTemp[4][$intKey] == ",fixed") {
                $strContent = uniStrReplace(
                    $strSearchString,
                    _webpath_."/image.php?image=".urlencode($arrTemp[1][$intKey])."&amp;fixedWidth=".$arrTemp[2][$intKey]."&amp;fixedHeight=".$arrTemp[3][$intKey],
                    $strContent
                );
            }
            else {
                $strContent = uniStrReplace(
                    $strSearchString,
                    _webpath_."/image.php?image=".urlencode($arrTemp[1][$intKey])."&amp;maxWidth=".$arrTemp[2][$intKey]."&amp;maxHeight=".$arrTemp[3][$intKey],
                    $strContent
                );
            }
        }

        //fast way, no urlencode required
        //$strContent = preg_replace("#\[img,([A-Za-z0-9_\./\\\]+),([0-9]+),([0-9]+)\]#i", _webpath_."/image.php?image=\${1}&amp;maxWidth=\${2}&amp;maxHeight=\${3}", $strContent);


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
        return interface_scriptlet::BIT_CONTEXT_PORTAL_ELEMENT;
    }

}
