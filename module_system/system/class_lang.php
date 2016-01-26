<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                              *
********************************************************************************************************/

use Kajona\System\System\CacheManager;

/**
 * Class managing access to lang-files
 *
 * @package module_system
 * @author sidler@mulchprod.de
 */
class class_lang {

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
     * @var string
     */
    private $strCommonsName = "commons";
    private $arrTexts;

    /**
     * Used to keep placeholders loaded from the fallback lang-file.
     * Only used, if the file itself exists in the target-language but misses a placeholder!
     *
     * @var array
     */
    private $arrFallbackTextEntrys = array();


    private static $objLang = null;

    private $bitSaveToCache = false;

    /**
     * Constructor, singleton
     */
    private function __construct() {
        //load texts from session
        $this->arrTexts = CacheManager::getInstance()->getValue("textSessionCache");
        if($this->arrTexts === false) {
            $this->arrTexts = array();
        }

        $this->arrFallbackTextEntrys = CacheManager::getInstance()->getValue("textSessionFallbackCache");
        if($this->arrFallbackTextEntrys === false) {
            $this->arrFallbackTextEntrys = array();
        }
    }

    public function __destruct() {
        //save texts to session
        if($this->bitSaveToCache) {
            CacheManager::getInstance()->addValue("textSessionCache", $this->arrTexts, class_config::getInstance()->getConfig("textcachetime"));
            CacheManager::getInstance()->addValue("textSessionFallbackCache", $this->arrFallbackTextEntrys, class_config::getInstance()->getConfig("textcachetime"));
        }
    }

    /**
     * Singleton
     */
    private function __clone() {
    }

    /**
     * Returning an instance of class_lang
     *
     * @return class_lang
     */
    public static function getInstance() {
        if(self::$objLang == null) {
            self::$objLang = new class_lang();
        }

        return self::$objLang;
    }

    /**
     * @param $strText
     * @param $strModule
     * @param $strArea
     *
     * @return string
     * @deprecated use getLang() instead
     */
    public function getText($strText, $strModule, $strArea) {
        class_logger::getInstance(class_logger::SYSTEMLOG)->addLogRow("deprecated class_lang::getText call, params: ".$strText.", ".$strModule.", ".$strArea, class_logger::$levelWarning);
        return $this->getLang($strText, $strModule);
    }

    /**
     * Returning the searched text-entry.
     * If you have placeholders in the property (like {1}, {2}, you may replace them with the values of the third param.
     *
     * @param string $strText
     * @param $strModule
     * @param array $arrParameters an array of variables which are embedded into the string
     *
     * @return string
     */
    public function getLang($strText, $strModule, $arrParameters = array()) {

        //Did we already load this text?
        if(!isset($this->arrTexts[$this->strLanguage][$strModule])) {
            $this->loadText($strModule);
        }

        //Searching for the text
        if(isset($this->arrTexts[$this->strLanguage][$strModule][$strText])) {
            $strReturn = $this->arrTexts[$this->strLanguage][$strModule][$strText];
        }
        else {

            //try to load the entry in the commons-list
            if(!isset($this->arrTexts[$this->strLanguage][$this->strCommonsName])) {
                $this->loadText($this->strCommonsName);
            }

            if(isset($this->arrTexts[$this->strLanguage][$this->strCommonsName][$strText])) {
                $strReturn = $this->arrTexts[$this->strLanguage][$this->strCommonsName][$strText];
            }
            else {
                //Try to find the text using the fallback language, mark the entry as not resolvable
                $strReturn = $this->loadFallbackPlaceholder($strModule, $strText);
                $this->arrTexts[$this->strLanguage][$strModule][$strText] = $strReturn;
            }
        }

        return $this->replaceParams($strReturn, $arrParameters);
    }

    /**
     * Returns all properties for a specific module
     *
     * @param string $strModule
     */
    public function getProperties($strModule)
    {
        //Did we already load this text?
        if(!isset($this->arrTexts[$this->strLanguage][$strModule])) {
            $this->loadText($strModule);
        }

        if (isset($this->arrTexts[$this->strLanguage][$strModule])) {
            return $this->arrTexts[$this->strLanguage][$strModule];
        } else {
            return array();
        }
    }

    /**
     *
     * Internal helper to fill parametrized properties.
     *
     * @param $strProperty
     * @param $arrParameters
     *
     * @return mixed
     */
    public function replaceParams($strProperty, $arrParameters) {
        foreach($arrParameters as $intKey => $strParameter)
            $strProperty = uniStrReplace("{".$intKey."}", $strParameter, $strProperty);

        return $strProperty;
    }


    public function stringToPlaceholder($strText) {
        $strReturn = "";
        $strLastChar = "";

        for ($i = 0; $i < uniStrlen($strText); $i++) {
            $strChar = uniSubstr($strText, $i, 1);
            $strCharLower = uniStrtolower($strChar);

            if ($i > 0 && $strChar != $strCharLower && $strLastChar != "_") {
                $strReturn .= "_" . $strCharLower;
            }
            else {
                $strReturn .= $strCharLower;
            }

            $strLastChar = $strChar;
        }

        return $strReturn;
    }


