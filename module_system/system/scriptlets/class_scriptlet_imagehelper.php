<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: interface_validator.php 4462 2012-01-22 17:11:20Z sidler $                               *
********************************************************************************************************/

/**
 * The imagehelper converts image-placeholders to real urls.
 * The syntax is
 *  [img,path_to_file,maxWidth,maxHeight]
 *
 *
 * @package module_system
 * @since 4.0
 * @author sidler@mulchprod.de
 */
class class_scriptlet_imagehelper implements interface_scriptlet {

    /**
     * Processes the content.
     * Make sure to return the string again, otherwise the output will remain blank.
     *
     * @param string $strContent
     *
     * @return string
     */
    public function processContent($strContent) {

        if(_admin_)
            return $strContent;


        $arrTemp = array();
        preg_match_all("#\[img,([+%A-Za-z0-9_\./\\\]+),([0-9]+),([0-9]+)(,fixed|,max|)\]#i", $strContent, $arrTemp);

        foreach($arrTemp[0] as $intKey => $strSearchString) {

            if(isset($arrTemp[4][$intKey]) && $arrTemp[4][$intKey] == ",fixed") {
                $strContent = uniStrReplace(
                    $strSearchString,
                    _webpath_."/image.php?image=".urlencode($arrTemp[1][$intKey])."&amp;fixedWidth=".$arrTemp[2][$intKey]."&amp;fixedHeight=".$arrTemp[3][$intKey],
                    $strContent
                );
            }
            else {
                $strContent = uniStrReplace(
                    $strSearchString,
                    _webpath_."/image.php?image=".urlencode($arrTemp[1][$intKey])."&amp;maxWidth=".$arrTemp[2][$intKey]."&amp;maxHeight=".$arrTemp[3][$intKey],
                    $strContent
                );
            }
        }

        //fast way, no urlencode required
        //$strContent = preg_replace("#\[img,([A-Za-z0-9_\./\\\]+),([0-9]+),([0-9]+)\]#i", _webpath_."/image.php?image=\${1}&amp;maxWidth=\${2}&amp;maxHeight=\${3}", $strContent);


        return $strContent;
    }

}
