<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\Flow\Admin;

use Kajona\Flow\System\FlowConditionAbstract;
use Kajona\System\Admin\AdminFormgenerator;

/**
 * FlowConditionFormgenerator
 *
 * @package module_flow
 * @author christoph.kappestein@gmail.com
 * @since 5.1
 */
class FlowConditionFormgenerator extends AdminFormgenerator
{
    use FlowConfigurationFormgeneratorTrait;

    protected function isValidSourceObject($objSource)
    {
        return $objSource instanceof FlowConditionAbstract;
    }
}
