<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Flow\System;

use Kajona\System\System\GenericPluginInterface;
use Kajona\System\System\Model;

/**
 * The status handler contains all informations about the status flow. Through the status handler we can move the model
 * to the next state and get a list of available status transitions. So we have one handler object which can have
 * multiple status options.
 *
 * @author christoph.kappestein@artemeon.de
 * @author stefan.meyer@artemeon.de
 * @module flow
 */
interface FlowHandlerInterface extends GenericPluginInterface
{
    const EXTENSION_POINT = "core.flow.handler";

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getTargetClass();

    /**
     * @return array
     */
    public function getAvailableActions();

    /**
     * @return array
     */
    public function getAvailableConditions();

    /**
     * Handles a status transition
     *
     * @param Model $objObject
     * @param FlowTransition $objTransition
     * @return boolean - true if transition is executed, false if not
     * @throws \Kajona\System\System\Exception
     */
    public function handleStatusTransition(Model $objObject, FlowTransition $objTransition);
}
