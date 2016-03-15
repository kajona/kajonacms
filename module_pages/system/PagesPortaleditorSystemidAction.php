<?php
/*"******************************************************************************************************
*   (c) 2015-2016 by Kajona, www.kajona.de                                                         *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pages\System;

/**
 * A single model for a portaleditor action
 *
 * @author sidler@mulchprod.de
 */
class PagesPortaleditorSystemidAction extends PagesPortaleditorActionAbstract
{


    /**
     * PagesPortaleditorAction constructor.
     *
     * @param PagesPortaleditorActionEnum $objAction
     * @param string $strLink
     * @param string $strSystemid
     */
    public function __construct(PagesPortaleditorActionEnum $objAction, $strLink, $strSystemid)
    {
        $this->setObjAction($objAction);
        $this->setStrLink($strLink);
        $this->setStrSystemid($strSystemid);
    }


}
