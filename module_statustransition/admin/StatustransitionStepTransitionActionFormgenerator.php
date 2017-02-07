<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\Statustransition\Admin;

use Kajona\Statustransition\System\StatustransitionFlowStep;
use Kajona\Statustransition\System\StatustransitionFlowStepTransition;
use Kajona\Statustransition\System\StatustransitionFlowStepTransitionAction;
use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\Admin\Formentries\FormentryHidden;
use Kajona\System\Admin\Formentries\FormentryObjectlist;
use Kajona\System\System\Carrier;
use Kajona\System\System\Lang;
use Kajona\System\System\Link;
use Kajona\System\System\Objectfactory;

/**
 * StatustransitionStepTransitionActionFormgenerator
 *
 * @package module_statustransition
 * @author christoph.kappestein@gmail.com
 * @since 5.1
 */
class StatustransitionStepTransitionActionFormgenerator extends AdminFormgenerator
{
    use StatustransitionStepTransitionFormgeneratorTrait;

    protected function isValidSourceObject($objSource)
    {
        return $objSource instanceof StatustransitionFlowStepTransitionAction;
    }
}
