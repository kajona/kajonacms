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


    public function readBlocks($strTemplate)
    {
        $arrBlocks = array();


        //find opening tag
        $arrMatches = array();
        while (preg_match("/<".class_template_kajona_sections::BLOCKS."([\ a-zA-Z0-9=']*)(.*) ".class_template_kajona_sections::ATTR_NAME."=(\"|\')([a-zA-Z0-9]*)(\"|\')(.*)>/i", $strTemplate, $arrMatches) > 0) {

            $strPattern = $arrMatches[0];
            $intStart = uniStrpos($strTemplate, $strPattern);

            $intEnd = uniStrpos($strTemplate, "</".class_template_kajona_sections::BLOCKS.">");
            $intEnd += uniStrlen("</".class_template_kajona_sections::BLOCKS.">");


            if ($intStart !== false && $intEnd !== false) {
                $intEnd = $intEnd - $intStart;

                if ($intEnd == 0) {
                    break;
                }
                else {
                    //delete substring before and after
                    $strTemplateSection = uniSubstr($strTemplate, $intStart, $intEnd);
                    $arrBlocks[$arrMatches[4]] = $strTemplateSection;

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


}

