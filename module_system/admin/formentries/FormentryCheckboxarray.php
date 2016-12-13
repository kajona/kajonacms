<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;

use ArrayObject;
use Kajona\System\Admin\FormentryPrintableInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\Validators\DummyValidator;

/**
 * A formelement rendering an array of checkboxes.
 * Requires both, a set of possible options and the set of options currently selected.
 *
 * @author sidler@mulchprod.de
 * @since 4.8
 * @package module_formgenerator
 */
class FormentryCheckboxarray extends FormentryBase implements FormentryPrintableInterface
{

    const TYPE_CHECKBOX = 1;
    const TYPE_RADIO = 2;

    private $intType = 1;
    private $bitInline = false;
    private $arrKeyValues = array();

    public function __construct($strFormName, $strSourceProperty, $objSourceObject = null)
    {
        parent::__construct($strFormName, $strSourceProperty, $objSourceObject);

        //set the default validator
        $this->setObjValidator(new DummyValidator());
    }

    public function setIntType($intType)
    {
        $this->intType = $intType;

        return $this;
    }

    public function setBitInline($bitInline)
    {
        $this->bitInline = $bitInline;

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
        $objToolkit = Carrier::getInstance()->getObjToolkit("admin");
        $strReturn = "";
        if ($this->getStrHint() != null) {
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());
        }

        $strReturn .= $objToolkit->formInputCheckboxArray($this->getStrEntryName(), $this->getStrLabel(), $this->intType, $this->arrKeyValues, $this->getStrValue(), $this->bitInline, $this->getBitReadonly());
        $strReturn .= $objToolkit->formInputHidden($this->getStrEntryName()."_prescheck", "1");



        return $strReturn;
    }

    /**
     * @param $strValue
     *
     * @return FormentryBase
     */
    public function setStrValue($strValue)
    {
        $arrTargetValues = array();

        if ((is_array($strValue) || $strValue instanceof ArrayObject) && count($strValue) > 0) {
            foreach ($strValue as $strKey => $strSingleValue) {
                //DB vals
                if (is_object($strSingleValue)) {
                    $arrTargetValues[] = $strSingleValue->getSystemid();
                } //POST vals
                elseif ($strSingleValue == "checked" || $strSingleValue == "on") {//on = from generic list
                    $arrTargetValues[] = $strKey;
                }
            }
        }

        return parent::setStrValue($arrTargetValues);
    }

    /**
     * @inheritDoc
     */
    protected function updateValue()
    {
        $arrParams = Carrier::getAllParams();
        if (isset($arrParams[$this->getStrEntryName()])) {
            $this->setStrValue($arrParams[$this->getStrEntryName()]);
        } elseif (isset($arrParams[$this->getStrEntryName()."_prescheck"])) {
            $this->setStrValue(array());
        } else {
            $this->setStrValue($this->getValueFromObject());
        }
    }


    /**
     * @return array
     */
    public function getArrKeyValues()
    {
        return $this->arrKeyValues;
    }

    /**
     * @param array $arrKeyValues
     *
     * @return $this
     */
    public function setArrKeyValues($arrKeyValues)
    {
        $this->arrKeyValues = $arrKeyValues;

        return $this;
    }

    /**
     * Returns a textual representation of the formentries' value.
     * May contain html, but should be stripped down to text-only.
     *
     * @return string
     */
    public function getValueAsText()
    {
        $arrNew = array();
        foreach ($this->getStrValue() as $strOneId) {
            if (validateSystemid($strOneId)) {
                $arrNew[] = Objectfactory::getInstance()->getObject($strOneId)->getStrDisplayName();
            } else {
                $arrNew[] = $this->arrKeyValues[$strOneId];
            }
        }
        return implode("<br />", $arrNew);
    }

}
