<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                   *
********************************************************************************************************/

namespace Kajona\Flow\Admin;

use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\System\Link;

/**
 * Formgenerator for a flow entry
 *
 * @package module_flow
 * @author christoph.kappestein@gmail.com
 * @since 5.1
 */
class FlowStatusFormgenerator extends AdminFormgenerator
{
    /**
     * @inheritDoc
     */
    public function generateFieldsFromObject()
    {
        parent::generateFieldsFromObject();

        $this->getField("viewgroups")
            ->setStrSource(Link::getLinkAdminXml("user", "getUserByFilter", "&user=false&group=true"));

        $this->getField("editgroups")
            ->setStrSource(Link::getLinkAdminXml("user", "getUserByFilter", "&user=false&group=true"));

        $this->getField("deletegroups")
            ->setStrSource(Link::getLinkAdminXml("user", "getUserByFilter", "&user=false&group=true"));
    }
}
