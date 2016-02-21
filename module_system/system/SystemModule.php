<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

namespace Kajona\System\System;

use Kajona\System\Admin\AdminController;
use Kajona\System\Admin\AdminInterface;
use Kajona\System\Portal\PortalInterface;


/**
 * Model for a single system-module
 *
 * @package module_system
 * @targetTable system_module.module_id
 * @sortManager Kajona\System\System\CommonSortmanager
 *
 * @module system
 * @moduleId _system_modul_id_
 *
 * @blockFromAutosave
 */
class SystemModule extends Model implements ModelInterface, AdminListableInterface
{

    /**
     * @var string
     * @tableColumn system_module.module_name
     * @tableColumnDatatype char254
     */
    private $strName = "";

    /**
     * @var string
     * @tableColumn system_module.module_filenameportal
     * @tableColumnDatatype char254
     */
    private $strNamePortal = "";

    /**
     * @var string
     * @tableColumn system_module.module_xmlfilenameportal
     * @tableColumnDatatype char254
     */
    private $strXmlNamePortal = "";

    /**
     * @var string
     * @tableColumn system_module.module_filenameadmin
     * @tableColumnDatatype char254
     */
    private $strNameAdmin = "";

    /**
     * @var string
     * @tableColumn system_module.module_xmlfilenameadmin
     * @tableColumnDatatype char254
     */
    private $strXmlNameAdmin = "";

    /**
     * @var string
     * @tableColumn system_module.module_version
     * @tableColumnDatatype char254
     */
    private $strVersion = "";

    /**
     * @var int
     * @tableColumn system_module.module_date
     * @tableColumnDatatype int
     */
    private $intDate = "";

    /**
     * @var int
     * @tableColumn system_module.module_navigation
     * @tableColumnDatatype int
     */
    private $intNavigation = "";

    /**
     * @var int
     * @tableColumn system_module.module_nr
     * @tableColumnDatatype int
     */
    private $intNr = -1;

    /**
     * @var string
     * @tableColumn system_module.module_aspect
     * @tableColumnDatatype char254
     */
    private $strAspect = "";

    /**
     * @var SystemModule[]
     */
    private static $arrModules = array();

    /**
     * @var string[][]
     */
    private static $arrModuleData = array();


