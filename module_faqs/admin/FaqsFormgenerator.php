<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Faqs\Admin;
use Kajona\Faqs\System\FaqsCategory;
use Kajona\System\Admin\AdminFormgenerator;


/**
 * The formgenerator for a single faqs object
 * @author sidler@mulchprod.de
 * @package module_faqs
 * @since 4.8
 */
class FaqsFormgenerator extends AdminFormgenerator  {

    /**
     * @inheritdoc
     */
    public function generateFieldsFromObject() {
        parent::generateFieldsFromObject();

        //inject the categories formentries
        $arrCats = FaqsCategory::getObjectList();
        if(count($arrCats) > 0) {
            $arrKeyValues = array();
            /** @var FaqsCategory $objOneCat */
            foreach($arrCats as $objOneCat) {
                $arrKeyValues[$objOneCat->getSystemid()] = $objOneCat->getStrDisplayName();
            }

            $this->getField("cats")->setStrLabel($this->getLang("commons_categories"))->setArrKeyValues($arrKeyValues);
        }
    }

}

