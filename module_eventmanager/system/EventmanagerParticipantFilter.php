<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/


namespace Kajona\Eventmanager\System;

use Kajona\System\System\FilterBase;

/**
 * Filter object, mainly used to fetch todos
 *
 * @module eventmanager
 */
class EventmanagerParticipantFilter extends FilterBase
{

    /**
     * @var string
     * @tableColumn system.system_status
     */
    private $intStatus;


    /**
     * @return string
     */
    public function getIntStatus()
    {
        return $this->intStatus;
    }

    /**
     * @param string $intStatus
     */
    public function setIntStatus($intStatus)
    {
        $this->intStatus = $intStatus;
    }

    
    
}