<?php
/*"******************************************************************************************************
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*    $Id$                                            *
********************************************************************************************************/

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
class class_resourceloader {

    private $strTemplatesCacheFile = "";
    private $strFoldercontentCacheFile = "";
    private $strFoldercontentLangFile = "";

    /**
     * @var class_resourceloader
     */
    private static $objInstance = null;

    private $arrModules = array();
    private $arrTemplates = array();
    private $arrFoldercontent = array();
    private $arrLangfiles = array();

    private $bitCacheSaveRequired = false;


    /**
     * Factory method returning an instance of class_resourceloader.
     * The resource-loader implements the singleton pattern.
     * @static
     * @return class_resourceloader
     */
    public static function getInstance() {
        if(self::$objInstance == null)
            self::$objInstance = new class_resourceloader();

        return self::$objInstance;
    }

    /**
     * Constructor, initializes the internal fields
     */
    private function __construct() {


        $this->strTemplatesCacheFile        = _realpath_."/project/temp/templates.cache";
        $this->strFoldercontentCacheFile    = _realpath_."/project/temp/foldercontent.cache";
        $this->strFoldercontentLangFile     = _realpath_."/project/temp/lang.cache";

        $this->arrModules = class_classloader::getInstance()->getArrModules();

        //try to load the caches from apc
        $this->arrTemplates = class_apc_cache::getInstance()->getValue(__CLASS__."templates");
        $this->arrFoldercontent = class_apc_cache::getInstance()->getValue(__CLASS__."foldercontent");
        $this->arrLangfiles = class_apc_cache::getInstance()->getValue(__CLASS__."langfiles");


        if($this->arrTemplates === false || $this->arrFoldercontent === false || $this->arrLangfiles === false) {
            $this->arrTemplates = array();
            $this->arrFoldercontent = array();
            $this->arrLangfiles = array();

            if(is_file($this->strTemplatesCacheFile) && is_file($this->strFoldercontentCacheFile) && is_file($this->strFoldercontentLangFile)) {
                $this->arrTemplates = unserialize(file_get_contents($this->strTemplatesCacheFile));
                $this->arrFoldercontent = unserialize(file_get_contents($this->strFoldercontentCacheFile));
                $this->arrLangfiles = unserialize(file_get_contents($this->strFoldercontentLangFile));
            }

        }

    }

    /**
     * Stores the currently cached entries back to the filesystem - if required.
     */
    public function __destruct() {

        if($this->bitCacheSaveRequired && class_config::getInstance()->getConfig('resourcecaching') == true) {

            class_apc_cache::getInstance()->addValue(__CLASS__."templates", $this->arrTemplates);
            class_apc_cache::getInstance()->addValue(__CLASS__."foldercontent", $this->arrFoldercontent);
            class_apc_cache::getInstance()->addValue(__CLASS__."langfiles", $this->arrLangfiles);

            file_put_contents($this->strTemplatesCacheFile, serialize($this->arrTemplates));
            file_put_contents($this->strFoldercontentCacheFile, serialize($this->arrFoldercontent));
            file_put_contents($this->strFoldercontentLangFile, serialize($this->arrLangfiles));
        }
    }

    /**
     * Deletes all cached resource-information,
     * so the .cache-files.
     */
    public function flushCache() {
        $objFilesystem = new class_filesystem();
        $objFilesystem->fileDelete($this->strTemplatesCacheFile);
        $objFilesystem->fileDelete($this->strFoldercontentCacheFile);
        $objFilesystem->fileDelete($this->strFoldercontentLangFile);
        $this->arrModules = class_classloader::getInstance()->getArrModules();
        $this->arrFoldercontent = array();
        $this->arrTemplates = array();
        $this->arrLangfiles = array();
    }

    /**
     * Looks up the real filename of a template passed.
     * The filename is the relative path, so adding /templates/[packname] is not required and not allowed.
     *
     * @param string $strTemplateName
     * @param bool $bitScanAdminSkin
     *
     * @throws class_exception
     * @return string The path on the filesystem, relative to the root-folder. Null if the file could not be mapped.
     */
    public function getTemplate($strTemplateName, $bitScanAdminSkin = false) {
        $strTemplateName = removeDirectoryTraversals($strTemplateName);
        if(isset($this->arrTemplates[$strTemplateName]))
            return $this->arrTemplates[$strTemplateName];

        $this->bitCacheSaveRequired = true;

        $strFilename = null;
        //first try: load the file in the current template-pack
        if(is_file(_realpath_._templatepath_."/"._packagemanager_defaulttemplate_."/tpl".$strTemplateName)) {
            $this->arrTemplates[$strTemplateName] = _templatepath_."/"._packagemanager_defaulttemplate_."/tpl".$strTemplateName;
            return _templatepath_."/"._packagemanager_defaulttemplate_."/tpl".$strTemplateName;
        }

        //second try: load the file from the default-pack
        if(is_file(_realpath_._templatepath_."/default/tpl".$strTemplateName)) {
            $this->arrTemplates[$strTemplateName] = _templatepath_."/default/tpl".$strTemplateName;
            return _templatepath_."/default/tpl".$strTemplateName;
        }

        //third try: try to load the file from a given module
        foreach($this->arrModules as $strOneModule) {
            if(is_file(_corepath_."/".$strOneModule."/templates/default/tpl".$strTemplateName)) {
                $strFilename = "/core/".$strOneModule."/templates/default/tpl".$strTemplateName;
                break;
            }
        }

        if($bitScanAdminSkin) {
            if(is_file(_realpath_.class_adminskin_helper::getPathForSkin(class_session::getInstance()->getAdminSkin()).$strTemplateName))
                $strFilename = class_adminskin_helper::getPathForSkin(class_session::getInstance()->getAdminSkin()).$strTemplateName;
        }

        if($strFilename === null)
            throw new class_exception("Required file ".$strTemplateName." could not be mapped on the filesystem.", class_exception::$level_ERROR);

        $this->arrTemplates[$strTemplateName] = $strFilename;

        return $strFilename;
    }


