<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\Admin\Formentries;


/**
 * A headline generated out of an objects' property
 *
 * @author sidler@mulchprod.de
 * @since 4.5
 * @package module_formgenerator
 */
class FormentryAutoheadline extends class_formentry_headline {

    public function __construct($strForm = "", $strName = "", $objSourceObject = null) {
        if($strName == "")
            $strName = generateSystemid();
        class_formentry_base::__construct($strForm, $strName, $objSourceObject);

        //set the default validator
        $this->setObjValidator(new class_dummy_validator());
    }


}
