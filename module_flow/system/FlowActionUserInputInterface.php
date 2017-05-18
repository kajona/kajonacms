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
 * If an action implements this interface the user has to provide data before the transition to the next step happens
 *
 * @author christoph.kappestein@artemeon.de
 * @module flow
 */
interface FlowActionUserInputInterface
{
    /**
     * Handles the user input which was provided by the user on a status transition
     *
     * @param Model $objObject
     * @param FlowTransition $objTransition
     * @param array $arrData
     */
    public function handleUserInput(Model $objObject, FlowTransition $objTransition, array $arrData);

    /**
     * @param AdminFormgenerator $objForm
     * @return void
     */
    public function configureUserInputForm(AdminFormgenerator $objForm);
}
