<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2008 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
* 																										*
* 	class_template.php																					*
* 	Handles all the things to do with templates															*
*																										*
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                           *
********************************************************************************************************/
/**
 * This class does all the template stuff as loading, parsing, etc..
 * Since 2.1.0.0 using a new templatecache
 *
 * @package modul_system
 */
class class_template {
	private $arrModul = array();
	private $arrCacheTemplates = array();
	private $arrCacheTemplateSections = array();
	private $strArea;

	private $strTempTemplate = "";

	private static $objTemplate = null;

	/**
	 * Constructor
	 *
	 */
	private function __construct() 	{
		$this->arrModul["name"] 		= "template";
		$this->arrModul["author"] 		= "sidler@mulchprod.de";

		$this->strArea = "";
	}


	/**
	 * Returns one Instance of the Template-Object, using a singleton pattern
	 *
	 * @return object The Template-Object
	 */
	public static function getInstance() {
		if(self::$objTemplate == null) {
			self::$objTemplate = new class_template();
		}

		return self::$objTemplate;
	}

	/**
	 * Reads a Template from the filesystem
	 *
	 * @param string $StrName
	 * @param string $strSection
	 * @param bool $bitForce Force the passed template name, not adding the current area
	 * @param bool $bitThrowErrors If set true, the method throws exceptions in case of errors
	 * @return string The identifier for further actions
	 * @throws class_exception
	 */
	public function readTemplate($strName, $strSection = "", $bitForce = false, $bitThrowErrors = false) {
		//Adding the current areaprefix
		if(!$bitForce)
			$strName = $this->strArea . $strName;
		$strTemplate = "Template not found";
		$bitKnownTemplate = false;
		//Is this template already in the cache?
		$strCacheTemplate = md5($strName);
		$strCacheSection = md5($strName.$strSection);

		if(isset($this->arrCacheTemplateSections[$strCacheSection]))
			return $strCacheSection;

		if(isset($this->arrCacheTemplates[$strCacheTemplate]))
			$bitKnownTemplate = true;

		if(!$bitKnownTemplate) {
			//Build the path-prefixes
			if($bitForce)
				$strTemplatePath = _realpath_;
			elseif (uniStrpos($this->strArea, "admin/skins/") !== false)
				$strTemplatePath = _realpath_;
			else
				$strTemplatePath = _templatepath_;

			//We have to read the whole Template from the filesystem
			if(file_exists($strTemplatePath."/".$strName) && is_file($strTemplatePath."/".$strName)) {
				$strTemplate = file_get_contents($strTemplatePath."/".$strName);
				//Saving to the cache
				$this->arrCacheTemplates[$strCacheTemplate] = $strTemplate;
			}
			else {
			    $strTemplate = "Template ".$strTemplatePath."/".$strName ." not found!";
			    if($bitThrowErrors)
			        throw new class_exception("Template ".$strTemplatePath."/".$strName ." not found!", class_exception::$level_FATALERROR);
			}
		}
		else
			$strTemplate = $this->arrCacheTemplates[$strCacheTemplate];

		//Now we have to extract the section
		if($strSection != "") {
		    //find opening tag
			$intStart = uniStrpos($strTemplate, "<".$strSection.">");

			//find closing tag
			$intEnd = uniStrpos($strTemplate, "</".$strSection.">");
			$intEnd = $intEnd-$intStart;

            if($intStart !== false && $intEnd !== false) {
    			//delete substring before and after
    			$strTemplate = uniSubstr($strTemplate, $intStart, $intEnd);

    			$strTemplate = str_replace("<".$strSection.">", "", $strTemplate);
    			$strTemplate = str_replace("</".$strSection.">", "", $strTemplate);
            }
            else
                $strTemplate = "";
		}

		//Saving the section to the cache
		$this->arrCacheTemplateSections[$strCacheSection] = $strTemplate;

		return $strCacheSection;
	}

	/**
	 * Fills a template with values passed in an array
	 *
	 * @param mixed $arrContent
	 * @param string $strIdentifier
	 * @param bool $bitRemovePlaceholder
	 * @return string The filled Template
	 */
	public function fillTemplate($arrContent, $strIdentifier, $bitRemovePlaceholder = false) {
		if(isset($this->arrCacheTemplateSections[$strIdentifier]))
			$strTemplate = $this->arrCacheTemplateSections[$strIdentifier];
		else
			$strTemplate = "Load template first!";

		if(count($arrContent) >= 1) {
			foreach($arrContent as $strPlaceholder => $strContent) {
				$strTemplate = str_replace("%%".$strPlaceholder."%%", $strContent."%%".$strPlaceholder."%%", $strTemplate);
			}
		}

		if($bitRemovePlaceholder)
		   $strTemplate = $this->deletePlaceholderRaw($strTemplate);
		return $strTemplate;
	}


	/**
	 * Fills the current temp-template with the passed values.<br /><b>Make sure to have the wanted template loaded before by using setTemplate()</b>
	 *
	 * @param mixed $arrContent
	 * @return string The filled Template
	 */
	public function fillCurrentTemplate($arrContent) {
		if(count($arrContent) >= 1) {
			foreach($arrContent as $strPlaceholder => $strContent) {
				$strTemplate = str_replace("%%".$strPlaceholder."%%", $strContent."%%".$strPlaceholder."%%", $this->strTempTemplate);
			}
		}
		return $strTemplate;
	}


