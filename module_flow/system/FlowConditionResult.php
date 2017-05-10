<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Flow\System;

/**
 * A result object of a condition
 *
 * @author christoph.kappestein@artemeon.de
 * @module flow
 */
class FlowConditionResult
{
    /**
     * @var array
     */
    protected $arrErrors;

    public function __construct()
    {
        $this->arrErrors = [];
    }

    public function isValid()
    {
        return count($this->arrErrors) === 0;
    }

    public function addError(string $strError)
    {
        $this->arrErrors[] = $strError;
    }

    public function getErrors()
    {
        return $this->arrErrors;
    }

    public function merge(FlowConditionResult $objResult)
    {
        $this->arrErrors = array_merge($this->arrErrors, $objResult->getErrors());
    }
}
