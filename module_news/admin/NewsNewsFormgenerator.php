<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\News\Admin;

use Kajona\News\System\NewsCategory;
use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\Admin\Formentries\FormentryDatetime;
use Kajona\System\Admin\LanguagesAdmin;
use Kajona\System\System\Carrier;
use Kajona\System\System\LanguagesLanguage;
use Kajona\System\System\LanguagesLanguageset;
use Kajona\System\System\Link;
use Kajona\System\System\SystemModule;
use Kajona\System\System\SystemSetting;

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
class NewsNewsFormgenerator extends AdminFormgenerator  {
    /**
     * @inheritDoc
     */
    public function generateFieldsFromObject() {
        parent::generateFieldsFromObject();


        $objNews = $this->getObjSourceobject();
        if($objNews->getSystemid() != SystemModule::getModuleByName("news")->getSystemid()) {
            //search the languages maintained
            $objLanguageManager = LanguagesLanguageset::getLanguagesetForSystemid($objNews->getSystemid());
            if($objLanguageManager != null) {

                $arrMaintained = $objLanguageManager->getArrLanguageSet();
                $arrDD = array();
                foreach($arrMaintained as $strLanguageId => $strSystemid) {
                    $objLanguage = new LanguagesLanguage($strLanguageId);
                    $arrDD[$strSystemid] = $this->getLang("lang_" . $objLanguage->getStrName(), "languages");
                }

                LanguagesAdmin::enableLanguageSwitch();
                LanguagesAdmin::setArrLanguageSwitchEntries($arrDD);
                LanguagesAdmin::setStrOnChangeHandler(
                    "window.location='" . Link::getLinkAdminHref("news", "editNews") . (SystemSetting::getConfigValue("_system_mod_rewrite_") == "true" ? "?" : "&") . "systemid='+this.value+'&pe=" . Carrier::getInstance()->getParam("pe") . "';"
                );
                LanguagesAdmin::setStrActiveKey($objNews->getSystemid());
            }
        }

        $arrCats = NewsCategory::getObjectList();
        if(count($arrCats) > 0) {
            $arrKeyValues = array();
            /** @var NewsCategory $objOneCat */
            foreach($arrCats as $objOneCat) {
                $arrKeyValues[$objOneCat->getSystemid()] = $objOneCat->getStrDisplayName();
            }
            $this->getField("cats")->setStrLabel($this->getLang("commons_categories"))->setArrKeyValues($arrKeyValues);
        }

        if(SystemSetting::getConfigValue("_news_news_datetime_") == "true") {
            $this->addField(new FormentryDatetime($this->getStrFormname(), "objDateStart", $objNews), "datestart")->setBitMandatory(true)->setStrLabel($this->getLang("form_news_datestart"));
            $this->addField(new FormentryDatetime($this->getStrFormname(), "objDateEnd", $objNews), "dateend")->setStrLabel($this->getLang("form_news_dateend"));
            $this->addField(new FormentryDatetime($this->getStrFormname(), "objDateSpecial", $objNews), "datespecial")->setStrLabel($this->getLang("form_news_datespecial"));
        }

    }


}

