<?php
/*"******************************************************************************************************
*   (c) 2015-2016 by Kajona, www.kajona.de                                                         *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pages\System;

use Kajona\System\System\EnumBase;

/**
 * A single model for a portaleditor action
 *
 * @author sidler@mulchprod.de
 * @since 5.0
 *
 * @method static PagesPortaleditorActionEnum EDIT()
 * @method static PagesPortaleditorActionEnum DELETE()
 * @method static PagesPortaleditorActionEnum SETACTIVE()
 * @method static PagesPortaleditorActionEnum SETINACTIVE()
 * @method static PagesPortaleditorActionEnum CREATE()
 * @method static PagesPortaleditorActionEnum COPY()
 * @method static PagesPortaleditorActionEnum MOVE()
 */
class PagesPortaleditorActionEnum extends EnumBase
{

    /**
     * @inheritDoc
     */
    protected function getArrValues()
    {
        return array("EDIT", "DELETE", "SETACTIVE", "SETINACTIVE", "CREATE", "COPY", "MOVE");
    }


}
