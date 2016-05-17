<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;

use Kajona\System\Admin\FormentryPrintableInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\Reflection;
use Kajona\System\System\Validators\TextValidator;


/**
 * @author sidler@mulchprod.de
 * @since 4.3
 * @package module_formgenerator
 */
class FormentryWysiwyg extends FormentryBase implements FormentryPrintableInterface {
    
    protected $strToolbarset = "standard";


    const STR_CONFIG_ANNOTATION = "@wysiwygConfig";


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


        if ($this->getObjSourceObject() != null && $this->getStrSourceProperty() != "") {
            $objReflection = new Reflection($this->getObjSourceObject());

            //try to find the matching source property
            $strSourceProperty = $this->getCurrentProperty(self::STR_CONFIG_ANNOTATION);
            if ($strSourceProperty != null) {
                $this->strToolbarset = $objReflection->getAnnotationValueForProperty($strSourceProperty, self::STR_CONFIG_ANNOTATION);
            }
        }
        
        
        
        $objToolkit = Carrier::getInstance()->getObjToolkit("admin");
        $strReturn = "";
        if($this->getStrHint() != null)
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());

        $strReturn .= $objToolkit->formWysiwygEditor($this->getStrEntryName(), $this->getStrLabel(), $this->getStrValue(), $this->strToolbarset);

        return $strReturn;
    }

    public function setValueToObject() {
        $strOldValue = $this->getStrValue();
        $this->setStrValue(processWysiwygHtmlContent($this->getStrValue()));
        $bitReturn = parent::setValueToObject();
        $this->setStrValue($strOldValue);
        return $bitReturn;
    }





    /**
     * Returns a textual representation of the formentries' value.
     * May contain html, but should be stripped down to text-only.
     *
     * @return string
     */
    public function getValueAsText() {
        return $this->getStrValue();
    }

}
