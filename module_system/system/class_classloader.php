<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                            *
********************************************************************************************************/

/**
 * Class-loader for all Kajona classes.
 * Implemented as a singleton.
 * May be extended by a project-specific setup. All classes defined in /project/classes/classloader.xml will
 * add or redefine the classes included in the xml-document.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_classloader {


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
     * Factory method returning an instance of class_classloader.
     * The class-loader implements the singleton pattern.
     * @static
     * @return class_classloader
     */
    public static function getInstance() {
        if(self::$objInstance == null)
            self::$objInstance = new class_classloader();

        return self::$objInstance;
    }

    /**
     * Constructor, initializes the internal fields
     */
    private function __construct() {


        $this->strModulesCacheFile          = _realpath_."/project/temp/modules.cache";
        $this->strClassesCacheFile          = _realpath_."/project/temp/classes.cache";

        include_once(__DIR__."/class_apc_cache.php");
        $this->arrModules = class_apc_cache::getInstance()->getValue(__CLASS__."modules");
        $this->arrFiles = class_apc_cache::getInstance()->getValue(__CLASS__."files");

        if($this->arrModules === false || $this->arrFiles == false) {
            $this->arrModules = array();
            $this->arrFiles = array();

            if(is_file($this->strModulesCacheFile) && is_file($this->strClassesCacheFile)) {
                $this->arrModules = unserialize(file_get_contents($this->strModulesCacheFile));
                $this->arrFiles = unserialize(file_get_contents($this->strClassesCacheFile));
            }
            else {

                $this->scanModules();

                $this->indexAvailableCodefiles();
                //$this->loadClassloaderConfig();

                $this->bitCacheSaveRequired = true;
            }
        }

    }

    /**
     * Stores the cached structures to file - if enabled and required
     */
    public function __destruct() {
        if($this->bitCacheSaveRequired && class_config::getInstance()->getConfig('resourcecaching') == true) {

            class_apc_cache::getInstance()->addValue(__CLASS__."modules", $this->arrModules);
            class_apc_cache::getInstance()->addValue(__CLASS__."files", $this->arrFiles);

            file_put_contents($this->strModulesCacheFile, serialize($this->arrModules));
            file_put_contents($this->strClassesCacheFile, serialize($this->arrFiles));
        }
    }

    private function scanModules() {
        $this->arrModules = scandir(_corepath_);

        $this->arrModules = array_filter(
            $this->arrModules,
            function($strValue) {
                return preg_match("/(module|element|_)+(.*)/i", $strValue) && is_dir(_corepath_."/".$strValue);
            }
        );
    }

    /**
     * Flushes the cache-files.
     * Use this method if you added new modules / classes.
     * The classes are reinitialized automatically.
     */
    public function flushCache() {
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
     */
    private function indexAvailableCodefiles() {

        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/admin/elements/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/admin/formentries/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/admin/statsreports/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/admin/systemtasks/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/admin/widgets/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/admin/searchplugins/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/admin/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/portal/elements/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/portal/searchplugins/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/portal/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/system/db/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/system/usersources/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/system/validators/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/system/workflows/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/system/messageproviders/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/system/scriptlets/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/system/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/installer/"));

    }

    /**
     * Loads all classes in a single folder, but traversing each module available.
     * Internal helper.
     * @param $strFolder
     * @return string[]
     */
    private function getClassesInFolder($strFolder) {

        $arrFiles = array();

        foreach($this->arrModules as $strSingleModule) {
            if(is_dir(_corepath_."/".$strSingleModule.$strFolder)) {
                $arrTempFiles = scandir(_corepath_."/".$strSingleModule.$strFolder);
                foreach($arrTempFiles as $strSingleFile) {
                    if(preg_match("/(class|interface)(.*)\.php/i", $strSingleFile)) {
                        $arrFiles[substr($strSingleFile, 0, -4)] = _corepath_."/".$strSingleModule.$strFolder.$strSingleFile;
                    }
                }
            }
        }

        //scan for overwrites
        if(is_dir(_realpath_."/project".$strFolder)) {
            $arrTempFiles = scandir(_realpath_."/project".$strFolder);
            foreach($arrTempFiles as $strSingleFile) {
                if(preg_match("/(class|interface)(.*)\.php/i", $strSingleFile)) {
                    $arrFiles[substr($strSingleFile, 0, -4)] = _realpath_."/project".$strFolder.$strSingleFile;
                }
            }
        }

        return $arrFiles;
    }



    /**
     * The class-loader itself. Loads the class, if existing. Otherwise the chain of class-loaders is triggered.
     *
     * @param $strClassName
     * @return bool
     */
    public function loadClass($strClassName) {

        if(isset($this->arrFiles[$strClassName])) {
            include $this->arrFiles[$strClassName];
            return true;
        }

        return false;

    }

    /**
     * Returns the list of modules indexed by the classloader, so residing under /core
     * @return string[]
     */
    public function getArrModules() {
        return $this->arrModules;
    }

}
