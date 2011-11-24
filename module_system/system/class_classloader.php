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
 * @package module_system
 */
class class_classloader {

    /**
     * @var class_classloader
     */
    private static $objInstance = null;

    private $arrModules = array();


    /**
     * Factory method returning an instance of class_classloader.
     * The classloader implements the singleton pattern.
     * @static
     * @return class_classloader|null
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

        $this->arrModules = array_filter($this->arrModules, function($strValue) {
            return preg_match("/(module|element|_)+.*/i", $strValue);
        });

    }

    private function __clone() {
    }


    /**
     * The classloader itself. Scans the folders for the required class based on
     * the passed classname.
     * @param $strClassName
     * @return bool
     */
    public function loadClass($strClassName) {

        //scan the system-folder as the first on, may produce the best hit
        if($this->scanSingleModule("/module_system", $strClassName))
            return true;

        foreach($this->arrModules as $strSingleModule) {
            if($this->scanSingleModule("/".$strSingleModule, $strClassName)) {
                return true;
            }
        }

        return false;

    }


    /**
     * Please note: Since require / include scans all of phps include-path vars, a is_file check is done before requiring the file.
     * So the is_file is cheaper than calling require multiple times (especially with large PEAR-repositories).
     * @param $strModule
     * @param $strClassName
     * @return bool
     */
    private function scanSingleModule($strModule, $strClassName) {


        //---ADMIN CLASSES-------------------------------------------------------------------------------
        //adminwidgets
        if(preg_match("/(class|interface)_adminwidget(.*)/", $strClassName)) {
            if(is_file(_corepath_.$strModule._adminpath_."/widgets/".$strClassName.".php") && require(_corepath_.$strModule._adminpath_."/widgets/".$strClassName.".php"))
                return true;
        }

        //systemtasks
        if(preg_match("/(class|interface)(.*)systemtask(.*)/", $strClassName)) {
            if(is_file(_corepath_.$strModule._adminpath_."/systemtasks/".$strClassName.".php") && require(_corepath_.$strModule._adminpath_."/systemtasks/".$strClassName.".php"))
                return true;
        }

        //statsreports
        if(preg_match("/(class)_(.*)stats_report(.*)/", $strClassName)) {
            if(is_file(_corepath_.$strModule._adminpath_."/statsreports/".$strClassName.".php")  && require(_corepath_.$strModule._adminpath_."/statsreports/".$strClassName.".php"))
                return true;
        }

        //admin classes
        //TODO: wtf? why strpos needed? whats wrong with that regex?
        if(preg_match("/(class|interface)_(.*)admin(_xml)?/", $strClassName) && !strpos($strClassName, "adminwidget")) {
            if(is_file(_corepath_.$strModule._adminpath_."/".$strClassName.".php") && require(_corepath_.$strModule._adminpath_."/".$strClassName.".php"))
                return true;
        }


        //---PORTAL CLASSES------------------------------------------------------------------------------

        //search plugins
        if(preg_match("/interface_search(.*)/", $strClassName)) {
            if(is_file(_corepath_.$strModule._portalpath_."/searchplugins/".$strClassName.".php") && require(_corepath_.$strModule._portalpath_."/searchplugins/".$strClassName.".php"))
                return true;
        }

        //portal classes
        if(preg_match("/(class|interface)_(.*)portal(.*)/", $strClassName)) {
            if(is_file(_corepath_.$strModule._portalpath_."/".$strClassName.".php") && require(_corepath_.$strModule._portalpath_."/".$strClassName.".php"))
                return true;
        }

        //---SYSTEM CLASSES------------------------------------------------------------------------------
        //db-drivers
        if(preg_match("/(class|interface)_db_(.*)/", $strClassName)) {
            if(is_file(_corepath_.$strModule._systempath_."/db/".$strClassName.".php") && require(_corepath_.$strModule._systempath_."/db/".$strClassName.".php"))
                return true;
        }

        //usersources
        if(preg_match("/(class|interface)_usersources_(.*)/", $strClassName)) {
            if(is_file(_corepath_.$strModule._systempath_."/usersources/".$strClassName.".php") && require(_corepath_.$strModule._systempath_."/usersources/".$strClassName.".php"))
                return true;
        }

        //workflows
        if(preg_match("/class_workflow_(.*)/", $strClassName)) {
            if(is_file(_corepath_.$strModule._systempath_."/workflows/".$strClassName.".php") && require(_corepath_.$strModule._systempath_."/workflows/".$strClassName.".php"))
                return true;
        }

        //system-classes
        if(preg_match("/(class|interface)_(.*)/", $strClassName)) {
            if(is_file(_corepath_.$strModule._systempath_."/".$strClassName.".php") && require(_corepath_.$strModule._systempath_."/".$strClassName.".php"))
                return true;
        }

        return false;
    }


}
?>