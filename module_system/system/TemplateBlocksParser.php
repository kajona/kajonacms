<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;


/**
 * Parses the blocks of a single template
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 5.0
 *
 */
class TemplateBlocksParser
{

    private $arrBlocksCache = array();
    private $bitCacheInit = false;


    private function cacheInit()
    {
        if($this->bitCacheInit) {
            return;
        }
        $this->bitCacheInit = true;
        $this->arrBlocksCache = Carrier::getInstance()->getContainer()->offsetGet(ServiceProvider::STR_CACHE_MANAGER)->getValue(__CLASS__);
        if($this->arrBlocksCache === false) {
            $this->arrBlocksCache = array();
        }
    }

    /**
     * @inheritDoc
     */
    public function __destruct()
    {
        if(Config::getInstance()->getConfig("templatecachetime") >=0) {
            Carrier::getInstance()->getContainer()->offsetGet(ServiceProvider::STR_CACHE_MANAGER)->addValue(__CLASS__, $this->arrBlocksCache, Config::getInstance()->getConfig("templatecachetime"));
        }
    }


    /**
     * @param $strTemplate
     * @param string $strBlockDefinition
     *
     * @return TemplateBlockContainer[]
     */
    public function readBlocks($strTemplate, $strBlockDefinition = TemplateKajonaSections::BLOCKS)
    {
        $this->cacheInit();
        $strHash = sha1($strTemplate.$strBlockDefinition);
        if(isset($this->arrBlocksCache[$strHash])) {
            return $this->arrBlocksCache[$strHash];
        }


        $arrBlocks = array();


        //find opening tag
        $arrMatches = array();
        while (preg_match("/<".$strBlockDefinition."([\ a-zA-Z0-9=']*)(.*) ".TemplateKajonaSections::ATTR_NAME."=(\"|\')([\-\ a-zA-Z0-9]*)(\"|\')(.*)>/i", $strTemplate, $arrMatches) > 0) {

            $strPattern = $arrMatches[0];
            $intStart = uniStrpos($strTemplate, $strPattern);

            $intEnd = uniStrpos($strTemplate, "</".$strBlockDefinition.">");
            $intEnd += uniStrlen("</".$strBlockDefinition.">");


            if ($intStart !== false && $intEnd !== false) {
                $intEnd = $intEnd - $intStart;

                if ($intEnd == 0) {
                    break;
                }
                else {
                    //delete substring before and after
                    $strTemplateSection = uniSubstr($strTemplate, $intStart, $intEnd);

                    $strContent = uniSubstr($strTemplateSection, uniStrlen($arrMatches[0]), uniStrlen("</".$strBlockDefinition.">") * -1);
                    $arrBlocks[$arrMatches[4]] = new TemplateBlockContainer($strBlockDefinition, $arrMatches[4], $arrMatches[0], $strContent, $strTemplateSection);

                    $strTemplate = uniStrReplace($strTemplateSection, "", $strTemplate);
                }
            }
            else {
                break;
            }
        }

        $this->arrBlocksCache[$strHash] = $arrBlocks;

        return $arrBlocks;
    }

    /**
     * @param $strTemplate
     * @param $arrBlocks [strBlockName -> strContent]
     * @param string $strBlocksDefinition
     *
     * @return mixed
     */
    public function fillBlocks($strTemplate, $arrBlocks, $strBlocksDefinition = TemplateKajonaSections::BLOCKS)
    {
        $arrBlocksOnTemplate = $this->readBlocks($strTemplate, $strBlocksDefinition);

        foreach ($arrBlocks as $strBlockName => $strContent) {
            if (isset($arrBlocksOnTemplate[$strBlockName])) {
                $objCurBlock = $arrBlocksOnTemplate[$strBlockName];
                $strTemplate = uniStrReplace($objCurBlock->getStrFullSection(), $strContent, $strTemplate);
            }

        }

        return $strTemplate;
    }


}

