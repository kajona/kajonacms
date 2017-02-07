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
 * StatustransitionFlow
 *
 * @author christoph.kappestein@artemeon.de
 * @targetTable flow.flow_id
 * @module statustransition
 * @moduleId _statustransition_module_id_
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
     * @var string
     * @tableColumn flow.flow_class
     * @tableColumnDatatype char254
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryText
     * @fieldMandatory
     */
    protected $strClass;

    /**
     * @var StatustransitionHandlerInterface
     */
    private $objHandler;

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
    public function getStrClass()
    {
        return $this->strClass;
    }

    /**
     * @param string $strClass
     */
    public function setStrClass(string $strClass)
    {
        $this->strClass = $strClass;
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
        return $this->strClass;
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
     * @return StatustransitionHandlerInterface
     */
    public function getHandler()
    {
        if (!$this->objHandler) {
            $strClass = $this->getStrClass();
            $this->objHandler = new $strClass();
        }

        return $this->objHandler;
    }

    public function getInitialStatus()
    {
        return 0;
    }

    /**
     *
     */
    public static function syncHandler()
    {
        $arrResult = self::getObjectListFiltered();
        $arrDbHandler = [];
        foreach ($arrResult as $objHandler) {
            $arrDbHandler[$objHandler->getStrClass()] = $objHandler;
        }

        $arrFileHandler = self::getAvailableHandler();
        foreach ($arrFileHandler as $strClass => $strTitle) {
            if (!isset($arrDbHandler[$strClass])) {
                $objHandler = new StatustransitionFlow();
                $objHandler->setStrName($strTitle);
                $objHandler->setStrClass($strClass);
                $objHandler->updateObjectToDb();
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
                $arrHandler[get_class($objPlugin)] = $objPlugin->getTitle();
            }
        }

        return $arrHandler;
    }

    /**
     * @param string $strClass
     * @return StatustransitionFlow|null
     */
    public static function getByClass(string $strClass)
    {
        $arrResult = self::getObjectListFiltered();
        foreach ($arrResult as $objHandler) {
            if ($objHandler->getStrClass() == $strClass) {
                return $objHandler;
            }
        }
        return null;
    }
}
