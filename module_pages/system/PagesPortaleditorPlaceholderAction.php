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
class PagesPortaleditorPlaceholderAction extends PagesPortaleditorActionAbstract
{

    private $strElement = "";

    /**
     * PagesPortaleditorAction constructor.
     *
     * @param PagesPortaleditorActionEnum $objAction
     * @param string $strLink
     * @param string $strPlaceholder
     */
    public function __construct(PagesPortaleditorActionEnum $objAction, $strLink, $strPlaceholder, $strElement)
    {
        $this->setObjAction($objAction);
        $this->setStrLink($strLink);
        $this->setStrPlaceholder($strPlaceholder);
        $this->setStrElement($strElement);
    }

    /**
     * @return string
     */
    public function getStrElement()
    {
        return $this->strElement;
    }

    /**
     * @param string $strElement
     */
    public function setStrElement($strElement)
    {
        $this->strElement = $strElement;
    }




}
