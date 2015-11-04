<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Used to manage kajona-placeholders within a template
 *
 * @package module_system
 * @author sidler@mulchprod.de
 * @since 5.0
 *
 */
class class_template_placeholder_parser
{

    /**
     * Deletes placeholder in the string
     *
     * @param string $strTemplate
     *
     * @return string
     */
    public function deletePlaceholder($strTemplate)
    {
        return preg_replace("^%%([A-Za-z0-9_\|]*)%%^", "", $strTemplate);
    }


    /**
     * Checks if the template referenced by the identifier contains the placeholder provided
     * by the second param.
     *
     * @param string $strIdentifier
     * @param string $strPlaceholdername
     *
     * @return bool
     */
    public function containsPlaceholder($strTemplate, $strPlaceholdername)
    {

        $arrElements = $this->getElements($strTemplate);
        foreach ($arrElements as $arrSinglePlaceholder) {
            if ($arrSinglePlaceholder["placeholder"] == $strPlaceholdername) {
                return true;
            }
        }

        return false;
    }


    /**
     * Returns the elements in a given template
     *
     * @param string $strIdentifier
     * @param int $intMode 0 = regular page, 1 = master page
     *
     * @return mixed
     */
    public function getElements($strTemplate, $intMode = 0)
    {
        $arrReturn = array();

        //search placeholders
        $arrTemp = array();

        preg_match_all("'(%%([A-Za-z0-9_]+?))+?\_([A-Za-z0-9_\|]+?)%%'i", $strTemplate, $arrTemp);

        $intCounter = 0;
        foreach ($arrTemp[0] as $strPlacehoder) {

            if (uniStrpos($strPlacehoder, "master") !== false && $intMode == class_template::INT_ELEMENT_MODE_REGULAR) {
                continue;
            }

            $strTemp = uniSubstr($strPlacehoder, 2, -2);
            $arrTemp = explode("_", $strTemp);
            //are there any pipes?
            if (uniStrpos($arrTemp[1], "|") !== false) {
                $arrElementTypes = explode("|", $arrTemp[1]);
                $intCount2 = 0;
                $arrReturn[$intCounter]["placeholder"] = $strTemp;

                foreach ($arrElementTypes as $strOneElementType) {
                    $arrReturn[$intCounter]["elementlist"][$intCount2]["name"] = $arrTemp[0];
                    $arrReturn[$intCounter]["elementlist"][$intCount2]["element"] = $strOneElementType;
                    $intCount2++;
                }
                $intCounter++;
            }
            else {
                $arrReturn[$intCounter]["placeholder"] = $strTemp;
                $arrReturn[$intCounter]["elementlist"][0]["name"] = $arrTemp[0];
                $arrReturn[$intCounter]["elementlist"][0]["element"] = $arrTemp[1];
                $intCounter++;
            }

        }

        return $arrReturn;
    }

}

