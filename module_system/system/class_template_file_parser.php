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
        if (uniSubstr($strFilename, -4) == ".tpl" && is_file($strFilename)) {
            $strTemplateContent = file_get_contents($strFilename);
        }
        else {
            $strTemplateContent = "Template ".$strTemplateFilename." not found!";
        }

        //search for includes
        preg_match_all("#\[KajonaTemplateInclude,([A-Za-z0-9_/\.]+)\]#i", $strTemplateContent, $arrTemp);

        foreach($arrTemp[0] as $intKey => $strSearchString) {
            $strTemplateContent = \Kajona\System\System\StringUtil::replace($strSearchString, $this->readTemplate($arrTemp[1][$intKey]), $strTemplateContent);
        }

        //Saving to the cache
        $this->arrCacheTemplates[$strHash] = $strTemplateContent;
        return $strTemplateContent;
    }


    private function getPathForTemplate($strTemplate)
    {
        $strName = null;
        foreach (class_classloader::getInstance()->getArrModules() as $strCorePath => $strOneModule) {

            if (is_dir(_realpath_."/".$strCorePath)) {
                echo "validating tpl path: "._realpath_."/".$strCorePath.$strTemplate."\n"; //TODO: temp debug
                if (is_file(_realpath_."/".$strCorePath.$strTemplate)) {
                    $strName = _realpath_."/".$strCorePath.$strTemplate;
                    break;
                }
            }
        }

        if($strName == null) {
            $strName = class_resourceloader::getInstance()->getTemplate($strTemplate, true);
        }
        return $strName;
    }


}
