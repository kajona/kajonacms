<?php
/*"******************************************************************************************************
*   (c) 2015-2016 by Kajona, www.kajona.de                                                         *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * Namespace for template parser exceptions
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 5.0
 *
 */
class TemplateBlocksParserException extends Exception
{

    private $strSectionWithError = "";

    /**
     * @return string
     */
    public function getStrSectionWithError()
    {
        return $this->strSectionWithError;
    }

    /**
     * @param string $strSectionWithError
     */
    public function setStrSectionWithError($strSectionWithError)
    {
        $this->strSectionWithError = $strSectionWithError;
    }

    

}

