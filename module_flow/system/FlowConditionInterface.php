<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Flow\System;

use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\System\Model;

/**
 * The condition validates whether a model has all required fields available to go into the next status. So it is called
 * before a status transition happens. It must explicit return true if all conditions are correct. If it returns false
 * or throws an Exception the transition to the next status fails
 *
 * @author christoph.kappestein@artemeon.de
 * @author stefan.meyer@artemeon.de
 * @module flow
 */
interface FlowConditionInterface
{
    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * Validates whether it is allowed to make a status transition
     *
     * @param Model $objObject
     * @param FlowTransition $objTransition
     * @return boolean
     */
    public function validateCondition(Model $objObject, FlowTransition $objTransition);

    /**
     * @param AdminFormgenerator $objForm
     * @return void
     */
    public function configureForm(AdminFormgenerator $objForm);
}
