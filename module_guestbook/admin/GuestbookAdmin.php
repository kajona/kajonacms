<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Guestbook\Admin;

use Kajona\Guestbook\System\GuestbookGuestbook;
use Kajona\System\Admin\AdminEvensimpler;
use Kajona\System\Admin\AdminInterface;


/**
 * Admin class to handle all guestbook-stuff like creating guestbook, deleting posts, ...
 *
 * @package module_guestbook
 * @author sidler@mulchprod.de
 *
 * @objectList Kajona\Guestbook\System\GuestbookGuestbook
 * @objectNew Kajona\Guestbook\System\GuestbookGuestbook
 * @objectEdit Kajona\Guestbook\System\GuestbookGuestbook
 * @objectFilter Kajona\Guestbook\System\GuestbookFilter
 *
 * @objectListPost Kajona\Guestbook\System\GuestbookPost
 * @objectEditPost Kajona\Guestbook\System\GuestbookPost
 * @objectFilterPost Kajona\Guestbook\System\GuestbookPostFilter
 *
 * @autoTestable list,new
 *
 * @module guestbook
 * @moduleId _guestbook_module_id_
 */
class GuestbookAdmin extends AdminEvensimpler implements AdminInterface
{


    public function getOutputModuleNavi()
    {
        $arrReturn = array();
        $arrReturn[] = array("view", getLinkAdmin($this->arrModule["modul"], "list", "", $this->getLang("commons_list"), "", "", true, "adminnavi"));
        return $arrReturn;
    }


    protected function getNewEntryAction($strListIdentifier, $bitDialog = false)
    {
        if ($this->getObjModule()->rightEdit() && $this->getStrCurObjectTypeName() != "Post") {
            return parent::getNewEntryAction($strListIdentifier, $bitDialog);
        }
        return "";
    }

    protected function renderAdditionalActions(\Kajona\System\System\Model $objListEntry)
    {
        if ($objListEntry instanceof GuestbookGuestbook) {
            return array(
                $this->objToolkit->listButton(
                    getLinkAdmin($this->arrModule["modul"], "listPost", "&systemid=".$objListEntry->getSystemid(), "", $this->getLang("action_view_guestbook"), "icon_bookLens")
                )
            );
        }
    }

}

