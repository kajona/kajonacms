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
    private $strPharModulesCacheFile = "";
    private $strClassesCacheFile = "";

    private $bitCacheSaveRequired = false;

    /**
     * @var class_classloader
     */
    private static $objInstance = null;

    private $arrModules = array();

    private $arrPharModules = array();

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


        $this->strModulesCacheFile = _realpath_."/project/temp/modules.cache";
        $this->strPharModulesCacheFile = _realpath_."/project/temp/pharmodules.cache";
        $this->strClassesCacheFile = _realpath_."/project/temp/classes.cache";

        include_once(__DIR__."/class_apc_cache.php");
        $this->arrModules = class_apc_cache::getInstance()->getValue(__CLASS__."modules");
        $this->arrPharModules = class_apc_cache::getInstance()->getValue(__CLASS__."pharmodules");
        $this->arrFiles = class_apc_cache::getInstance()->getValue(__CLASS__."files");

        if ($this->arrModules === false || $this->arrPharModules === false || $this->arrFiles == false) {
            $this->arrModules = array();
            $this->arrPharModules = array();
            $this->arrFiles = array();

            if (is_file($this->strModulesCacheFile)
              && is_file($this->strPharModulesCacheFile)
              && is_file($this->strClassesCacheFile)) {
                $this->arrModules = unserialize(file_get_contents($this->strModulesCacheFile));
                $this->arrPharModules = unserialize(file_get_contents($this->strPharModulesCacheFile));
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
            class_apc_cache::getInstance()->addValue(__CLASS__."pharmodules", $this->arrPharModules);
            class_apc_cache::getInstance()->addValue(__CLASS__."files", $this->arrFiles);

            file_put_contents($this->strModulesCacheFile, serialize($this->arrModules));
            file_put_contents($this->strPharModulesCacheFile, serialize($this->arrPharModules));
            file_put_contents($this->strClassesCacheFile, serialize($this->arrFiles));
        }
    }

    /**
     * We autoload all classes which are in the event folder of each module. Theses classes can register events etc.
     *
     * @throws class_exception
     */
    public function includeClasses()
    {
        foreach ($this->arrFiles as $strClass => $strOneFile) {
            if (uniStrpos($strOneFile, "/event/") !== false) {
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

            if (uniStrpos($strRootFolder, "core") === false) {
                continue;
            }

            foreach (scandir(_realpath_."/".$strRootFolder) as $strOneModule) {
                $strModuleName = null;
                $boolIsPhar = false;

                $pharExtensionPos = uniStrpos($strOneModule, ".phar");
                if ($pharExtensionPos !== false) {
                    $strModuleName = substr($strOneModule, 0, $pharExtensionPos);
                    $boolIsPhar = true;
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

        $this->arrModules = $arrModules;
        $this->arrPharModules = array_diff($arrPharModules, $arrModules);
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
        $objFilesystem->fileDelete($this->strPharModulesCacheFile);
        $objFilesystem->fileDelete($this->strClassesCacheFile);

        $this->arrFiles = array();
        $this->arrModules = array();
        $this->arrPharModules = array();
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
        foreach (self::$arrCodeFolders as $strFolder) {
            $this->addClassFolder($strFolder);
        }

        foreach ($this->arrPharModules as $strPath => $strSingleModule) {
            $this->addPhar($strPath);
        }
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

        foreach (array_merge($this->arrModules, array("project")) as $strPath => $strSingleModule) {
            if (is_dir(_realpath_."/".$strPath.$strFolder)) {
                $arrTempFiles = scandir(_realpath_."/".$strPath.$strFolder);
                foreach ($arrTempFiles as $strSingleFile) {
                    if (strpos($strSingleFile, ".php") !== false) {
                        $arrFiles[substr($strSingleFile, 0, -4)] = _realpath_."/".$strPath.$strFolder.$strSingleFile;
                    }
                }
            }
        }

        //scan for overwrites
        //FIXME: remove in 5.x, only needed for backwards compatibility where content under /project was not organized within module-folders
        if (is_dir(_realpath_."/project".$strFolder)) {
            $arrTempFiles = scandir(_realpath_."/project".$strFolder);
            foreach ($arrTempFiles as $strSingleFile) {
                if (strpos($strSingleFile, ".php") !== false) {
                    $arrFiles[substr($strSingleFile, 0, -4)] = _realpath_."/project".$strFolder.$strSingleFile;
                }
            }
        }

        return $arrFiles;
    }

    private function addPhar($strPharPath)
    {
        $phar = new Phar(_realpath_."/".$strPharPath, 0);
        foreach (new RecursiveIteratorIterator($phar) as $file) {
            // Make sure the file is a PHP file and is inside the requested folder
            $strArchivePath = DIRECTORY_SEPARATOR.substr($file->getPathName(), strlen("phar://"._realpath_."/".$strPharPath));
            $strArchivePath = str_replace("\\", "/", $strArchivePath);

            foreach (self::$arrCodeFolders as $strFolder) {
              $strFolder = str_replace("\\", "/", $strFolder);

              if (substr($strArchivePath, -4) === ".php"
                && substr($strArchivePath, 0, strlen($strFolder)) === $strFolder) {
                  $strFilename = substr($file->getFileName(), 0, -4);
                  // PHAR archive files must never override existing file system files
                  if (!isset($this->arrFiles[$strFilename])) {
                      $this->arrFiles[$strFilename] = $file->getPathName();
                  }
              }
            }

            // Include the module ID
            if (preg_match("/module\_([a-z0-9\_])+\_id\.php/", $file->getFileName())) {
                include_once $file->getPathName();
            }
        }
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

            // strtolower all parts except for the class name
            $strClass = array_pop($arrParts);
            $arrParts = array_map("strtolower", $arrParts);
            array_push($arrParts, $strClass);

            // remove vendor part
            $strVendor = array_shift($arrParts);

            $strModule = "module_".array_shift($arrParts);
            $strFolder = array_shift($arrParts);
            $strRest = implode(DIRECTORY_SEPARATOR, $arrParts);

            if (!empty($strModule) && !empty($strFolder) && !empty($strRest)) {
                $arrDirs = array_merge(array("project"), $this->arrCoreDirs);
                foreach ($arrDirs as $strDir) {
                    $strFile = _realpath_.implode(DIRECTORY_SEPARATOR, array($strDir, $strModule, $strFolder, $strRest.".php"));
                    if ($this->addAndIncludeFile($strClassName, $strFile)) {
                        return true;
                    }
                }

                foreach ($this->arrCoreDirs as $strDir) {
                    foreach (array(".phar", ".phar.gz", ".phar.bz2", ".phar.zip") as $strPharExtension) {
                        $strFile = 'phar://' . _realpath_.implode(DIRECTORY_SEPARATOR, array($strDir, $strModule.$strPharExtension, $strFolder, $strRest.".php"));
                        if ($this->addAndIncludeFile($strClassName, $strFile)) {
                            return true;
                        }
                    }
                }

            }
        }

        return false;
    }

    private function addAndIncludeFile($strClassName, $strFile)
    {
        if (is_file($strFile)) {
            $this->arrFiles[$strClassName] = $strFile;
            $this->bitCacheSaveRequired = true;

            $this->intNumberOfClassesLoaded++;
            include_once $strFile;
            return true;
        }

        return false;
    }

    /**
     * Extracts the class-name out of a filename.
     * Normally this method is only used by getInstanceFromFilename, so no use to call it directly.
     *
     * @param $strFilename
     *
     * @return null|string
     */
    public function getClassnameFromFilename($strFilename)
    {
        //blacklisting!
        if (uniStrpos(basename($strFilename), "class_testbase") === 0) {
            return null;
        }

        // check is_file in case of a phar:// or absolute path
        if (is_file($strFilename)) {
            include_once $strFilename;
        } else {
            include_once _realpath_.$strFilename;
        }

        $strResolvedClassname = null;
        $strClassname = uniSubstr(basename($strFilename), 0, -4);

        if (class_exists($strClassname)) {
            $strResolvedClassname = $strClassname;
        }
        else {
            $strSource = file_get_contents(_realpath_.$strFilename);
            preg_match('/namespace ([a-zA-Z0-9_\x7f-\xff\\\\]+);/', $strSource, $arrMatches);

            $strNamespace = isset($arrMatches[1]) ? $arrMatches[1] : null;
            if (!empty($strNamespace)) {
                $strClassname = $strNamespace."\\".$strClassname;
                if (class_exists($strClassname)) {
                    $strResolvedClassname = $strClassname;
                }
            }
        }

        return $strResolvedClassname;
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

    /**
     * Returns the list of modules indexed by the classloader, so residing under /core
     *
     * @return string[]
     */
    public function getArrModules()
    {
        return $this->arrModules + $this->arrPharModules;
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
