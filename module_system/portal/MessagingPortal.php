<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$									*
********************************************************************************************************/

namespace Kajona\System\Portal;

use Kajona\System\System\HttpResponsetypes;
use Kajona\System\System\MessagingMessage;
use Kajona\System\System\Objectfactory;
use Kajona\System\System\ResponseObject;

/**
 * Portal-class of the messaging framework
 *
 * @author sidler@mulchprod.de
 *
 * @module messaging
 * @moduleId _messaging_module_id_
 */
class MessagingPortal extends PortalController implements PortalInterface
{


    /**
     * Default implementation to avoid mail-spamming.
     *
     * @return void
     */
    protected function actionList()
    {

    }

    /**
     * Marks a message as read and returns a 1x1px transparent gif as a "read indicator"
     *
     * @return string
     * @responseType gif
     */
    protected function actionSetRead()
    {
        $objMessage = Objectfactory::getInstance()->getObject($this->getSystemid());

        if ($objMessage !== null && $objMessage instanceof MessagingMessage && $objMessage->getBitRead() == 0) {
            $objMessage->setBitRead(1);
            $objMessage->updateObjectToDb();
        }

        return base64_decode("R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==");
    }

}
