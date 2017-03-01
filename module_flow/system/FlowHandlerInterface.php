<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

declare(strict_types=1);

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
     * Sets the record status of the object to the target status of the transition. The transition is only possible if
     * all conditions are valid
     *
     * @param Model $objObject
     * @param FlowTransition $objTransition
     * @return boolean - true if transition is executed, false if not
     * @throws \Kajona\System\System\Exception
     */
    public function handleStatusTransition(Model $objObject, FlowTransition $objTransition) : bool;

    /**
     * Validates whether the model contains valid data to be transitioned to the next step. Returns an array of error
     * messages
     *
     * @param Model $objModel
     * @param FlowTransition $objTransition
     * @return array
     */
    public function validateStatusTransition(Model $objObject, FlowTransition $objTransition) : array;

    /**
     * Returns whether a transition is visible in the menu. In the end the transition is only visible in the menu if:
     * all conditions of the transition are true and this method returns true
     *
     * @param Model $objModel
     * @param FlowTransition $objTransition
     * @return bool
     */
    public function isTransitionVisible(Model $objObject, FlowTransition $objTransition) : bool;
}
