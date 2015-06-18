<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/


/**
 * Class to represent a guestbook book
 *
 * @package module_guestbook
 * @author sidler@mulchprod.de
 *
 * @targetTable guestbook_book.guestbook_id
 *
 * @module guestbook
 * @moduleId _guestbook_module_id_
 */
class class_module_guestbook_guestbook extends class_model implements interface_model, interface_admin_listable  {

    /**
     * @var string
     * @tableColumn guestbook_book.guestbook_title
     * @tableColumnDatatype char254
     * @listOrder
     *
     * @fieldMandatory
     * @fieldType text
     * @fieldLabel commons_title
     *
     * @addSearchIndex
     */
    private $strGuestbookTitle = "";

    /**
     * @var int
     * @tableColumn guestbook_book.guestbook_moderated
     * @tableColumnDatatype int
     *
     * @fieldMandatory
     * @fieldType yesno
     */
    private $intGuestbookModerated = 0;


    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {
        return "icon_book";
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
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrGuestbookTitle();
    }


    /**
     * Adds the right of the guest to sign the book
     *
     * @return bool
     */
    protected function onInsertToDb() {
        return class_carrier::getInstance()->getObjRights()->addGroupToRight(class_module_system_setting::getConfigValue("_guests_group_id_"), $this->getSystemid(), "right1");
    }

    /**
     * @param string $strGuestbookTitle
     */
    public function setStrGuestbookTitle($strGuestbookTitle) {
        $this->strGuestbookTitle = $strGuestbookTitle;
    }

    public function getStrGuestbookTitle() {
        return $this->strGuestbookTitle;
    }

    /**
     *
     * @param string $intGuestbookModerated
     */
    public function setIntGuestbookModerated($intGuestbookModerated) {
        $this->intGuestbookModerated = $intGuestbookModerated;
    }

    public function getIntGuestbookModerated() {
        return $this->intGuestbookModerated;
    }




}
