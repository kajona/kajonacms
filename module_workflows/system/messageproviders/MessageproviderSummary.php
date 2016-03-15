<?php
/*"******************************************************************************************************
*   (c) 2013-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Workflows\System\Messageproviders;

use Kajona\System\System\Carrier;
use Kajona\System\System\Messageproviders\MessageproviderInterface;


/**
 * The summary message creates an overview of unread messages and sends them to the user.
 * In most cases this only makes sense if sent by mail.
 *
 * @author sidler@kajona.de
 * @package module_workflows
 * @since 4.5
 */
class MessageproviderSummary implements MessageproviderInterface
{


    /**
     * Returns the name of the message-provider
     *
     * @return string
     */
    public function getStrName()
    {
        return Carrier::getInstance()->getObjLang()->getLang("messageprovider_workflows_summary", "workflows");
    }

    /**
     * @inheritDoc
     */
    public function isAlwaysActive()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isAlwaysByMail()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isVisibleInConfigView()
    {
        return true;
    }
}
