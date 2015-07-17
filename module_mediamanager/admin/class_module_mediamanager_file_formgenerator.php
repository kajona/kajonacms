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
class class_module_mediamanager_file_formgenerator extends class_admin_formgenerator {
    /**
     * @inheritDoc
     */
    public function generateFieldsFromObject() {
        parent::generateFieldsFromObject();

        $this->addField(new class_formentry_hidden("", "source"))->setStrValue(class_carrier::getInstance()->getParam("source"));
    }

}

