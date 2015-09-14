<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$	                        *
********************************************************************************************************/

/**
 * Object which represents a event entry
 *
 * @package module_dashboard
 * @author christoph.kappestein@gmail.com
 */
class class_event_entry extends class_entry_abstract
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
    public function setObjStartDate(class_data $objStartDate)
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
    public function setObjEndDate(class_data $objEndDate)
    {
        $this->objEndDate = $objEndDate;
    }
}
