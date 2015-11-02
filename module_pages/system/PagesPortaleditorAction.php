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
 */
class PagesPortaleditorAction  {

    private $strSystemid = "";
    private $strPlaceholder = "";
    private $strLink = "";
    /** @var PagesPortaleditorActionEnum  */
    private $objAction = null;

    /**
     * PagesPortaleditorAction constructor.
     *
     * @param PagesPortaleditorActionEnum $objAction
     * @param string $strLink
     * @param string $strPlaceholder
     * @param string $strSystemid
     */
    public function __construct(PagesPortaleditorActionEnum $objAction, $strLink, $strPlaceholder, $strSystemid)
    {
        $this->objAction = $objAction;
        $this->strLink = $strLink;
        $this->strPlaceholder = $strPlaceholder;
        $this->strSystemid = $strSystemid;
    }


}
