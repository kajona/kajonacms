<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*    $Id$                        *
********************************************************************************************************/

/**
 * The formgenerator for a mediamanager repo
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 * @since 4.8
 *
 */
class class_module_mediamanager_repo_formgenerator extends class_admin_formgenerator {
    /**
     * @inheritDoc
     */
    public function generateFieldsFromObject() {
        parent::generateFieldsFromObject();

        $this->getField("path")->setStrOpener(
            class_link::getLinkAdminDialog(
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

