<?php
/*"******************************************************************************************************
*   (c) 2015-2016 by Kajona, www.kajona.de                                                         *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * Used to parse template files and to extract single sections from a template.
 * By default sections are unique within a template, so only the first section is returned.
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 5.0
 *
 */
class TemplateSectionParser
{

    private $arrCacheTemplateSection = array();
    private $bitCacheInit = false;


    private function cacheInit()
    {
        if($this->bitCacheInit) {
            return;
        }
        $this->bitCacheInit = true;

        $this->arrCacheTemplateSection = Carrier::getInstance()->getContainer()->offsetGet(ServiceProvider::STR_CACHE_MANAGER)->getValue(__CLASS__);
        if($this->arrCacheTemplateSection === false) {
            $this->arrCacheTemplateSection = array();
        }
    }

    /**
     * @inheritDoc
     */
    public function __destruct()
    {
        if(Config::getInstance()->getConfig("templatecachetime") >=0) {
            Carrier::getInstance()->getContainer()->offsetGet(ServiceProvider::STR_CACHE_MANAGER)->addValue(__CLASS__, $this->arrCacheTemplateSection, Config::getInstance()->getConfig("templatecachetime"));
        }
    }


    public function readSection($strTemplate, $strSection, $bitKeepSectionTag = false)
    {
        $this->cacheInit();
        $strHash = ($strTemplate.$strSection.$bitKeepSectionTag);

        if (isset($this->arrCacheTemplateSection[$strHash])) {
            return $this->arrCacheTemplateSection[$strHash];
        }


        //find opening tag
        $arrMatches = array();
        $intStart = false;
        if (preg_match("/<".$strSection."([\ a-zA-Z0-9\-='\"])*>/i", $strTemplate, $arrMatches) > 0) {
            $strPattern = $arrMatches[0];
            $intStart = uniStrpos($strTemplate, $strPattern);

            if (!$bitKeepSectionTag) {
                $intStart += uniStrlen($strPattern);
            }
        }

        //find closing tag
        $intEnd = uniStrpos($strTemplate, "</".$strSection.">");
        if ($bitKeepSectionTag) {
            $intEnd += uniStrlen("</".$strSection.">");
        }


        if ($intStart !== false && $intEnd !== false) {
            $intEnd = $intEnd - $intStart;

            if ($intEnd == 0) {
                $strTemplate = "";
            }
            else {
                //delete substring before and after
                $strTemplate = uniSubstr($strTemplate, $intStart, $intEnd);
            }
        }
        else {
            $strTemplate = null;
        }

        $this->arrCacheTemplateSection[$strHash] = $strTemplate;
        return $strTemplate;
    }


    /**
     * Checks if the template referenced by the given identifier provides the section passed.
     *
     * @param $strIdentifier
     * @param $strSection
     *
     * @return bool
     */
    public function containsSection($strTemplate, $strSection)
    {
        return $this->readSection($strTemplate, $strSection) !== null;
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
        do {
            $strFullSection = $this->readSection($strTemplate, $strSection, true);
            $strTemplate = uniStrReplace($strFullSection, "", $strTemplate);
        } while ($strFullSection != "" && $strFullSection != null);

        return $strTemplate;
    }

}

