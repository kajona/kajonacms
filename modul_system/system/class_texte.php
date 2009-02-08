<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2009 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                              *
********************************************************************************************************/


/**
 * Class managing access to textfiles
 *
 * @package modul_system
 */
class class_texte {
	private $arrModul;

	/**
	 * This is the default language. 
	 *
	 * @var string
	 */
	private $strLanguage = "";

	private $strFallbackLanguage = "en";
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
		$this->arrModul["t_name"] 		= "class_texte";
		$this->arrModul["t_author"]		= "sidler@mulchprod.de";
		$this->arrModul["t_nummer"]		= _system_modul_id_;
		
		$this->strLanguage = class_carrier::getInstance()->getObjConfig()->getConfig("portallanguage");
        
        //load texts from session
        //TODO: reneable before release
        //$this->arrTexts = class_session::getInstance()->getSession("textSessionCache");
        if($this->arrTexts === false)
            $this->arrTexts = array();
	}
    
    public function __destruct() {
    	//save texts to session
        class_session::getInstance()->setSession("textSessionCache", $this->arrTexts);
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
	 * @param strings $strModul
	 * @param string $strArea
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
            //Try to find the text using the fallback language
            $strReturn = $this->loadFallbackPlaceholder($strModule, $strArea, $strText);
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
            include_once(_systempath_."/class_filesystem.php");
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
	 * @return void
	 */
	private function loadText($strModule, $strArea) {

	    $bitFileMatched = false;
		include_once(_systempath_."/class_filesystem.php");
		$objFilesystem = new class_filesystem();

		//load files
		$arrFiles = $objFilesystem->getFilelist(_langpath_."/".$strArea."/modul_".$strModule);
		if(is_array($arrFiles)) {
			foreach($arrFiles as $strFile) {
				$lang = array();
				$strTemp = str_replace(".php", "", $strFile);
			 	$arrName = explode("_", $strTemp);


			 	if($arrName[0] == "lang" && $arrName[2] == $this->strLanguage && $this->strLanguage != "") {
			 	    $bitFileMatched = true;
                    $this->loadAndMergeTextfile($strArea, $strModule, $strFile, $this->strLanguage, $this->arrTexts);
                    
			 	}
			}
			if($bitFileMatched)
		        return true;

			//if we reach up here, no matching file was found. search for fallback file (fallback language)
			foreach($arrFiles as $strFile) {
				$lang = array();
				$strTemp = str_replace(".php", "", $strFile);
			 	$arrName = explode("_", $strTemp);

			 	if($arrName[0] == "lang" && $arrName[2] == $this->strFallbackLanguage) {
                    $this->loadAndMergeTextfile($strArea, $strModule, $strFile, $this->strFallbackLanguage, $this->arrTexts);
                    
			 	}
			}
		}
	}

    /**
     * Includes the file from the filesystem and merges the contents to the passed array.
     * NOTE: this array is used as a reference!!!
     * @param string $strArea
     * @param string $strModule
     * @param string $strFile
     * @param string $strLanguage
     * @param array $arrTargetArray
     */
    private function loadAndMergeTextfile($strArea, $strModule, $strFile, $strLanguage, &$arrTargetArray) {
        $lang = array();
        include_once(_langpath_."/".$strArea."/modul_".$strModule."/".$strFile);

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

} //class_texte()
?>