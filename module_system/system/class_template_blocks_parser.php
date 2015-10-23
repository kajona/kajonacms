<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Parses the blocks of a single template
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 5.0
 *
 */
class class_template_blocks_parser
{

    /**
     * @param $strTemplate
     * @param string $strBlockDefinition
     *
     * @return class_template_block_container[]
     */
    public function readBlocks($strTemplate, $strBlockDefinition = class_template_kajona_sections::BLOCKS)
    {
        $arrBlocks = array();


        //find opening tag
        $arrMatches = array();
        while (preg_match("/<".$strBlockDefinition."([\ a-zA-Z0-9=']*)(.*) ".class_template_kajona_sections::ATTR_NAME."=(\"|\')([\ a-zA-Z0-9]*)(\"|\')(.*)>/i", $strTemplate, $arrMatches) > 0) {

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

                    $strContent = uniSubstr($strTemplateSection, uniStrlen($arrMatches[0]), uniStrlen("</".$strBlockDefinition.">")*-1);
                    $arrBlocks[$arrMatches[4]] = new class_template_block_container($strBlockDefinition, $arrMatches[4], $arrMatches[0], $strContent, $strTemplateSection);

                    $strTemplate = uniStrReplace($strTemplateSection, "", $strTemplate);
                }
            }
            else {
                break;
            }
        }

        //find closing tag

        return $arrBlocks;
    }

    /**
     * @param $strTemplate
     * @param $arrBlocks strBlockName -> strContent
     * @param string $strBlocksDefinition
     */
    public function fillBlocks($strTemplate, $arrBlocks, $strBlocksDefinition = class_template_kajona_sections::BLOCKS) {
        $arrBlocksOnTemplate = $this->readBlocks($strTemplate, $strBlocksDefinition);

        foreach($arrBlocks as $strBlockName => $strContent) {
            if(isset($arrBlocksOnTemplate[$strBlockName])) {
                $objCurBlock = $arrBlocksOnTemplate[$strBlockName];
                $strTemplate = uniStrReplace($objCurBlock->getStrFullSection(), $strContent, $strTemplate);
            }

        }

        return $strTemplate;
    }


}

