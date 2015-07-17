<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/


/**
 * Formgenerator for a single news entry
 *
 * @package module_news
 * @author sidler@mulchprod.de
 * @since 4.8
 * 
 * @module news
 * @moduleId _news_module_id_
 */
class class_module_news_news_formgenerator extends class_admin_formgenerator  {
    /**
     * @inheritDoc
     */
    public function generateFieldsFromObject() {
        parent::generateFieldsFromObject();


        $objNews = $this->getObjSourceobject();
        if($objNews->getSystemid() != class_module_system_module::getModuleByName("news")->getSystemid()) {
            //search the languages maintained
            $objLanguageManager = class_module_languages_languageset::getLanguagesetForSystemid($objNews->getSystemid());
            if($objLanguageManager != null) {

                $arrMaintained = $objLanguageManager->getArrLanguageSet();
                $arrDD = array();
                foreach($arrMaintained as $strLanguageId => $strSystemid) {
                    $objLanguage = new class_module_languages_language($strLanguageId);
                    $arrDD[$strSystemid] = $this->getLang("lang_" . $objLanguage->getStrName(), "languages");
                }

                class_module_languages_admin::enableLanguageSwitch();
                class_module_languages_admin::setArrLanguageSwitchEntries($arrDD);
                class_module_languages_admin::setStrOnChangeHandler(
                    "window.location='" . class_link::getLinkAdminHref("news", "editNews") . (class_module_system_setting::getConfigValue("_system_mod_rewrite_") == "true" ? "?" : "&") . "systemid='+this.value+'&pe=" . class_carrier::getInstance()->getParam("pe") . "';"
                );
                class_module_languages_admin::setStrActiveKey($objNews->getSystemid());
            }
        }

        $arrCats = class_module_news_category::getObjectList();
        if(count($arrCats) > 0) {
            $arrKeyValues = array();
            /** @var class_module_news_category $objOneCat */
            foreach($arrCats as $objOneCat) {
                $arrKeyValues[$objOneCat->getSystemid()] = $objOneCat->getStrDisplayName();
            }
            $this->getField("cats")->setStrLabel($this->getLang("commons_categories"))->setArrKeyValues($arrKeyValues);
        }

        if(class_module_system_setting::getConfigValue("_news_news_datetime_") == "true") {
            $this->addField(new class_formentry_datetime($this->getStrFormname(), "objDateStart", $objNews), "datestart")->setBitMandatory(true)->setStrLabel($this->getLang("form_news_datestart"));
            $this->addField(new class_formentry_datetime($this->getStrFormname(), "objDateEnd", $objNews), "dateend")->setStrLabel($this->getLang("form_news_dateend"));
            $this->addField(new class_formentry_datetime($this->getStrFormname(), "objDateSpecial", $objNews), "datespecial")->setStrLabel($this->getLang("form_news_datespecial"));
        }

    }


}

