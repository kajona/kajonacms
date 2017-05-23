<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Flow\System;

use AGP\Agp_Commons\System\ArtemeonCommon;
use Kajona\System\System\AdminListableInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\Lang;
use Kajona\System\System\Model;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\Pluginmanager;

/**
 * FlowConfig
 *
 * @author christoph.kappestein@artemeon.de
 * @targetTable flow.flow_id
 * @module flow
 * @moduleId _flow_module_id_
 */
class FlowConfig extends Model implements ModelInterface, AdminListableInterface
{
    /**
     * @var string
     * @tableColumn flow.flow_name
     * @tableColumnDatatype char20
     */
    protected $strName;

    /**
     * @var string
     * @tableColumn flow.flow_target_class
     * @tableColumnDatatype char254
     */
    protected $strTargetClass;

    /**
     * @var string
     * @tableColumn flow.flow_handler_class
     * @tableColumnDatatype char254
     */
    protected $strHandlerClass;

    /**
     * @var FlowHandlerInterface
     */
    private $objHandler;

    /**
     * @var FlowStatus[]
     */
    private $arrStatus;

    /**
     * @var FlowManager
     */
    private $objFlowManager;

    /**
     * @var boolean
     */
    private $bitValidateConsistency = true;

    public function __construct($strSystemid = "")
    {
        parent::__construct($strSystemid);

        $this->objFlowManager = Carrier::getInstance()->getContainer()->offsetGet(ServiceProvider::STR_MANAGER);
    }

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
    public function getStrTargetClass()
    {
        return $this->strTargetClass;
    }

    /**
     * @param string $strTargetClass
     */
    public function setStrTargetClass($strTargetClass)
    {
        $this->strTargetClass = $strTargetClass;
    }

    /**
     * @return string
     */
    public function getStrHandlerClass()
    {
        return $this->strHandlerClass;
    }

    /**
     * @param string $strHandlerClass
     */
    public function setStrHandlerClass($strHandlerClass)
    {
        $this->strHandlerClass = $strHandlerClass;
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
        return $this->getHandler()->getTitle();
    }

    public function getStrLongDescription()
    {
        return Lang::getInstance()->getLang("list_flow_long_description", "flow", [dateToString($this->getObjCreateDate())]);
    }

    /**
     * @return FlowStatus[]
     */
    public function getArrStatus()
    {
        return $this->arrStatus === null ? $this->arrStatus = FlowStatus::getObjectListFiltered(null, $this->getStrSystemid()) : $this->arrStatus;
    }

    /**
     * @param FlowStatus $objStatus
     */
    public function addStep(FlowStatus $objStatus)
    {
        $objStatus->updateObjectToDb($this->getSystemid());
    }

    /**
     * @param int $intStatus
     * @return FlowStatus|null
     */
    public function getStatusByIndex($intStatus)
    {
        $arrStatus = $this->getArrStatus();
        foreach ($arrStatus as $objStatus) {
            /** @var FlowStatus $objStatus */
            if ($objStatus->getIntIndex() == $intStatus) {
                return $objStatus;
            }
        }
        return null;
    }

    /**
     * @return FlowHandlerInterface
     */
    public function getHandler()
    {
        if (!$this->objHandler) {
            $strClass = $this->getStrHandlerClass();
            $this->objHandler = new $strClass($this->objFlowManager);
        }

        return $this->objHandler;
    }

    public function updateObjectToDb($strPrevId = false)
    {
        if ($this->bitValidateConsistency) {
            $this->validateStatus($this->getIntRecordStatus());
        }

        return parent::updateObjectToDb($strPrevId);
    }

