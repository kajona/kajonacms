<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;


/**
 * An list of tags which can be added or removed.
 *
 * @author christoph.kappestein@gmail.com
 * @since 4.7
 * @package module_formgenerator
 */
class FormentryTageditor extends class_formentry_multiselect {

    protected $strOnChangeCallback;

    public function setOnChangeCallback($strOnChangeCallback) {
        $this->strOnChangeCallback = $strOnChangeCallback;

        return $this;
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

        $strReturn.= $objToolkit->formInputTagEditor($this->getStrEntryName(), $this->getStrLabel(), $this->arrKeyValues, $this->strOnChangeCallback);
        return $strReturn;
    }

    public function setValueToObject()
    {
        $objSourceObject = $this->getObjSourceObject();
        if($objSourceObject == null)
            return "";

        $objReflection = new class_reflection($objSourceObject);
        $strSetter = $objReflection->getSetter($this->getStrSourceProperty());
        if($strSetter === null)
            throw new class_exception("unable to find setter for value-property ".$this->getStrSourceProperty()."@".get_class($objSourceObject), class_exception::$level_ERROR);

        return $objSourceObject->{$strSetter}(json_encode(explode(",", $this->getStrValue())));
    }

    public function validateValue()
    {
        $arrValues = explode(",", $this->getStrValue());
        foreach($arrValues as $strValue) {
            $strValue = trim($strValue);
            if(empty($strValue)) {
                return false;
            }
        }

        return true;
    }

    public function getValueAsText() {
        return implode(", ", $this->arrKeyValues);
    }
}
