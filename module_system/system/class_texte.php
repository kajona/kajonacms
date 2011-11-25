<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2011 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                              *
********************************************************************************************************/


/**
 * Class managing access to textfiles
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_texte {

	/**
	 * This is the default language.
	 *
	 * @var string
	 */
	private $strLanguage = "";

    /**
     * Identifier of the fallback-language, taken into account if loading an entry using the current language failed.
     *
     * @var string
     */
	private $strFallbackLanguage = "en";

    /**
     * The commons-name indicates the fake-module-name of lang-files bundled for common usage (in order to
     * reduce duplicate lang-entries).
     *
     * @var type
     */
    private $strCommonsName = "commons";
	private $arrTexts;

    /**
     * Used to keep placeholders loaded from the fallback lang-file.
     * Only used, if the file itself exists in the target-language but misses a placeholder!
     * @var array
     */
    private $arrFallbackTextEntrys = array();


	private static $objText = null;

	/**
	 * Constructor, singleton
	 *
	 */
	private function __construct() 	{

        //load texts from session

        if(class_config::getInstance()->getConfig("cache_texts") === true) {
            $this->arrTexts = class_session::getInstance()->getSession("textSessionCache");
            if($this->arrTexts === false)
                $this->arrTexts = array();

            $this->arrFallbackTextEntrys = class_session::getInstance()->getSession("textSessionFallbackCache");
            if($this->arrFallbackTextEntrys === false)
                $this->arrFallbackTextEntrys = array();
        }
	}

    public function __destruct() {
    	//save texts to session
        if(class_config::getInstance()->getConfig("cache_texts") === true) {
            class_session::getInstance()->setSession("textSessionCache", $this->arrTexts);
            class_session::getInstance()->setSession("textSessionFallbackCache", $this->arrFallbackTextEntrys);
        }
    }

	/**
	 * Singleton
	 *
	 */
	private function __clone() {

	}

	/**
	 * Returning an instance of class_texte
	 *
	 * @return class_texte
	 */
	public static function getInstance() {
		if(self::$objText == null) {
			self::$objText = new class_texte();
		}

		return self::$objText;
	}

    /**
     * Returning the searched text
     *
     * @param string $strText
     * @param $strModule
     * @param string $strArea
     *
     * @return string
     */
	public function getText($strText, $strModule, $strArea) {
		$strReturn = "";

		//Did we already load this text?
		if(!isset($this->arrTexts[$strArea.$this->strLanguage][$strModule]))
			$this->loadText($strModule, $strArea);

		//Searching for the text
		if(isset($this->arrTexts[$strArea.$this->strLanguage][$strModule][$strText])) {
			$strReturn = $this->arrTexts[$strArea.$this->strLanguage][$strModule][$strText];
		}
		else {

            //try to load the entry in the commons-list
            if(!isset($this->arrTexts[$strArea.$this->strLanguage][$this->strCommonsName]))
                $this->loadText($this->strCommonsName, $strArea);

            if(isset($this->arrTexts[$strArea.$this->strLanguage][$this->strCommonsName][$strText])) {
                $strReturn = $this->arrTexts[$strArea.$this->strLanguage][$this->strCommonsName][$strText];
            }
            else {

                //Try to find the text using the fallback language
                $strReturn = $this->loadFallbackPlaceholder($strModule, $strArea, $strText);
            }
		}

		return $strReturn;
	}


    private function loadFallbackPlaceholder($strModule, $strArea, $strText) {
        $strReturn = "";

        if(isset($this->arrTexts[$strArea.$this->strFallbackLanguage][$strModule][$strText])) {
            $strReturn = $this->arrTexts[$strArea.$this->strFallbackLanguage][$strModule][$strText];
        }
        else {
            //try to load the fallback-files
            $objFilesystem = new class_filesystem();
            //load files
            $arrFiles = $objFilesystem->getFilelist(_langpath_."/".$strArea."/modul_".$strModule);
            if(is_array($arrFiles)) {
                foreach($arrFiles as $strFile) {
                    $lang = array();
                    $strTemp = str_replace(".php", "", $strFile);
                    $arrName = explode("_", $strTemp);

                    if($arrName[0] == "lang" && $arrName[2] == $this->strFallbackLanguage) {
                        $bitFileMatched = true;
                        $this->loadAndMergeTextfile($strArea, $strModule, $strFile, $this->strFallbackLanguage, $this->arrFallbackTextEntrys);

                    }
                }
            }
            if(isset($this->arrFallbackTextEntrys[$strArea.$this->strFallbackLanguage][$strModule][$strText])) {
                $strReturn = $this->arrFallbackTextEntrys[$strArea.$this->strFallbackLanguage][$strModule][$strText];
            }
            else
                $strReturn = "!".$strText."!";
        }

        return $strReturn;
    }


    /**
     * Loading texts from textfiles
     *
     * @param string $strModule
     * @param $strArea
     * @return void
     */
	private function loadText($strModule, $strArea) {

	    $bitFileMatched = false;
		$objFilesystem = new class_filesystem();

		//load files
		$arrFiles = $objFilesystem->getFilelist(_langpath_."/".$strArea."/modul_".$strModule);

        $arrFiles = class_resourceloader::getInstance()->getLanguageFiles("modul_".$strModule, $strArea);

		if(is_array($arrFiles)) {
			foreach($arrFiles as $strPath => $strFilename) {
				$lang = array();
				$strTemp = str_replace(".php", "", $strFilename);
			 	$arrName = explode("_", $strTemp);


			 	if($arrName[0] == "lang" && $arrName[2] == $this->strLanguage && $this->strLanguage != "") {
			 	    $bitFileMatched = true;
                    $this->loadAndMergeTextfile($strArea, $strModule, $strPath, $this->strLanguage, $this->arrTexts);

			 	}
			}
			if($bitFileMatched)
		        return true;

			//if we reach up here, no matching file was found. search for fallback file (fallback language)
			foreach($arrFiles as $strFilename) {
				$lang = array();
				$strTemp = str_replace(".php", "", $strFilename);
			 	$arrName = explode("_", $strTemp);

			 	if($arrName[0] == "lang" && $arrName[2] == $this->strFallbackLanguage) {
                    $this->loadAndMergeTextfile($strArea, $strModule, $strPath, $this->strFallbackLanguage, $this->arrTexts);

			 	}
			}
		}
	}

    /**
     * Includes the file from the filesystem and merges the contents to the passed array.
     * NOTE: this array is used as a reference!!!
     * @param string $strArea
     * @param string $strModule
     * @param string $strFilename
     * @param string $strLanguage
     * @param array $arrTargetArray
     */
    private function loadAndMergeTextfile($strArea, $strModule, $strFilename, $strLanguage, &$arrTargetArray) {
        $lang = array();
        include_once(_realpath_.$strFilename);

        if(!isset($arrTargetArray[$strArea.$strLanguage]))
            $arrTargetArray[$strArea.$strLanguage] = array();

        if(isset($arrTargetArray[$strArea.$strLanguage][$strModule]))
            $arrTargetArray[$strArea.$strLanguage][$strModule] = array_merge($arrTargetArray[$strArea.$strLanguage][$strModule], $lang);
        else
            $arrTargetArray[$strArea.$strLanguage][$strModule] = $lang;
    }


	/**
	 * Sets the language to load textfiles
	 *
	 * @param string $strLanguage
	 */
	public function setStrTextLanguage($strLanguage) {
	    if($strLanguage == "")
	        return;

	    $this->strLanguage = $strLanguage;
	}

	/**
	 * Gets the current language set to the class_texte
	 *
	 * @return string
	 */
	public function getStrTextLanguage() {
		return $this->strLanguage;
	}

}
?>