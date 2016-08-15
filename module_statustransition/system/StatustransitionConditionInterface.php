<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Statustransition\System;

use Kajona\System\System\Model;

/**
 * StatustransitionConditionInterface
 *
 * @author christoph.kappestein@artemeon.de
 * @author stefan.meyer@artemeon.de
 * @module statustransition
 */
interface StatustransitionConditionInterface
{
    /**
     * Is called on a status change
     *
     * @param integer $intOldStatus
     * @param integer $intNewStatus
     * @param Model $objObject
     * @return boolean
     */
    public function validateCondition($intOldStatus, $intNewStatus, Model $objObject);
}
