<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Flow\System;

use Kajona\System\System\AdminListableInterface;
use Kajona\System\System\Database;
use Kajona\System\System\IdGenerator;
use Kajona\System\System\Model;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\Objectfactory;

/**
 * FlowStatus
 *
 * @author christoph.kappestein@artemeon.de
 * @targetTable flow_step.step_id
 * @module flow
 * @moduleId _flow_module_id_
 * @formGenerator Kajona\Flow\Admin\FlowStatusFormgenerator
 * @sortManager Kajona\System\System\CommonSortmanager
 */
class FlowStatus extends Model implements ModelInterface, AdminListableInterface
{
    /**
     * @var string
     * @tableColumn flow_step.step_name
     * @tableColumnDatatype char254
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldMandatory
     */
    protected $strName;

    /**
     * @var integer
     * @tableColumn flow_step.step_index
     * @tableColumnDatatype int
     */
    protected $intIndex;

    /**
     * @var string
     * @tableColumn flow_step.step_icon
     * @tableColumnDatatype char20
     * @fieldType Kajona\System\Admin\Formentries\FormentryDropdown
     * @fieldDDValues [icon_flag_black => flow_step_icon_0],[icon_flag_blue => flow_step_icon_1],[icon_flag_brown => flow_step_icon_2],[icon_flag_green => flow_step_icon_3],[icon_flag_grey => flow_step_icon_4],[icon_flag_orange => flow_step_icon_5],[icon_flag_purple => flow_step_icon_6],[icon_flag_red => flow_step_icon_7],[icon_flag_yellow => flow_step_icon_8]
     * @fieldMandatory
     */
    protected $strIcon;

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
     * @return int
     */
    public function getIntIndex()
    {
        return $this->intIndex;
    }

    /**
     * @param int $intIndex
     */
    public function setIntIndex($intIndex)
    {
        $this->intIndex = $intIndex;
    }

    /**
     * @return string
     */
    public function getStrIcon()
    {
        return $this->strIcon;
    }

    /**
     * @param string $strIcon
     */
    public function setStrIcon($strIcon)
    {
        $this->strIcon = $strIcon;
    }

    /**
     * Return the status int for this step
     *
     * @return int
     */
    public function getIntStatus()
    {
        return $this->getIntIndex();
    }

    /**
     * Returns all available transitions
     *
     * @return FlowTransition[]
     */
    public function getArrTransitions()
    {
        return FlowTransition::getObjectListFiltered(null, $this->getSystemid());
    }

    /**
     * @param FlowTransition $objTransition
     */
    public function addTransition(FlowTransition $objTransition)
    {
        $objTransition->updateObjectToDb($this->getSystemid());
    }

    /**
     * @return string
     */
    public function getStrDisplayName()
    {
        return $this->strName;
    }

    /**
     * @return FlowConfig
     */
    public function getFlowConfig()
    {
        return Objectfactory::getInstance()->getObject($this->getPrevId());
    }

    public function getStrAdditionalInfo()
    {
        return "";
    }

    public function getStrLongDescription()
    {
        return "";
    }

    public function updateObjectToDb($strPrevId = false)
    {
        // set index if we create a new record
        if (!validateSystemid($this->getSystemid())) {
            $this->intIndex = IdGenerator::generateNextId($this->getPrevId()) - 1;
        }

        return parent::updateObjectToDb($strPrevId);
    }

    public function deleteObject()
    {
        $this->assertNoRecordsAreAssignedToThisStatus();

        parent::deleteObject();
    }

    public function deleteObjectFromDatabase()
    {
        $this->assertNoRecordsAreAssignedToThisStatus();

        parent::deleteObjectFromDatabase();
    }

    private function assertNoRecordsAreAssignedToThisStatus()
    {
        $dbPrefix = _dbprefix_;
        $strTargetClass = $this->getFlowConfig()->getStrTargetClass();
        $intStatus = $this->getIntStatus();
        $arrRow = Database::getInstance()->getPRow("SELECT COUNT(*) AS cnt FROM {$dbPrefix}system WHERE system_class = ? AND system_status = ?", [$strTargetClass, $intStatus]);
        $intCount = isset($arrRow["cnt"]) ? (int) $arrRow["cnt"] : 0;

        if ($intCount > 0) {
            throw new \RuntimeException("There are already " . $intCount . " records assigned to the status " . $intStatus);
        }
    }
}
