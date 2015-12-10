<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

require_once __DIR__."/PharModule.php";
require_once __DIR__."/BootstrapCache.php";

use Kajona\System\System\BootstrapCache;
use Kajona\System\System\PharModule;

/**
 * Class-loader for all Kajona classes.
 * Implemented as a singleton.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_classloader
{

    const PREFER_PHAR = false;


    private $intNumberOfClassesLoaded = 0;

    /**
     * @var class_classloader
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
      "/admin/statsreports/",
      "/admin/systemtasks/",
      "/admin/widgets/",
      "/admin/",
      "/portal/elements/",
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
      "/legacy/"
    );

    /**
     * Factory method returning an instance of class_classloader.
     * The class-loader implements the singleton pattern.
     *
     * @static
     * @return class_classloader
     */
    public static function getInstance()
    {
        if (self::$objInstance == null) {
            self::$objInstance = new class_classloader();
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
     * @throws class_exception
     */
    public function includeClasses()
    {
        foreach (BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_CLASSES) as $strClass => $strOneFile) {
            if (strpos($strOneFile, "/event/") !== false) {
                // include all classes which are in the event folder
                $this->loadClass($strClass);
            }

            /*if (ob_get_contents() !== "") {
                throw new class_exception("Whitespace outside php-tags in file ".$strOneFile." @ ".$strClass.", aborting system-startup", class_exception::$level_FATALERROR);
            }*/
        }
    }

    /**
     * Scans all core directories for matching modules
     *
     * @return void
     */
    private function scanModules()
    {

        if(BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_MODULES) !== false && BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_PHARMODULES) !== false) {
            return;
        }


        $arrExcludedModules = array();
        $arrIncludedModules = array();
        if (is_file(_realpath_."/project/system/config/packageconfig.php")) {
            include(_realpath_."/project/system/config/packageconfig.php");
        }

        //Module-Constants
        $this->arrCoreDirs = self::getCoreDirectories();

        $arrModules = array();
        $arrPharModules = array();
        foreach ($this->arrCoreDirs as $strRootFolder) {

            if (strpos($strRootFolder, "core") === false) {
                continue;
            }

            foreach (scandir(_realpath_."/".$strRootFolder) as $strOneModule) {
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

        if(self::PREFER_PHAR) {
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

        if(!empty(BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_CLASSES))) {
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
            foreach($arrFiles as $strName => $strPath) {
                $arrResolved[$this->getClassnameFromFilename($strPath)] = $strPath;
            }

            // PHAR archive files must never override existing file system files
            if(self::PREFER_PHAR) {
                $arrMergedFiles = array_merge($arrMergedFiles, $arrResolved);
            }
            else {
                $arrMergedFiles += array_diff_key($arrResolved, $arrMergedFiles);
            }
        }

        BootstrapCache::getInstance()->updateCache(BootstrapCache::CACHE_CLASSES, $arrMergedFiles);
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

        foreach (array_merge(BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_MODULES), array("project")) as $strPath => $strSingleModule) {
            if (is_dir(_realpath_."/".$strPath.$strFolder)) {
                $arrTempFiles = scandir(_realpath_."/".$strPath.$strFolder);
                foreach ($arrTempFiles as $strSingleFile) {
                    if (strpos($strSingleFile, ".php") !== false) {

                        // if there is an underscore we have a legacy class name else a camel case
                        if (strpos($strSingleFile, "_") !== false) {
                            if (preg_match("/(class|interface|trait)(.*)\.php$/i", $strSingleFile)) {
                                $arrFiles[substr($strSingleFile, 0, -4)] = _realpath_.$strPath.$strFolder.$strSingleFile;
                            }
                        } else {
                            $strClassName = $this->getClassnameFromFilename(_realpath_.$strPath.$strFolder.$strSingleFile);
                            if (!empty($strClassName)) {
                                $arrFiles[$strClassName] = _realpath_."/".$strPath.$strFolder.$strSingleFile;
                            }
                        }
                    }
                }
            }
        }

        //scan for overwrites
        //FIXME: remove in 5.x, only needed for backwards compatibility where content under /project was not organized within module-folders
        if (is_dir(_realpath_."/project".$strFolder)) {
            $arrTempFiles = scandir(_realpath_."/project".$strFolder);
            foreach ($arrTempFiles as $strSingleFile) {
                if (preg_match("/(class|interface|trait)(.*)\.php$/i", $strSingleFile)) {
                    $arrFiles[substr($strSingleFile, 0, -4)] = _realpath_."/project".$strFolder.$strSingleFile;
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
     * @return null|string
     */
    public function getClassnameFromFilename($strFilename)
    {
        // if empty we cant resolve a class name
        if (empty($strFilename) || uniSubstr($strFilename, -4) != '.php') {
            return null;
        }

        // blacklisting!
        if (uniStrpos(basename($strFilename), "class_testbase") === 0) {
            return null;
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
                $strClassname = $strNamespace . "\\" . $strFile;
            }
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
    public function getInstanceFromFilename($strFilename, $strBaseclass = null, $strImplementsInterface = null, $arrConstructorParams = null)
    {
        $strResolvedClassname = $this->getClassnameFromFilename($strFilename);

        if ($strResolvedClassname != null) {
            // if the class does not exist we simply include the filename and hope that the class is defined there. This
            // is the case where the filename is not equal to the class name i.e. installer_sc_zzlanguages.php
            if (!class_exists($strResolvedClassname)) {
                if (!preg_match("/(class|interface|trait)(.*)$/i", $strResolvedClassname)) {
                    $strResolvedClassname = "class_" . $strResolvedClassname;
                }

                include_once $strFilename;
            }

            $objReflection = new ReflectionClass($strResolvedClassname);
            if ($objReflection->isInstantiable() && ($strBaseclass == null || $objReflection->isSubclassOf($strBaseclass)) && ($strImplementsInterface == null || $objReflection->implementsInterface($strImplementsInterface))) {
                if (!empty($arrConstructorParams)) {
                    return $objReflection->newInstanceArgs($arrConstructorParams);
                }
                else {
                    return $objReflection->newInstance();
                }
            }
        }

        return null;
    }


    public function bootstrapIncludeModuleIds()
    {
        //fetch all phars and registered modules
        foreach(BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_MODULES) as $strPath => $strOneModule) {

            if(!in_array($strOneModule, BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_PHARMODULES))) {
                if (is_dir(_realpath_."/".$strPath."/system/") && is_dir(_realpath_."/".$strPath."/system/config/")) {
                    foreach (scandir(_realpath_."/".$strPath."/system/config/") as $strModuleEntry) {
                        if (preg_match("/module\_([a-z0-9\_])+\_id\.php/", $strModuleEntry)) {
                            @include_once _realpath_."/".$strPath."/system/config/".$strModuleEntry;
                        }
                    }
                }
            }
        }

        foreach(BootstrapCache::getInstance()->getCacheContent(BootstrapCache::CACHE_PHARMODULES) as $strPath => $strOneModule) {
            $objPhar = new PharModule($strPath);
            $objPhar->loadModuleIds();
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
