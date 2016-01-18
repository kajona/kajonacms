<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Languageswitch\Portal\Elements;

use class_module_languages_language;
use class_module_languages_languageset;
use class_objectfactory;
use Kajona\Pages\Portal\ElementPortal;
use Kajona\Pages\Portal\PortalElementInterface;
use Kajona\Pages\System\PagesPage;


/**
 * Portal-Class of the picture element
 *
 * @author sidler@mulchprod.de
 *
 * @targetTable element_universal.content_id
 */
class ElementLanguageswitchPortal extends ElementPortal implements PortalElementInterface {

    /**
     * Returns the ready switch-htmlcode
     *
     * @return string
     */
    public function loadData() {

        //fallback for old elements not yet using the template
        if(!isset($this->arrElementData["char1"]) || $this->arrElementData["char1"] == "")
            $this->arrElementData["char1"] = "languageswitch.tpl";

        $arrObjLanguages = class_module_languages_language::getObjectList(true);

        //load the languageset in order to generate more specific switches
        $objLanguageset = class_module_languages_languageset::getLanguagesetForSystemid($this->getParam("systemid"));

        //Iterate over all languages
        $strRows = "";
        foreach($arrObjLanguages as $objOneLanguage) {
            //Check, if the current page has elements
            $objPage = PagesPage::getPageByName($this->getPagename());
            $objPage->setStrLanguage($objOneLanguage->getStrName());
            if($objPage === null)
                continue;

            if((int)$objPage->getNumberOfElementsOnPage(true) == 0)
                continue;


            $strTargetSystemid = null;
            if($objLanguageset != null) {
                $strTargetSystemid = $objLanguageset->getSystemidForLanguageid($objOneLanguage->getSystemid());
            }

            //the languageswitch is content aware. check if the target id is a news-entry
            $strSeoAddon = "";
            if(validateSystemid($strTargetSystemid)) {
                $objRecord = class_objectfactory::getInstance()->getObject($strTargetSystemid);
                $strSeoAddon = $objRecord->getStrDisplayName();
            }

            //and the link
            $arrTemplate = array();
            if($strTargetSystemid === null)
                $arrTemplate["href"] = getLinkPortalHref($objPage->getStrName(), "", "", "", "", $objOneLanguage->getStrName(), $strSeoAddon);
            else
                $arrTemplate["href"] = getLinkPortalHref($objPage->getStrName(), "", $this->getAction(), "", $strTargetSystemid, $objOneLanguage->getStrName(), $strSeoAddon);

            $arrTemplate["langname_short"] = $objOneLanguage->getStrName();
            $arrTemplate["langname_long"] = $this->getLang("lang_".$objOneLanguage->getStrName());

            $strTemplateRowID = $this->objTemplate->readTemplate("/module_languageswitch/".$this->arrElementData["char1"], "languageswitch_entry");
            $strTemplateActiveRowID = $this->objTemplate->readTemplate("/module_languageswitch/".$this->arrElementData["char1"], "languageswitch_entry_active");

            if($objOneLanguage->getStrName() == $this->getStrPortalLanguage())
                $strRows .= $this->fillTemplate($arrTemplate, $strTemplateActiveRowID);
            else
                $strRows .= $this->fillTemplate($arrTemplate, $strTemplateRowID);

        }

        $strTemplateWrapperID = $this->objTemplate->readTemplate("/module_languageswitch/".$this->arrElementData["char1"], "languageswitch_wrapper");
        return $this->fillTemplate(array("languageswitch_entries" => $strRows), $strTemplateWrapperID);
    }

}
