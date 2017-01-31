<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System\Scriptlets;

use Kajona\System\System\Carrier;
use Kajona\System\System\ScriptletInterface;
use Kajona\System\System\StringUtil;


/**
 * The lang-scriptlet may be used to embed language-file entries into a
 * template. The placeholder is replaced with the matching element from the lang-files.
 * Therefore, the following syntax may be used:
 *  [lang,title,module]
 *
 *
 * @package module_system
 * @since 4.0
 * @author sidler@mulchprod.de
 */
class ScriptletLang implements ScriptletInterface
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

        $objLang = Carrier::getInstance()->getObjLang();

        $arrTemp = array();
        preg_match_all("#\[lang,([A-Za-z0-9_]+),([0-9A-Za-z_]+)\]#i", $strContent, $arrTemp);

        foreach ($arrTemp[0] as $intKey => $strSearchString) {
            $strContent = StringUtil::replace($strSearchString, $objLang->getLang($arrTemp[1][$intKey], $arrTemp[2][$intKey]), $strContent);
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
        return ScriptletInterface::BIT_CONTEXT_ADMIN | ScriptletInterface::BIT_CONTEXT_PORTAL_ELEMENT | ScriptletInterface::BIT_CONTEXT_PORTAL_PAGE;
    }

}
