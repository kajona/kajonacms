<?php
/*"******************************************************************************************************
*   (c) 2015-2016 by Kajona, www.kajona.de                                                         *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * Used to parse template files and to extract single sections from a template
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 5.0
 *
 */
class TemplateFileParser
{

    private $arrCacheTemplates = array();
    private $bitCacheInit = false;


    private function cacheInit()
    {
        if($this->bitCacheInit) {
            return;
        }
        $this->bitCacheInit = true;

        $this->arrCacheTemplates = Carrier::getInstance()->getContainer()->offsetGet(ServiceProvider::STR_CACHE_MANAGER)->getValue(__CLASS__);
        if($this->arrCacheTemplates === false) {
            $this->arrCacheTemplates = array();
        }
    }

    /**
     * @inheritDoc
     */
    public function __destruct()
    {
        if(Config::getInstance()->getConfig("templatecachetime") >=0) {
            Carrier::getInstance()->getContainer()->offsetGet(ServiceProvider::STR_CACHE_MANAGER)->addValue(__CLASS__, $this->arrCacheTemplates, Config::getInstance()->getConfig("templatecachetime"));
        }
    }


    public function readTemplate($strTemplateFilename)
    {
        $this->cacheInit();
        $strHash = ($strTemplateFilename);
        $strFilename = $this->getPathForTemplate($strTemplateFilename);

        if (isset($this->arrCacheTemplates[$strHash])) {
            return $this->arrCacheTemplates[$strHash];
        }


        //We have to read the whole template from the filesystem
        if (uniSubstr($strFilename, -4) == ".tpl" && is_file($strFilename)) {
            $strTemplateContent = file_get_contents($strFilename);
        }
        else {
            $strTemplateContent = "Template ".$strTemplateFilename." not found!";
        }

        //search for includes
        preg_match_all("#\[KajonaTemplateInclude,([A-Za-z0-9_/\.]+)\]#i", $strTemplateContent, $arrTemp);

        foreach ($arrTemp[0] as $intKey => $strSearchString) {
            $strTemplateContent = \Kajona\System\System\StringUtil::replace($strSearchString, $this->readTemplate($arrTemp[1][$intKey]), $strTemplateContent);
        }

        //Saving to the cache
        $this->arrCacheTemplates[$strHash] = $strTemplateContent;
        return $strTemplateContent;
    }


    private function getPathForTemplate($strTemplate)
    {
        $strName = Resourceloader::getInstance()->getTemplate($strTemplate, true);
        return $strName;
    }




}
