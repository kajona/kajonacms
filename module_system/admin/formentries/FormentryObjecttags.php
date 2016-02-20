<?php
/*"******************************************************************************************************
*   (c) 2010-2015 ARTEMEON                                                                              *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                         *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;

use Kajona\System\System\Carrier;
use Kajona\System\System\Exception;
use Kajona\System\System\Model;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\UserUser;
use Traversable;


/**
 * A tag editor with autocomplete object selection
 *
 * @author christoph.kappestein@gmail.com
 * @since 4.7
 * @package module_formgenerator
 */
class FormentryObjecttags extends FormentryTageditor
{
    const TYPE_USER = 1;
    const TYPE_OBJECT = 2;

    protected $strSource;
    protected $intType = self::TYPE_USER;

    /**
     * @param string $strSource
     */
    public function setStrSource($strSource)
    {
        $this->strSource = $strSource;

        return $this;
    }

    /**
     * @param integer $intType
     */
    public function setIntType($intType)
    {
        $this->intType = $intType;

        return $this;
    }

    public function renderField()
    {
        $objToolkit = Carrier::getInstance()->getObjToolkit("admin");
        $strReturn = "";
        if($this->getStrHint() != null) {
            $strReturn .= $objToolkit->formTextRow($this->getStrHint());
        }

        $strReturn.= $objToolkit->formInputObjectTags($this->getStrEntryName(), $this->getStrLabel(), $this->strSource, $this->arrKeyValues, $this->strOnChangeCallback);
        return $strReturn;
    }

    /**
     * The normal field contains the actual display names which are shown in each tag. The _id field contains an array
     * of corresponding systemids
     *
     * @throws Exception
     */
    protected function updateValue()
    {
        $arrParams = Carrier::getAllParams();
        if (isset($arrParams[$this->getStrEntryName()."_id"])) {
            $this->setStrValue($arrParams[$this->getStrEntryName()."_id"]);
        } else {
            $this->setStrValue($this->getValueFromObject());
        }
    }

    /**
     * The value is either an array of objects or systemids. We normalize the value so that arrKeyValues always contains
     * an array of objects
     *
     * @param $strValue
     * @return FormentryBase
     */
    public function setStrValue($strValue)
    {
        $arrValuesIds = array();
        if (is_array($strValue) || $strValue instanceof Traversable) {
            foreach ($strValue as $objValue) {
                if ($objValue instanceof Model) {
                    $arrValuesIds[] = $objValue->getStrSystemid();
                }
                else {
                    $arrValuesIds[] = $objValue;
                }
            }
        }
        $strValue = implode(",", $arrValuesIds);

        $objReturn = parent::setStrValue($strValue);
        $this->setArrKeyValues($this->toObjectArray());

        return $objReturn;
    }

    /**
     * Converts an array of systemids to objects
     *
     * @return array
     */
    private function toObjectArray()
    {
        $strValue = $this->getStrValue();
        if (!empty($strValue)) {
            $arrIds = explode(",", $strValue);
            $intType = $this->intType;
            $arrObjects = array_map(function ($strId) use ($intType) {
                $objObject = null;
                if ($intType === FormentryObjecttags::TYPE_USER) {
                    $objObject = new UserUser($strId);
                } elseif ($intType === FormentryObjecttags::TYPE_OBJECT) {
                    $objObject = Objectfactory::getInstance()->getObject($strId);
                }
                return $objObject;
            }, $arrIds);
            return $arrObjects;
        }

        return array();
    }
}
