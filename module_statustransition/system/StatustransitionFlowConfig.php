<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Statustransition\System;

use Kajona\System\System\AdminListableInterface;
use Kajona\System\System\Database;
use Kajona\System\System\Model;
use Kajona\System\System\ModelInterface;
use Kajona\System\System\Pluginmanager;

/**
 * StatustransitionFlowConfig
 *
 * @author christoph.kappestein@artemeon.de
 * @targetTable flow.flow_id
 * @module statustransition
 * @moduleId _statustransition_module_id_
 */
class StatustransitionFlowConfig extends Model implements ModelInterface, AdminListableInterface
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
     * @var StatustransitionHandlerInterface
     */
    private $objHandler;

    /**
     * @var array
     */
    private $arrStatus;

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
        return "";
    }

    /**
     * @return StatustransitionFlowStep[]
     */
    public function getSteps()
    {
        return StatustransitionFlowStep::getObjectListFiltered(null, $this->getStrSystemid());
    }

    /**
     * @param StatustransitionFlowStep $objStep
     */
    public function addStep(StatustransitionFlowStep $objStep)
    {
        $objStep->updateObjectToDb($this->getSystemid());
    }

    /**
     * @param int $intStatus
     * @return StatustransitionFlowStep|null
     */
    public function getStepForStatus($intStatus)
    {
        $arrSteps = StatustransitionFlowStep::getObjectListFiltered(null, $this->getStrSystemid());
        foreach ($arrSteps as $objStep) {
            /** @var StatustransitionFlowStep $objStep */
            if ($objStep->getIntStatus() == $intStatus) {
                return $objStep;
            }
        }
        return null;
    }

    /**
     * @return StatustransitionHandlerInterface
     */
    public function getHandler()
    {
        if (!$this->objHandler) {
            $strClass = $this->getStrHandlerClass();
            $this->objHandler = new $strClass();
        }

        return $this->objHandler;
    }

    /**
     *
     */
    public static function syncHandler()
    {
        $arrResult = self::getObjectListFiltered();
        $arrDbHandler = [];
        foreach ($arrResult as $objHandler) {
            /** @var StatustransitionFlowConfig $objHandler */
            $arrDbHandler[$objHandler->getStrHandlerClass()] = $objHandler;
        }

        $arrFileHandler = self::getAvailableHandler();
        foreach ($arrFileHandler as $strClass => $objHandler) {
            /** @var StatustransitionHandlerInterface $objHandler */
            if (!isset($arrDbHandler[$strClass])) {
                $objFlow = new StatustransitionFlowConfig();
                $objFlow->setStrName($objHandler->getTitle());
                $objFlow->setStrTargetClass($objHandler->getTargetClass());
                $objFlow->setStrHandlerClass($strClass);
                $objFlow->updateObjectToDb();
            } else {
                // @TODO maybe update
            }
        }
    }

    /**
     * Returns all available handler classes
     *
     * @return StatustransitionHandlerInterface[]
     */
    public static function getAvailableHandler()
    {
        $objPluginManager = new Pluginmanager(StatustransitionHandlerInterface::EXTENSION_POINT);
        $arrPlugins = $objPluginManager->getPlugins();

        /** @var StatustransitionHandlerInterface[] $arrHandler */
        $arrHandler = array();
        foreach ($arrPlugins as $objPlugin) {
            if ($objPlugin instanceof StatustransitionHandlerInterface) {
                $arrHandler[get_class($objPlugin)] = $objPlugin;
            }
        }

        return $arrHandler;
    }

    /**
     * @param string $strClass
     * @return StatustransitionFlowConfig|null
     */
    public static function getByModelClass(string $strClass)
    {
        $arrResult = self::getObjectListFiltered();
        foreach ($arrResult as $objHandler) {
            /** @var StatustransitionFlowConfig $objHandler */
            if ($objHandler->getStrTargetClass() == $strClass) {
                return $objHandler;
            }
        }
        return null;
    }
}
