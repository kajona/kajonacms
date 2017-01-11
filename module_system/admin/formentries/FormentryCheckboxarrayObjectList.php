<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;

use Kajona\System\System\Carrier;
use Kajona\System\System\Objectfactory;


/**
 * Formelement which lists objects
 *
 * @author stefan.meyer@mulchprod.de
 * @author christoph.kappestein@mulchprod.de
 * @since 5.1
 * @package module_system
 */
class FormentryCheckboxarrayObjectList extends FormentryCheckboxarray
{
    protected $bitShowPath = true;
    protected $bitPathCallback;
    protected $strAddLink;

    public function __construct($strFormName, $strSourceProperty, $objSourceObject = null)
    {
        parent::__construct($strFormName, $strSourceProperty, $objSourceObject);
    }

    public function setBitShowPath($bitShowPath)
    {
        $this->bitShowPath = $bitShowPath;
        return $this;
    }

    public function getBitShowPath()
    {
        return $this->bitShowPath;
    }

    public function setBitPathCallback($bitPathCallback)
    {
        $this->bitPathCallback = $bitPathCallback;
        return $this;
    }

    public function getBitPathCallback()
    {
        return $this->bitPathCallback;
    }

    public function setStrAddLink($strAddLink)
    {
        $this->strAddLink = $strAddLink;
    }

    public function getStrAddLink()
    {
        return $this->strAddLink;
    }

    public function renderField()
    {
        $objToolkit = Carrier::getInstance()->getObjToolkit("admin");
        $strReturn = "";
        if ($this->getStrHint() != null) {
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());
        }

        if (empty($this->getAvailableItems()) && !empty($this->getSelectedItems())) {
            $arrValues = array();
            foreach ($this->getSelectedItems() as $strOneId) {
                $arrValues[$strOneId] = Objectfactory::getInstance()->getObject($strOneId);
            }
            $this->setArrKeyValues($arrValues);
        }

        $strReturn .= $objToolkit->formInputCheckboxArrayObjectList($this->getStrEntryName(), $this->getStrLabel(), $this->getAvailableItems(), $this->getSelectedItems(), $this->getBitReadonly(), $this->getBitShowPath(), $this->getBitPathCallback(), $this->getStrAddLink());
        $strReturn .= $objToolkit->formInputHidden($this->getStrEntryName()."_prescheck", "1");

        return $strReturn;

    }

    /**
     * Returns the selection items in the list
     *
     * @return array|null
     */
    private function getSelectedItems()
    {
        $arrSelectedItems = is_array($this->getStrValue()) ? $this->getStrValue() : array();
        return $arrSelectedItems;
    }

    /**
     * Returns the available items for the list
     *
     * @return array
     */
    private function getAvailableItems()
    {
        return $this->getArrKeyValues();
    }
}
