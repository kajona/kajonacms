<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


/**
 * The formgenerator for a single faqs object
 * @author sidler@mulchprod.de
 * @package module_faqs
 * @since 4.8
 */
class class_module_faqs_formgenerator extends class_admin_formgenerator  {

    /**
     * @inheritdoc
     */
    public function generateFieldsFromObject() {
        parent::generateFieldsFromObject();

        //inject the categories formentries
        $arrCats = class_module_faqs_category::getObjectList();
        if(count($arrCats) > 0) {
            $arrKeyValues = array();
            /** @var class_module_faqs_category $objOneCat */
            foreach($arrCats as $objOneCat) {
                $arrKeyValues[$objOneCat->getSystemid()] = $objOneCat->getStrDisplayName();
            }

            $this->getField("cats")->setStrLabel($this->getLang("commons_categories"))->setArrKeyValues($arrKeyValues);
        }
    }

}

