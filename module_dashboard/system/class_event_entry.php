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
}
