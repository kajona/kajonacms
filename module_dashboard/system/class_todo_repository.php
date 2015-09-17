<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

/**
 * Repository class which provides all methods to retrieve todo entries
 *
 * @package module_dashboard
 * @author christoph.kappestein@gmail.com
 */
class class_todo_repository
{
    /**
     * Internal cache
     *
     * @var array
     */
    protected static $arrCategories = array();

    /**
     * Internal cache
     *
     * @var array
     */
    protected static $arrTodos = array();

    /**
     * Uses the pluginmanager to query all todo provider to get a list of available todo entries
     *
     * @return class_todo_entry[]
     */
    public static function getOpenTodos($strCategory)
    {
        if (isset(self::$arrTodos[$strCategory])) {
            return self::$arrTodos[$strCategory];
        }

        $objPluginManager = new class_pluginmanager(interface_todo_provider::EXTENSION_POINT);
        $arrPlugins = $objPluginManager->getPlugins();

        /** @var class_todo_entry[] $arrTodos */
        $arrTodos = array();
        foreach ($arrPlugins as $objPlugin) {
            if ($objPlugin instanceof interface_todo_provider && $objPlugin->rightView()) {
                $arrTodos = array_merge($arrTodos, $objPlugin->getCurrentTodosByCategory($strCategory));
            }
        }

        self::sortTodos($arrTodos);

        return self::$arrTodos[$strCategory] = $arrTodos;
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
     * Returns all available categories
     *
     * @return array
     */
    public static function getAllCategories()
    {
        if (self::$arrCategories) {
            return self::$arrCategories;
        }

        $objPluginManager = new class_pluginmanager(interface_todo_provider::EXTENSION_POINT);
        $arrPlugins = $objPluginManager->getPlugins();

        $arrCategories = array();
        foreach ($arrPlugins as $objPlugin) {
            if ($objPlugin instanceof interface_todo_provider && $objPlugin->rightView()) {
                $arrCategories[$objPlugin->getName()] = $objPlugin->getCategories();
            }
        }

        return self::$arrCategories = $arrCategories;
    }

    /**
     * Returns the category name based on the identifier
     *
     * @return string
     */
    public static function getCategoryName($strCategoryKey)
    {
        $arrCategories = self::getAllCategories();
        foreach ($arrCategories as $arrCategory) {
            foreach ($arrCategory as $strKey => $strValue) {
                if ($strCategoryKey == $strKey) {
                    return $strValue;
                }
            }
        }
        return null;
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
            return ($intA > $intB) ? -1 : 1;
        });
    }
}
