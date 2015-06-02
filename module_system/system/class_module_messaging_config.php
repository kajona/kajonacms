<?php
/*"******************************************************************************************************
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

/**
 * Model for a single message, emitted by the messaging subsytem.
 * Each message is directed to a single user.
 * On message creation, the current date is set as the sent-date.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_messaging
 *
 * @targetTable messages_cfg.config_id
 *
 * @module messaging
 * @moduleId _messaging_module_id_
 */
class class_module_messaging_config extends class_model implements interface_model  {

    /**
     * @var string
     * @tableColumn messages_cfg.config_provider
     * @tableColumnDatatype char254
     */
    private $strMessageprovider = "";

    /**
     * @var string
     * @tableColumn messages_cfg.config_user
     * @tableColumnDatatype char20
     */
    private $strUser = "";

    /**
     * @var bool
     * @tableColumn messages_cfg.config_enabled
     * @tableColumnDatatype int
     */
    private $bitEnabled = true;

    /**
     * @var bool
     * @tableColumn messages_cfg.config_bymail
     * @tableColumnDatatype int
     */
    private $bitBymail = false;


    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrMessageprovider();
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {
        return "icon_mail";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     * @return string
     */
    public function getStrAdditionalInfo() {
        return "";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     * @return string
     */
    public function getStrLongDescription() {
        return "";
    }


    /**
     * Returns the configuration of a single provider for a user.
     *
     * @param string $strUserid
     * @param \interface_messageprovider|string $objProvider
     *
     * @return class_module_messaging_config
     * @static
     */
    public static function getConfigForUserAndProvider($strUserid, interface_messageprovider $objProvider) {
        $objORM = new class_orm_objectlist();
        $objORM->addWhereRestriction(new class_orm_objectlist_restriction("AND config_user = ?", $strUserid));
        $objORM->addWhereRestriction(new class_orm_objectlist_restriction("AND config_provider = ?", get_class($objProvider)));
        $objConfig = $objORM->getSingleObject(get_called_class());

        if($objConfig === null) {
            $objConfig = new class_module_messaging_config();
            $objConfig->setStrUser($strUserid);
            $objConfig->setStrMessageprovider(get_class($objProvider));
        }
        return $objConfig;
    }

    /**
     * Returns a new instance of the referenced messageprovider
     *
     * @return null|interface_messageprovider|interface_messageprovider_extended
     */
    private function getObjProvider() {
        if($this->getStrMessageprovider() != "") {
            $objRefl = new ReflectionClass($this->getStrMessageprovider());
            $objInstance = $objRefl->newInstance();

            return $objInstance;
        }

        return null;
    }


    /**
     * @param string $strUser
     */
    public function setStrUser($strUser) {
        $this->strUser = $strUser;
    }

    /**
     * @return string
     */
    public function getStrUser() {
        return $this->strUser;
    }

    /**
     * @param string $strMessageprovider
     */
    public function setStrMessageprovider($strMessageprovider) {
        $this->strMessageprovider = $strMessageprovider;
    }

    /**
     * @return string
     */
    public function getStrMessageprovider() {
        return $this->strMessageprovider;
    }

    /**
     * @param boolean $bitEnabled
     */
    public function setBitEnabled($bitEnabled) {
        $this->bitEnabled = $bitEnabled;
    }

    /**
     * @return boolean
     */
    public function getBitEnabled() {
        if($this->getObjProvider() instanceof interface_messageprovider_extended) {
            if($this->getObjProvider()->isAlwaysActive())
                return true;
        }

        return $this->bitEnabled;
    }

    /**
     * @param boolean $bitBymail
     */
    public function setBitBymail($bitBymail) {
        $this->bitBymail = $bitBymail;
    }

    /**
     * @return boolean
     */
    public function getBitBymail() {
        if($this->getObjProvider() instanceof interface_messageprovider_extended) {
            if($this->getObjProvider()->isAlwaysByMail())
                return true;
        }
        return $this->bitBymail;
    }


}
