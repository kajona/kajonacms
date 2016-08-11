<?php
/*"******************************************************************************************************
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\System\System;

use Kajona\System\System\Messageproviders\MessageproviderInterface;
use Kajona\System\System\Validators\EmailValidator;


/**
 * The messagehandler provides common methods to interact with the messaging-subsystem
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_messaging
 */
class MessagingMessagehandler
{

    /**
     * @return MessageproviderInterface[]
     */
    public function getMessageproviders()
    {
        $arrHandler = Resourceloader::getInstance()->getFolderContent("/system/messageproviders", array(".php"), false, null,
            function (&$strOneFile, $strPath) {

                $objInstance = Classloader::getInstance()->getInstanceFromFilename($strPath);

                if ($objInstance != null && $objInstance instanceof MessageproviderInterface) {
                    $strOneFile = $objInstance;
                }
                else {
                    $strOneFile = null;
                }
            });

        return array_filter($arrHandler, function ($objInstance) {
            return $objInstance != null;
        });
    }


    /**
     * Sends a message.
     * If the list of recipients contains a group, the message is duplicated for each member.
     *
     * @param string $strContent
     * @param UserGroup[]|UserUser[]|UserGroup|UserUser $arrRecipients
     * @param MessageproviderInterface $objProvider
     * @param string $strInternalIdentifier
     * @param string $strSubject
     *
     * @deprecated use @link{class_module:messaging_messagehandler::sendMessageObject()} instead
     *
     * @return bool
     */
    public function sendMessage($strContent, $arrRecipients, MessageproviderInterface $objProvider, $strInternalIdentifier = "", $strSubject = "")
    {

        //build a default message and pass it to sendMessageObject
        $objMessage = new MessagingMessage();
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
     * @param MessagingMessage $objMessage
     * @param UserGroup[]|UserUser[]|UserGroup|UserUser $arrRecipients
     *
     * @return bool
     */
    public function sendMessageObject(MessagingMessage $objMessage, $arrRecipients)
    {
        $objValidator = new EmailValidator();

        if ($arrRecipients instanceof UserGroup || $arrRecipients instanceof UserUser) {
            $arrRecipients = array($arrRecipients);
        }

        $arrRecipients = $this->getRecipientsFromArray($arrRecipients);

        foreach ($arrRecipients as $objOneUser) {

            //skip inactive users
            if ($objOneUser->getIntRecordStatus() != 1) {
                continue;
            }

            $objConfig = MessagingConfig::getConfigForUserAndProvider($objOneUser->getSystemid(), $objMessage->getObjMessageProvider());

            if ($objConfig->getBitEnabled()) {

                //clone the message
                $objCurrentMessage = new MessagingMessage();
                $objCurrentMessage->setStrTitle($objMessage->getStrTitle());
                $objCurrentMessage->setStrBody($objMessage->getStrBody());
                $objCurrentMessage->setStrUser($objOneUser->getSystemid());
                $objCurrentMessage->setStrInternalIdentifier($objMessage->getStrInternalIdentifier());
                $objCurrentMessage->setStrMessageProvider($objMessage->getStrMessageProvider());
                $objCurrentMessage->setStrMessageRefId($objMessage->getStrMessageRefId());
                $objCurrentMessage->setStrSenderId(validateSystemid($objMessage->getStrSenderId()) ? $objMessage->getStrSenderId() : Carrier::getInstance()->getObjSession()->getUserID());

                $objCurrentMessage->updateObjectToDb();

                if ($objConfig->getBitBymail() && $objValidator->validate($objOneUser->getStrEmail())) {
                    $this->sendMessageByMail($objCurrentMessage, $objOneUser);
                }
            }
        }

    }


    /**
     * Sends a copy of the message to the user by mail
     *
     * @param MessagingMessage $objMessage
     * @param UserUser $objUser
     *
     * @return bool
     */
    private function sendMessageByMail(MessagingMessage $objMessage, UserUser $objUser)
    {

        $strOriginalLang = Carrier::getInstance()->getObjLang()->getStrTextLanguage();

        Carrier::getInstance()->getObjLang()->setStrTextLanguage($objUser->getStrAdminlanguage());

        $strSubject = $objMessage->getStrTitle() != "" ? $objMessage->getStrTitle() : Carrier::getInstance()->getObjLang()->getLang("message_notification_subject", "messaging");

        $strBody = Carrier::getInstance()->getObjLang()->getLang("message_prolog", "messaging");
        $strBody .= "\n\n".Link::getLinkAdminHref("messaging", "view", "&systemid=".$objMessage->getSystemid(), false)."\n\n";
        $strBody .= $objMessage->getStrBody();

        $objMail = new Mail();

        //try to get a matching sender and place it into the mail
        if (validateSystemid($objMessage->getStrSenderId())) {
            $objSenderUser = new UserUser($objMessage->getStrSenderId());
            $objValidator = new EmailValidator();
            if ($objValidator->validate($objSenderUser->getStrEmail())) {
                $objMail->setSender($objSenderUser->getStrEmail());
            }
        }

        $objMail->setSubject($strSubject);
        $objMail->setText($strBody);
        $objMail->addTo($objUser->getStrEmail());

        Carrier::getInstance()->getObjLang()->setStrTextLanguage($strOriginalLang);

        return $objMail->sendMail();
    }

    /**
     * Transforms a mixed array of users and groups into a list of users.
     *
     * @param UserGroup[]|UserUser[] $arrRecipients
     *
     * @return UserUser[]
     */
    private function getRecipientsFromArray($arrRecipients)
    {
        $arrReturn = array();

        foreach ($arrRecipients as $objOneRecipient) {
            if ($objOneRecipient instanceof UserUser) {
                $arrReturn[$objOneRecipient->getStrSystemid()] = $objOneRecipient;
            }
            elseif ($objOneRecipient instanceof UserGroup) {
                $objUsersources = new UserSourcefactory();
                if ($objUsersources->getSourceGroup($objOneRecipient) != null) {
                    $arrMembers = $objUsersources->getSourceGroup($objOneRecipient)->getUserIdsForGroup();

                    foreach ($arrMembers as $strOneId) {
                        if (!isset($arrReturn[$strOneId])) {
                            $arrReturn[$strOneId] = new UserUser($strOneId);
                        }
                    }
                }
            }
        }


        return $arrReturn;
    }
}
