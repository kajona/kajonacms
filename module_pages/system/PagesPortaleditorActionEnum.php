<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace pages\system;

/**
 * A single model for a portaleditor action
 *
 * @author sidler@mulchprod.de
 * @since 5.0
 *
 * @method static PagesPortaleditorActionEnum EDIT()
 * @method static PagesPortaleditorActionEnum DELETE()
 * @method static PagesPortaleditorActionEnum SETACTIVE()
 * @method static PagesPortaleditorActionEnum BSETINACTIVE()
 * @method static PagesPortaleditorActionEnum NEW()
 */
class PagesPortaleditorActionEnum extends \class_enum  {

    /**
     * @inheritDoc
     */
    protected function getArrValues()
    {
        return array("EDIT", "DELETE", "SETACTIVE", "SETINACTIVE", "NEW");
    }


}
