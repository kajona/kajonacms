<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                              *
********************************************************************************************************/

namespace Kajona\System\Admin;

use Kajona\System\System\MessagingMessage;
use Kajona\System\System\Objectfactory;


/**
 * Formgenerator for a single message
 *
 * @package module_messaging
 * @author sidler@mulchprod.de
 * @since 4.8
 *
 */
class MessagingMessageFormgenerator extends AdminFormgenerator {

    /**
     * @inheritDoc
     */
    public function generateFieldsFromObject() {
        parent::generateFieldsFromObject();

        if(validateSystemid($this->getField("messagerefid")->getStrValue()) && $this->getField("body")->getStrValue() == "") {
            $objRefMessage = Objectfactory::getInstance()->getObject($this->getField("messagerefid")->getStrValue());
            if($objRefMessage instanceof MessagingMessage) {

                $arrBody = preg_split('/$\R?^/m', $objRefMessage->getStrBody());
                array_walk($arrBody, function (&$strValue) {
                    $strValue = "> ".$strValue;
                });

                $this->getField("body")->setStrValue("\r\n\r\n\r\n".implode("\r\n", $arrBody));
            }
        }
    }

}
