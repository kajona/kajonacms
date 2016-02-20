<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*    $Id$                        *
********************************************************************************************************/

namespace Kajona\Mediamanager\Admin;

use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\System\Link;


/**
 * The formgenerator for a mediamanager repo
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 * @since 4.8
 *
 */
class MediamanagerRepoFormgenerator extends AdminFormgenerator {
    /**
     * @inheritDoc
     */
    public function generateFieldsFromObject() {
        parent::generateFieldsFromObject();

        $this->getField("path")->setStrOpener(
            Link::getLinkAdminDialog(
                "mediamanager",
                "folderListFolderview",
                "&form_element=".$this->getField("path")->getStrEntryName(),
                $this->getLang("commons_open_browser"),
                $this->getLang("commons_open_browser"),
                "icon_externalBrowser",
                $this->getLang("commons_open_browser")
            )
        )->setStrHint($this->getLang("mediamanager_path_h"));

        $this->getField("uploadfilter")->setStrHint($this->getLang("mediamanager_upload_filter_h"));
        $this->getField("viewfilter")->setStrHint($this->getLang("mediamanager_view_filter_h"));

    }
}

