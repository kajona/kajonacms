<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\Statustransition\Admin;

use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\System\Carrier;
use Kajona\System\System\Lang;
use Kajona\System\System\Link;

/**
 * Formgenerator for a statustransition flow entry
 *
 * @package module_statustransition
 * @author christoph.kappestein@gmail.com
 * @since 5.1
 */
class StatustransitionStepFormgenerator extends AdminFormgenerator
{
    /**
     * @inheritDoc
     */
    public function generateFieldsFromObject()
    {
        parent::generateFieldsFromObject();
    }
}
