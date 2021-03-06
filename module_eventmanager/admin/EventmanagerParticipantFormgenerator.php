<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Eventmanager\Admin;
use Kajona\System\Admin\AdminFormgenerator;


/**
 * A formgenerator for eventmanager participant
 *
 * @package module_eventmanager
 * @author sidler@mulchprod.de
 * @since 4.8
 *
 */
class EventmanagerParticipantFormgenerator extends AdminFormgenerator  {
    /**
     * @inheritDoc
     */
    public function generateFieldsFromObject() {
        parent::generateFieldsFromObject();

        if(validateSystemid($this->getField("userid")->getStrValue())) {
            $this->getField("userid")->setBitMandatory(true);
            $this->getField("forename")->setBitMandatory(false);
            $this->getField("lastname")->setBitMandatory(false);
            $this->getField("email")->setBitMandatory(false);

        }
    }

}
