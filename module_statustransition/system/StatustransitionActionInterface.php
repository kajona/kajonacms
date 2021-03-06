<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Statustransition\System;

use Kajona\System\System\Model;

/**
 * The action class is executed if a status transition happens. A status transition is always triggered by a user
 * interaction and not by an automatic event. Actions can be attached to a StatustransitionTransition object
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
