<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_carrier.php 4059 2011-08-09 14:52:41Z sidler $                                            *
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

    /**
     * @var class_classloader
     */
    private static $objInstance = null;

    private $arrModules = array();

    /**
     * Cached index of class-files available
     *
     * @var String[]
     * @todo: could be moved to the session or an apc var
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

        $this->arrModules = scandir(_corepath_);

        $this->arrModules = array_filter(
            $this->arrModules,
            function($strValue) {
                return preg_match("/(module|element|_)+.*/i", $strValue);
            }
        );

        $this->indexAvailableCodefiles();
        $this->loadClassloaderConfig();

    }

    private function __clone() {
    }

    /**
     * Loads an merges all class-mappings as defined in the class-loader config-file.
     *
     * @see /project/system/classes/classloader.xml
     */
    private function loadClassloaderConfig() {
        if(is_file(_realpath_."/project/system/classes/classloader.xml")) {
            $objReader = new XMLReader();
            $objReader->open(_realpath_."/project/system/classes/classloader.xml");

            while($objReader->read() && $objReader->name !== "class");

            while($objReader->name === "class") {

                $strName = "";
                $strPath = "";

                while($objReader->read() && $objReader->name !== 'name');

                if($objReader->name === "name")
                    $strName = $objReader->readString();

                while($objReader->read() && $objReader->name !== 'path');

                if($objReader->name === "path")
                    $strPath = $objReader->readString();


                if($strName != "" && $strPath != "")
                    $this->arrFiles[$strName] = _realpath_.$strPath;

                while($objReader->read() && $objReader->name !== "class");
            }
        }
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
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/admin/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/portal/elements/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/portal/searchplugins/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/portal/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/system/db/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/system/usersources/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/system/validators/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/system/workflows/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/system/messageproviders/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/system/"));

    }

    /**
     * Loads all classes in a single folder, but traversing each module available.
     * Internal helper.
     * @param $strFolder
     * @return String[]
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

}
