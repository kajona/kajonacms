<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                           *
********************************************************************************************************/
/**
 * This class does all the template stuff as loading, parsing, etc..
 *
 * @package module_system
 * @author sidler@mulchprod.de
 *
 * @todo add attribute parsing
 *
 * @todo consider refactoring the class to a more object-oriented approach, so once instance per template (section). or split up the class in class_template and class_template_section
 *
 */
class class_template {

    const INT_ELEMENT_MODE_MASTER = 1;
    const INT_ELEMENT_MODE_REGULAR = 0;


    private $strTempTemplate = "";

    private static $objTemplate = null;


    /** @var array for backwards compatibility */
    private $arrTemplateIdMap = array();


    /** @var  class_template_file_parser */
    private $objFileParser;
    /** @var  class_template_section_parser */
    private $objSectionParser;

    /**
     * @inheritDoc
     */
    private function __construct()
    {
        $this->objFileParser = new class_template_file_parser();
        $this->objSectionParser = new class_template_section_parser();
    }




    /**
     * Returns one instance of the template object, using a singleton pattern
     *
     * @return class_template The template object
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
     *
     * @return string The identifier for further actions
     * @throws class_exception
     *
     * @deprecated
     */
    public function readTemplate($strName, $strSection = "", $bitForce = false, $bitThrowErrors = false) {

        $strTemplate = $this->objFileParser->readTemplate($strName);

        if($strSection != "")
            $strTemplate = $this->objSectionParser->readSection($strTemplate, $strSection);


        $strHash = md5($strName.$strSection);
        $this->arrTemplateIdMap[$strHash] = $strTemplate;

        return $strHash;
    }

    /**
     * Helper to parse a single section out of a given template
     * @param $strTemplate
     * @param $strSection
     *
     * @return string|null
     */
    public function getSectionFromTemplate($strTemplate, $strSection, $bitKeepSectionTag = false) {
        return $this->objSectionParser->readSection($strTemplate, $strSection, $bitKeepSectionTag);
    }


    /**
     * Fills a template with values passed in an array.
     * As an optional parameter an instance of class_lang_wrapper can be passed
     * to fill placeholders matching the schema %%lang_...%% automatically.
     *
     * @param mixed $arrContent
     * @param string $strIdentifier
     * @param bool $bitRemovePlaceholder
     *
     * @return string The filled template
     */
    public function fillTemplate($arrContent, $strIdentifier, $bitRemovePlaceholder = true) {
        if(array_key_exists($strIdentifier, $this->arrCacheTemplateSections)) {
            $strTemplate = (string)$this->arrCacheTemplateSections[$strIdentifier];
        }
        else {
            $strTemplate = "Load template first!";
        }

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
     * Fills the current temp-template with the passed values.
     * <b>Make sure to have the wanted template loaded before by using setTemplate()</b>
     *
     * @param mixed $arrContent
     * @param bool $bitRemovePlaceholder
     *
     * @return string The filled template
     * @deprecated use setTemplate() and fillTemplate() instead
     */
    public function fillCurrentTemplate($arrContent, $bitRemovePlaceholder = true) {
        $strIdentifier = $this->setTemplate($this->strTempTemplate);
        return $this->fillTemplate($arrContent, $strIdentifier, $bitRemovePlaceholder);
    }


    /**
     * Replaces constants in the template set by setTemplate()
     *
     * @deprecated use scriptlets instead
     */
    public function fillConstants() {
        $objConstantScriptlet = new class_scriptlet_xconstants();
        $this->strTempTemplate = $objConstantScriptlet->processContent($this->strTempTemplate);
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
     *
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
     *
     * @return bool
     * @deprecated replaced by containsPlaceholder
     * @see class_template::containsPlaceholder
     */
    public function containesPlaceholder($strIdentifier, $strPlaceholdername) {
        return $this->containsPlaceholder($strIdentifier, $strPlaceholdername);
    }


    /**
     * Checks if the template referenced by the identifier contains the placeholder provided
     * by the second param.
     *
     * @param string $strIdentifier
     * @param string $strPlaceholdername
     *
     * @return bool
     */
    public function containsPlaceholder($strIdentifier, $strPlaceholdername) {
        if(!isset($this->arrCacheTemplateSections[$strIdentifier]))
            return array();

        return false;
    }

    /**
     * Checks if the template referenced by the given identifier provides the section passed.
     * @param $strIdentifier
     * @param $strSection
     *
     * @return bool
     */
    public function containsSection($strIdentifier, $strSection) {
        return (isset($this->arrTemplateIdMap[$strIdentifier])
            && $this->objSectionParser->containsSection($this->arrTemplateIdMap[$strIdentifier], $strSection) !== null);
    }

    /**
     * Removes a section with all contents from the given (template) string
     * @param $strTemplate
     * @param $strSection
     *
     * @return string
     */
    public function removeSection($strTemplate, $strSection) {
        return $this->objSectionParser->removeSection($strTemplate, $strSection);
    }

    /**
     * Returns the elements in a given template
     *
     * @param string $strIdentifier
     * @param int $intMode 0 = regular page, 1 = master page
     *
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

        $strTemplate = $this->removeSection($strTemplate, class_template_kajona_sections::BLOCKS);

        preg_match_all("'(%%([A-Za-z0-9_]+?))+?\_([A-Za-z0-9_\|]+?)%%'i", $strTemplate, $arrTemp);

        $intCounter = 0;
        foreach($arrTemp[0] as $strPlacehoder) {

            if(uniStrpos($strPlacehoder, "master") !== false && $intMode == class_template::INT_ELEMENT_MODE_REGULAR) {
                continue;
            }

            $strTemp = uniSubstr($strPlacehoder, 2, -2);
            $arrTemp = explode("_", $strTemp);
            //are there any pipes?
            if(uniStrpos($arrTemp[1], "|") !== false) {
                $arrElementTypes = explode("|", $arrTemp[1]);
                $intCount2 = 0;
                $arrReturn[$intCounter]["placeholder"] = $strTemp;

                foreach($arrElementTypes as $strOneElementType) {
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

        return $arrReturn;
    }

    /**
     * Sets the passed template as the current temp-template
     *
     * @param string $strTemplate
     *
     * @return string
     */
    public function setTemplate($strTemplate) {
        $this->strTempTemplate = $strTemplate;
        $strIdentifier = generateSystemid();
        $this->arrCacheTemplates[$strIdentifier] = $strTemplate;
        $this->arrCacheTemplateSections[$strIdentifier] = $strTemplate;
        return $strIdentifier;
    }

    public function isValidTemplate($strTemplateId) {
        return isset($this->arrTemplateIdMap[$strTemplateId]) && $this->arrTemplateIdMap[$strTemplateId] != "";
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

