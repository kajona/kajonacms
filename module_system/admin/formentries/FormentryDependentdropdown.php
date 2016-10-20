<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;

use Kajona\System\Admin\FormentryPrintableInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\Reflection;
use Kajona\System\System\StringUtil;
use Kajona\System\System\Validators\TextValidator;


/**
 * The dependent dropdown is only useful in combination with a masterdropdown.
 *
 *
 * @author sidler@mulchprod.de
 * @since 4.6
 * @package module_formgenerator
 */
class FormentryDependentdropdown extends FormentryBase implements FormentryPrintableInterface {

    const STR_VALUE_ANNOTATION = "@fieldValuePrefix";

    public function __construct($strFormName, $strSourceProperty, $objSourceObject = null) {
        parent::__construct($strFormName, $strSourceProperty, $objSourceObject);

        //set the default validator
        $this->setObjValidator(new TextValidator());
    }

    /**
     * Renders the field itself.
     * In most cases, based on the current toolkit.
     *
     * @return string
     */
    public function renderField() {

        $objToolkit = Carrier::getInstance()->getObjToolkit("admin");
        return $objToolkit->formInputDropdown($this->getStrEntryName(), array(), $this->getStrLabel(), "", "", !$this->getBitReadonly(), " data-kajona-selected='".$this->getStrValue()."' ");
    }



    /**
     * Returns a textual representation of the formentries' value.
     * May contain html, but should be stripped down to text-only.
     *
     * @return string
     */
    public function getValueAsText() {

        //load all matching and possible values based on the prefix
        if($this->getObjSourceObject() == null || $this->getStrSourceProperty() == "")
            return $this->getStrValue(). " Error: No target object mapped or missing @fieldValuePrefix annotation!";



        $objReflection = new Reflection($this->getObjSourceObject());

        //try to find the matching source property
        $arrProperties = $objReflection->getPropertiesWithAnnotation(self::STR_VALUE_ANNOTATION);
        $strSourceProperty = null;
        foreach($arrProperties as $strPropertyName => $strValue) {
            if(StringUtil::substring(uniStrtolower($strPropertyName), (StringUtil::length($this->getStrSourceProperty()))*-1) == $this->getStrSourceProperty())
                $strSourceProperty = $strPropertyName;
        }

        if($strSourceProperty == null)
            return $this->getStrValue();

        $strPrefix = trim($objReflection->getAnnotationValueForProperty($strSourceProperty, self::STR_VALUE_ANNOTATION));
        if($this->getStrValue() !== null && $this->getStrValue() !== "") {
            return $this->getObjSourceObject()->getLang($strPrefix.$this->getStrValue());
        }

        return "";
    }








}
