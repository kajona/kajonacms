<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


require_once __DIR__."/PharModule.php";
require_once __DIR__."/BootstrapCache.php";

use ReflectionClass;

/**
 * Class-loader for all Kajona classes.
 * Implemented as a singleton.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class Classloader
{

    const PREFER_PHAR = false;


    private $intNumberOfClassesLoaded = 0;

    /**
     * @var Classloader
     */
    private static $objInstance = null;

    /**
     * Cached array of the core dirs
     *
     * @var array
     */
    private $arrCoreDirs = array();

    /**
     * List of folder names that are supposed to contain code.
     *
     * @var array
     */
    private static $arrCodeFolders = array(
        "/admin/elements/",
        "/admin/formentries/",
        "/admin/reports/",
        "/admin/systemtasks/",
        "/admin/widgets/",
        "/admin/",
        "/portal/elements/",
        "/portal/forms/",
        "/portal/templatemapper/",
        "/portal/",
        "/system/db/",
        "/system/usersources/",
        "/system/imageplugins/",
        "/system/validators/",
        "/system/workflows/",
        "/system/messageproviders/",
        "/system/scriptlets/",
        "/system/",
        "/installer/",
        "/event/",
        "/legacy/",
        "/tests/"
    );

    /**
     * Factory method returning an instance of Classloader.
     * The class-loader implements the singleton pattern.
     *
     * @static
     * @return Classloader
     */
    public static function getInstance()
    {
        if (self::$objInstance == null) {
            self::$objInstance = new Classloader();
        }

        return self::$objInstance;
    }

    /**
     * Constructor, initializes the internal fields
     */
    private function __construct()
    {
        $this->scanModules();
        $this->indexAvailableCodefiles();
        $this->bootstrapIncludeModuleIds();
    }


    /**
     * We autoload all classes which are in the event folder of each module. Theses classes can register events etc.
     *
     * @throws Exception
     */
    public function includeClasses()
    {
        foreach (BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_CLASSES) as $strClass => $strOneFile) {
            if (strpos($strOneFile, "/event/") !== false) {
                // include all classes which are in the event folder
                $this->loadClass($strClass);
            }
        }
    }

    /**
     * Registers all service providers to the DI container
     *
     * @param \Pimple\Container $objContainer
     */
    public function registerModuleServices(\Pimple\Container $objContainer)
    {
        foreach (BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_SERVICES) as $strClass => $strOneFile) {
            $objServiceProvider = new $strClass();
            if ($objServiceProvider instanceof \Pimple\ServiceProviderInterface) {
                $objServiceProvider->register($objContainer);
            }
        }
    }

    /**
     * Scans all core directories for matching modules
     *
     * @return void
     */
    private function scanModules()
    {

        if (BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_MODULES) !== false && BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_PHARMODULES) !== false) {
            return;
        }


        $arrExcludedModules = array();
        $arrIncludedModules = array();
        if (is_file(_realpath_."project/packageconfig.php")) {
            include(_realpath_."project/packageconfig.php");
        }

        //Module-Constants
        $this->arrCoreDirs = self::getCoreDirectories();

        $arrModules = array();
        $arrPharModules = array();
        foreach ($this->arrCoreDirs as $strRootFolder) {

            if (strpos($strRootFolder, "core") === false) {
                continue;
            }

            foreach (scandir(_realpath_.$strRootFolder) as $strOneModule) {
                $strModuleName = null;
                $boolIsPhar = PharModule::isPhar($strOneModule);

                if ($boolIsPhar) {
                    $strModuleName = PharModule::getPharBasename($strOneModule);
                }
                elseif (preg_match("/^(module|element|_)+.*/i", $strOneModule)) {
                    $strModuleName = $strOneModule;
                }

                if ($strModuleName != null) {
                    //skip excluded modules
                    if (isset($arrExcludedModules[$strRootFolder]) && in_array($strModuleName, $arrExcludedModules[$strRootFolder])) {
                        continue;
                    }

                    //skip module if not marked as to be included
                    if (count($arrIncludedModules) > 0 && (isset($arrIncludedModules[$strRootFolder]) && !in_array($strModuleName, $arrIncludedModules[$strRootFolder]))) {
                        continue;
                    }

                    if ($boolIsPhar) {
                        $arrPharModules[$strRootFolder."/".$strOneModule] = $strModuleName;
                    }
                    else {
                        $arrModules[$strRootFolder."/".$strOneModule] = $strModuleName;
                    }
                }
            }
        }

        if (self::PREFER_PHAR) {
            $arrDiffedPhars = $arrPharModules;
            $arrModules = array_diff($arrModules, $arrPharModules);
        }
        else {
            $arrDiffedPhars = array_diff($arrPharModules, $arrModules);
        }

        BootstrapCache::getInstance()->updateCache(BootstrapCache::CACHE_MODULES, $arrModules);
        BootstrapCache::getInstance()->updateCache(BootstrapCache::CACHE_PHARMODULES, $arrDiffedPhars);
    }

    /**
     * Returns a list of all core directories available
     *
     * @return array
     */
    public static function getCoreDirectories()
    {
        $arrCores = array();
        foreach (scandir(_realpath_) as $strRootFolder) {

            if (strpos($strRootFolder, "core") === false) {
                continue;
            }

            $arrCores[] = $strRootFolder;
        }

        return $arrCores;
    }


    /**
     * Flushes the cache-files.
     * Use this method if you added new modules / classes.
     * The classes are reinitialized automatically.
     *
     * @return void
     */
    public function flushCache()
    {
        BootstrapCache::getInstance()->flushCache();
        $this->scanModules();
        $this->indexAvailableCodefiles();
    }

    /**
     * Indexes all available code-files, so classes.
     * Therefore, all relevant folders are traversed.
     *
     * @return void
     */
    private function indexAvailableCodefiles()
    {

        if (!empty(BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_CLASSES))) {
            return;
        }

        $arrMergedFiles = array();

        foreach (self::$arrCodeFolders as $strFolder) {
            $arrMergedFiles = array_merge($arrMergedFiles, $this->getClassesInFolder($strFolder));
        }


        foreach (BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_PHARMODULES) as $strPath => $strSingleModule) {
            $objPhar = new PharModule($strPath);
            $arrFiles = $objPhar->load(self::$arrCodeFolders);

            $arrResolved = array();
            foreach ($arrFiles as $strName => $strPath) {
                $arrResolved[$this->getClassnameFromFilename($strPath)] = $strPath;
            }

            // PHAR archive files must never override existing file system files
            if (self::PREFER_PHAR) {
                $arrMergedFiles = array_merge($arrMergedFiles, $arrResolved);
            }
            else {
                $arrMergedFiles += array_diff_key($arrResolved, $arrMergedFiles);
            }
        }

        $arrServiceProvider = array();
        foreach ($arrMergedFiles as $strClassName => $strFile) {
            if (strpos($strClassName, "\\ServiceProvider") !== false) {
                $arrServiceProvider[$strClassName] = $strFile;
            }
        }

        BootstrapCache::getInstance()->updateCache(BootstrapCache::CACHE_CLASSES, $arrMergedFiles);
        BootstrapCache::getInstance()->updateCache(BootstrapCache::CACHE_SERVICES, $arrServiceProvider);
    }

    /**
     * Loads all classes in a single folder, but traversing each module available.
     * Internal helper.
     *
     * @param string $strFolder
     *
     * @return string[]
     */
    private function getClassesInFolder($strFolder)
    {
        $arrFiles = array();

        $arrModules = BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_MODULES);

        // add module redefinitions from /project for both, phars and non phars
        foreach ($this->getArrModules() as $strModulePath => $strSingleModule) {
            $strPath = "project/" . $strSingleModule;
            if (is_dir(_realpath_.$strPath.$strFolder)) {
                $arrModules[$strPath] = $strSingleModule;
            }
        }

        foreach ($arrModules as $strPath => $strSingleModule) {
            if (is_dir(_realpath_.$strPath.$strFolder)) {
                $arrTempFiles = scandir(_realpath_.$strPath.$strFolder);
                foreach ($arrTempFiles as $strSingleFile) {
                    if (strpos($strSingleFile, ".php") !== false) {

                        // if there is an underscore we have a legacy class name else a camel case
                        if (strpos($strSingleFile, "_") !== false) {
                            if (preg_match("/(class|interface|trait)(.*)\.php$/i", $strSingleFile)) {
                                $arrFiles[substr($strSingleFile, 0, -4)] = _realpath_.$strPath.$strFolder.$strSingleFile;
                            }
                        }
                        else {
                            $strClassName = $this->getClassnameFromFilename(_realpath_.$strPath.$strFolder.$strSingleFile);
                            if (!empty($strClassName)) {
                                $arrFiles[$strClassName] = _realpath_.$strPath.$strFolder.$strSingleFile;
                            }
                        }
                    }
                }
            }
        }

        return $arrFiles;
    }


    /**
     * The class-loader itself. Loads the class, if existing. Otherwise the chain of class-loaders is triggered.
     *
     * @param string $strClassName
     *
     * @return bool
     */
    public function loadClass($strClassName)
    {
        if (BootstrapCache::getInstance()->getCacheRow(BootstrapCache::CACHE_CLASSES, $strClassName)) {
            $this->intNumberOfClassesLoaded++;
            include_once BootstrapCache::getInstance()->getCacheRow(BootstrapCache::CACHE_CLASSES, $strClassName);
            return true;
        }

        return false;
    }

    /**
     * Extracts the class-name out of a filename.
     * Normally this method is only used by getInstanceFromFilename, so no use to call it directly.
     * The method does not include the file so it does not trigger any other autoload calls
     *
     * @param $strFilename
     *
     * @return null|string
     */
    public function getClassnameFromFilename($strFilename)
    {
        // if empty we cant resolve a class name
        if (empty($strFilename) || uniSubstr($strFilename, -4) != '.php') {
            return null;
        }

        //perform a reverse lookup using the cache, maybe the file was indexed before
        $arrMap = BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_CLASSES);
        if($arrMap !== false) {
            $strHit = array_search($strFilename, $arrMap);
            if ($strHit !== false && $strHit !== null) {
                return $strHit;
            }
        }

        $strFile = uniSubstr(basename($strFilename), 0, -4);
        $strClassname = null;

        // if the filename contains an underscore we have an old class else a camelcase one
        if (strpos($strFile, "_") !== false) {
            $strClassname = $strFile;
        }
        else {
            $strSource = file_get_contents($strFilename);
            preg_match('/namespace ([a-zA-Z0-9_\x7f-\xff\\\\]+);/', $strSource, $arrMatches);

            $strNamespace = isset($arrMatches[1]) ? $arrMatches[1] : null;
            if (!empty($strNamespace)) {
                $strClassname = $strNamespace."\\".$strFile;
            }
            else {
                //ugly fallback for ioncube encoded files, could be upgrade to an improved regex
                //TODO: move this name-based detection to the general approach, replacing the content parsing
                if(strpos($strSource, "ioncube") !== false|| strpos($strSource, "sg_load") !== false) {
                    if($strFile === "functions") {
                        return null;
                    }

                    $strParsedFilename = str_replace(array("\\", ".phar"), array("/", ""), uniSubstr($strFilename, 0, -4));


                    $strClassname = "Kajona\\";
                    if(strpos($strParsedFilename, "core_") !== false) {
                        $strClassname = "AGP\\";
                    }

                    $arrPath = array();
                    $arrSections = array_reverse(explode("/", $strParsedFilename));
                    foreach($arrSections as $strOnePart) {
                        if($strOnePart !== "core"
                            && $strOnePart !== "project"
                            && strpos($strOnePart, "core_") === false)
                        {
                            if(strpos($strOnePart, "module_") !== false) {
                                $strOnePart = substr($strOnePart, 7);
                            }

                            //e.g. agp_commons will become Agp_Commons
                            //e.g. commons will become Commons
                            $arrExp = explode("_", $strOnePart);
                            $arrNew = array();
                            foreach ($arrExp as $str) {
                                $arrNew[] = ucfirst($str);
                            }
                            $arrPath[] = implode("_", $arrNew);
                        }
                        else {
                            break;
                        }
                    }

                    //file is in project path?
                    if(strpos($strParsedFilename, "/project/") !== false) {
                        if(is_dir(_realpath_."core/module_".strtolower(array_reverse($arrPath)[0]))) {
                            $strClassname = "Kajona\\";
                        }
                        else {
                            $strClassname = "AGP\\";
                        }
                    }

                    $strClassname .= implode("\\", array_reverse($arrPath));
                }
            }
        }
        if($arrMap !== false) {
            BootstrapCache::getInstance()->addCacheRow(BootstrapCache::CACHE_CLASSES, $strClassname, $strFilename);
        }
        return $strClassname;
    }


    /**
     * Creates a new instance of an object based on the filename
     *
     * @param $strFilename
     * @param string $strBaseclass an optional filter-restriction based on a base class
     * @param string $strImplementsInterface
     * @param array $arrConstructorParams
     *
     * @return null|object
     */
    public function getInstanceFromFilename($strFilename, $strBaseclass = null, $strImplementsInterface = null, $arrConstructorParams = null, $bitInject = false)
    {
        $strResolvedClassname = $this->getClassnameFromFilename($strFilename);

        if ($strResolvedClassname != null) {
            // if the class does not exist we simply include the filename and hope that the class is defined there. This
            // is the case where the filename is not equal to the class name i.e. installer_sc_zzlanguages.php
            if (!class_exists($strResolvedClassname, false)) {
                if ($strResolvedClassname[0] != strtoupper($strResolvedClassname[0]) && !preg_match("/(class|interface|trait)(.*)$/i", $strResolvedClassname)) {
                    $strResolvedClassname = "class_".$strResolvedClassname;
                }

                include_once $strFilename;
            }

            $objReflection = new ReflectionClass($strResolvedClassname);
            if ($objReflection->isInstantiable() && ($strBaseclass == null || $objReflection->isSubclassOf($strBaseclass)) && ($strImplementsInterface == null || $objReflection->implementsInterface($strImplementsInterface))) {
                if ($bitInject) {
                    $objFactory = Carrier::getInstance()->getContainer()->offsetGet(ServiceProvider::STR_OBJECT_BUILDER);
                    if (!empty($arrConstructorParams)) {
                        return $objFactory->factory($objReflection->getName(), $arrConstructorParams);
                    }
                    else {
                        return $objFactory->factory($objReflection->getName());
                    }
                }
                else {
                    if (!empty($arrConstructorParams)) {
                        return $objReflection->newInstanceArgs($arrConstructorParams);
                    }
                    else {
                        return $objReflection->newInstance();
                    }
                }
            }
        }

        return null;
    }


    public function bootstrapIncludeModuleIds()
    {
        $arrModuleIds = BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_MODULEIDS);

        if (!empty($arrModuleIds)) {
            foreach ($arrModuleIds as $strConstant => $strValue) {
                define($strConstant, $strValue);
            }
        } else {
            $arrExistingConstants = get_defined_constants();

            //fetch all phars and registered modules
            foreach (BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_MODULES) as $strPath => $strOneModule) {

                if (!in_array($strOneModule, BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_PHARMODULES))) {
                    if (is_dir(_realpath_.$strPath."/system/") && is_dir(_realpath_.$strPath."/system/config/")) {
                        foreach (scandir(_realpath_.$strPath."/system/config/") as $strModuleEntry) {
                            if (preg_match("/module\_([a-z0-9\_])+\_id\.php/", $strModuleEntry)) {
                                @include_once _realpath_.$strPath."/system/config/".$strModuleEntry;
                            }
                        }
                    }
                }
            }

            foreach (BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_PHARMODULES) as $strPath => $strOneModule) {
                $objPhar = new PharModule($strPath);
                $objPhar->loadModuleIds();
            }

            $arrModuleIds = array_diff_key(get_defined_constants(), $arrExistingConstants);

            BootstrapCache::getInstance()->updateCache(BootstrapCache::CACHE_MODULEIDS, $arrModuleIds);
        }
    }


    /**
     * Returns the list of modules indexed by the classloader, so residing under /core
     *
     * @return string[]
     */
    public function getArrModules()
    {
        return BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_MODULES) + BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_PHARMODULES);
    }

    /**
     * Returns the list of phar-based modules
     *
     * @return array
     */
    public function getArrPharModules()
    {
        return BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_PHARMODULES);
    }

    /**
     * Returns the number of classes loaded internally
     *
     * @return int
     */
    public function getIntNumberOfClassesLoaded()
    {
        return $this->intNumberOfClassesLoaded;
    }


}
