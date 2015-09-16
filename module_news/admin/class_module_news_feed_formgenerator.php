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
class class_module_news_feed_formgenerator extends class_admin_formgenerator  {
    /**
     * @inheritDoc
     */
    public function generateFieldsFromObject() {
        parent::generateFieldsFromObject();

        /** @var class_module_news_category[] $arrNewsCats */
        $arrNewsCats = class_module_news_category::getObjectList();
        $arrCatsDD = array();
        foreach($arrNewsCats as $objOneCat) {
            $arrCatsDD[$objOneCat->getSystemid()] = $objOneCat->getStrTitle();
        }
        $arrCatsDD["0"] = $this->getLang("commons_all_categories");
        $this->getField("cat")->setArrKeyValues($arrCatsDD);

    }

}

