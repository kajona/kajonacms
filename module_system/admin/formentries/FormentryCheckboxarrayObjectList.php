<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;

use Kajona\System\System\AdminskinHelper;
use Kajona\System\System\ArraySectionIterator;
use Kajona\System\System\Carrier;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\StringUtil;


/**
 * A formelement rendering an array of checkboxes.
 * Requires both, a set of possible options and the set of options currently selected.
 *
 * @author sidler@mulchprod.de
 * @since 4.8
 * @package module_formgenerator
 */
class FormentryCheckboxarrayObjectList extends FormentryCheckboxarray {

    public function __construct($strFormName, $strSourceProperty, $objSourceObject = null) {
        parent::__construct($strFormName, $strSourceProperty, $objSourceObject);
    }

    public function renderField()
    {
        $objToolkit = Carrier::getInstance()->getObjToolkit("admin");
        $strReturn = "";
        if($this->getStrHint() != null)
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());

        $strReturn .= $objToolkit->formInputCheckboxArrayObjectList($this->getStrEntryName(), $this->getStrLabel(), $this->getAvailableItems(), $this->getSelectedItems(), $this->getBitReadonly());

        return $strReturn;

    }

    /**
     * Returns the selection items in the list
     *
     * @return array|null
     */
    private function getSelectedItems() {
        $arrSelectedItems = is_array($this->getStrValue()) ? $this->getStrValue() : array();
        return $arrSelectedItems;
    }

    /**
     * Returns the available items for the list
     *
     * @return array
     */
    private function getAvailableItems() {
        return $this->getArrKeyValues();
    }
}
