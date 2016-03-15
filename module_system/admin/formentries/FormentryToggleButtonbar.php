<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;

use Kajona\System\System\Carrier;


/**
 * Returns a toggle button bar which can be used in the same way as an multiselect
 *
 * @author christoph.kappestein@gmail.com
 * @since 4.7
 * @package module_formgenerator
 */
class FormentryToggleButtonbar extends FormentryMultiselect {

    protected $strType = "checkbox";

    /**
     * @return string
     */
    public function getStrType()
    {
        return $this->strType;
    }

    /**
     * The type either "checkbox" or "radio"
     *
     * @param string $strType
     */
    public function setStrType($strType)
    {
        $this->strType = $strType;

        return $this;
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
        if($this->getStrHint() != null) {
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());
        }

        $arrSelectedKeys = array();
        if($this->getStrValue() !== "" && $this->getStrValue() !== null) {
            $arrSelectedKeys = explode(",", $this->getStrValue());
        }
        $strReturn .= $objToolkit->formToggleButtonBar($this->getStrEntryName(), $this->arrKeyValues, $this->getStrLabel(), $arrSelectedKeys, !$this->getBitReadonly(), $this->strType);
        return $strReturn;
    }

}
