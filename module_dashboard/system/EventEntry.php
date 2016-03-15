<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

namespace Kajona\Dashboard\System;

/**
 * Object which represents a event entry
 *
 * @package module_dashboard
 * @author christoph.kappestein@gmail.com
 */
class EventEntry extends EntryAbstract
{
    protected $strHref;
    protected $objStartDate;
    protected $objEndDate;

    /**
     * @return string
     */
    public function getStrHref()
    {
        return $this->strHref;
    }

    /**
     * @param string $strHref
     */
    public function setStrHref($strHref)
    {
        $this->strHref = $strHref;
    }

    /**
     * @return mixed
     */
    public function getObjStartDate()
    {
        return $this->objStartDate;
    }

    /**
     * @param mixed $objStartDate
     */
    public function setObjStartDate(\Kajona\System\System\Date $objStartDate)
    {
        $this->objStartDate = $objStartDate;
    }

    /**
     * @return mixed
     */
    public function getObjEndDate()
    {
        return $this->objEndDate;
    }

    /**
     * @param mixed $objEndDate
     */
    public function setObjEndDate(\Kajona\System\System\Date $objEndDate)
    {
        $this->objEndDate = $objEndDate;
    }
}
