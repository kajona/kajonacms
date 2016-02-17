<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                              *
********************************************************************************************************/

namespace Kajona\System\Admin;


/**
 * Formgenerator for a single message
 *
 * @package module_messaging
 * @author sidler@mulchprod.de
 * @since 4.8
 *
 */
class MessagingMessageFormgenerator extends class_admin_formgenerator {

    /**
     * @inheritDoc
     */
    public function generateFieldsFromObject() {
        parent::generateFieldsFromObject();

        if(validateSystemid($this->getField("messagerefid")->getStrValue()) && $this->getField("body")->getStrValue() == "") {
            $objRefMessage = class_objectfactory::getInstance()->getObject($this->getField("messagerefid")->getStrValue());
            if($objRefMessage instanceof class_module_messaging_message) {

                $arrBody = preg_split('/$\R?^/m', $objRefMessage->getStrBody());
                array_walk($arrBody, function (&$strValue) {
                    $strValue = "> ".$strValue;
                });

                $this->getField("body")->setStrValue("\r\n\r\n\r\n".implode("\r\n", $arrBody));
            }
        }
    }

}
