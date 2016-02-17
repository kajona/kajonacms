<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;

use Kajona\System\Admin\FormentryInterface;
use Kajona\System\Admin\FormentryPrintableInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\Exception;
use Kajona\System\System\Reflection;


/**
 * A formelement which provides an div container. The container can optional contain other formentry elements.
 *
 * @author  christoph.kappestein@gmail.com
 * @since   4.8
 * @package module_formgenerator
 */
class FormentryContainer extends FormentryBase implements FormentryPrintableInterface {

    protected $arrFields = array();
    protected $strOpener = "";

    public function __construct($strFormName, $strSourceProperty)
    {
        parent::__construct($strFormName, $strSourceProperty);
    }

    /**
     * @param FormentryInterface $formentry
     * @return FormentryBase|FormentryInterface
     */
    public function addField(FormentryBase $objField, $strKey = "")
    {
        if($strKey == "")
            $strKey = $objField->getStrEntryName();

        $this->arrFields[$strKey] = $objField;

        return $objField;
    }

    /**
     * Renders the field itself.
     * In most cases, based on the current toolkit.
     *
     * @return string
     */
    public function renderField() {
        $objToolkit = Carrier::getInstance()->getObjToolkit("admin");
        $strReturn = "";
        if($this->getStrHint() != null)
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());

        $arrFields = array();
        foreach($this->arrFields as $objField) {
            /** @var FormentryInterface $objField */
            $arrFields[] = $objField->renderField();
        }

        $strReturn.= $objToolkit->formInputContainer($this->getStrEntryName(), $this->getStrLabel(), $arrFields, $this->strOpener);

        return $strReturn;
    }

    public function setValueToObject()
    {
        $objSourceObject = $this->getObjSourceObject();
        if($objSourceObject == null)
            return "";

        $objReflection = new Reflection($objSourceObject);
        $strSetter = $objReflection->getSetter($this->getStrSourceProperty());
        if($strSetter === null)
            throw new Exception("unable to find setter for value-property ".$this->getStrSourceProperty()."@".get_class($objSourceObject), Exception::$level_ERROR);

        return $objSourceObject->{$strSetter}(json_encode($this->getStrValue()));
    }

    public function validateValue() {
        return true;
    }

    /**
     * Returns a textual representation of the formentries' value.
     * May contain html, but should be stripped down to text-only.
     *
     * @return string
     */
    public function getValueAsText() {
        $arrFields = array();
        foreach($this->arrFields as $objField) {
            /** @var FormentryPrintableInterface $objField */
            if($objField instanceof FormentryPrintableInterface) {
                $arrFields[] = $objField->getValueAsText();
            }
        }
        return implode(", ", $arrFields);
    }

    /**
     * @return string
     */
    public function getStrOpener()
    {
        return $this->strOpener;
    }

    /**
     * @param string $strOpener
     */
    public function setStrOpener($strOpener)
    {
        $this->strOpener = $strOpener;

        return $this;
    }
}
