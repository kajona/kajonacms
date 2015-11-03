<?php
/*"******************************************************************************************************
*   (c) 2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Pages\System;

/**
 * A single model for a portaleditor action
 *
 * @author sidler@mulchprod.de
 */
abstract class PagesPortaleditorActionAbstract
{

    private $strSystemid = "";
    private $strPlaceholder = "";
    private $strLink = "";
    /** @var PagesPortaleditorActionEnum  */
    private $objAction = null;



    /**
     * @return PagesPortaleditorActionEnum
     */
    public function getObjAction()
    {
        return $this->objAction;
    }

    /**
     * @param PagesPortaleditorActionEnum $objAction
     */
    public function setObjAction($objAction)
    {
        $this->objAction = $objAction;
    }

    /**
     * @return string
     */
    public function getStrLink()
    {
        return $this->strLink;
    }

    /**
     * @param string $strLink
     */
    public function setStrLink($strLink)
    {
        $this->strLink = $strLink;
    }

    /**
     * @return string
     */
    public function getStrPlaceholder()
    {
        return $this->strPlaceholder;
    }

    /**
     * @param string $strPlaceholder
     */
    public function setStrPlaceholder($strPlaceholder)
    {
        $this->strPlaceholder = $strPlaceholder;
    }

    /**
     * @return string
     */
    public function getStrSystemid()
    {
        return $this->strSystemid;
    }

    /**
     * @param string $strSystemid
     */
    public function setStrSystemid($strSystemid)
    {
        $this->strSystemid = $strSystemid;
    }





}
