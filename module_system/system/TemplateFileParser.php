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

    public function readTemplate($strTemplateFilename)
    {
        $strFilename = $this->getPathForTemplate($strTemplateFilename);

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
        return $strTemplateContent;
    }


    private function getPathForTemplate($strTemplate)
    {
        $strName = Resourceloader::getInstance()->getTemplate($strTemplate, true);
        return $strName;
    }




}
