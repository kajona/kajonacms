<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Search\System\Scriptlets;

use Kajona\System\System\Carrier;
use Kajona\System\System\ScriptletInterface;


/**
 * Replaces searched words with a highlighted background.
 * Calls the relevant script entries.
 *
 *
 * @package module_search
 * @since 4.0
 * @author sidler@mulchprod.de
 */
class ScriptletSearchhighlight implements ScriptletInterface
{

    /**
     * Processes the content.
     * Make sure to return the string again, otherwise the output will remain blank.
     *
     * @param string $strContent
     *
     * @return string
     */
    public function processContent($strContent)
    {

        $strHighlight = trim(Carrier::getInstance()->getParam("highlight"));
        if ($strHighlight != "") {
            $strHighlight = strip_tags($strHighlight);
            $strJS = <<<JS
KAJONA.portal.loader.loadFile('/templates/default/js/jquery.highlight.js', function() { $("body div[class='contentRight']").highlight("{$strHighlight}"); });
JS;

            $strJS = "<script type=\"text/javascript\">".$strJS."</script>\n";

            $intBodyClose = uniStripos($strContent, "</body>");
            if ($intBodyClose !== false) {
                $strContent = uniSubstr($strContent, 0, $intBodyClose).$strJS.uniSubstr($strContent, $intBodyClose);
            }

        }

        return $strContent;
    }

    /**
     * Define the context the scriptlet is applied to.
     * A combination of contexts is allowed using an or-concatenation.
     * Examples:
     *   return ScriptletInterface::BIT_CONTEXT_ADMIN
     *   return ScriptletInterface::BIT_CONTEXT_ADMIN | ScriptletInterface::BIT_CONTEXT_PORTAL_ELEMENT
     *
     * @return mixed
     */
    public function getProcessingContext()
    {
        return ScriptletInterface::BIT_CONTEXT_PORTAL_PAGE;
    }

}
