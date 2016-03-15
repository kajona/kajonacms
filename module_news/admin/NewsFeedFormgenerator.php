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
class NewsFeedFormgenerator extends AdminFormgenerator  {
    /**
     * @inheritDoc
     */
    public function generateFieldsFromObject() {
        parent::generateFieldsFromObject();

        /** @var NewsCategory[] $arrNewsCats */
        $arrNewsCats = NewsCategory::getObjectList();
        $arrCatsDD = array();
        foreach($arrNewsCats as $objOneCat) {
            $arrCatsDD[$objOneCat->getSystemid()] = $objOneCat->getStrTitle();
        }
        $arrCatsDD["0"] = $this->getLang("commons_all_categories");
        $this->getField("cat")->setArrKeyValues($arrCatsDD);

    }

}

