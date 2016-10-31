<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pages\Admin\Formentries;

use Kajona\System\Admin\Formentries\FormentryBase;
use Kajona\System\Admin\FormentryInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\Reflection;
use Kajona\System\System\Resourceloader;
use Kajona\System\System\Validators\TextValidator;


/**
 * A yes-no field renders a dropdown containing a list of entries.
 * Make sure to pass the list of possible entries before rendering the form.
 *
 * @author sidler@mulchprod.de
 * @since 4.3
 */
class FormentryTemplate extends FormentryBase implements FormentryInterface
{

    /**
     * the path to the folder of matching templates
     */
    const STR_TEMPLATEDIR_ANNOTATION = "@fieldTemplateDir";


    private $arrKeyValues = array();
    private $strAddons = "";

    public function __construct($strFormName, $strSourceProperty, $objSourceObject = null)
    {
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
    public function renderField()
    {
        $objToolkit = Carrier::getInstance()->getObjToolkit("admin");
        $strReturn = "";
        if ($this->getStrHint() != null) {
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());
        }

        if (count($this->arrKeyValues) == 1 && $this->getStrValue() == "") {
            $arrKeys = array_keys($this->arrKeyValues);
            $this->setStrValue($arrKeys[0]);
        }


        $strReturn .= $objToolkit->formInputDropdown($this->getStrEntryName(), $this->arrKeyValues, $this->getStrLabel(), $this->getStrValue(), "", !$this->getBitReadonly(), $this->getStrAddons());
        return $strReturn;
    }

    /**
     * Overwritten in order to load key-value pairs declared by annotations
     */
    protected function updateValue()
    {
        parent::updateValue();

        if ($this->getObjSourceObject() != null && $this->getStrSourceProperty() != "") {
            $objReflection = new Reflection($this->getObjSourceObject());

            //try to find the matching source property
            $arrProperties = $objReflection->getPropertiesWithAnnotation(self::STR_TEMPLATEDIR_ANNOTATION);
            $strSourceProperty = null;

            foreach ($arrProperties as $strPropertyName => $strValue) {
                if (uniSubstr(uniStrtolower($strPropertyName), (uniStrlen($this->getStrSourceProperty())) * -1) == $this->getStrSourceProperty()) {
                    $strSourceProperty = $strPropertyName;
                }
            }

            if ($strSourceProperty == null) {
                return;
            }

            $strTemplateDir = $objReflection->getAnnotationValueForProperty($strSourceProperty, self::STR_TEMPLATEDIR_ANNOTATION);

            //load templates, array_reverse so that the template-pack entry is handled as the last entry overwriting those from the default module
            $arrTemplates = array_reverse(Resourceloader::getInstance()->getTemplatesInFolder($strTemplateDir, true));
            $arrTemplatesDD = array();
            if (count($arrTemplates) > 0) {
                foreach ($arrTemplates as $strPath => $strTemplate) {
                    $arrTemplatesDD[$strTemplate] = $strTemplate . " (" .$strPath.")";
                }
            }
            $this->setArrKeyValues($arrTemplatesDD);

        }
    }


    /**
     * @param $arrKeyValues
     *
     * @return FormentryTemplate
     */
    public function setArrKeyValues($arrKeyValues)
    {
        $this->arrKeyValues = $arrKeyValues;
        return $this;
    }

    public function getArrKeyValues()
    {
        return $this->arrKeyValues;
    }

    /**
     * @param string $strAddons
     *
     * @return FormentryTemplate
     */
    public function setStrAddons($strAddons)
    {
        $this->strAddons = $strAddons;
        return $this;
    }

    /**
     * @return string
     */
    public function getStrAddons()
    {
        return $this->strAddons;
    }


}
