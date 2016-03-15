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
 * A yes-no field renders a dropdown containing one entry for yes and one for no.
 * 0 is no whereas 1 is rendered as yes.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_formgenerator
 */
class FormentryYesno extends FormentryBase implements FormentryPrintableInterface
{
    protected $strAddons;

    public function __construct($strFormName, $strSourceProperty, $objSourceObject = null) {
        parent::__construct($strFormName, $strSourceProperty, $objSourceObject);

        //set the default validator
        $this->setObjValidator(new TextValidator());
    }

    public function setAddons($strAddons)
    {
        $this->strAddons = $strAddons;
    }

    /**
     * Renders the field itself.
     * In most cases, based on the current toolkit.
     *
     * @return string
     */
    public function renderField() {
        $objToolkit = Carrier::getInstance()->getObjToolkit("admin");
        $objLang = Carrier::getInstance()->getObjLang();
        $arrYesNo = array(
            0 => $objLang->getLang("commons_no", "system"), 1 => $objLang->getLang("commons_yes", "system")
        );

        $strReturn = "";
        if($this->getStrHint() != null)
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());
        $strReturn .=  $objToolkit->formInputDropdown($this->getStrEntryName(), $arrYesNo, $this->getStrLabel(), $this->getStrValue(), "", !$this->getBitReadonly(), $this->strAddons);
        return $strReturn;
    }

    /**
     * Returns a textual representation of the formentries' value.
     * May contain html, but should be stripped down to text-only.
     *
     * @return string
     */
    public function getValueAsText() {
        if($this->getStrValue())
            return Carrier::getInstance()->getObjLang()->getLang("commons_yes", "system");
        else
            return Carrier::getInstance()->getObjLang()->getLang("commons_no", "system");
    }

    public function validateValue() {
        return in_array($this->getStrValue(), array(0,1));
    }

}
