<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                           *
********************************************************************************************************/

namespace Kajona\System\System;

use Kajona\System\System\Scriptlets\ScriptletXConstants;


/**
 * This class does all the template stuff as loading, parsing, etc..
 * An instance should be resolved by the service container
 *
 * @package module_system
 * @author sidler@mulchprod.de
 *
 */
class Template
{

    const INT_ELEMENT_MODE_MASTER = 1;
    const INT_ELEMENT_MODE_REGULAR = 0;


    private $strTempTemplate = "";

    private static $objTemplate = null;


    /** @var array for backwards compatibility */
    private $arrTemplateIdMap = array();


    /** @var  TemplateFileParser */
    private $objFileParser;

    /** @var  TemplateSectionParser */
    private $objSectionParser;

    /** @var  TemplatePlaceholderParser */
    private $objPlaceholderParser;

    /** @var  TemplateBlocksParser */
    private $objBlocksParser;
    
    
    
    private $arrTemplateCache = array();

    /**
     * @param TemplateFileParser $objFileParser
     * @param TemplateSectionParser $objSectionParser
     * @param TemplatePlaceholderParser $objPlaceholderParser
     * @param TemplateBlocksParser $objBlocksParser
     */
    public function __construct(TemplateFileParser $objFileParser, TemplateSectionParser $objSectionParser, TemplatePlaceholderParser $objPlaceholderParser, TemplateBlocksParser $objBlocksParser)
    {
        $this->objFileParser = $objFileParser;
        $this->objSectionParser = $objSectionParser;
        $this->objPlaceholderParser = $objPlaceholderParser;
        $this->objBlocksParser = $objBlocksParser;
        
        $this->arrTemplateCache = Carrier::getInstance()->getContainer()->offsetGet(ServiceProvider::STR_CACHE_MANAGER)->getValue(__CLASS__);
        if($this->arrTemplateCache === false) {
            $this->arrTemplateCache = array();
        }
    }



    /**
     * @inheritDoc
     */
    public function __destruct()
    {
        if(Config::getInstance()->getConfig("templatecachetime") >=0) {
            Carrier::getInstance()->getContainer()->offsetGet(ServiceProvider::STR_CACHE_MANAGER)->addValue(__CLASS__, $this->arrTemplateCache, Config::getInstance()->getConfig("templatecachetime"));
        }
    }


