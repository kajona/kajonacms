<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

namespace Kajona\Dashboard\System;

use Kajona\System\System\Pluginmanager;

/**
 * Repository class which provides all methods to retrieve todo entries
 *
 * @package module_dashboard
 * @author christoph.kappestein@gmail.com
 */
class TodoRepository
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
     * @return TodoEntry[]
     */
    public static function getOpenTodos($strCategory, $bitLimited = true)
    {
        if (isset(self::$arrTodos[$strCategory])) {
            return self::$arrTodos[$strCategory];
        }

        $objPluginManager = new Pluginmanager(TodoProviderInterface::EXTENSION_POINT);
        $arrPlugins = $objPluginManager->getPlugins();

        /** @var TodoEntry[] $arrTodos */
        $arrTodos = array();
        foreach ($arrPlugins as $objPlugin) {
            if ($objPlugin instanceof TodoProviderInterface && $objPlugin->rightView()) {
                $arrTodos = array_merge($arrTodos, $objPlugin->getCurrentTodosByCategory($strCategory, $bitLimited));
            }
        }

        self::sortTodos($arrTodos);

        return self::$arrTodos[$strCategory] = $arrTodos;
    }

    /**
     * Returns all available open todos
     *
     * @return TodoEntry[]
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

        $objPluginManager = new Pluginmanager(TodoProviderInterface::EXTENSION_POINT);
        $arrPlugins = $objPluginManager->getPlugins();

        $arrCategories = array();
        foreach ($arrPlugins as $objPlugin) {
            if ($objPlugin instanceof TodoProviderInterface && $objPlugin->rightView()) {

                if(!array_key_exists($objPlugin->getName(), $arrCategories)) {
                    $arrCategories[$objPlugin->getName()] = $objPlugin->getCategories();
                }
                else {
                    $arrCategories[$objPlugin->getName()] = array_merge($arrCategories[$objPlugin->getName()], $objPlugin->getCategories());
                }
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
        usort($arrTodos, function(TodoEntry $objEntryA, TodoEntry $objEntryB){
            $intA = $objEntryA->getObjValidDate() instanceof \Kajona\System\System\Date ? $objEntryA->getObjValidDate()->getTimeInOldStyle() : 0;
            $intB = $objEntryB->getObjValidDate() instanceof \Kajona\System\System\Date ? $objEntryB->getObjValidDate()->getTimeInOldStyle() : 0;
            if ($intA == $intB) {
                return 0;
            }
            return ($intA > $intB) ? -1 : 1;
        });
    }
}
