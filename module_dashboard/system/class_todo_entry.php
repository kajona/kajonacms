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
    public function setObjValidDate($objValidDate)
    {
        $this->objValidDate = $objValidDate;
    }

    /**
     * @param array $arrModuleNavi
     */
    public function setArrModuleNavi($arrModuleNavi)
    {
        $this->arrModuleNavi = $arrModuleNavi;
    }

    /**
     * Uses the pluginmanager to query all todo provider to get a list of available todo entries
     *
     * @param class_date $objStarDate
     * @param class_date $objEndDate
     * @return class_todo_entry[]
     */
    public static function getOpenTodos(class_date $objStarDate, class_date $objEndDate)
    {
        $objPluginManager = new class_pluginmanager(interface_todo_provider::EXTENSION_POINT);
        $arrPlugins = $objPluginManager->getPlugins();

        /** @var class_todo_entry[] $arrTodos */
        $arrTodos = array();
        foreach ($arrPlugins as $objPlugin) {
            if ($objPlugin instanceof interface_todo_provider) {
                $arrTodos = array_merge($arrTodos, $objPlugin->getEvents($objStarDate, $objEndDate));
            }
        }

        // sort all events after date
        usort($arrTodos, function(class_todo_entry $objEntryA, class_todo_entry $objEntryB){
            $intA = $objEntryA->getObjValidDate()->getTimeInOldStyle();
            $intB = $objEntryB->getObjValidDate()->getTimeInOldStyle();
            if ($intA == $intB) {
                return 0;
            }
            return ($intA < $intB) ? -1 : 1;
        });

        return $arrTodos;
    }
}
