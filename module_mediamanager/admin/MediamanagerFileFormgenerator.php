<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*    $Id$                        *
********************************************************************************************************/

namespace Kajona\Mediamanager\Admin;

use Kajona\System\Admin\AdminFormgenerator;
use Kajona\System\Admin\Formentries\FormentryHeadline;
use Kajona\System\System\Carrier;


/**
 * The formgenerator for a mediamanager repo
 *
 * @package module_mediamanager
 * @author sidler@mulchprod.de
 * @since 4.8
 *
 */
class MediamanagerFileFormgenerator extends AdminFormgenerator {
    /**
     * @inheritDoc
     */
    public function generateFieldsFromObject() {
        parent::generateFieldsFromObject();

        $this->addField(new FormentryHeadline("", "source"))->setStrValue(Carrier::getInstance()->getParam("source"));
    }

}

