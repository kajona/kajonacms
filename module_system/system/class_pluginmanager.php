<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                         *
********************************************************************************************************/

/**
 * The pluginmanager is a central object used to load implementers of interface_generic_plugin.
 * Plugins identify themselves using a plugin / extension point key.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 4.5
 */
class class_pluginmanager {

    /**
     * @var string[]
     */
    private static $arrPluginClasses = array();

    private $strSearchPath = "/system";
    private $strPluginPoint = "";

    /**
     * @param string $strPluginPoint
     * @param string $strSearchPath
     */
    function __construct($strPluginPoint, $strSearchPath = "/system") {
        $this->strPluginPoint = $strPluginPoint;
        $this->strSearchPath = $strSearchPath;
    }


    /**
     * This method returns all plugins registered for the current extension point searching at the predefined path.
     * By default, new instances of the classes are returned. If the implementing
     * class requires specific constructor arguments, pass them as the second argument and they will be
     * used during instantiation.
     *
     * @param array $arrConstructorArguments
     *
     * @static
     * @return interface_generic_plugin[]
     */
    public function getPlugins($arrConstructorArguments = array()) {
        //load classes in passed-folders
        $strKey = md5($this->strSearchPath.$this->strPluginPoint);
        if(!array_key_exists($strKey, self::$arrPluginClasses)) {
            $strPluginPoint = $this->strPluginPoint;
            $arrClasses = class_resourceloader::getInstance()->getFolderContent($this->strSearchPath, array(".php"), false, function(&$strOneFile) use ($strPluginPoint) {
                $strOneFile = uniSubstr($strOneFile, 0, -4);

                if(uniStripos($strOneFile, "class_") === false || uniStrpos($strOneFile, "class_testbase") !== false)
                    return false;

                $objReflection = new ReflectionClass($strOneFile);
                if(!$objReflection->isAbstract() && $objReflection->implementsInterface("interface_generic_plugin")) {
                    $objMethod = $objReflection->getMethod("getExtensionName");
                    if($objMethod->invoke(null) == $strPluginPoint)
                        return true;
                }

                return false;
            });

            self::$arrPluginClasses[$strKey] = $arrClasses;
        }

        $arrReturn = array();
        foreach(self::$arrPluginClasses[$strKey] as $strOneClass) {
            $objReflection = new ReflectionClass($strOneClass);
            if(count($arrConstructorArguments) > 0)
                $arrReturn[] = $objReflection->newInstanceArgs($arrConstructorArguments);
            else
                $arrReturn[] = $objReflection->newInstance();
        }

        return $arrReturn;
    }

}

