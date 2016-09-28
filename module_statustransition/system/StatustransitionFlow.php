<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Statustransition\System;

use Kajona\System\System\AdminListableInterface;
use Kajona\System\System\Database;
use Kajona\System\System\Exception;
use Kajona\System\System\Model;
use Kajona\System\System\ModelInterface;

/**
 * StatustransitionFlow
 *
 * @author christoph.kappestein@artemeon.de
 * @targetTable flow.flow_id
 * @module statustransition
 * @moduleId _statustransition_module_id_
 * @formGenerator Kajona\Statustransition\Admin\StatustransitionFormgenerator
 */
class StatustransitionFlow extends Model implements ModelInterface, AdminListableInterface
{
    /**
     * @var string
     * @tableColumn flow.flow_name
     * @tableColumnDatatype char20
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldMandatory
     */
    protected $strName;

    /**
     * @return string
     */
    public function getStrName()
    {
        return $this->strName;
    }

    /**
     * @param string $strName
     */
    public function setStrName($strName)
    {
        $this->strName = $strName;
    }

    /**
     * @return string
     */
    public function getStrDisplayName()
    {
        return $this->strName;
    }

    public function getStrIcon()
    {
        return "icon_play";
    }

    public function getStrAdditionalInfo()
    {
        return "";
    }

    public function getStrLongDescription()
    {
        return "";
    }

    /**
     * @return StatustransitionFlow[]
     */
    public function getSteps()
    {
        return StatustransitionFlowStep::getObjectListFiltered(null, $this->getStrSystemid());
    }
}