    /**
     * Initialises the internal modules-cache.
     * Loads all module-data into a single array.
     * Avoids multiple queries against the module-table.
     *
     * @param bool $bitCache
     *
     * @return array
     * @static
     */
    private static function loadModuleData($bitCache = true)
    {

        if ((count(self::$arrModuleData) == 0 || !$bitCache) && count(Carrier::getInstance()->getObjDB()->getTables()) > 0) {
            $strQuery = "SELECT *
                           FROM "._dbprefix_."system_right,
                                "._dbprefix_."system_module,
                                "._dbprefix_."system
                      LEFT JOIN "._dbprefix_."system_date
                             ON system_id = system_date_id
                          WHERE system_id = right_id
                            AND system_id = module_id
                       ORDER BY system_sort ASC, system_comment ASC   ";

            $arrRows = Carrier::getInstance()->getObjDB()->getPArray($strQuery, array(), null, null, $bitCache);
            OrmRowcache::addArrayOfInitRows($arrRows);
            self::$arrModuleData = $arrRows;
        }
        return self::$arrModuleData;
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName()
    {
        return $this->getStrName();
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon()
    {
        return "icon_module";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo()
    {
        return "V ".$this->getStrVersion()." &nbsp;(".timeToString($this->getIntDate(), true).")";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription()
    {
        $objAdminInstance = $this->getAdminInstanceOfConcreteModule();
        if ($objAdminInstance != null) {
            $strDescription = $objAdminInstance->getModuleDescription();
        }
        else {
            $strDescription = "";
        }

        return $strDescription;
    }

    /**
     * Loads an array containing all installed modules from database
     *
     * @param bool $intStart
     * @param bool $intEnd
     *
     * @return SystemModule[]
     * @static
     */
    public static function getAllModules($intStart = null, $intEnd = null)
    {

        if (count(self::$arrModules) == 0) {
            if (count(Database::getInstance()->getTables()) == 0) {
                return array();
            }
            self::$arrModules = parent::getObjectList();
        }

        if ($intStart === null || $intEnd === null) {
            return self::$arrModules;
        }

        $arrReturn = array();
        $intI = 0;
        foreach (self::$arrModules as $objOneModule) {
            if ($intI >= $intStart && $intI <= $intEnd) {
                $arrReturn[] = $objOneModule;
            }
            $intI++;
        }

        return $arrReturn;
    }

    public function deleteObject()
    {
        self::flushCache();
        return parent::deleteObject(); // TODO: Change the autogenerated stub
    }

    public function deleteObjectFromDatabase()
    {
        self::flushCache();
        return parent::deleteObjectFromDatabase(); // TODO: Change the autogenerated stub
    }


    /**
     * Counts the number of modules available
     *
     * @param string $strPrevid
     *
     * @return int
     * @static
     */
    public static function getObjectCount($strPrevid = "")
    {
        return count(self::loadModuleData());
    }

    /**
     * Tries to look up a module using the given name. If the module
     * is not active / not installed, null is returned instead
     *
     * @param string $strName
     * @param bool $bitIgnoreStatus
     *
     * @return SystemModule
     * @static
     */
    public static function getModuleByName($strName, $bitIgnoreStatus = false)
    {

        foreach (self::getAllModules() as $objOneModule) {
            if ($objOneModule->getStrName() == $strName) {

                if (!$bitIgnoreStatus && $objOneModule->getIntRecordStatus() == 0) {
                    return null;

                }

                return $objOneModule;
            }
        }

        return null;

    }


    /**
     * Creates a new instance of a module or returns an already instantiated one.
     * For modules, this is the preferred way of generating instances.
     *
     * @param string $strSystemid
     *
     * @return SystemModule
     * @static
     */
    public static function getModuleBySystemid($strSystemid)
    {
        return Objectfactory::getInstance()->getObject($strSystemid);

    }

    /**
     * Looks up the id of a module using the passed module-number
     *
     * @param int $strNr
     *
     * @return string $strNr
     * @static
     */
    public static function getModuleIdByNr($strNr)
    {
        foreach (self::getAllModules() as $objOneModule) {
            if ($objOneModule->getIntNr() == $strNr) {
                return $objOneModule->getSystemid();
            }
        }
        return "";

    }

    /**
     * Looks up all modules being active and allowed to appear in the admin-navigation
     * Creates a simple array, NO OBJECTS
     *
     * @param string $strAspectFilter
     *
     * @return array[]
     * @static
     */
    public static function getModulesInNaviAsArray($strAspectFilter = "")
    {

        $arrParams = array();
        if ($strAspectFilter != "") {
            $arrParams[] = "%".$strAspectFilter."%";
            $strAspectFilter = " AND (module_aspect = '' OR module_aspect IS NULL OR module_aspect LIKE ? )";
        }

        //Loading all Modules
        $strQuery = "SELECT module_id, module_name
		               FROM "._dbprefix_."system_module,
		                    "._dbprefix_."system
		              WHERE module_navigation = 1
		                AND system_status = 1
		                AND module_id = system_id
                            ".$strAspectFilter."
		              ORDER BY system_sort ASC, system_comment ASC";
        return Carrier::getInstance()->getObjDB()->getPArray($strQuery, $arrParams);
    }

    /**
     * Factory method, creates an instance of the admin-module referenced by the current
     * module-object.
     * The object returned is being initialized with a systemid optionally.
     *
     * @param string $strSystemid
     *
     * @return AdminInterface|AdminController
     */
    public function getAdminInstanceOfConcreteModule($strSystemid = "", $bitXml = false)
    {

        /** @var \Kajona\System\System\ObjectBuilder $objBuilder */
        $objBuilder = Carrier::getInstance()->getContainer()->offsetGet("object_builder");

        $strClassname = $bitXml ? $this->getStrXmlNameAdmin() : $this->getStrNameAdmin();
        if (uniStrpos($strClassname, ".php") !== false) {
            $strFullpath = Resourceloader::getInstance()->getPathForFile("/admin/".$strClassname);
            if ($strFullpath == "") {
                $strFullpath = Resourceloader::getInstance()->getPathForFile("/legacy/".$strClassname);
            }
            $strClassname = Classloader::getInstance()->getClassnameFromFilename($strFullpath);
        }

        if ($strClassname != "") {
            if (validateSystemid($strSystemid)) {
                return $objBuilder->factory($strClassname, array($strSystemid));
            }
            else {
                return $objBuilder->factory($strClassname);
            }
        }

        return null;

    }

    /**
     * Factory method, creates an instance of the portal-module referenced by the current
     * module-object.
     * The object returned is being initialized with the config-array optionally.
     *
     * @param string $arrElementData
     *
     * @return PortalInterface
     */
    public function getPortalInstanceOfConcreteModule($arrElementData = null, $bitXml = false)
    {

        /** @var \Kajona\System\System\ObjectBuilder $objBuilder */
        $objBuilder = Carrier::getInstance()->getContainer()->offsetGet("object_builder");

        $strClassname = $bitXml ? $this->getStrXmlNamePortal() : $this->getStrNamePortal();
        if (uniStrpos($strClassname, ".php") !== false) {
            $strFullpath = Resourceloader::getInstance()->getPathForFile("/portal/".$strClassname);
            if ($strFullpath == "") {
                $strFullpath = Resourceloader::getInstance()->getPathForFile("/legacy/".$strClassname);
            }
            $strClassname = Classloader::getInstance()->getClassnameFromFilename($strFullpath);
        }

        if ($strClassname != "") {
            if (is_array($arrElementData)) {
                return $objBuilder->factory($strClassname, array($arrElementData));
            }
            else {
                return $objBuilder->factory($strClassname, array());
            }
        }

        return null;

    }

    /**
     * Returns the data for a registered module as given in the database
     *
     * @param string $strName
     * @param bool $bitCache
     *
     * @return mixed
     */
    public static function getPlainModuleData($strName, $bitCache = true)
    {
        $arrModules = self::loadModuleData($bitCache);

        foreach ($arrModules as $arrOneModule) {
            if ($arrOneModule["module_name"] == $strName) {
                return $arrOneModule;
            }
        }

        return array();
    }

    /**
     * Flushes the internal module-cache, so the rows queried from the database
     *
     * @return void
     */
    public static function flushCache()
    {
        self::$arrModules = array();
        self::$arrModuleData = array();
    }

    /**
     * @return string
     */
    public function getStrName()
    {
        return $this->strName;
    }

    /**
     * @return string
     */
    public function getStrNamePortal()
    {
        return $this->strNamePortal;
    }

    /**
     * @return string
     */
    public function getStrXmlNamePortal()
    {
        return $this->strXmlNamePortal;
    }

    /**
     * @return string
     */
    public function getStrNameAdmin()
    {
        return $this->strNameAdmin;
    }

    /**
     * @return string
     */
    public function getStrXmlNameAdmin()
    {
        return $this->strXmlNameAdmin;
    }

    /**
     * @return string
     */
    public function getStrVersion()
    {
        return $this->strVersion;
    }

    /**
     * @return int
     */
    public function getIntDate()
    {
        return $this->intDate;
    }

    /**
     * @return int
     */
    public function getIntNavigation()
    {
        return $this->intNavigation;
    }

    /**
     * @return int
     */
    public function getIntNr()
    {
        return $this->intNr;
    }

    /**
     * @param string $strName
     *
     * @return void
     */
    public function setStrName($strName)
    {
        $this->strName = $strName;
    }

    /**
     * @param string $strPortal
     *
     * @return void
     */
    public function setStrNamePortal($strPortal)
    {
        $this->strNamePortal = $strPortal;
    }

    /**
     * @param string $strXmlPortal
     *
     * @return void
     */
    public function setStrXmlNamePortal($strXmlPortal)
    {
        $this->strXmlNamePortal = $strXmlPortal;
    }

    /**
     * @param string $strAdmin
     *
     * @return void
     */
    public function setStrNameAdmin($strAdmin)
    {
        $this->strNameAdmin = $strAdmin;
    }

    /**
     * @param string $strXmlAdmin
     *
     * @return void
     */
    public function setStrXmlNameAdmin($strXmlAdmin)
    {
        $this->strXmlNameAdmin = $strXmlAdmin;
    }

    /**
     * @param string $strVersion
     *
     * @return void
     */
    public function setStrVersion($strVersion)
    {
        $this->strVersion = $strVersion;
    }

    /**
     * @param int $intDate
     *
     * @return void
     */
    public function setIntDate($intDate)
    {
        $this->intDate = $intDate;
    }

    /**
     * @param int $intNavigation
     *
     * @return void
     */
    public function setIntNavigation($intNavigation)
    {
        $this->intNavigation = $intNavigation;
    }

    /**
     * @param int $intNr
     *
     * @return void
     */
    public function setIntNr($intNr)
    {
        $this->intNr = $intNr;
    }

    /**
     * @return string
     */
    public function getStrAspect()
    {
        return $this->strAspect;
    }

    /**
     * @param string $strAspect
     *
     * @return void
     */
    public function setStrAspect($strAspect)
    {
        $this->strAspect = $strAspect;
    }

}