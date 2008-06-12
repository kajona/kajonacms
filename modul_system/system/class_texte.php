<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_texte.php																						*
* 	Loading textfiles																					*
*																										*
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
	 * This is the default language. Set to other languages as you like
	 *
	 * @var string
	 */
	private $strLanguage = "";

	private $strFallbackLanguage = "de";
	private $arrTexts;

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
		$arrFiles = $objFilesystem->getFilelist(_textpath_."/".$strArea."/modul_".$strModule);
		if(is_array($arrFiles)) {
			foreach($arrFiles as $strFile) {
				$text = array();
				$strTemp = str_replace(".php", "", $strFile);
			 	$arrName = explode("_", $strTemp);


			 	if($arrName[0] == "texte" && $arrName[2] == $this->strLanguage && $this->strLanguage != "") {
			 	    $bitFileMatched = true;
			 		include_once(_textpath_."/".$strArea."/modul_".$strModule."/".$strFile);
			 		
			 		if(!isset($this->arrTexts[$strArea.$this->strLanguage]))
			 		    $this->arrTexts[$strArea.$this->strLanguage] = array();
                    
			 		if(isset($this->arrTexts[$strArea.$this->strLanguage][$strModule]))
			 			$this->arrTexts[$strArea.$this->strLanguage][$strModule] = array_merge($this->arrTexts[$strArea.$this->strLanguage][$strModule], $text);
			 		else
			 			$this->arrTexts[$strArea.$this->strLanguage][$strModule] = $text;
                    
			 	}
			}
			if($bitFileMatched)
		        return true;

			//if we reach up here, no matching file was found. search for fallback file
			foreach($arrFiles as $strFile) {
				$text = array();
				$strTemp = str_replace(".php", "", $strFile);
			 	$arrName = explode("_", $strTemp);

			 	if($arrName[0] == "texte" && $arrName[2] == $this->strFallbackLanguage) {
			 		include_once(_textpath_."/".$strArea."/modul_".$strModule."/".$strFile);
                    
			 		if(!isset($this->arrTexts[$strArea.$this->strLanguage]))
                        $this->arrTexts[$strArea.$this->strLanguage] = array();
                        
			 		if(isset($this->arrTexts[$strArea.$this->strLanguage][$strModule]))
			 			$this->arrTexts[$strArea.$this->strLanguage][$strModule] = array_merge($this->arrTexts[$strArea.$this->strLanguage][$strModule], $text);
			 		else
			 			$this->arrTexts[$strArea.$this->strLanguage][$strModule] = $text;

			 	}
			}
		}
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