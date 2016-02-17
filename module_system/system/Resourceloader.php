<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*    $Id$                                            *
********************************************************************************************************/

namespace Kajona\System\System;


use Closure;

/**
 * Loader to dynamically resolve and load resources (this is mapping a virtual file-name to a real filename,
 * relative to the project-root).
 * Currently, this includes the loading of templates and lang-files.
 * In addition, the resource-loader supports the listing of files in a given folder.
 * Therefore, the merged file-list of each module below /core may be read.
 *
 * The loader is, as usual, implemented as a singleton.
 * All lookups are cached, so subsequent lookups will be done without filesystem-queries.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class Resourceloader
{

    /**
     * @var Resourceloader
     */
    private static $objInstance = null;


    /**
     * Factory method returning an instance of Resourceloader.
     * The resource-loader implements the singleton pattern.
     *
     * @static
     * @return Resourceloader
     */
    public static function getInstance()
    {
        if (self::$objInstance == null) {
            self::$objInstance = new Resourceloader();
        }

        return self::$objInstance;
    }

    /**
     * Constructor, initializes the internal fields
     */
    private function __construct()
    {

    }


    /**
     * Deletes all cached resource-information,
     * so the .cache-files.
     *
     * @return void
     * @deprecated
     */
    public function flushCache()
    {
        Classloader::getInstance()->flushCache();
    }

    /**
     * Looks up the real filename of a template passed.
     * The filename is the relative path, so adding /templates/[packname] is not required and not allowed.
     *
     * @param string $strTemplateName
     * @param bool $bitScanAdminSkin
     *
     * @throws Exception
     * @return string The path on the filesystem, relative to the root-folder. Null if the file could not be mapped.
     */
    public function getTemplate($strTemplateName, $bitScanAdminSkin = false)
    {
        $strTemplateName = removeDirectoryTraversals($strTemplateName);
        if (BootstrapCache::getInstance()->getCacheRow(BootstrapCache::CACHE_TEMPLATES, $strTemplateName)) {
            return BootstrapCache::getInstance()->getCacheRow(BootstrapCache::CACHE_TEMPLATES, $strTemplateName);
        }

        $strFilename = null;
        //first try: load the file in the current template-pack
        $strDefaultTemplate = SystemSetting::getConfigValue("_packagemanager_defaulttemplate_");
        if (is_file(_realpath_._templatepath_."/".$strDefaultTemplate."/tpl".$strTemplateName)) {
            BootstrapCache::getInstance()->addCacheRow(BootstrapCache::CACHE_TEMPLATES, $strTemplateName, _realpath_._templatepath_."/".$strDefaultTemplate."/tpl".$strTemplateName);
            return _realpath_._templatepath_."/".$strDefaultTemplate."/tpl".$strTemplateName;
        }

        //second try: load the file from the default-pack
        if (is_file(_realpath_._templatepath_."/default/tpl".$strTemplateName)) {
            BootstrapCache::getInstance()->addCacheRow(BootstrapCache::CACHE_TEMPLATES, $strTemplateName, _realpath_._templatepath_."/default/tpl".$strTemplateName);
            return _realpath_._templatepath_."/default/tpl".$strTemplateName;
        }

        //third try: try to load the file from a given module
        foreach (Classloader::getInstance()->getArrModules() as $strCorePath => $strOneModule) {
            if (is_dir(_realpath_."/".$strCorePath)) {
                if (is_file(_realpath_."/".$strCorePath."/templates/default/tpl".$strTemplateName)) {
                    $strFilename = _realpath_."/".$strCorePath."/templates/default/tpl".$strTemplateName;
                    break;
                }
                if (is_file(_realpath_."/".$strCorePath.$strTemplateName)) {
                    $strFilename = _realpath_."/".$strCorePath.$strTemplateName;
                    break;
                }
            } elseif (PharModule::isPhar(_realpath_."/".$strCorePath)) {
                $strAbsolutePath = PharModule::getPharStreamPath(_realpath_."/".$strCorePath, "/templates/default/tpl".$strTemplateName);
                if (is_file($strAbsolutePath)) {
                    $strFilename = $strAbsolutePath;
                    break;
                }

                $strAbsolutePath = PharModule::getPharStreamPath(_realpath_."/".$strCorePath, $strTemplateName);
                if (is_file($strAbsolutePath)) {
                    $strFilename = $strAbsolutePath;
                    break;
                }
            }
        }

        if($strFilename !== null) {
            BootstrapCache::getInstance()->addCacheRow(BootstrapCache::CACHE_TEMPLATES, $strTemplateName, $strFilename);
            return $strFilename;
        }



        if ($bitScanAdminSkin) {
            //scan directly
            if (is_file($strTemplateName)) {
                $strFilename = $strTemplateName;
            }

            //prepend path
            if (is_file(AdminskinHelper::getPathForSkin(Session::getInstance()->getAdminSkin()).$strTemplateName)) {
                $strFilename = AdminskinHelper::getPathForSkin(Session::getInstance()->getAdminSkin()).$strTemplateName;
            }
        }

        if ($strFilename === null) {
            throw new Exception("Required file ".$strTemplateName." could not be mapped on the filesystem.", Exception::$level_ERROR);
        }

        BootstrapCache::getInstance()->addCacheRow(BootstrapCache::CACHE_TEMPLATES, $strTemplateName, $strFilename);

        return $strFilename;
    }


    /**
     * Looks up the real filename of a template passed.
     * The filename is the relative path, so adding /templates/[packname] is not required and not allowed.
     *
     * @param string $strFolder
     *
     * @return array A list of templates, so the merged result of the current template-pack + default-pack + fallback-files
     */
    public function getTemplatesInFolder($strFolder)
    {

        $arrReturn = array();

        //first try: load the file in the current template-pack
        if (is_dir(_realpath_._templatepath_."/".SystemSetting::getConfigValue("_packagemanager_defaulttemplate_")."/tpl".$strFolder)) {
            $arrFiles = scandir(_realpath_._templatepath_."/".SystemSetting::getConfigValue("_packagemanager_defaulttemplate_")."/tpl".$strFolder);
            foreach ($arrFiles as $strOneFile) {
                if (substr($strOneFile, -4) == ".tpl") {
                    $arrReturn[] = $strOneFile;
                }
            }
        }

        //second try: load the file from the default-pack
        if (is_dir(_realpath_._templatepath_."/default/tpl".$strFolder)) {
            $arrFiles = scandir(_realpath_._templatepath_."/default/tpl".$strFolder);
            foreach ($arrFiles as $strOneFile) {
                if (substr($strOneFile, -4) == ".tpl") {
                    $arrReturn[] = $strOneFile;
                }
            }
        }

        //third try: try to load the file from given modules
        foreach (Classloader::getInstance()->getArrModules() as $strCorePath => $strOneModule) {
            if (is_dir(_realpath_."/".$strCorePath."/templates/default/tpl".$strFolder)) {
                $arrFiles = scandir(_realpath_."/".$strCorePath."/templates/default/tpl".$strFolder);
                foreach ($arrFiles as $strOneFile) {
                    if (substr($strOneFile, -4) == ".tpl") {
                        $arrReturn[] = $strOneFile;
                    }
                }
            }
        }


        return $arrReturn;
    }

    /**
     * Loads all lang-files in a passed folder (module or element).
     * The loader resolves the files stored in the project-folder, overwriting the files found in the default-installation.
     * The array returned is based on [path_to_file] = [filename] where the key is relative to the project-root.
     * No caching is done for lang-files, since the entries are cached by the lang-class, too.
     *
     * @param string $strFolder
     *
     * @return array
     */
    public function getLanguageFiles($strFolder)
    {

        if (BootstrapCache::getInstance()->getCacheRow(BootstrapCache::CACHE_LANG, $strFolder) !== false) {
            return BootstrapCache::getInstance()->getCacheRow(BootstrapCache::CACHE_LANG, $strFolder);
        }
        $arrReturn = array();

        //loop all given modules
        foreach (Classloader::getInstance()->getArrModules() as $strCorePath => $strSingleModule) {
            if (is_dir(_realpath_."/".$strCorePath._langpath_."/".$strFolder)) {
                $arrContent = scandir(_realpath_."/".$strCorePath._langpath_."/".$strFolder);
                foreach ($arrContent as $strSingleEntry) {

                    if (substr($strSingleEntry, -4) == ".php") {
                        $arrReturn[_realpath_.$strCorePath._langpath_."/".$strFolder."/".$strSingleEntry] = $strSingleEntry;
                    }
                }
            } elseif (PharModule::isPhar(_realpath_."/".$strCorePath)) {

                $objPhar = new PharModule($strCorePath);
                foreach($objPhar->getContentMap() as $strFilename => $strPharPath) {
                    if (strpos($strFilename, _langpath_."/".$strFolder."/") !== false) {
                        $arrReturn[$strPharPath] = basename($strPharPath);
                    }
                }
            }
        }

        //check if the same is available in the projects-folder
        if (is_dir(_realpath_._projectpath_._langpath_."/".$strFolder)) {
            $arrContent = scandir(_realpath_._projectpath_._langpath_."/".$strFolder);
            foreach ($arrContent as $strSingleEntry) {

                if (substr($strSingleEntry, -4) == ".php") {

                    $strKey = array_search($strSingleEntry, $arrReturn);
                    if ($strKey !== false) {
                        unset($arrReturn[$strKey]);
                    }
                    $arrReturn[_realpath_._projectpath_._langpath_."/".$strFolder."/".$strSingleEntry] = $strSingleEntry;

                }

            }
        }

        BootstrapCache::getInstance()->addCacheRow(BootstrapCache::CACHE_LANG, $strFolder, $arrReturn);
        return $arrReturn;
    }

    /**
     * Loads all files in a passed folder, as usual relative to the core whereas the single module-folders may be skipped.
     * The array returned is based on [path_to_file] = [filename] where the key is relative to the project-root.
     * If you want to filter the list of files being returned, pass a callback/closure as the 4th argument. The callback is used
     * as defined in array_filter.
     * If you want to apply a custom function on each (filtered) element, use the 5th param to pass a closure. The callback is passed to array_walk,
     * so the same conventions should be applied,
     *
     * @param string $strFolder
     * @param array $arrExtensionFilter
     * @param bool $bitWithSubfolders includes folders into the return set, otherwise only files will be returned
     * @param Closure $objFilterFunction
     * @param Closure $objWalkFunction
     *
     * @return array
     * @see http://php.net/manual/de/function.array-filter.php
     * @see http://php.net/manual/de/function.array-walk.php
     */
    public function getFolderContent($strFolder, $arrExtensionFilter = array(), $bitWithSubfolders = false, Closure $objFilterFunction = null, Closure $objWalkFunction = null)
    {
        $arrReturn = array();
        $strCachename = md5($strFolder.implode(",", $arrExtensionFilter).($bitWithSubfolders ? "sub" : "nosub"));

        if (BootstrapCache::getInstance()->getCacheRow(BootstrapCache::CACHE_FOLDERCONTENT, $strCachename)) {
            return $this->applyCallbacks(BootstrapCache::getInstance()->getCacheRow(BootstrapCache::CACHE_FOLDERCONTENT, $strCachename), $objFilterFunction, $objWalkFunction);
        }

        //loop all given modules
        foreach (Classloader::getInstance()->getArrModules() as $strCorePath => $strSingleModule) {
            if (is_dir(_realpath_."/".$strCorePath.$strFolder)) {
                $arrContent = scandir(_realpath_."/".$strCorePath.$strFolder);
                foreach ($arrContent as $strSingleEntry) {

                    if (($strSingleEntry != "." && $strSingleEntry != "..") && ($bitWithSubfolders || is_file(_realpath_."/".$strCorePath.$strFolder."/".$strSingleEntry))) {
                        //Wanted Type?
                        if (count($arrExtensionFilter) == 0) {
                            $arrReturn[_realpath_.$strCorePath.$strFolder."/".$strSingleEntry] = $strSingleEntry;
                        }
                        else {
                            //check, if suffix is in allowed list
                            $strFileSuffix = uniSubstr($strSingleEntry, uniStrrpos($strSingleEntry, "."));
                            if (in_array($strFileSuffix, $arrExtensionFilter)) {
                                $arrReturn[_realpath_.$strCorePath.$strFolder."/".$strSingleEntry] = $strSingleEntry;
                            }
                        }
                    }

                }
            } elseif (is_file(_realpath_."/".$strCorePath)) {
                $objPhar = new PharModule($strCorePath);

                foreach($objPhar->getContentMap() as $strPath => $strAbsolutePath) {
                    if(strpos($strPath, $strFolder."/".basename($strPath)) === 0) {
                        $arrReturn[$strAbsolutePath] = basename($strPath);
                    }
                }
            }
        }

        //check if the same is available in the projects-folder and overwrite the first hits
        if (is_dir(_realpath_."project/".$strFolder)) {
            $arrContent = scandir(_realpath_."project/".$strFolder);
            foreach ($arrContent as $strSingleEntry) {

                //Wanted Type?
                if (count($arrExtensionFilter) == 0) {

                    $strKey = array_search($strSingleEntry, $arrReturn);
                    if ($strKey !== false) {
                        unset($arrReturn[$strKey]);
                    }
                    $arrReturn[_realpath_."project/".$strFolder."/".$strSingleEntry] = $strSingleEntry;

                }
                else {
                    //check, if suffix is in allowed list
                    $strFileSuffix = uniSubstr($strSingleEntry, uniStrrpos($strSingleEntry, "."));
                    if (in_array($strFileSuffix, $arrExtensionFilter)) {
                        $strKey = array_search($strSingleEntry, $arrReturn);
                        if ($strKey !== false) {
                            unset($arrReturn[$strKey]);
                        }
                        $arrReturn[_realpath_."project/".$strFolder."/".$strSingleEntry] = $strSingleEntry;
                    }

                }

            }
        }


        BootstrapCache::getInstance()->addCacheRow(BootstrapCache::CACHE_FOLDERCONTENT, $strCachename, $arrReturn);
        return $this->applyCallbacks($arrReturn, $objFilterFunction, $objWalkFunction);
    }

    /**
     * Internal helper to apply the passed callback as an array_filter callback to the list of matching files
     *
     * @param string[] $arrEntries
     * @param callable $objFilterCallback
     * @param callable $objWalkCallback
     *
     * @return array
     */
    private function applyCallbacks($arrEntries, Closure $objFilterCallback = null, Closure $objWalkCallback = null)
    {
        if (($objFilterCallback == null || !is_callable($objFilterCallback)) && ($objWalkCallback == null || !is_callable($objWalkCallback))) {
            return $arrEntries;
        }

        $arrTemp = array();
        foreach ($arrEntries as $strKey => $strValue) {
            $arrTemp[$strKey] = $strValue;
        }

        if ($objFilterCallback !== null) {
            $arrTemp = array_filter($arrTemp, $objFilterCallback);
        }

        if ($objWalkCallback !== null) {
            array_walk($arrTemp, $objWalkCallback);
        }

        return $arrTemp;
    }

    /**
     * Converts a relative path to a real path on the filesystem.
     * If the file can't be found, false is returned instead.
     *
     * @param string $strFile the relative path
     * @param bool $bitCheckProject en- or disables the lookup in the /project folder
     *
     * @return string|bool the absolute path
     *
     */
    public function getPathForFile($strFile)
    {
        //fallback on the resourceloader
        $arrContent = $this->getFolderContent(dirname($strFile));
        $strSearchedFilename = basename($strFile);
        foreach ($arrContent as $strPath => $strContentFile) {
            if ($strContentFile == $strSearchedFilename) {
                return $strPath;
            }
        }

        return false;
    }


    /**
     * Converts a relative path to a real path on the filesystem.
     * If the file can't be found, false is returned instead.
     *
     * @param string $strFolder the relative path
     *
     * @return string|bool the absolute path
     */
    public function getPathForFolder($strFolder)
    {

        $arrContent = $this->getFolderContent($strFolder, array(), true);
        foreach ($arrContent as $strPath => $strContentFile) {
            if(strpos($strPath, $strFolder) !== false) {
                return substr($strPath, 0, strpos($strPath, $strFolder)+strlen($strFolder));
            }
        }

        return false;
    }

    /**
     * Returns the folder the passed module is located in.
     * E.g., when passing module_system, the matching "/core" will be returned.
     *
     * @param string $strModule
     * @param bool $bitPrependRealpath
     *
     * @return string
     */
    public function getCorePathForModule($strModule, $bitPrependRealpath = false)
    {
        $arrFlipped = array_flip(Classloader::getInstance()->getArrModules());

        if (!array_key_exists($strModule, $arrFlipped)) {
            return null;
        }

        $strPath = uniSubstr(uniStrReplace(array($strModule.".phar", $strModule), "", $arrFlipped[$strModule]), 0, -1);

        return ($bitPrependRealpath ? _realpath_ : "")."/".$strPath;
    }

    /**
     * Returns the absolute path for a module.
     * Validates if the module is a phar and adds the phar:// stream wrapper automatically.
     * @param $strModule
     *
     * @return null|string
     */
    public function getAbsolutePathForModule($strModule)
    {
        $arrFlipped = array_flip(Classloader::getInstance()->getArrModules());

        if (!array_key_exists($strModule, $arrFlipped)) {
            return null;
        }

        $strPath = _realpath_.$arrFlipped[$strModule];
        if(\Kajona\System\System\StringUtil::endsWith($strPath, ".phar")) {
            $strPath = "phar://".$strPath;
        }
        return $strPath;
    }

    /**
     * Returns the web-path of a module, useful when loading static content such as images or css from
     * a phar-based module
     * @param $strModule
     *
     * @return string
     */
    public function getWebPathForModule($strModule)
    {
        $arrPhars = Classloader::getInstance()->getArrPharModules();
        if (in_array($strModule, $arrPhars)) {
            return "/files/extract/".$strModule;
        }

        return $this->getCorePathForModule($strModule)."/".$strModule;

    }

    /**
     * Returns the core-folder the passed file is located in, e.g. core or core2.
     * Pass a full file-path, so the absolute path and filename.
     *
     * @param string $strPath
     * @param bool $bitPrependRealpath
     *
     * @return string
     */
    public function getCorePathForPath($strPath, $bitPrependRealpath = false)
    {
        $strPath = uniStrReplace(_realpath_."/", "", $strPath);
        $strPath = uniSubstr($strPath, 0, uniStrpos($strPath, "/"));

        return ($bitPrependRealpath ? _realpath_ : "")."/".$strPath;
    }


    /**
     * Returns the list of modules and elements under the /core folder
     *
     * @return array [folder/module] => [module]
     * @deprecated use class_classloader::getInstance()->getArrModules() instead
     */
    public function getArrModules()
    {
        return Classloader::getInstance()->getArrModules();
    }


}
