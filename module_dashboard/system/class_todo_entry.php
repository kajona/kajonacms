<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

/**
 * Object which represents a todo entry
 *
 * @package module_dashboard
 * @author christoph.kappestein@gmail.com
 */
class class_todo_entry implements interface_admin_listable, interface_model
{
    /**
     * @var string
     */
    protected $strCategory;

    /**
     * @var string
     */
    protected $strSystemId;

    /**
     * @var string
     */
    protected $strIcon;

    /**
     * @var string
     */
    protected $strDisplayName;

    /**
     * @var string
     */
    protected $strAdditionalInfo;

    /**
     * @var string
     */
    protected $strLongDescription;

    /**
     * @var class_date
     */
    protected $objValidDate;

    /**
     * @var array
     */
    protected $arrModuleNavi;

    /**
     * Internal cache
     *
     * @var array
     */
    protected static $categories = array();

    /**
     * Internal cache
     *
     * @var array
     */
    protected static $todos = array();

    /**
     * @return string
     */
    public function getStrCategory()
    {
        return $this->strCategory;
    }

    /**
     * @return string
     */
    public function getSystemid()
    {
        return $this->strSystemId;
    }

    /**
     * @return string
     */
    public function getStrSystemId()
    {
        return $this->strSystemId;
    }

    /**
     * @return string
     */
    public function getStrIcon()
    {
        return $this->strIcon;
    }

    /**
     * @return string
     */
    public function getStrAdditionalInfo()
    {
        return $this->strAdditionalInfo;
    }

    /**
     * @return string
     */
    public function getStrLongDescription()
    {
        return $this->strLongDescription;
    }

    /**
     * @return string
     */
    public function getStrDisplayName()
    {
        return $this->strDisplayName;
    }

    /**
     * @param string $strSystemId
     */
    public function setStrSystemId($strSystemId)
    {
        $this->strSystemId = $strSystemId;
    }

    /**
     * @param string $strIcon
     */
    public function setStrIcon($strIcon)
    {
        $this->strIcon = $strIcon;
    }

    /**
     * @return class_date
     */
    public function getObjValidDate()
    {
        return $this->objValidDate;
    }

    /**
     * @return array
     */
    public function getArrModuleNavi()
    {
        return $this->arrModuleNavi;
    }

    /**
     * @param string $strCategory
     */
    public function setStrCategory($strCategory)
    {
        $this->strCategory = $strCategory;
    }

    /**
     * @param string $strDisplayName
     */
    public function setStrDisplayName($strDisplayName)
    {
        $this->strDisplayName = $strDisplayName;
    }

    /**
     * @param string $strAdditionalInfo
     */
    public function setStrAdditionalInfo($strAdditionalInfo)
    {
        $this->strAdditionalInfo = $strAdditionalInfo;
    }

    /**
     * @param string $strLongDescription
     */
    public function setStrLongDescription($strLongDescription)
    {
        $this->strLongDescription = $strLongDescription;
    }

    /**
     * @param class_date $objValidDate
     */
    public function setObjValidDate(class_date $objValidDate)
    {
        $this->objValidDate = $objValidDate;
    }

    /**
     * @param array $arrModuleNavi
     */
    public function setArrModuleNavi(array $arrModuleNavi)
    {
        $this->arrModuleNavi = $arrModuleNavi;
    }

    /**
     * Uses the pluginmanager to query all todo provider to get a list of available todo entries
     *
     * @return class_todo_entry[]
     */
    public static function getOpenTodos($strCategory)
    {
        if (isset(self::$todos[$strCategory])) {
            return self::$todos[$strCategory];
        }

        $objPluginManager = new class_pluginmanager(interface_todo_provider::EXTENSION_POINT);
        $arrPlugins = $objPluginManager->getPlugins();

        /** @var class_todo_entry[] $arrTodos */
        $arrTodos = array();
        foreach ($arrPlugins as $objPlugin) {
            if ($objPlugin instanceof interface_todo_provider && $objPlugin->rightView()) {
                $arrTodos = array_merge($arrTodos, $objPlugin->getCurrentEventsByCategory($strCategory));
            }
        }

        self::sortTodos($arrTodos);

        return self::$todos[$strCategory] = $arrTodos;
    }

    /**
     * Returns all available open todos
     *
     * @return class_todo_entry[]
     */
    public static function getAllOpenTodos()
    {
        $arrCategories = self::getAllCategories();
        $arrTodos = array();

        foreach ($arrCategories as $strTitle => $arrCategory) {
            foreach ($arrCategory as $strKey => $strCategoryName) {
                $arrTodos = array_merge($arrTodos, self::getOpenTodos($strKey));
            }
        }

        self::sortTodos($arrTodos);

        return $arrTodos;
    }

    /**
     * Returns all available todo entries for a specific date
     *
     * @param class_date $objDate
     * @return class_todo_entry[]
     */
    public static function getTodoByDate(class_date $objDate)
    {
        $objPluginManager = new class_pluginmanager(interface_todo_provider::EXTENSION_POINT);
        $arrPlugins = $objPluginManager->getPlugins();

        /** @var class_todo_entry[] $arrTodos */
        $arrTodos = array();
        foreach ($arrPlugins as $objPlugin) {
            if ($objPlugin instanceof interface_todo_provider && $objPlugin->rightView()) {
                $arrTodos = array_merge($arrTodos, $objPlugin->getEventsByDate($objDate));
            }
        }

        self::sortTodos($arrTodos);

        return $arrTodos;
    }

    /**
     * Returns all available categories
     *
     * @return array
     */
    public static function getAllCategories()
    {
        if (self::$categories) {
            return self::$categories;
        }

        $objPluginManager = new class_pluginmanager(interface_todo_provider::EXTENSION_POINT);
        $arrPlugins = $objPluginManager->getPlugins();

        $arrCategories = array();
        foreach ($arrPlugins as $objPlugin) {
            if ($objPlugin instanceof interface_todo_provider && $objPlugin->rightView()) {
                $arrCategories[$objPlugin->getName()] = $objPlugin->getCategories();
            }
        }

        return self::$categories = $arrCategories;
    }

    /**
     * Sorts all events after the valid date
     *
     * @param array $arrTodos
     */
    protected static function sortTodos(array &$arrTodos)
    {
        usort($arrTodos, function(class_todo_entry $objEntryA, class_todo_entry $objEntryB){
            $intA = $objEntryA->getObjValidDate() instanceof class_date ? $objEntryA->getObjValidDate()->getTimeInOldStyle() : 0;
            $intB = $objEntryB->getObjValidDate() instanceof class_date ? $objEntryB->getObjValidDate()->getTimeInOldStyle() : 0;
            if ($intA == $intB) {
                return 0;
            }
            return ($intA < $intB) ? -1 : 1;
        });
    }
}
