<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\Statustransition\Admin;

use Kajona\System\Admin\AdminEvensimpler;
use Kajona\System\Admin\AdminInterface;
use Kajona\System\System\Link;

/**
 * Admin class to setup status transition flows
 *
 * @author christoph.kappestein@gmail.com
 *
 * @objectListFlow Kajona\Statustransition\System\StatustransitionFlow
 * @objectNewFlow Kajona\Statustransition\System\StatustransitionFlow
 * @objectEditFlow Kajona\Statustransition\System\StatustransitionFlow
 *
 * @objectListAssignment Kajona\Statustransition\System\StatustransitionFlowAssignment
 * @objectNewAssignment Kajona\Statustransition\System\StatustransitionFlowAssignment
 * @objectEditAssignment Kajona\Statustransition\System\StatustransitionFlowAssignment
 *
 * @module statustransition
 * @moduleId _statustransition_module_id_
 */
class StatustransitionAdmin extends AdminEvensimpler implements AdminInterface
{
    /**
     * @return array
     */
    public function getOutputModuleNavi()
    {
        $arrReturn = array();
        $arrReturn[] = array("view", Link::getLinkAdmin($this->getArrModule("modul"), "listFlow", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        $arrReturn[] = array("view", Link::getLinkAdmin($this->getArrModule("modul"), "listAssignment", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        return $arrReturn;
    }
}
