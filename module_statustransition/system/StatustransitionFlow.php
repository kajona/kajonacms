<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Statustransition\System;

use Kajona\System\System\Database;
use Kajona\System\System\Exception;
use Kajona\System\System\Model;

/**
 * StatustransitionFlow
 *
 * @author christoph.kappestein@artemeon.de
 * @targetTable flow_step.flow_id
 * @module statustransition
 * @moduleId _statustransition_module_id_
 */
class StatustransitionFlow extends Model
{
    /**
     * @var string
     * @tableColumn flow_step.step_name
     * @tableColumnDatatype char20
     */
    protected $strClass;

    /**
     * @return string
     */
    public function getStrClass()
    {
        return $this->strClass;
    }

    /**
     * @param string $strClass
     */
    public function setStrClass($strClass)
    {
        $this->strClass = $strClass;
    }
}
