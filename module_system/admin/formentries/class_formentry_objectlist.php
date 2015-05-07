<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * An list of objects which can be added or removed.
 *
 * @author christoph.kappestein@gmail.com
 * @since 4.7
 * @package module_formgenerator
 */
class class_formentry_objectlist extends class_formentry_multiselect {

    protected $strAddLink;

    public function setStrAddLink($strAddLink)
    {
        $this->strAddLink = $strAddLink;
    }

    /**
     * Renders the field itself.
     * In most cases, based on the current toolkit.
     *
     * @return string
     */
    public function renderField()
    {
        $objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
        $strReturn = "";
        if($this->getStrHint() != null) {
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());
        }

        $strReturn.= $objToolkit->formInputObjectList($this->getStrEntryName(), $this->getStrLabel(), $this->arrKeyValues, $this->strAddLink);
        return $strReturn;
    }

    public function validateValue()
    {
        $arrIds = explode(",", $this->getStrValue());
        foreach($arrIds as $strId) {
            if(!validateSystemid($strId)) {
                return false;
            }
        }

        return true;
    }
}
