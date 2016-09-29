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

        // add button
        $objField = $this->getField("transitions");
        $strAddButton = Link::getLinkAdminDialog(
            "statustransition",
            "stepBrowser",
            "&folderview=1&form_element=".$objField->getStrEntryName()."&systemid=".Carrier::getInstance()->getParam("systemid"),
            Lang::getInstance()->getLang("commons_objectlist_manage_assignment", "system"),
            Lang::getInstance()->getLang("commons_objectlist_manage_assignment", "system"),
            "icon_new",
            Lang::getInstance()->getLang("commons_objectlist_manage_assignment", "system"),
            true,
            false,
            ""
        );
        $objField->setStrAddLink($strAddButton);

        $this->getField("usergroup")
            ->setBitGroups(true)
            ->setBitUser(false);
    }
}
