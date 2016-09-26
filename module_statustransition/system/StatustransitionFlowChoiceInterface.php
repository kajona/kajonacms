<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Statustransition\System;

use Kajona\System\System\Database;
use Kajona\System\System\Exception;
use Kajona\System\System\Model;

/**
 * StatustransitionFlowChoiceInterface
 *
 * @author christoph.kappestein@artemeon.de
 * @module statustransition
 */
interface StatustransitionFlowChoiceInterface
{
    /**
     * Returns the fitting flow key for the current status of the object. This is a key from the getPossibleFlows method
     * or null if no flow fits
     *
     * @return string
     */
    public function getStatusTransitionFlow();

    /**
     * Returns an array [key => description] which lists possible flows
     *
     * @return array
     */
    public function getPossibleFlows();
}
