<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Qrcode\System\Scriptlets;

use Kajona\Qrcode\System\Qrcode;
use Kajona\System\System\ScriptletInterface;


/**
 * The qrcode scriptlet generates a qrcode based on the passed url.
 * The second, optional param is a size-value, ranging from 1 to 3
 * The syntax is
 *  [qrcode,url(,size)]
 *
 *
 * @package module_qrcode
 * @since 4.0
 * @author sidler@mulchprod.de
 */
class ScriptletQrcode implements ScriptletInterface
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

        $arrTemp = array();
        preg_match_all("#\[qrcode,([ \?\&\-=:+%;A-Za-z0-9_\./\\\]+)(,[1-3])\]#i", $strContent, $arrTemp);

        foreach ($arrTemp[0] as $intKey => $strSearchString) {

            $intSize = 1;
            $strSubstr = isset($arrTemp[2][$intKey]) ? (int)substr($arrTemp[2][$intKey], 1) : 1;
            if ($strSubstr >= 1 && $strSubstr <= 3) {
                $intSize = $strSubstr;
            }

            $objQrCode = new Qrcode();
            $objQrCode->setIntSize($intSize);
            $strImage = $objQrCode->getImageForString($arrTemp[1][$intKey]);

            $strContent = uniStrReplace(
                $strSearchString,
                _webpath_ . "/" . $strImage,
                $strContent
            );

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
        return ScriptletInterface::BIT_CONTEXT_PORTAL_ELEMENT;
    }

}
