<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Search\System\Scriptlets;

use Kajona\System\System\Carrier;
use Kajona\System\System\ScriptletInterface;
use Kajona\System\System\StringUtil;


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
KAJONA.portal.loader.loadFile('/templates/default/js/jquery.highlight.js', function() { $("body div[class='container']").highlight("{$strHighlight}"); });
JS;

            $strJS = "<script type='text/javascript'>".$strJS."</script><style type='text/css'>.searchHighlight { background-color: #ffff00;}</style>\n";

            $intBodyClose = StringUtil::indexOf($strContent, "</body>", false);
            if ($intBodyClose !== false) {
                $strContent = StringUtil::substring($strContent, 0, $intBodyClose).$strJS.StringUtil::substring($strContent, $intBodyClose);
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
