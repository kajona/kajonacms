<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2013 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                              *
********************************************************************************************************/


/**
 * Class to represent a guestbook book
 *
 * @package module_guestbook
 * @author sidler@mulchprod.de
 *
 * @targetTable guestbook_book.guestbook_id
 */
class class_module_guestbook_guestbook extends class_model implements interface_model, interface_admin_listable  {

    /**
     * @var string
     * @tableColumn guestbook_book.guestbook_title
     * @listOrder
     *
     * @fieldMandatory
     * @fieldType text
     * @fieldLabel commons_title
     */
    private $strGuestbookTitle = "";

    /**
     * @var int
     * @tableColumn guestbook_book.guestbook_moderated
     *
     * @fieldMandatory
     * @fieldType yesno
     */
    private $intGuestbookModerated = 0;


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
		$this->setArrModuleEntry("moduleId", _guestbook_module_id_);
		$this->setArrModuleEntry("modul", "guestbook");

		//base class
		parent::__construct($strSystemid);
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin()
     */
    public function getStrIcon() {
        return "icon_book.png";
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
        return $this->objRights->addGroupToRight(_guests_group_id_, $this->getSystemid(), "right1");
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