    /**
     * If someone wants to set a flow to active we must validate whether we can use this flow on the current data
     * and whether we have another flow which fulfills the requirements of the previous flow
     *
     * @param int $intRecordStatus
     */
    private function validateStatus($intRecordStatus)
    {
        // in case we have no target class we cant validate the status
        $strTargetClass = $this->getStrTargetClass();
        if (empty($strTargetClass)) {
            return;
        }

        // get the current active flow
        $objConfig = FlowConfig::getByModelClass($strTargetClass);
        if ($objConfig instanceof FlowConfig) {
            if ($intRecordStatus == ArtemeonCommon::INT_STATUS_RELEASED) {
                if ($objConfig->getSystemid() == $this->getSystemid()) {
                    // if this is the same object no problem
                } else {
                    // if this is another object we check whether there was not index removed which is used
                    $arrCurrentStatus = $this->getStatusIndexMap($objConfig->getArrStatus());
                    $arrNewStatus = $this->getStatusIndexMap($this->getArrStatus());

                    $arrDiff = array_diff_key($arrCurrentStatus, $arrNewStatus);
                    if (!empty($arrDiff)) {
                        foreach ($arrDiff as $objStatus) {
                            /** @var FlowStatus $objStatus */
                            $objStatus->assertNoRecordsAreAssignedToThisStatus();
                        }
                    }
                }
            } else {
                if ($objConfig->getSystemid() == $this->getSystemid()) {
                    // we can not deactivate this flow if there are records which are assigned to a status other then 1
                    // or 0
                    $arrCurrentStatus = $this->getStatusIndexMap($this->getArrStatus());

                    $arrDiff = array_diff_key($arrCurrentStatus, [ArtemeonCommon::INT_STATUS_CAPTURED, ArtemeonCommon::INT_STATUS_RELEASED]);
                    if (!empty($arrDiff)) {
                        foreach ($arrDiff as $objStatus) {
                            /** @var FlowStatus $objStatus */
                            $objStatus->assertNoRecordsAreAssignedToThisStatus();
                        }
                    }
                }
            }
        }

        if ($intRecordStatus == ArtemeonCommon::INT_STATUS_RELEASED) {
            // we must check that we have the 0 and 1 status
            $arrNeedStatus = [ArtemeonCommon::INT_STATUS_CAPTURED, ArtemeonCommon::INT_STATUS_RELEASED];
            foreach ($arrNeedStatus as $intStatus) {
                $objStatus = $this->getStatusByIndex($intStatus);
                if ($objStatus instanceof FlowStatus) {
                } else {
                    throw new \RuntimeException("It is required that the status " . $intStatus . " is available");
                }
            }

            // validate the status chain of this flow
            $this->validateStatusChain();
        }
    }

    /**
     * Validates whether every step is connected through a transition
     *
     * @throws \RuntimeException
     */
    private function validateStatusChain()
    {
        $arrMap = $this->getStatusIndexTransitions($this->getArrStatus());
        $arrVisited = [];

        $this->walkStatusMap($arrMap, ArtemeonCommon::INT_STATUS_CAPTURED, $arrVisited);

        foreach ($arrMap as $intStatus => $arrTargetStatus) {
            if (!in_array($intStatus, $arrVisited)) {
                throw new \RuntimeException("Status " . $intStatus . " is not used in a transition");
            }
        }
    }

    /**
     * Walks through all transitions and saves the visited status in the array $arrVisited. All status which are not
     * in this array after traversing are not connected through a transition
     *
     * @param array $arrMap
     * @param integer $intStatus
     * @param array $arrVisited
     */
    private function walkStatusMap($arrMap, $intStatus, array &$arrVisited)
    {
        if (in_array($intStatus, $arrVisited)) {
            return;
        }

        $arrVisited[] = $intStatus;

        $arrTransitions = $arrMap[$intStatus];
        foreach ($arrTransitions as $intTargetStatus) {
            $this->walkStatusMap($arrMap, $intTargetStatus, $arrVisited);
        }
    }

    /**
     * Returns an array where the key is the status and the value is the status object
     *
     * @param $arrStatus
     * @return FlowStatus[]
     */
    private function getStatusIndexMap($arrStatus)
    {
        $arrResult = [];
        foreach ($arrStatus as $objStatus) {
            /** @var FlowStatus $objStatus */
            $arrResult[$objStatus->getIntIndex()] = $objStatus;
        }
        return $arrResult;
    }

    /**
     * Returns an array where the key is the status and the value is an array of possible target status
     *
     * @param $arrStatus
     * @return array
     */
    private function getStatusIndexTransitions($arrStatus)
    {
        $arrResult = [];
        foreach ($arrStatus as $objStatus) {
            /** @var FlowStatus $objStatus */
            $arrTransitions = array_map(function(FlowTransition $objTransition){
                return $objTransition->getTargetStatus()->getIntIndex();
            }, $objStatus->getArrTransitions());

            $arrResult[$objStatus->getIntIndex()] = $arrTransitions;
        }
        return $arrResult;
    }

