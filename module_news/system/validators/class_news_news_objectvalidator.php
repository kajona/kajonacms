<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Validates a news start/end/archive date for a correct logical order.
 *
 * @package module_news
 * @author stefan.meyer1@yahoo.de
 * @since 4.6
 */
class class_news_news_objectvalidator extends class_objectvalidator_base {

    /**
     * Validates a news start/end/archive date for a correct logical order.
     *
     *
     * @param \Kajona\System\System\Model $objObject - the model object to the given form
     * @return bool
     */
    public function validateObject(\Kajona\System\System\Model $objObject) {
        $objLang = class_carrier::getInstance()->getObjLang();
        $strModuleName = $objObject->getArrModule("modul");

        if($objObject instanceof class_module_news_news) {

            //validate: $objStartDate < $objSpecialDate < $objEndDate
            $objStartDate = $objObject->getObjStartDate();
            $objEndDate = $objObject->getObjEndDate();
            $objSpecialDate = $objObject->getObjSpecialDate();

            $strLabelStartDate = $objLang->getLang("form_".$objObject->getArrModule("modul")."_datestart", $strModuleName);
            $strLabelEndDate = $objLang->getLang("form_".$objObject->getArrModule("modul")."_dateend", $strModuleName);
            $strLabelSpecialDate = $objLang->getLang("form_".$objObject->getArrModule("modul")."_datespecial", $strModuleName);



            if($objStartDate!= null && $objEndDate != null) {
                if(class_objectvalidator_helper::compareDates($objStartDate, $objEndDate) === 1) {
                    $this->addValidationError("startdate", $objLang->getLang("commons_object_validator_datecompare_validationmessage_before", $strModuleName, array($strLabelStartDate, $strLabelEndDate)));
                }
            }
            if($objSpecialDate!= null && $objEndDate != null) {
                if(class_objectvalidator_helper::compareDates($objSpecialDate, $objEndDate) === 1) {
                    $this->addValidationError("startdate", $objLang->getLang("commons_object_validator_datecompare_validationmessage_before", $strModuleName, array($strLabelSpecialDate, $strLabelEndDate)));
                }
            }
            if($objStartDate!= null && $objSpecialDate != null) {
                if(class_objectvalidator_helper::compareDates($objStartDate, $objSpecialDate) === 1) {
                    $this->addValidationError("startdate", $objLang->getLang("commons_object_validator_datecompare_validationmessage_before", $strModuleName, array($strLabelStartDate, $strLabelSpecialDate)));
                }
            }
        }
        else
            return false;

        return count($this->getArrValidationMessages()) == 0;
    }
}