    /**
     * Looks up the real filename of a template passed.
     * The filename is the relative path, so adding /templates/[packname] is not required and not allowed.
     *
     * @param string $strFolder
     * @return array A list of templates, so the merged result of the current template-pack + default-pack + fallback-files
     */
    public function getTemplatesInFolder($strFolder) {

        $arrReturn = array();

        //first try: load the file in the current template-pack
        if(is_dir(_realpath_._templatepath_."/"._packagemanager_defaulttemplate_."/tpl".$strFolder)) {
            $arrFiles = scandir(_realpath_._templatepath_."/"._packagemanager_defaulttemplate_."/tpl".$strFolder);
            foreach($arrFiles as $strOneFile)
                if(substr($strOneFile, -4) == ".tpl")
                    $arrReturn[] = $strOneFile;
        }

        //second try: load the file from the default-pack
        if(is_dir(_realpath_._templatepath_."/default/tpl".$strFolder)) {
            $arrFiles = scandir(_realpath_._templatepath_."/default/tpl".$strFolder);
            foreach($arrFiles as $strOneFile)
                if(substr($strOneFile, -4) == ".tpl")
                    $arrReturn[] = $strOneFile;
        }

        //third try: try to load the file from given modules
        foreach($this->arrModules as $strOneModule) {
            if(is_dir(_corepath_."/".$strOneModule."/templates/default/tpl".$strFolder)) {
                $arrFiles = scandir(_corepath_."/".$strOneModule."/templates/default/tpl".$strFolder);
                foreach($arrFiles as $strOneFile)
                    if(substr($strOneFile, -4) == ".tpl")
                        $arrReturn[] = $strOneFile;
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
     * @param $strFolder
     * @return array
     */
    public function getLanguageFiles($strFolder) {
        $arrReturn = array();

        if(isset($this->arrLangfiles[$strFolder]))
            return $this->arrLangfiles[$strFolder];

        $this->bitCacheSaveRequired = true;

        //loop all given modules
        foreach($this->arrModules as $strSingleModule) {
            if(is_dir(_corepath_."/".$strSingleModule._langpath_."/".$strFolder)) {
                $arrContent = scandir(_corepath_."/".$strSingleModule._langpath_."/".$strFolder);
                foreach($arrContent as $strSingleEntry) {

                    if(substr($strSingleEntry, -4) == ".php") {
                        $arrReturn["/core/".$strSingleModule._langpath_."/".$strFolder."/".$strSingleEntry] = $strSingleEntry;
                    }
                }
            }
        }

        //check if the same is available in the projects-folder
        if(is_dir(_realpath_._projectpath_._langpath_."/".$strFolder)) {
            $arrContent = scandir(_realpath_._projectpath_._langpath_."/".$strFolder);
            foreach($arrContent as $strSingleEntry) {

                if(substr($strSingleEntry, -4) == ".php") {

                    $strKey = array_search($strSingleEntry, $arrReturn);
                    if($strKey !== false) {
                        unset($arrReturn[$strKey]);
                        $arrReturn[_projectpath_._langpath_."/".$strFolder."/".$strSingleEntry] = $strSingleEntry;
                    }

                }

            }
        }

        $this->arrLangfiles[$strFolder] = $arrReturn;

        return $arrReturn;
    }

    /**
     * Loads all files in a passed folder, as usual relative to the core whereas the single module-folders may be skipped.
     * The array returned is based on [path_to_file] = [filename] where the key is relative to the project-root.
     *
     * If you want to filter the list of files being returned, pass a callback/closure as the 4th argument. The callback is used
     * as defined in array_filter
     *
     * @param $strFolder
     * @param array $arrExtensionFilter
     * @param bool $bitWithSubfolders
     * @param callable $objFilterFunction
     *
     * @return array (path => filename)
     *
     * @see array_filter
     */
    public function getFolderContent($strFolder, $arrExtensionFilter = array(), $bitWithSubfolders = false, $objFilterFunction = null) {
        $arrReturn = array();
        $strCachename = md5($strFolder.implode(",", $arrExtensionFilter).($bitWithSubfolders ? "sub" : "nosub"));

        if(isset($this->arrFoldercontent[$strCachename])) {
            return $this->applyCallback($this->arrFoldercontent[$strCachename], $objFilterFunction);
        }

        $this->bitCacheSaveRequired = true;

        //loop all given modules
        foreach($this->arrModules as $strSingleModule) {
            if(is_dir(_corepath_."/".$strSingleModule.$strFolder)) {
                $arrContent = scandir(_corepath_."/".$strSingleModule.$strFolder);
                foreach($arrContent as $strSingleEntry) {

                    if(($strSingleEntry != "." && $strSingleEntry != "..") && ($bitWithSubfolders || is_file(_corepath_."/".$strSingleModule.$strFolder."/".$strSingleEntry))) {
                        //Wanted Type?
                        if(count($arrExtensionFilter)==0) {
                            $arrReturn["/core/".$strSingleModule.$strFolder."/".$strSingleEntry] = $strSingleEntry;
                        }
                        else {
                            //check, if suffix is in allowed list
                            $strFileSuffix = uniSubstr($strSingleEntry, uniStrrpos($strSingleEntry, "."));
                            if(in_array($strFileSuffix, $arrExtensionFilter)) {
                                $arrReturn["/core/".$strSingleModule.$strFolder."/".$strSingleEntry] = $strSingleEntry;
                            }
                        }
                    }

                }
            }
        }


        //check if the same is available in the projects-folder and overwrite the first hits
        if(is_dir(_realpath_._projectpath_."/".$strFolder)) {
            $arrContent = scandir(_realpath_._projectpath_."/".$strFolder);
            foreach($arrContent as $strSingleEntry) {

                //Wanted Type?
                if(count($arrExtensionFilter)==0) {

                    $strKey = array_search($strSingleEntry, $arrReturn);
                    if($strKey !== false) {
                        unset($arrReturn[$strKey]);
                    }
                    $arrReturn[_projectpath_."/".$strFolder."/".$strSingleEntry] = $strSingleEntry;

                }
                else {
                    //check, if suffix is in allowed list
                    $strFileSuffix = uniSubstr($strSingleEntry, uniStrrpos($strSingleEntry, "."));
                    if(in_array($strFileSuffix, $arrExtensionFilter)) {
                        $strKey = array_search($strSingleEntry, $arrReturn);
                        if($strKey !== false) {
                            unset($arrReturn[$strKey]);
                        }
                        $arrReturn[_projectpath_."/".$strFolder."/".$strSingleEntry] = $strSingleEntry;
                    }

                }

            }
        }


        $this->arrFoldercontent[$strCachename] = $arrReturn;
        return $this->applyCallback($arrReturn, $objFilterFunction);
    }

    /**
     * Internal helper to apply the passed callback as an array_walk callback to the list of matching files
     * @param $arrEntries
     * @param callable $objCallback
     *
     * @return array
     */
    private function applyCallback($arrEntries, $objCallback = null) {
        if($objCallback == null || !is_callable($objCallback))
            return $arrEntries;

        $arrTemp = array();
        foreach($arrEntries as $strKey => $strValue)
            $arrTemp[$strKey] = $strValue;

        return array_filter($arrTemp, $objCallback);
    }

    /**
     * Converts a relative path to a real path on the filesystem.
     * If the file can't be found, false is returned instead.
     *
     * @param string $strFile the relative path
     * @param bool $bitCheckProject en- or disables the lookup in the /project folder
     * @return string|bool the absolute path
     */
    public function getPathForFile($strFile, $bitCheckProject = true) {

        //check if the same is available in the projects-folder
        if($bitCheckProject && is_file(_realpath_._projectpath_."/".$strFile)) {
            return str_replace("//", "/", _projectpath_."/".$strFile);
        }

        //loop all given modules
        foreach($this->arrModules as $strSingleModule) {
            if(is_file(_corepath_."/".$strSingleModule."/".$strFile)) {
                return str_replace("//", "/", "/core/".$strSingleModule."/".$strFile);
            }
        }

        return false;
    }


    /**
     * Converts a relative path to a real path on the filesystem.
     * If the file can't be found, false is returned instead.
     *
     * @param string $strFolder the relative path
     * @param bool $bitCheckProject en- or disables the lookup in the /project folder
     * @return string|bool the absolute path
     *
     * @todo may be cached?
     */
    public function getPathForFolder($strFolder, $bitCheckProject = true) {

        //check if the same is available in the projects-folder
        if($bitCheckProject && is_dir(_realpath_._projectpath_."/".$strFolder)) {
            return str_replace("//", "/", _projectpath_."/".$strFolder);
        }

        //loop all given modules
        foreach($this->arrModules as $strSingleModule) {
            if(is_dir(_corepath_."/".$strSingleModule."/".$strFolder)) {
                return str_replace("//", "/", "/core/".$strSingleModule."/".$strFolder);

            }
        }

        return false;
    }

    /**
     * Returns the list of modules and elements under the /core folder
     * @return array
     */
    public function getArrModules() {
        return $this->arrModules;
    }


}
