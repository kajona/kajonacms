<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Flow\System;

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
        return $this->getStrTargetClass();
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

    private function validateFlow()
    {
        $arrStatus = $this->getArrStatus();

        if (count($arrStatus) < 2) {
            throw new \InvalidArgumentException("Flow must have at least two status");
        }

        // check whether there is a flow between the start and end status
        $arrMatrix = $this->getDependencyMatrix();
        $arrVisited = [];
        if (!$this->findWayThroughFlow($arrMatrix, 0, 1, $arrVisited)) {
            throw new \InvalidArgumentException("Inconsistent status flow");
        }

        // we have nodes which are not connected to the flow
        $arrVisited = array_unique($arrVisited);
        if (count($arrVisited) != $arrStatus) {
            throw new \InvalidArgumentException("Inconsistent status flow");
        }
    }

    /**
     * Recursive method to determine whether all status entries are connected
     *
     * @param array $arrMatrix
     * @param int $intFromStatus
     * @param int $intToStatus
     * @param array $arrVisited
     * @return bool
     */
    private function findWayThroughFlow(array $arrMatrix, $intFromStatus, $intToStatus, array &$arrVisited) : bool
    {
        if ($intFromStatus === $intToStatus) {
            return true;
        }

        if (in_array($intFromStatus, $arrVisited)) {
            return false;
        }

        if (isset($arrMatrix[$intFromStatus])) {
            $arrVisited[] = $intFromStatus;

            foreach ($arrMatrix[$intFromStatus] as $intStatus) {
                if ($this->findWayThroughFlow($arrMatrix, $intStatus, $intToStatus, $arrVisited)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return array
     */
    private function getDependencyMatrix() : array
    {
        $arrStatus = $this->getArrStatus();
        $arrMatrix = [];
        foreach ($arrStatus as $objStatus) {
            $arrTransitions = $objStatus->getArrTransitions();
            $arrTargets = [];
            foreach ($arrTransitions as $objTransition) {
                $arrTargets[] = $objTransition->getTargetStatus()->getIntIndex();
            }
            $arrMatrix[$objStatus->getIntIndex()] = $arrTargets;
        }

        return $arrMatrix;
    }

    /**
     * @param string $strNewPrevid
     * @param bool $bitChangeTitle
     * @param bool $bitCopyChilds
     */
    public function copyObject($strNewPrevid = "", $bitChangeTitle = true, $bitCopyChilds = true)
    {
        $bitReturn = parent::copyObject($strNewPrevid, $bitChangeTitle, $bitCopyChilds);

        $this->setIntRecordStatus(0);
        $this->updateObjectToDb();

        return $bitReturn;
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
