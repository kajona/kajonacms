<?php
/*"******************************************************************************************************
*   (c) 2010-2016 ARTEMEON                                                                              *
********************************************************************************************************/

namespace AGP\Statustransition\System;

use Kajona\System\System\Model;

/**
 * StatustransitionActionInterface
 *
 * @author christoph.kappestein@artemeon.de
 * @author stefan.meyer@artemeon.de
 * @module statustransition
 */
interface StatustransitionActionInterface
{
    /**
     * Is called on a status change
     *
     * @param integer $intOldStatus
     * @param integer $intNewStatus
     * @param Model $objObject
     * @return mixed
     */
    public function executeAction($intOldStatus, $intNewStatus, Model $objObject);
}
