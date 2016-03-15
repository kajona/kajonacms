<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\News\System\Validators;

use Kajona\News\System\NewsNews;
use Kajona\System\System\Carrier;
use Kajona\System\System\ObjectvalidatorHelper;
use Kajona\System\System\Validators\ObjectvalidatorBase;

/**
 * Validates a news start/end/archive date for a correct logical order.
 *
 * @package module_news
 * @author stefan.meyer1@yahoo.de
 * @since 4.6
 */
class NewsNewsObjectvalidator extends ObjectvalidatorBase {

    /**
     * Validates a news start/end/archive date for a correct logical order.
     *
     *
     * @param \Kajona\System\System\Model $objObject - the model object to the given form
     * @return bool
     */
    public function validateObject(\Kajona\System\System\Model $objObject) {
        $objLang = Carrier::getInstance()->getObjLang();
        $strModuleName = $objObject->getArrModule("modul");

        if($objObject instanceof NewsNews) {

            //validate: $objStartDate < $objSpecialDate < $objEndDate
            $objStartDate = $objObject->getObjStartDate();
            $objEndDate = $objObject->getObjEndDate();
            $objSpecialDate = $objObject->getObjSpecialDate();

            $strLabelStartDate = $objLang->getLang("form_".$objObject->getArrModule("modul")."_datestart", $strModuleName);
            $strLabelEndDate = $objLang->getLang("form_".$objObject->getArrModule("modul")."_dateend", $strModuleName);
            $strLabelSpecialDate = $objLang->getLang("form_".$objObject->getArrModule("modul")."_datespecial", $strModuleName);



            if($objStartDate!= null && $objEndDate != null) {
                if(ObjectvalidatorHelper::compareDates($objStartDate, $objEndDate) === 1) {
                    $this->addValidationError("startdate", $objLang->getLang("commons_object_validator_datecompare_validationmessage_before", $strModuleName, array($strLabelStartDate, $strLabelEndDate)));
                }
            }
            if($objSpecialDate!= null && $objEndDate != null) {
                if(ObjectvalidatorHelper::compareDates($objSpecialDate, $objEndDate) === 1) {
                    $this->addValidationError("startdate", $objLang->getLang("commons_object_validator_datecompare_validationmessage_before", $strModuleName, array($strLabelSpecialDate, $strLabelEndDate)));
                }
            }
            if($objStartDate!= null && $objSpecialDate != null) {
                if(ObjectvalidatorHelper::compareDates($objStartDate, $objSpecialDate) === 1) {
                    $this->addValidationError("startdate", $objLang->getLang("commons_object_validator_datecompare_validationmessage_before", $strModuleName, array($strLabelStartDate, $strLabelSpecialDate)));
                }
            }
        }
        else
            return false;

        return count($this->getArrValidationMessages()) == 0;
    }
}