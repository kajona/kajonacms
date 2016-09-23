<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Statustransition\System;

use Kajona\System\System\Database;
use Kajona\System\System\Exception;
use Kajona\System\System\FilterBase;
use Kajona\System\System\Model;

/**
 * StatustransitionFlowStep
 *
 * @author christoph.kappestein@artemeon.de
 */
class StatustransitionFlowStepFilter extends FilterBase
{
    /**
     * @var string
     * @tableColumn flow_step.flow_id
     * @tableColumnDatatype char20
     */
    protected $strFlow;

    /**
     * @return string
     */
    public function getStrFlow()
    {
        return $this->strFlow;
    }

    /**
     * @param string $strFlow
     */
    public function setStrFlow($strFlow)
    {
        $this->strFlow = $strFlow;
    }
}
