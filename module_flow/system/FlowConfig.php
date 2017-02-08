<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Flow\System;

use Kajona\System\System\AdminListableInterface;
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
     * @return FlowStatus[]
     */
    public function getArrStatus()
    {
        return FlowStatus::getObjectListFiltered(null, $this->getStrSystemid());
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
    public function getStepForStatus($intStatus)
    {
        $arrStatus = $this->getArrStatus();
        foreach ($arrStatus as $objStatus) {
            /** @var FlowStatus $objStatus */
            if ($objStatus->getIntStatus() == $intStatus) {
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
                $objFlow->updateObjectToDb();
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
        $objPluginManager = new Pluginmanager(FlowHandlerInterface::EXTENSION_POINT);
        $arrPlugins = $objPluginManager->getPlugins();

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
            if ($objHandler->getStrTargetClass() == $strClass) {
                return $objHandler;
            }
        }
        return null;
    }
}
