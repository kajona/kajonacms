<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Class-loader for all Kajona classes.
 * Implemented as a singleton.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_classloader
{

    private $intNumberOfClassesLoaded = 0;

    private $strModulesCacheFile = "";
    private $strClassesCacheFile = "";

    private $bitCacheSaveRequired = false;

    /**
     * @var class_classloader
     */
    private static $objInstance = null;

    private $arrModules = array();

    /**
     * Cached index of class-files available
     *
     * @var String[]
     */
    private $arrFiles = array();


    /**
     * Cached array of the core dirs
     *
     * @var array
     */
    private $arrCoreDirs = array();

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


        $this->strModulesCacheFile = _realpath_."/project/temp/modules.cache";
        $this->strClassesCacheFile = _realpath_."/project/temp/classes.cache";

        include_once(__DIR__."/class_apc_cache.php");
        $this->arrModules = class_apc_cache::getInstance()->getValue(__CLASS__."modules");
        $this->arrFiles = class_apc_cache::getInstance()->getValue(__CLASS__."files");

        if ($this->arrModules === false || $this->arrFiles == false) {
            $this->arrModules = array();
            $this->arrFiles = array();

            if (is_file($this->strModulesCacheFile) && is_file($this->strClassesCacheFile)) {
                $this->arrModules = unserialize(file_get_contents($this->strModulesCacheFile));
                $this->arrFiles = unserialize(file_get_contents($this->strClassesCacheFile));
            }
            else {

                $this->scanModules();

                $this->indexAvailableCodefiles();

                $this->bitCacheSaveRequired = true;
            }
        }

    }

    /**
     * Stores the cached structures to file - if enabled and required
     */
    public function __destruct()
    {
        if ($this->bitCacheSaveRequired && class_config::getInstance()->getConfig('resourcecaching') == true) {

            class_apc_cache::getInstance()->addValue(__CLASS__."modules", $this->arrModules);
            class_apc_cache::getInstance()->addValue(__CLASS__."files", $this->arrFiles);

            file_put_contents($this->strModulesCacheFile, serialize($this->arrModules));
            file_put_contents($this->strClassesCacheFile, serialize($this->arrFiles));
        }
    }

    /**
     * Internal helper, triggers the loading/inclusion of all classes.
     * This is required since otherwise static init blocks would be skipped.
     * Currently enabled for classes matching the pattern "class_module_" only.
     *
     * @throws class_exception
     */
    public function includeClasses()
    {
        foreach ($this->arrFiles as $strClass => $strOneFile) {
            if (uniStrpos($strClass, "class_module_") !== false /*$strClass != "class_testbase"*/) {
                $this->loadClass($strClass);

                /*if (ob_get_contents() !== "") {
                    throw new class_exception("Whitespace outside php-tags in file ".$strOneFile." @ ".$strClass.", aborting system-startup", class_exception::$level_FATALERROR);
                }*/
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

        $arrExcludedModules = array();
        $arrIncludedModules = array();
        if (is_file(_realpath_."/project/system/config/packageconfig.php")) {
            include(_realpath_."/project/system/config/packageconfig.php");
        }

        //Module-Constants
        $this->arrCoreDirs = self::getCoreDirectories();

        $arrModules = array();
        foreach ($this->arrCoreDirs as $strRootFolder) {

            if (uniStrpos($strRootFolder, "core") === false) {
                continue;
            }

            foreach (scandir(_realpath_."/".$strRootFolder) as $strOneModule) {

                if (preg_match("/^(module|element|_)+.*/i", $strOneModule)) {

                    //skip excluded modules
                    if (isset($arrExcludedModules[$strRootFolder]) && in_array($strOneModule, $arrExcludedModules[$strRootFolder])) {
                        continue;
                    }

                    //skip module if not marked as to be included
                    if (count($arrIncludedModules) > 0 && (isset($arrIncludedModules[$strRootFolder]) && !in_array($strOneModule, $arrIncludedModules[$strRootFolder]))) {
                        continue;
                    }

                    $arrModules[$strRootFolder."/".$strOneModule] = $strOneModule;
                }

            }
        }

        $this->arrModules = $arrModules;
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

            if (uniStrpos($strRootFolder, "core") === false) {
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
        $objFilesystem = new class_filesystem();
        $objFilesystem->fileDelete($this->strModulesCacheFile);
        $objFilesystem->fileDelete($this->strClassesCacheFile);

        $this->arrFiles = array();
        $this->arrModules = array();
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
        $this->addClassFolder("/admin/elements/");
        $this->addClassFolder("/admin/formentries/");
        $this->addClassFolder("/admin/statsreports/");
        $this->addClassFolder("/admin/systemtasks/");
        $this->addClassFolder("/admin/widgets/");
        $this->addClassFolder("/admin/");
        $this->addClassFolder("/portal/elements/");
        $this->addClassFolder("/portal/templatemapper/");
        $this->addClassFolder("/portal/");
        $this->addClassFolder("/system/db/");
        $this->addClassFolder("/system/usersources/");
        $this->addClassFolder("/system/imageplugins/");
        $this->addClassFolder("/system/validators/");
        $this->addClassFolder("/system/workflows/");
        $this->addClassFolder("/system/messageproviders/");
        $this->addClassFolder("/system/scriptlets/");
        $this->addClassFolder("/system/");
        $this->addClassFolder("/installer/");
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

        foreach ($this->arrModules as $strPath => $strSingleModule) {
            if (is_dir(_realpath_."/".$strPath.$strFolder)) {
                $arrTempFiles = scandir(_realpath_."/".$strPath.$strFolder);
                foreach ($arrTempFiles as $strSingleFile) {
                    if (preg_match("/(class|interface|trait)(.*)\.php$/i", $strSingleFile)) {
                        $arrFiles[substr($strSingleFile, 0, -4)] = _realpath_."/".$strPath.$strFolder.$strSingleFile;
                    }
                }
            }
        }

        //scan for overwrites
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
        if (isset($this->arrFiles[$strClassName])) {
            $this->intNumberOfClassesLoaded++;
            include_once $this->arrFiles[$strClassName];
            return true;
        }

        // check whether we can autoload a class which has a namespace
        if (strpos($strClassName, "\\") !== false) {
            $arrParts = explode("\\", $strClassName);
            $strVendor = array_shift($arrParts); // remove vendor part
            $strModule = "module_" . strtolower(array_shift($arrParts));
            $strFolder = strtolower(array_shift($arrParts));
            $strRest = implode(DIRECTORY_SEPARATOR, $arrParts);

            if (!empty($strModule) && !empty($strFolder) && !empty($strRest)) {
                $arrDirs = array_merge(array("project"), $this->arrCoreDirs);
                foreach ($arrDirs as $strDir) {
                    $strFile = implode(DIRECTORY_SEPARATOR, array($strDir, $strModule, $strFolder, $strRest . ".php"));
                    if (is_file($strFile)) {
                        $this->arrFiles[$strClassName] = $strFile;
                        $this->bitCacheSaveRequired = true;

                        $this->intNumberOfClassesLoaded++;
                        include_once $strFile;
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Returns the list of modules indexed by the classloader, so residing under /core
     *
     * @return string[]
     */
    public function getArrModules()
    {
        return $this->arrModules;
    }

    /**
     * Adds a new folder to the autoload locations
     *
     * @param string $strPath
     *
     * @return void
     */
    public function addClassFolder($strPath)
    {
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder($strPath));
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
