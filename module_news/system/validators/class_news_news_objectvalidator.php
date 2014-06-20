<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

/**
 * @package module_projecttracker
 */
class class_news_news_objectvalidator implements interface_object_validator{
    private $arrValidationMessages = array();


    /**
     * Validates the passed chunk of data.
     *
     * The returning array contains the given error messages. Each key in the array contains an array of error messages.
     * Format of the returned array is:
     *      array("<messageKey>" => array())
     *
     * e.g. a key could be
     * array("<formName>_<errorMessageKey>" => array())
     *
     *
     * @abstract
     * @param class_model $objObject - the model object to the given form
     * @return array
     */
    public function validateObject(class_model $objObject) {
        $objLang = class_carrier::getInstance()->getObjLang();
        $strModuleName = $objObject->getArrModule("modul");

        if($objObject instanceof class_module_projecttracker_project) {
            $objStartDate = $objObject->getObjStartDate();
            $objEndDate = $objObject->getObjEndDate();
            $objSpecialDate = $objObject->getObjSpecialDate();

            $strLabelStartDate = $objLang->getLang("form_".$objObject->getArrModule("modul")."_objStartDate", $strModuleName);
            $strLabelEndDate = $objLang->getLang("form_".$objObject->getArrModule("modul")."_objEndDate", $strModuleName);
            $strLabelSpecialDate = $objLang->getLang("form_".$objObject->getArrModule("modul")."_objSpecialDate", $strModuleName);


            if($objStartDate!= null && $objEndDate != null) {
                if(class_objectvalidator_helper::compareDates($objStartDate, $objEndDate) === 1) {
                    $this->arrValidationMessages["objStartDate"] = $objLang->getLang("commons_object_validator_datecompare_validationmessage_before", $strModuleName, array($strLabelStartDate, $strLabelEndDate));
                }
            }
            if($objEndDate!= null && $objSpecialDate != null) {
                if(class_objectvalidator_helper::compareDates($objEndDate, $objSpecialDate) === 1) {
                    $this->arrValidationMessages["objEndDate"] = $objLang->getLang("commons_object_validator_datecompare_validationmessage_before", $strModuleName, array($strLabelEndDate, $strLabelSpecialDate));
                }

            }
            if($objStartDate!= null && $objSpecialDate != null) {
                if(class_objectvalidator_helper::compareDates($objStartDate, $objSpecialDate) === 1) {
                    $this->arrValidationMessages["objStartDate"] = $objLang->getLang("commons_object_validator_datecompare_validationmessage_before", $strModuleName, array($strLabelStartDate, $strLabelSpecialDate));
                }
            }
        }
        return $this->arrValidationMessages;
    }
}