	/**
	 * Replaces Constants in the Template set by setTemplate()
	 *
	 */
	public function fillConstants() {
		$arrConstants 	= array(  	0 => "_indexpath_",
							 		1 => "_webpath_",
							 		2 => "_gentime_");
		$arrValues		= array(  	0 => _indexpath_,
		                      		1 => _webpath_,
		                      		2 => date("d.m.y H:i" , time()));
		if(defined("_skinwebpath_")) {
			$arrConstants[] = "_skinwebpath_";
			$arrValues[] = _skinwebpath_;
		}

		$this->strTempTemplate = str_replace($arrConstants, $arrValues, $this->strTempTemplate);
	}

	/**
	 * Deletes Placholder in the Template set by setTemplate()
	 *
	 * @param string $strText
	 */
	public function deletePlaceholder() {
		$this->strTempTemplate =  preg_replace("^%%([A-Zaeoeuea-zaeoeue0-9_\|]*)%%^", "", $this->strTempTemplate);
	}

	/**
	 * Deletes Placholder in the String
	 *
	 * @param string $strText
	 */
	private function deletePlaceholderRaw($strText) {
		return preg_replace("^%%([A-Zaeoeuea-zaeoeue0-9_\|]*)%%^", "", $strText);
	}

	/**
	 * Returns the template set by setTemplate() and sets its back to ""
	 *
	 * @return string
	 */
	public function getTemplate() {
		$strTemp = $this->strTempTemplate;
		$this->strTempTemplate = "";
		return $strTemp;
	}


	/**
	 * Returns the elements in a given template
	 *
	 * @param string $strIdentifier
	 * @param int $intMode 0 = regular page, 1 = master page
	 * @return mixed
	 */
	public function getElements($strIndetifier, $intMode = 0) {
		$arrReturn = array();

		if(isset($this->arrCacheTemplateSections[$strIndetifier]))
			$strTemplate = $this->arrCacheTemplateSections[$strIndetifier];
		else
			return array();

		//Platzhalter suchen
		$arrTemp = array();
		preg_match_all("'(%%([A-Zaeoeuea-zaeoeue0-9_]+?))+?\_([A-Zaeoeuea-zaeoeue0-9_\|]+?)%%'i", $strTemplate, $arrTemp);

		//Aufbereiten der Platzhalter
		$intCounter = 0;
		if(count($arrTemp[0]) > 0) {
			foreach($arrTemp[0] as $strPlacehoder) {
				//regular page
				if($intMode != 1) {
					if(uniStrpos($strPlacehoder, "master") === false) {
						$strTemp = uniSubstr($strPlacehoder, 2, -2);
						$arrTemp = explode("_", $strTemp);
						//are there any pipes?
                        if(uniStrpos($arrTemp[1], "|") !== false) {
                            $arrElementTypes = explode("|", $arrTemp[1]);
                            $intCount2 = 0;
                            $arrReturn[$intCounter]["placeholder"] = $strTemp;

                            foreach ($arrElementTypes as $strOneElementType) {
            					$arrReturn[$intCounter]["elementlist"][$intCount2]["name"] = $arrTemp[0];
            					$arrReturn[$intCounter]["elementlist"][$intCount2]["element"] = $strOneElementType;
            					$intCount2++;
                            }
                            $intCounter++;
                        }
                        else {
        					$arrReturn[$intCounter]["placeholder"] = $strTemp;
        					$arrReturn[$intCounter]["elementlist"][0]["name"] = $arrTemp[0];
        					$arrReturn[$intCounter]["elementlist"][0]["element"] = $arrTemp[1];
        					$intCounter++;
                        }
					}
				}
				//master page
				else {
					$strTemp = uniSubstr($strPlacehoder, 2, -2);
					$arrTemp = explode("_", $strTemp);
                    //are there any pipes?
                    if(uniStrpos($arrTemp[1], "|") !== false) {
                        $arrElementTypes = explode("|", $arrTemp[1]);
                        $arrReturn[$intCounter]["placeholder"] = $strTemp;
                        $intCount2 = 0;
                        foreach ($arrElementTypes as $strOneElementType) {
        					$arrReturn[$intCounter]["elementlist"][$intCount2]["name"] = $arrTemp[0];
        					$arrReturn[$intCounter]["elementlist"][$intCount2]["element"] = $strOneElementType;
                            $intCount2++;
                        }
                        $intCounter++;
                    }
                    else {
    					$arrReturn[$intCounter]["placeholder"] = $strTemp;
    					$arrReturn[$intCounter]["elementlist"][0]["name"] = $arrTemp[0];
    					$arrReturn[$intCounter]["elementlist"][0]["element"] = $arrTemp[1];
    					$intCounter++;
                    }
				}
			}
		}

		return $arrReturn;
	}

	/**
	 * Sets the passed Template as the current temp-Template
	 *
	 * @param string $strTemplate
	 */
	public function setTemplate($strTemplate) 	{
		$this->strTempTemplate = $strTemplate;
	}

	/**
	 * Sets an Area suchs as portal / admin or admin/style/...
	 *
	 * @param string $strArea
	 */
	public function setArea($strArea) {
	    //when coming from the installer, do nothing, plz. installer uses force-option when loading templates
	    if($strArea == "installer")
	       return;

		//If we are in the admin-area, we have to add the current skin
		if($strArea == "admin") {
			//We need the session-object
			include_once(_systempath_."/class_carrier.php");
			$objCarrier = class_carrier::getInstance();
			$objSession = $objCarrier->getObjSession();
			$strArea .= "/skins/".$objSession->getAdminSkin();
			if(!defined("_skinwebpath_"))
				define("_skinwebpath_", _webpath_."/admin/skins/".$objSession->getAdminSkin());
		}
		$this->strArea = $strArea;
	}

	/**
	 * Returns the number of cached template sections
	 *
	 * @return int
	 */
	public function getNumberCacheSize() {
	    return count($this->arrCacheTemplateSections);
	}
}

?>