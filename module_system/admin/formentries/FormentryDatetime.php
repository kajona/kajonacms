<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;

use Kajona\System\System\Carrier;
use Kajona\System\System\Date;


/**
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_formgenerator
 */
class FormentryDatetime extends FormentryDate {


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

        $objDate = null;
        if($this->getStrValue() instanceof Date)
            $objDate = $this->getStrValue();
        elseif($this->getStrValue() != "")
            $objDate = new Date($this->getStrValue());

        if($this->getBitReadonly()) {
            $strReturn .= $objToolkit->formInputText($this->getStrEntryName(), $this->getStrLabel(), dateToString($objDate, false), "", "", true);
        }
        else {
            $strReturn .= $objToolkit->formDateSingle($this->getStrEntryName(), $this->getStrLabel(), $objDate, "inputDate", true, $this->getBitReadonly());
        }


        return $strReturn;
    }

    /**
     * Returns a textual representation of the formentries' value.
     * May contain html, but should be stripped down to text-only.
     *
     * @return string
     */
    public function getValueAsText() {
        $objDate = null;
        if($this->getStrValue() instanceof Date)
            $objDate = $this->getStrValue();
        elseif($this->getStrValue() != "")
            $objDate = new Date($this->getStrValue());

        if($objDate != null)
            return dateToString($objDate, true);

        return "";
    }

}
