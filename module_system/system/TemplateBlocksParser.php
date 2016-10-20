<?php
/*"******************************************************************************************************
*   (c) 2015-2016 by Kajona, www.kajona.de                                                         *
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

    /**
     * @param $strTemplate
     * @param string $strBlockDefinition
     *
     * @return TemplateBlockContainer[]
     * @throws TemplateBlocksParserException
     */
    public function readBlocks($strTemplate, $strBlockDefinition = TemplateKajonaSections::BLOCKS)
    {

        $arrBlocks = array();

        //find opening tag
        $arrMatches = array();
        while (preg_match("/<".$strBlockDefinition."([\ a-zA-Z0-9=']*)(.*) ".TemplateKajonaSections::ATTR_NAME."=(\"|\')([\-\ a-zA-Z0-9]*)(\"|\')(.*)>/i", $strTemplate, $arrMatches) > 0) {

            $strPattern = $arrMatches[0];
            $intStart = StringUtil::indexOf($strTemplate, $strPattern);

            $intEnd = StringUtil::indexOf($strTemplate, "</".$strBlockDefinition.">");
            $intEnd += StringUtil::length("</".$strBlockDefinition.">");


            if ($intStart !== false && $intEnd !== false) {
                $intEnd = $intEnd - $intStart;

                if ($intEnd == 0) {
                    break;
                }
                else {


                    if($intEnd < 0) {
                        $objException = new TemplateBlocksParserException($strBlockDefinition." parsing failed, maybe there is an illegal character in the ".$strBlockDefinition." name attribute?", Exception::$level_ERROR);
                        $objException->setStrSectionWithError($strTemplate);
                        throw $objException;
                    }

                    //delete substring before and after
                    $strTemplateSection = StringUtil::substring($strTemplate, $intStart, $intEnd);

                    $strContent = StringUtil::substring($strTemplateSection, StringUtil::length($arrMatches[0]), StringUtil::length("</".$strBlockDefinition.">") * -1);
                    $arrBlocks[$arrMatches[4]] = new TemplateBlockContainer($strBlockDefinition, $arrMatches[4], $arrMatches[0], $strContent, $strTemplateSection);

                    $strTemplate = StringUtil::replace($strTemplateSection, "", $strTemplate);
                }
            }
            else {
                break;
            }
        }

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
                $strTemplate = StringUtil::replace($objCurBlock->getStrFullSection(), $strContent, $strTemplate);
            }

        }

        return $strTemplate;
    }


}

