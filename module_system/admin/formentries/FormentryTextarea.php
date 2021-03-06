<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;

use Kajona\System\Admin\FormentryPrintableInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\Validators\TextValidator;


/**
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_formgenerator
 */
class FormentryTextarea extends FormentryBase implements FormentryPrintableInterface {

    private $strOpener = "";
    private $bitLarge = false;
    private $intNumberOfRows = 4;

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
        $strReturn = "";
        if($this->getStrHint() != null)
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());

        $strReturn .= $objToolkit->formInputTextArea($this->getStrEntryName(), $this->getStrLabel(), $this->getStrValue(), $this->bitLarge ? "input-large" : "", $this->getBitReadonly(), $this->getIntNumberOfRows());

        return $strReturn;
    }

    /**
     * Returns a textual representation of the formentries' value.
     * May contain html, but should be stripped down to text-only.
     *
     * @return string
     */
    public function getValueAsText() {
        return nl2br($this->getStrValue());
    }

    /**
     * @param $strOpener
     * @return FormentryText
     */
    public function setStrOpener($strOpener) {
        $this->strOpener = $strOpener;
        return $this;
    }

    public function getStrOpener() {
        return $this->strOpener;
    }

    /**
     * @param boolean $bitLarge
     *
     * @return $this
     */
    public function setBitLarge($bitLarge) {
        $this->bitLarge = $bitLarge;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getBitLarge() {
        return $this->bitLarge;
    }

    /**
     * @param int $intNumberOfRows
     */
    public function setIntNumberOfRows($intNumberOfRows) {
        $this->intNumberOfRows = $intNumberOfRows;
        return $this;
    }

    /**
     * @return int
     */
    public function getIntNumberOfRows() {
        return $this->intNumberOfRows;
    }




}
