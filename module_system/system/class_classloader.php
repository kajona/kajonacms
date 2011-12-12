<?php
/*"******************************************************************************************************
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_carrier.php 4059 2011-08-09 14:52:41Z sidler $                                            *
********************************************************************************************************/

/**
 * Classloader for all Kajona classes.
 * Implemented as a singleton.
 *
 * @todo: add xml-based redefinition based on project-files
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
     * The classloader implements the singleton pattern.
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
    }

    private function __clone() {
    }



    private function indexAvailableCodefiles() {

        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/admin/widgets/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/admin/systemtasks/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/admin/statsreports/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/admin/elements/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/admin/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/portal/searchplugins/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/portal/elements/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/portal/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/system/db/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/system/usersources/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/system/workflows/"));
        $this->arrFiles = array_merge($this->arrFiles, $this->getClassesInFolder("/system/"));

    }

    /**
     * Loads all classes in a single folder.
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
     * The classloader itself. Loads the class, if existing. Otherwise the chain of class-loaders is triggered.
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
