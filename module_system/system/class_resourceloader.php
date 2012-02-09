<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: class_carrier.php 4059 2011-08-09 14:52:41Z sidler $                                            *
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

    /**
     * @var class_resourceloader
     */
    private static $objInstance = null;

    private $arrModules = array();

    //FIXME: caches may be moved to the session/apc. validate if this makes sense. (invalidation, size, ...)
    private $arrTemplates = array();
    private $arrFoldercontent = array();


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
        $this->arrModules = scandir(_corepath_);

        $this->arrModules = array_filter($this->arrModules, function($strValue) {
            return preg_match("/(module|element|_)+.*/i", $strValue);
        });

    }

    private function __clone() {
    }


    /**
     * Looks up the real filename of a template passed.
     * The filename is the relative path, so adding /templates/[packname] is not required and not allowed.
     *
     * @param string $strTemplateName
     * @return string The path on the filesystem, relative to the root-folder. Null if the file could not be mapped.
     * @throws class_exception in case the filename could not be mapped
     */
    public function getTemplate($strTemplateName) {
        $strTemplateName = removeDirectoryTraversals($strTemplateName);
        if(isset($this->arrTemplates[$strTemplateName]))
            return $this->arrTemplates[$strTemplateName];

        $strFilename = null;
        //first try: load the file in the current template-pack
        //FIXME add current pack


        //second try: load the file from the default-pack
        if(is_file(_realpath_._templatepath_."/default/tpl".$strTemplateName)) {
            return _templatepath_."/default/tpl".$strTemplateName;
        }

        //third try: try to load the file from a given module
        foreach($this->arrModules as $strOneModule) {
            if(is_file(_corepath_."/".$strOneModule."/templates/default/tpl".$strTemplateName)) {
                $strFilename = "/core/".$strOneModule."/templates/default/tpl".$strTemplateName;
                break;
            }

        }



        if($strFilename === null)
            throw new class_exception("Required file ".$strTemplateName." could not be mapped on the filesystem.", class_exception::$level_ERROR);

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


        //second try: load the file from the default-pack
        if(is_dir(_corepath_._templatepath_."/default/tpl".$strFolder)) {
            $arrFiles = scandir(_corepath_._templatepath_."/default/tpl".$strFolder);
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

                if(substr($strSingleEntry, 0, -4) == ".php") {

                    $strKey = array_search($strSingleEntry, $arrReturn);
                    if($strKey !== false) {
                        unset($arrReturn[$strKey]);
                        $arrReturn[_projectpath_._langpath_."/".$strFolder."/".$strSingleEntry] = $strSingleEntry;
                    }

                }

            }
        }

        return $arrReturn;
    }

    /**
     * Loads all files in a passed folder, as usual relative to the core whereas the single module-folders may be skipped.
     * The array returned is based on [path_to_file] = [filename] where the key is relative to the project-root.
     *
     * @param $strFolder
     * @param array $arrExtensionFilter
     * @param bool $bitWithSubfolders
     * @return array
     */
    public function getFolderContent($strFolder, $arrExtensionFilter = array(), $bitWithSubfolders = false) {
        $arrReturn = array();
        $strCachename = md5($strFolder.implode(",", $arrExtensionFilter));

        if(isset($this->arrFoldercontent[$strCachename]))
            return $this->arrFoldercontent[$strCachename];

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
                        $arrReturn[_projectpath_."/".$strFolder."/".$strSingleEntry] = $strSingleEntry;
                    }

                }
                else {
                    //check, if suffix is in allowed list
                    $strFileSuffix = uniSubstr($strSingleEntry, uniStrrpos($strSingleEntry, "."));
                    if(in_array($strFileSuffix, $arrExtensionFilter)) {
                        $strKey = array_search($strSingleEntry, $arrReturn);
                        if($strKey !== false) {
                            unset($arrReturn[$strKey]);
                            $arrReturn[_projectpath_."/".$strFolder."/".$strSingleEntry] = $strSingleEntry;
                        }
                    }
                }

            }
        }


        $this->arrFoldercontent[$strCachename] = $arrReturn;

        return $arrReturn;
    }


    /**
     * Converts a relative path to a real path on the filesystem.
     * If the file can't be found, false is returned instead.
     *
     * @param string $strFile the relative path
     * @param bool $bitCheckProject en- or disables the lookup in the /project folder
     * @return string|false the absolute path
     */
    public function getPathForFile($strFile, $bitCheckProject = true) {
        $strReturn = array();

        //check if the same is available in the projects-folder
        if($bitCheckProject && is_file(_realpath_._projectpath_."/".$strFile)) {
            return _projectpath_."/".$strFile;
        }

        //loop all given modules
        foreach($this->arrModules as $strSingleModule) {
            if(is_file(_corepath_."/".$strSingleModule."/".$strFile)) {
                return "/core/".$strSingleModule."/".$strFile;

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
     * @return string|false the absolute path
     */
    public function getPathForFolder($strFolder, $bitCheckProject = true) {
        $strReturn = array();

        //check if the same is available in the projects-folder
        if($bitCheckProject && is_dir(_realpath_._projectpath_."/".$strFolder)) {
            return _projectpath_."/".$strFolder;
        }

        //loop all given modules
        foreach($this->arrModules as $strSingleModule) {
            if(is_dir(_corepath_."/".$strSingleModule."/".$strFolder)) {
                return "/core/".$strSingleModule."/".$strFolder;

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
