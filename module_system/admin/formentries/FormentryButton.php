<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;

use Kajona\System\Admin\FormentryInterface;
use Kajona\System\System\Carrier;
use Kajona\System\System\Validators\DummyValidator;


/**
 * @author sidler@mulchprod.de
 * @since 4.3
 * @package module_formgenerator
 */
class FormentryButton extends FormentryBase implements FormentryInterface {

    private $strEventhandler = "";

    public function __construct($strFormName, $strSourceProperty = "", $objSourceObject = null) {
        parent::__construct($strFormName, $strSourceProperty, $objSourceObject);

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
        $strReturn = "";
        if($this->getStrHint() != null)
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());

        $strReturn .= $objToolkit->formInputSubmit($this->getStrLabel(), $this->getStrValue(), $this->getStrEventhandler(), "", !$this->getBitReadonly());

        return $strReturn;
    }

    /**
     * @param string $strEventhandler
     * @return FormentryButton
     */
    public function setStrEventhandler($strEventhandler) {
        $this->strEventhandler = $strEventhandler;
        return $this;
    }

    /**
     * @return string
     */
    public function getStrEventhandler() {
        return $this->strEventhandler;
    }




}
