<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * The messagehandler provides common methods to interact with the messaging-subsystem
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_messaging
 */
class class_module_messaging_messagehandler {

    /**
     * @return interface_messageprovider[]
     */
    public function getMessageproviders() {
        return class_resourceloader::getInstance()->getFolderContent("/system/messageproviders", array(".php"), false, function($strOneFile) {
            if(uniStrpos($strOneFile, "interface") !== false)
                return false;

            return true;
        },
        function(&$strOneFile) {
            $strOneFile = uniSubstr($strOneFile, 0, -4);
            $strOneFile = new $strOneFile();
        });
    }


    /**
     * Sends a message.
     * If the list of recipients contains a group, the message is duplicated for each member.
     *
     * @param string $strContent
     * @param class_module_user_group[]|class_module_user_user[]|class_module_user_group|class_module_user_user $arrRecipients
     * @param interface_messageprovider $objProvider
     * @param string $strInternalIdentifier
     * @param string $strSubject
     *
     * @deprecated use @link{class_module:messaging_messagehandler::sendMessageObject()} instead
     *
     * @return bool
     */
    public function sendMessage($strContent, $arrRecipients, interface_messageprovider $objProvider, $strInternalIdentifier = "", $strSubject = "") {

        //build a default message and pass it to sendMessageObject
        $objMessage = new class_module_messaging_message();
        $objMessage->setStrTitle($strSubject);
        $objMessage->setStrBody($strContent);
        $objMessage->setStrInternalIdentifier($strInternalIdentifier);
        $objMessage->setStrMessageProvider(get_class($objProvider));
        return $this->sendMessageObject($objMessage, $arrRecipients);
    }


    /**
     * Sends a message.
     * If the list of recipients contains a group, the message is duplicated for each member.
     *
     *
     * @param class_module_messaging_message $objMessage
     * @param class_module_user_group[]|class_module_user_user[]|class_module_user_group|class_module_user_user $arrRecipients
     *
     * @return bool
     */
    public function sendMessageObject(class_module_messaging_message $objMessage, $arrRecipients) {
        $objValidator = new class_email_validator();

        if($arrRecipients instanceof class_module_user_group || $arrRecipients instanceof class_module_user_user)
            $arrRecipients = array($arrRecipients);

        $arrRecipients = $this->getRecipientsFromArray($arrRecipients);

        foreach($arrRecipients as $objOneUser) {

            //skip inactive users
            if($objOneUser->getIntActive() != 1)
                continue;

            $objConfig = class_module_messaging_config::getConfigForUserAndProvider($objOneUser->getSystemid(), $objMessage->getObjMessageProvider());

            if($objConfig->getBitEnabled()) {

                //clone the message
                $objCurrentMessage = new class_module_messaging_message();
                $objCurrentMessage->setStrTitle($objMessage->getStrTitle());
                $objCurrentMessage->setStrBody($objMessage->getStrBody());
                $objCurrentMessage->setStrUser($objOneUser->getSystemid());
                $objCurrentMessage->setStrInternalIdentifier($objMessage->getStrInternalIdentifier());
                $objCurrentMessage->setStrMessageProvider($objMessage->getStrMessageProvider());
                $objCurrentMessage->setStrMessageRefId($objMessage->getStrMessageRefId());
                $objCurrentMessage->setStrSenderId(validateSystemid($objMessage->getStrSenderId()) ? $objMessage->getStrSenderId() : class_carrier::getInstance()->getObjSession()->getUserID());

                $objCurrentMessage->updateObjectToDb();

                if($objConfig->getBitBymail() && $objValidator->validate($objOneUser->getStrEmail()))
                    $this->sendMessageByMail($objCurrentMessage, $objOneUser);
            }
        }

    }



    /**
     * Sends a copy of the message to the user by mail
     *
     * @param class_module_messaging_message $objMessage
     * @param class_module_user_user $objUser
     * @return bool
     */
    private function sendMessageByMail(class_module_messaging_message $objMessage, class_module_user_user $objUser) {

        $strOriginalLang = class_carrier::getInstance()->getObjLang()->getStrTextLanguage();

        class_carrier::getInstance()->getObjLang()->setStrTextLanguage($objUser->getStrAdminlanguage());

        $strSubject = $objMessage->getStrTitle() != "" ? $objMessage->getStrTitle() : class_carrier::getInstance()->getObjLang()->getLang("message_notification_subject", "messaging");

        $strBody = class_carrier::getInstance()->getObjLang()->getLang("message_prolog", "messaging");
        $strBody .= "\n\n".class_link::getLinkAdminHref("messaging", "view", "&systemid=".$objMessage->getSystemid(), false)."\n\n";
        $strBody .= $objMessage->getStrBody();

        $objMail = new class_mail();

        //try to get a matching sender and place it into the mail
        if(validateSystemid($objMessage->getStrSenderId())) {
            $objSenderUser = new class_module_user_user($objMessage->getStrSenderId());
            $objValidator = new class_email_validator();
            if($objValidator->validate($objSenderUser->getStrEmail()))
                $objMail->setSender($objSenderUser->getStrEmail());
        }

        $objMail->setSubject($strSubject);
        $objMail->setText($strBody);
        $objMail->addTo($objUser->getStrEmail());

        class_carrier::getInstance()->getObjLang()->setStrTextLanguage($strOriginalLang);

        return $objMail->sendMail();
    }

    /**
     * Transforms a mixed array of users and groups into a list of users.
     *
     * @param class_module_user_group[]|class_module_user_user[] $arrRecipients
     * @return class_module_user_user[]
     */
    private function getRecipientsFromArray($arrRecipients) {
        $arrReturn = array();

        foreach($arrRecipients as $objOneRecipient) {
            if($objOneRecipient instanceof class_module_user_user) {
                $arrReturn[$objOneRecipient->getStrSystemid()] = $objOneRecipient;
            }
            else if($objOneRecipient instanceof class_module_user_group) {
                $objUsersources = new class_module_user_sourcefactory();
                if($objUsersources->getSourceGroup($objOneRecipient) != null) {
                    $arrMembers = $objUsersources->getSourceGroup($objOneRecipient)->getUserIdsForGroup();

                    foreach($arrMembers as $strOneId) {
                        if(!isset($arrReturn[$strOneId]))
                            $arrReturn[$strOneId] = new class_module_user_user($strOneId);
                    }
                }
            }
        }


        return $arrReturn;
    }
}
