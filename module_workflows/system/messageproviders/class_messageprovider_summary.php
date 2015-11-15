<?php
/*"******************************************************************************************************
*   (c) 2013-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


/**
 * The summary message creates an overview of unread messages and sends them to the user.
 * In most cases this only makes sense if sent by mail.
 *
 * @author sidler@kajona.de
 * @package module_workflows
 * @since 4.5
 */
class class_messageprovider_summary implements interface_messageprovider_extended {



    /**
     * Returns the name of the message-provider
     *
     * @return string
     */
    public function getStrName() {
        return class_carrier::getInstance()->getObjLang()->getLang("messageprovider_workflows_summary", "workflows");
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
