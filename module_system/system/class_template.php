<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                           *
********************************************************************************************************/
/**
 * This class does all the template stuff as loading, parsing, etc..
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_template {
	private $arrCacheTemplates = array();
	private $arrCacheTemplateSections = array();

	private $strTempTemplate = "";

	private static $objTemplate = null;

    //enable this cache on high-performance environments.
    //since template-caching can be very memory-consumptive, it is disabled by default.
    private $bitSessionCacheEnabled = false;

	/**
	 * Constructor
	 *
	 */
	private function __construct() 	{


        //any caches to load from session?
        $objSession = class_session::getInstance();
        if($this->bitSessionCacheEnabled) {
            $this->arrCacheTemplates = $objSession->getSession("templateSessionCacheTemplate");
            if($this->arrCacheTemplates === false)
                $this->arrCacheTemplates = array();

            $this->arrCacheTemplateSections = $objSession->getSession("templateSessionCacheTemplateSections");
            if($this->arrCacheTemplateSections === false)
                $this->arrCacheTemplateSections = array();
        }

	}

    public function __destruct() {
    	//save cache to session
        if($this->bitSessionCacheEnabled) {
            class_session::getInstance()->setSession("templateSessionCacheTemplate", $this->arrCacheTemplates);
            class_session::getInstance()->setSession("templateSessionCacheTemplateSections", $this->arrCacheTemplateSections);
        }
    }


	/**
	 * Returns one instance of the template object, using a singleton pattern
	 *
	 * @return object The template object
	 */
	public static function getInstance() {
		if(self::$objTemplate == null) {
			self::$objTemplate = new class_template();
		}

		return self::$objTemplate;
	}

	/**
	 * Reads a template from the filesystem
	 *
	 * @param string $strName
	 * @param string $strSection
	 * @param bool $bitForce Force the passed template name, not adding the current area
	 * @param bool $bitThrowErrors If set true, the method throws exceptions in case of errors
	 * @return string The identifier for further actions
	 * @throws class_exception
	 */
	public function readTemplate($strName, $strSection = "", $bitForce = false, $bitThrowErrors = false) {



		//avoid directory traversals
        $strName = removeDirectoryTraversals($strName);
        if(!$bitForce) {
            try {
                $strName = class_resourceloader::getInstance()->getTemplate($strName);
            }
            catch (class_exception $objEx) {
                if($strName == "/elements.tpl" || _admin_) {
                    //try to resolve the file in the current skin
                    if(is_file(_realpath_.class_adminskin_helper::getPathForSkin(class_session::getInstance()->getAdminSkin()).$strName))
                        $strName = class_adminskin_helper::getPathForSkin(class_session::getInstance()->getAdminSkin()).$strName;
                }
                else
                    throw $objEx;
            }

        }

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
			//We have to read the whole template from the filesystem
            if(uniSubstr($strName, -4) == ".tpl" ) {
				$strTemplate = file_get_contents(_realpath_."/".$strName);
				//Saving to the cache
				$this->arrCacheTemplates[$strCacheTemplate] = $strTemplate;
			}
			else {
			    $strTemplate = "Template ".$strName ." not found!";
			    if($bitThrowErrors)
			        throw new class_exception("Template ".$strName ." not found!", class_exception::$level_FATALERROR);
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
	 * Fills a template with values passed in an array.
     * As an optional parameter an instance of class_lang_wrapper can be passed
     * to fill placeholders matching the schema %%lang_...%% automatically.
	 *
	 * @param mixed $arrContent
	 * @param string $strIdentifier
	 * @param bool $bitRemovePlaceholder
     * @param class_lang_wrapper $objLangWrapper
	 * @return string The filled template
	 */
	public function fillTemplate($arrContent, $strIdentifier, $bitRemovePlaceholder = true, $objLangWrapper = null) {
		if(isset($this->arrCacheTemplateSections[$strIdentifier]))
			$strTemplate = $this->arrCacheTemplateSections[$strIdentifier];
		else
			$strTemplate = "Load template first!";

		if(count($arrContent) >= 1) {
			foreach($arrContent as $strPlaceholder => $strContent) {
				$strTemplate = str_replace("%%".$strPlaceholder."%%", $strContent."%%".$strPlaceholder."%%", $strTemplate);
			}
		}

        //any language-keys to fill?
        if($objLangWrapper != null && $objLangWrapper instanceof class_lang_wrapper) {
            //load placeholders
            $arrTemp = array();
            preg_match_all("'%%lang_([A-Za-z0-9_]*)%%'i", $strTemplate, $arrTemp);

            if(isset($arrTemp[1]) && count($arrTemp[1]) > 0) {
                foreach ($arrTemp[1] as $strStrippedPlaceholders) {
                    $strTemplate = str_replace("%%lang_".$strStrippedPlaceholders."%%", $objLangWrapper->getLang($strStrippedPlaceholders), $strTemplate);
                }
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
     * @param bool $bitRemovePlaceholder
	 * @return string The filled template
	 */
	public function fillCurrentTemplate($arrContent, $bitRemovePlaceholder = true) {
        $strTemplate = $this->strTempTemplate;
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
	 * Replaces constants in the template set by setTemplate()
	 *
	 */
	public function fillConstants() {

        if(!defined("_system_browser_cachebuster_"))
            define("_system_browser_cachebuster_", 0);

		$arrConstants 	= array(  	0 => "_indexpath_",
							 		1 => "_webpath_",
							 		2 => "_system_browser_cachebuster_",
							 		3 => "_gentime_");
		$arrValues		= array(  	0 => _indexpath_,
		                      		1 => _webpath_,
		                      		2 => _system_browser_cachebuster_,
		                      		3 => date("d.m.y H:i" , time()));
		if(defined("_skinwebpath_")) {
			$arrConstants[] = "_skinwebpath_";
			$arrValues[] = _skinwebpath_;
		}

		$this->strTempTemplate = str_replace($arrConstants, $arrValues, $this->strTempTemplate);
	}

    /**
     * Deletes placeholder in the template set by setTemplate()
     */
	public function deletePlaceholder() {
		$this->strTempTemplate = preg_replace("^%%([A-Za-z0-9_\|]*)%%^", "", $this->strTempTemplate);
	}

	/**
	 * Deletes placeholder in the string
	 *
	 * @param string $strText
     * @return string
	 */
	private function deletePlaceholderRaw($strText) {
		return preg_replace("^%%([A-Za-z0-9_\|]*)%%^", "", $strText);
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
     * Checks if the template referenced by the identifier contains the placeholder provided
     * by the second param.
     *
     * @param string $strIdentifier
     * @param string $strPlaceholdername
     * @return bool
     */
    public function containesPlaceholder($strIdentifier, $strPlaceholdername) {
        $arrElements = $this->getElements($strIdentifier);
        foreach($arrElements as $arrSinglePlaceholder)
            if($arrSinglePlaceholder["placeholder"] == $strPlaceholdername)
                return true;

        return false;
    }

	/**
	 * Returns the elements in a given template
	 *
	 * @param string $strIdentifier
	 * @param int $intMode 0 = regular page, 1 = master page
	 * @return mixed
	 */
	public function getElements($strIdentifier, $intMode = 0) {
		$arrReturn = array();

		if(isset($this->arrCacheTemplateSections[$strIdentifier]))
			$strTemplate = $this->arrCacheTemplateSections[$strIdentifier];
		else
			return array();

		//search placeholders
		$arrTemp = array();
		preg_match_all("'(%%([A-Za-z0-9_]+?))+?\_([A-Za-z0-9_\|]+?)%%'i", $strTemplate, $arrTemp);


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
	 * Sets the passed template as the current temp-template
	 *
	 * @param string $strTemplate
	 */
	public function setTemplate($strTemplate) 	{
		$this->strTempTemplate = $strTemplate;
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