    private function loadFallbackPlaceholder($strModule, $strText) {

        if(isset($this->arrTexts[$this->strFallbackLanguage][$strModule][$strText])) {
            $strReturn = $this->arrTexts[$this->strFallbackLanguage][$strModule][$strText];
        }
        else {
            //try to load the fallback-files
            //load files
            $arrFiles = class_resourceloader::getInstance()->getLanguageFiles("module_" . $strModule);
            if(is_array($arrFiles)) {
                foreach($arrFiles as $strPath => $strFilename) {
                    $strTemp = str_replace(".php", "", $strFilename);
                    $arrName = explode("_", $strTemp);

                    if($arrName[0] == "lang" && $arrName[2] == $this->strFallbackLanguage) {
                        $this->loadAndMergeTextfile($strModule, $strPath, $this->strFallbackLanguage, $this->arrFallbackTextEntrys);
                    }
                }
            }
            if(isset($this->arrFallbackTextEntrys[$this->strFallbackLanguage][$strModule][$strText])) {
                $strReturn = $this->arrFallbackTextEntrys[$this->strFallbackLanguage][$strModule][$strText];
            }
            else {

                if(!isset($this->arrFallbackTextEntrys[$this->strFallbackLanguage][$this->strCommonsName])) {
                    $arrFiles = class_resourceloader::getInstance()->getLanguageFiles("module_" . $this->strCommonsName);
                    if(is_array($arrFiles)) {
                        foreach($arrFiles as $strPath => $strFilename) {
                            $strTemp = str_replace(".php", "", $strFilename);
                            $arrName = explode("_", $strTemp);

                            if($arrName[0] == "lang" && $arrName[2] == $this->strFallbackLanguage) {
                                $this->loadAndMergeTextfile($this->strCommonsName, $strPath, $this->strFallbackLanguage, $this->arrFallbackTextEntrys);
                            }
                        }
                    }
                }

                if(isset($this->arrFallbackTextEntrys[$this->strFallbackLanguage][$this->strCommonsName][$strText])) {
                    $strReturn = $this->arrFallbackTextEntrys[$this->strFallbackLanguage][$this->strCommonsName][$strText];
                }
                else {
                    $strReturn = "!" . $strText . "!";
                    //class_logger::getInstance(class_logger::LANG)->addLogRow("failed to load lang-property for: ".$strText, class_logger::$levelWarning);
                }
            }
        }

        return $strReturn;
    }


    /**
     * Loading texts from textfiles
     *
     * @param string $strModule
     *
     * @return void
     */
    private function loadText($strModule) {
        $bitFileMatched = false;

        //load files
        $arrFiles = class_resourceloader::getInstance()->getLanguageFiles("module_" . $strModule);

        if(is_array($arrFiles)) {
            foreach($arrFiles as $strPath => $strFilename) {
                /** @noinspection PhpUnusedLocalVariableInspection */
                $lang = array();
                $strTemp = str_replace(".php", "", $strFilename);
                $arrName = explode("_", $strTemp);

                if($arrName[0] == "lang" && $arrName[count($arrName)-1] == $this->strLanguage && $this->strLanguage != "") {
                    $bitFileMatched = true;
                    $this->loadAndMergeTextfile($strModule, $strPath, $this->strLanguage, $this->arrTexts);

                }
            }
            if($bitFileMatched) {
                return;
            }

            //if we reach up here, no matching file was found. search for fallback file (fallback language)
            foreach($arrFiles as $strPath => $strFilename) {
                $strTemp = str_replace(".php", "", $strFilename);
                $arrName = explode("_", $strTemp);

                if($arrName[0] == "lang" && $arrName[count($arrName)-1] == $this->strFallbackLanguage) {
                    $this->loadAndMergeTextfile($strModule, $strPath, $this->strFallbackLanguage, $this->arrTexts);
                }
            }
        }
    }

    /**
     * Includes the file from the filesystem and merges the contents to the passed array.
     * NOTE: this array is used as a reference!!!
     *
     * @param string $strModule
     * @param string $strFilename
     * @param string $strLanguage
     * @param array $arrTargetArray
     */
    private function loadAndMergeTextfile($strModule, $strFilename, $strLanguage, &$arrTargetArray) {
        $lang = array();
        $this->bitSaveToCache = true;

        include_once $strFilename;

        if(!isset($arrTargetArray[$strLanguage])) {
            $arrTargetArray[$strLanguage] = array();
        }

        if(isset($arrTargetArray[$strLanguage][$strModule])) {
            $arrTargetArray[$strLanguage][$strModule] = array_merge($arrTargetArray[$strLanguage][$strModule], $lang);
        }
        else {
            $arrTargetArray[$strLanguage][$strModule] = $lang;
        }
    }


    /**
     * Sets the language to load textfiles
     *
     * @param string $strLanguage
     */
    public function setStrTextLanguage($strLanguage) {
        if($strLanguage == "") {
            return;
        }

        $this->strLanguage = $strLanguage;
    }

    /**
     * Gets the current language set to the class_lang
     *
     * @return string
     */
    public function getStrTextLanguage() {
        return $this->strLanguage;
    }

}
