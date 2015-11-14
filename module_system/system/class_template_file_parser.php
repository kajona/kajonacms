<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Used to parse template files and to extract single sections from a template
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 5.0
 *
 */
class class_template_file_parser
{

    private $arrCacheTemplates = array();


    public function readTemplate($strTemplateFilename)
    {
        $strFilename = $this->getPathForTemplate($strTemplateFilename);
        $strHash = md5($strFilename);

        if (isset($this->arrCacheTemplates[$strHash])) {
            return $this->arrCacheTemplates[$strHash];
        }


        //We have to read the whole template from the filesystem
        if (substr($strFilename, 0, 7) == "phar://") {
            $strFullPath = $strFilename;
        } else {
            $strFullPath = _realpath_."/".$strFilename;
        }

        if (uniSubstr($strFilename, -4) == ".tpl" && is_file($strFullPath)) {
            $strTemplateContent = file_get_contents($strFullPath);
        }
        else {
            $strTemplateContent = "Template ".$strTemplateFilename." not found!";
        }

        //Saving to the cache
        $this->arrCacheTemplates[$strHash] = $strTemplateContent;
        return $strTemplateContent;
    }


    private function getPathForTemplate($strTemplate)
    {
        $strName = class_resourceloader::getInstance()->getTemplate($strTemplate, true);
        return $strName;
    }


}

