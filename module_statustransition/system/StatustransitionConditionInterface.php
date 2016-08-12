<?php
/*"******************************************************************************************************
*   (c) 2010-2016 ARTEMEON                                                                              *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                         *
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
