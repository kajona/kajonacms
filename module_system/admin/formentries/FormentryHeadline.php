<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;

use Kajona\System\Admin\FormentryPrintableInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\Validators\DummyValidator;


/**
 * A fieldset may be used to group content
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_formgenerator
 */
class FormentryHeadline extends FormentryBase implements FormentryPrintableInterface {

    protected $strLevel = "h2";
    protected $strClass = "";

    public function __construct($strName = "") {
        if($strName == "")
            $strName = generateSystemid();
        parent::__construct("", $strName);

        //set the default validator
        $this->setObjValidator(new DummyValidator());
    }

    /**
     * Renders the field itself.
     * In most cases, based on the current toolkit.
     *
     * @return string
     */
    public function renderField() {
        $objToolkit = Carrier::getInstance()->getObjToolkit("admin");
        return $objToolkit->formHeadline($this->getStrValue(), $this->getStrClass(), $this->getStrLevel());
    }

    public function updateLabel($strKey = "") {
        return "";
    }

    /**
     * Returns a textual representation of the formentries' value.
     * May contain html, but should be stripped down to text-only.
     *
     * @return string
     */
    public function getValueAsText() {
        return Carrier::getInstance()->getObjToolkit("admin")->formHeadline($this->getStrValue());
    }

    /**
     * @return string
     */
    public function getStrLevel()
    {
        return $this->strLevel;
    }

    /**
     * @param string $strLevel
     *
     * @return $this
     */
    public function setStrLevel($strLevel)
    {
        $this->strLevel = $strLevel;
        return $this;
    }

    /**
     * @return string
     */
    public function getStrClass()
    {
        return $this->strClass;
    }

    /**
     * @param string $strClass
     */
    public function setStrClass($strClass)
    {
        $this->strClass = $strClass;
    }
}