    /**
     * @param string $strNewPrevid
     * @param bool $bitChangeTitle
     * @param bool $bitCopyChilds
     */
    public function copyObject($strNewPrevid = "", $bitChangeTitle = true, $bitCopyChilds = true)
    {
        $arrSystemIdName = [];
        $arrStatus = $this->getArrStatus();
        foreach ($arrStatus as $objStatus) {
            $arrSystemIdName[$objStatus->getSystemid()] = $objStatus->getStrName();
        }

        $this->setIntRecordStatus(0);

        $bitReturn = parent::copyObject($strNewPrevid, $bitChangeTitle, $bitCopyChilds);

        $this->arrStatus = null;

        $arrNameSystemId = [];
        $arrStatus = $this->getArrStatus();
        foreach ($arrStatus as $objStatus) {
            $arrNameSystemId[$objStatus->getStrName()] = $objStatus->getSystemid();
        }

        // fix the target status systemids of the transitions
        $arrStatus = $this->getArrStatus();
        foreach ($arrStatus as $objStatus) {
            $arrTransitions = $objStatus->getArrTransitions();
            foreach ($arrTransitions as $objTransition) {
                $strName = $arrSystemIdName[$objTransition->getStrTargetStatus()];
                $strNewSystemId = $arrNameSystemId[$strName];
                $objTransition->setStrTargetStatus($strNewSystemId);
                $objTransition->updateObjectToDb();
            }
        }

        return $bitReturn;
    }

    /**
     * @return bool
     */
    public function getBitValidateConsistency(): bool
    {
        return $this->bitValidateConsistency;
    }

    /**
     * @param bool $bitValidateConsistency
     */
    public function setBitValidateConsistency(bool $bitValidateConsistency)
    {
        $this->bitValidateConsistency = $bitValidateConsistency;
    }

    /**
     * Reads all available handler from the file system and syncs them with the database
     */
    public static function syncHandler()
    {
        $arrResult = self::getObjectListFiltered();
        $arrDbHandler = [];
        foreach ($arrResult as $objFlow) {
            /** @var FlowConfig $objFlow */
            $arrDbHandler[$objFlow->getStrHandlerClass()] = $objFlow;
        }

        $arrFileHandler = self::getAvailableHandler();
        foreach ($arrFileHandler as $strClass => $objHandler) {
            /** @var FlowHandlerInterface $objHandler */
            if (!isset($arrDbHandler[$strClass])) {
                $objFlow = new FlowConfig();
                $objFlow->setStrName($objHandler->getTitle());
                $objFlow->setStrTargetClass($objHandler->getTargetClass());
                $objFlow->setStrHandlerClass($strClass);
                $objFlow->setIntRecordStatus(0);
                $objFlow->updateObjectToDb();

                // we create automatically the start and end status
                $objRedStatus = new FlowStatus();
                $objRedStatus->setStrName("In Bearbeitung");
                $objRedStatus->setStrIcon("icon_flag_red");
                $objRedStatus->updateObjectToDb($objFlow->getSystemid());

                $objGreenStatus = new FlowStatus();
                $objGreenStatus->setStrName("Freigegeben");
                $objGreenStatus->setStrIcon("icon_flag_green");
                $objGreenStatus->updateObjectToDb($objFlow->getSystemid());

                $objTransition = new FlowTransition();
                $objTransition->setStrTargetStatus($objGreenStatus->getSystemid());
                $objTransition->updateObjectToDb($objRedStatus->getSystemid());
            } else {
                // @TODO maybe update
            }
        }
    }

    /**
     * Returns all available handler classes
     *
     * @return FlowHandlerInterface[]
     */
    public static function getAvailableHandler()
    {
        $objFlowManager = Carrier::getInstance()->getContainer()->offsetGet(ServiceProvider::STR_MANAGER);
        $objPluginManager = new Pluginmanager(FlowHandlerInterface::EXTENSION_POINT);
        $arrPlugins = $objPluginManager->getPlugins([$objFlowManager]);

        /** @var FlowHandlerInterface[] $arrHandler */
        $arrHandler = array();
        foreach ($arrPlugins as $objPlugin) {
            if ($objPlugin instanceof FlowHandlerInterface) {
                $arrHandler[get_class($objPlugin)] = $objPlugin;
            }
        }

        return $arrHandler;
    }

    /**
     * @param string $strClass
     * @return FlowConfig|null
     */
    public static function getByModelClass(string $strClass)
    {
        $arrResult = self::getObjectListFiltered();
        foreach ($arrResult as $objHandler) {
            /** @var FlowConfig $objHandler */
            if ($objHandler->getIntRecordStatus() === 1 && $objHandler->getStrTargetClass() == $strClass) {
                return $objHandler;
            }
        }
        return null;
    }
}