    /**
     * Returns one instance of the template object, using a singleton pattern
     *
     * @param TemplateFileParser $objFileParser
     * @param TemplateSectionParser $objSectionParser
     * @param TemplatePlaceholderParser $objPlaceholderParser
     * @param TemplateBlocksParser $objBlocksParser
     *
     * @return Template The template object
     * @deprecated
     */
    public static function getInstance(TemplateFileParser $objFileParser, TemplateSectionParser $objSectionParser, TemplatePlaceholderParser $objPlaceholderParser, TemplateBlocksParser $objBlocksParser)
    {
        if (self::$objTemplate == null) {
            self::$objTemplate = new Template($objFileParser, $objSectionParser, $objPlaceholderParser, $objBlocksParser);
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
     * @throws Exception
     *
     * @deprecated use the direct fill / parse methods instead
     */
    public function readTemplate($strName, $strSection = "", $bitForce = false, $bitThrowErrors = false)
    {

        $strTemplate = $this->objFileParser->readTemplate($strName);

        if ($strSection != "") {
            $strTemplate = $this->objSectionParser->readSection($strTemplate, $strSection);
        }


        $strHash = md5($strName.$strSection);
        $this->arrTemplateIdMap[$strHash] = $strTemplate;

        return $strHash;
    }


    /**
     * Parses the passed template string and returns the contained blocks and elements.
     * The contained blocks and elements are nested within a root TemplateBlockContainer element
     * @param $strContent
     * @param int $intMode
     * @throws Exception
     * @return TemplateBlockContainer
     */
    public function parsePageTemplateString($strContent, $intMode = Template::INT_ELEMENT_MODE_REGULAR)
    {

        if(isset($this->arrTemplateCache[__METHOD__][md5($strContent.$intMode)])) {
            return $this->arrTemplateCache[__METHOD__][md5($strContent.$intMode)];
        }

        //read top level placeholder
        $arrPlaceholder = $this->objPlaceholderParser->getElements($this->removeSection($strContent, TemplateKajonaSections::BLOCKS), $intMode);
        $objRoot = new TemplateBlockContainer(TemplateKajonaSections::ROOT, "", "", "", "");
        $objRoot->setArrPlaceholder($arrPlaceholder);

        //fetch blocks sections
        $arrBlocksSections = $this->objBlocksParser->readBlocks($strContent, TemplateKajonaSections::BLOCKS);
        $objRoot->setArrBlocks($arrBlocksSections);

        //fetch block sections
        foreach($arrBlocksSections as $objOneBlock) {
            $arrBlockSections = $this->objBlocksParser->readBlocks($objOneBlock->getStrContent(), TemplateKajonaSections::BLOCK);

            $objOneBlock->setArrBlocks($arrBlockSections);

            //fetch elements per block section
            foreach($arrBlockSections as $objOneBlockSection) {
                $arrElements = $this->objPlaceholderParser->getElements($objOneBlockSection->getStrContent(), $intMode);
                $objOneBlockSection->setArrPlaceholder($arrElements);
            }
        }

        $this->arrTemplateCache[__METHOD__][md5($strContent.$intMode)] = $objRoot;
        return $objRoot;
    }

    /**
     * Runs both, the loading of a template from the filesystem and the parsing of
     * the content of the file.
     * @param $strName
     * @param int $intMode
     *
     * @return TemplateBlockContainer
     */
    public function parsePageTemplate($strName, $intMode = Template::INT_ELEMENT_MODE_REGULAR)
    {
        if(isset($this->arrTemplateCache[__METHOD__][$strName.$intMode])) {
            return $this->arrTemplateCache[__METHOD__][$strName.$intMode];
        }

        $strTemplate = $this->objFileParser->readTemplate($strName);
        $objReturn = $this->parsePageTemplateString($strTemplate, $intMode);

        $this->arrTemplateCache[__METHOD__][$strName.$intMode] = $objReturn;
        return $objReturn;
    }

    /**
     * Reads the blocks from a passed template. Does both, the loading from the filesystem and the
     * parsing of the loaded string.
     *
     * @param $strTemplateFile
     * @param string $strSection
     *
     * @return TemplateBlockContainer[]
     */
    public function getBlocksElementsFromTemplate($strTemplateFile, $strSection = "")
    {
        
        if(isset($this->arrTemplateCache[__METHOD__][$strTemplateFile.$strSection])) {
            return $this->arrTemplateCache[__METHOD__][$strTemplateFile.$strSection];
        }
        
        $strTemplate = $this->objFileParser->readTemplate($strTemplateFile);

        if ($strSection != "") {
            $strTemplate = $this->objSectionParser->readSection($strTemplate, $strSection);
        }

        $arrReturn = $this->objBlocksParser->readBlocks($strTemplate, TemplateKajonaSections::BLOCKS);
        $this->arrTemplateCache[__METHOD__][$strTemplateFile.$strSection] = $arrReturn;
        return $arrReturn;
    }

    /**
     * Parses the single block elements from a blocks-string
     * @param $strBlock
     *
     * @return TemplateBlockContainer[]
     */
    public function getBlockElementsFromBlock($strBlock)
    {
        if(isset($this->arrTemplateCache[__METHOD__][$strBlock])) {
            return $this->arrTemplateCache[__METHOD__][$strBlock];
        }
        $arrBlock = $this->objBlocksParser->readBlocks($strBlock, TemplateKajonaSections::BLOCK);

        $this->arrTemplateCache[__METHOD__][$strBlock] = $arrBlock;
        return $arrBlock;
    }

    /**
     * Helper to parse a single section out of a given template
     *
     * @param $strTemplateContent
     * @param $strSection
     *
     * @param bool $bitKeepSectionTag
     *
     * @return null|string
     */
    public function getSectionFromTemplate($strTemplateContent, $strSection, $bitKeepSectionTag = false)
    {
        $strHash = md5($strTemplateContent.$strSection.$bitKeepSectionTag);
        if(isset($this->arrTemplateCache[__METHOD__][$strHash])) {
            return $this->arrTemplateCache[__METHOD__][$strHash];
        }
        $strSection = $this->objSectionParser->readSection($strTemplateContent, $strSection, $bitKeepSectionTag);
        $this->arrTemplateCache[__METHOD__][$strHash] = $strSection;
        return $strSection;
    }


    /**
     * Fills a template with values passed in an array.
     *
     * @param mixed $arrContent
     * @param string $strIdentifier
     * @param bool $bitRemovePlaceholder
     *
     * @return string The filled template
     * @deprecated switch to filesystem based methods instead
     */
    public function fillTemplate($arrContent, $strIdentifier, $bitRemovePlaceholder = true)
    {
        if (array_key_exists($strIdentifier, $this->arrTemplateIdMap)) {
            $strTemplate = (string)$this->arrTemplateIdMap[$strIdentifier];
        }
        else {
            $strTemplate = "Load template first!";
        }

        if (count($arrContent) >= 1) {
            foreach ($arrContent as $strPlaceholder => $strContent) {
                $strTemplate = str_replace("%%".$strPlaceholder."%%", $strContent."%%".$strPlaceholder."%%", $strTemplate);
            }
        }

        if ($bitRemovePlaceholder) {
            $strTemplate = $this->objPlaceholderParser->deletePlaceholder($strTemplate);
        }
        return $strTemplate;
    }

    /**
     * Fills a template with values passed in an array.
     * Does both, the loading of the file from the filesystem and the replacement of the placeholders.
     *
     * @param $arrPlaceholderContent
     * @param $strTemplateFilename
     * @param string $strSection
     * @param bool $bitRemovePlaceholder
     *
     * @return string The filled template
     */
    public function fillTemplateFile($arrPlaceholderContent, $strTemplateFilename, $strSection = "", $bitRemovePlaceholder = true)
    {

        if(!isset($this->arrTemplateCache[__METHOD__][$strTemplateFilename.$strSection])) {

            $strTemplate = $this->objFileParser->readTemplate($strTemplateFilename);

            if ($strSection != "") {
                $strTemplate = $this->objSectionParser->readSection($strTemplate, $strSection);
            }

            $this->arrTemplateCache[__METHOD__][$strTemplateFilename.$strSection] = $strTemplate;
        }
        else {
            $strTemplate = $this->arrTemplateCache[__METHOD__][$strTemplateFilename.$strSection];
        }

        foreach ($arrPlaceholderContent as $strPlaceholder => $strContent) {
            $strTemplate = str_replace("%%".$strPlaceholder."%%", $strContent."%%".$strPlaceholder."%%", $strTemplate);
        }

        if ($bitRemovePlaceholder) {
            $strTemplate = $this->objPlaceholderParser->deletePlaceholder($strTemplate);
        }
        return $strTemplate;
    }

    /**
     * Replaces the given blocks within the passed template string
     *
     * @param $arrBlocks
     * @param $strTemplateContent
     * @param string $strBlocksDefinition
     *
     * @return mixed
     */
    public function fillBlocksToTemplateFile($arrBlocks, $strTemplateContent, $strBlocksDefinition = TemplateKajonaSections::BLOCKS)
    {
        return $this->objBlocksParser->fillBlocks($strTemplateContent, $arrBlocks, $strBlocksDefinition);
    }

    /**
     * Removes all blocks from teh passed tempalte-string
     *
     * @param $strTemplateContent
     * @param string $strBlockDefinition
     *
     * @return mixed
     */
    public function deleteBlocksFromTemplate($strTemplateContent, $strBlockDefinition = TemplateKajonaSections::BLOCKS)
    {
        foreach($this->objBlocksParser->readBlocks($strTemplateContent, $strBlockDefinition) as $objOneContainer) {
            $strTemplateContent = uniStrReplace($objOneContainer->getStrFullSection(), "", $strTemplateContent);
        }
        return $strTemplateContent;
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
    public function fillCurrentTemplate($arrContent, $bitRemovePlaceholder = true)
    {
        $strIdentifier = $this->setTemplate($this->strTempTemplate);
        return $this->fillTemplate($arrContent, $strIdentifier, $bitRemovePlaceholder);
    }


    /**
     * Replaces constants in the template set by setTemplate()
     *
     * @deprecated use scriptlets instead
     */
    public function fillConstants()
    {
        $objConstantScriptlet = new ScriptletXConstants();
        $this->strTempTemplate = $objConstantScriptlet->processContent($this->strTempTemplate);
    }

    /**
     * Deletes placeholder in the template set by setTemplate()
     *
     * @deprecated
     */
    public function deletePlaceholder()
    {
        $this->strTempTemplate = $this->objPlaceholderParser->deletePlaceholder($this->strTempTemplate);
    }


    /**
     * Returns the template set by setTemplate() and sets its back to ""
     *
     * @return string
     * @deprecated
     */
    public function getTemplate()
    {
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
     * @see Template::containsPlaceholder
     */
    public function containesPlaceholder($strIdentifier, $strPlaceholdername)
    {
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
     * @deprecated
     */
    public function containsPlaceholder($strIdentifier, $strPlaceholdername)
    {
        return (isset($this->arrTemplateIdMap[$strIdentifier])
            && $this->objPlaceholderParser->containsPlaceholder($this->arrTemplateIdMap[$strIdentifier], $strPlaceholdername));
    }

    /**
     * Validates if the passed templates-string provides a given section
     * Does both, the loading from the filesystem and the parsing itself
     *
     * @param $strTemplateFilename
     * @param $strSection
     *
     * @return bool
     */
    public function providesSection($strTemplateFilename, $strSection)
    {
        return $this->objSectionParser->containsSection($this->objFileParser->readTemplate($strTemplateFilename), $strSection);
    }

    /**
     * Checks if the template referenced by the given identifier provides the section passed.
     *
     * @param $strIdentifier
     * @param $strSection
     *
     * @return bool
     * @deprecated
     */
    public function containsSection($strIdentifier, $strSection)
    {
        return (isset($this->arrTemplateIdMap[$strIdentifier])
            && $this->objSectionParser->containsSection($this->arrTemplateIdMap[$strIdentifier], $strSection));
    }

    /**
     * Removes a section with all contents from the given (template) string
     *
     * @param $strTemplate
     * @param $strSection
     *
     * @return string
     */
    public function removeSection($strTemplate, $strSection)
    {
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
    public function getElements($strIdentifier, $intMode = 0)
    {

        if (isset($this->arrTemplateIdMap[$strIdentifier])) {
            $strTemplate = $this->arrTemplateIdMap[$strIdentifier];
        }
        else {
            return array();
        }

        $strTemplate = $this->removeSection($strTemplate, TemplateKajonaSections::BLOCKS);

        return $this->objPlaceholderParser->getElements($strTemplate, $intMode);
    }


    /**
     * Returns the elements in a given template
     *
     * @param $strTemplateFile
     * @param string $strSection
     * @param int $intMode 0 = regular page, 1 = master page
     *
     * @return mixed
     */
    public function getElementsFromTemplateFile($strTemplateFile, $strSection = "", $intMode = 0)
    {

        $strTemplate = $this->objFileParser->readTemplate($strTemplateFile);
        if ($strSection != "") {
            $strTemplate = $this->objSectionParser->readSection($strTemplate, $strSection);
        }

        return $this->objPlaceholderParser->getElements($strTemplate, $intMode);
    }

    /**
     * Sets the passed template as the current temp-template
     *
     * @param string $strTemplate
     *
     * @return string
     * @deprecated
     */
    public function setTemplate($strTemplate)
    {
        $this->strTempTemplate = $strTemplate;
        $strIdentifier = generateSystemid();
        $this->arrTemplateIdMap[$strIdentifier] = $strTemplate;
        return $strIdentifier;
    }

    /**
     * @param $strTemplateId
     *
     * @return bool
     * @deprecated
     */
    public function isValidTemplate($strTemplateId)
    {
        return isset($this->arrTemplateIdMap[$strTemplateId]) && $this->arrTemplateIdMap[$strTemplateId] != "";
    }

}

