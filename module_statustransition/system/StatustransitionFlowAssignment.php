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
use Kajona\System\System\ModelInterface;

/**
 * StatustransitionFlowAssignmentFilter
 *
 * @author christoph.kappestein@artemeon.de
 * @targetTable flow_assign.assign_id
 * @module statustransition
 * @moduleId _statustransition_module_id_
 */
class StatustransitionFlowAssignment extends Model implements ModelInterface
{
    /**
     * @var string
     * @tableColumn flow_assign.assign_class
     * @tableColumnDatatype char254
     */
    protected $strClass;

    /**
     * @var string
     * @tableColumn flow_assign.assign_key
     * @tableColumnDatatype char20
     */
    protected $strKey;

    /**
     * @var string
     * @tableColumn flow_assign.assign_flow
     * @tableColumnDatatype char20
     */
    protected $strFlow;

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

    /**
     * @return string
     */
    public function getStrKey()
    {
        return $this->strKey;
    }

    /**
     * @param string $strKey
     */
    public function setStrKey($strKey)
    {
        $this->strKey = $strKey;
    }

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

    /**
     * @return string
     */
    public function getStrDisplayName()
    {
        return $this->strClass;
    }
}
