<?php
/*"******************************************************************************************************
*   (c) 2010-2015 ARTEMEON                                                                              *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                         *
********************************************************************************************************/

/**
 * A tag editor with autocomplete object selection
 *
 * @author christoph.kappestein@gmail.com
 * @since 4.7
 * @package module_formgenerator
 */
class class_formentry_objecttags extends class_formentry_tageditor
{
    protected $strSource;
    protected $objFactory;

    /**
     * @param string $strSource
     */
    public function setStrSource($strSource)
    {
        $this->strSource = $strSource;

        return $this;
    }

    public function renderField()
    {
        $objToolkit = class_carrier::getInstance()->getObjToolkit("admin");
        $strReturn = "";
        if($this->getStrHint() != null) {
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());
        }

        $strReturn.= $objToolkit->formInputObjectTags($this->getStrEntryName(), $this->getStrLabel(), $this->strSource, $this->arrKeyValues, $this->strOnChangeCallback);
        return $strReturn;
    }

    protected function updateValue()
    {
        $arrParams = class_carrier::getAllParams();
        if (isset($arrParams[$this->getStrEntryName()."_id"])) {
            $this->setStrValue($arrParams[$this->getStrEntryName()."_id"]);
        } else {
            $this->setStrValue($this->getValueFromObject());
        }
    }

    public function setStrValue($strValue)
    {
        $arrValuesIds = array();
        if (is_array($strValue) || $strValue instanceof Traversable) {
            foreach ($strValue as $objValue) {
                if ($objValue instanceof class_model) {
                    $arrValuesIds[] = $objValue->getStrSystemid();
                }
                else {
                    $arrValuesIds[] = $objValue;
                }
            }
        }
        $strValue = implode(",", $arrValuesIds);

        $objReturn = parent::setStrValue($strValue);
        $this->setArrKeyValues($this->toObjectArray());

        return $objReturn;
    }

    private function toObjectArray()
    {
        $strValue = $this->getStrValue();
        if (!empty($strValue)) {
            $arrIds = explode(",", $strValue);
            $arrObjects = array_map(function ($strId) {
                $objObject = class_objectfactory::getInstance()->getObject($strId);
                if (empty($objObject)) {
                    // @TODO fix better handling user ids
                    $objObject = new class_module_user_user($strId);
                }
                return $objObject;
            }, $arrIds);
            return $arrObjects;
        }

        return array();
    }
}
