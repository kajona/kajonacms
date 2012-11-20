<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                *
********************************************************************************************************/

/**
 * Model for a faqs category
 *
 * @package module_faqs
 * @author sidler@mulchprod.de
 * @targetTable faqs_category.faqs_cat_id
 */
class class_module_faqs_category extends class_model implements interface_model, interface_admin_listable {

    /**
     * @var string
     * @tableColumn faqs_category.faqs_cat_title
     * @listOrder asc
     *
     * @fieldType text
     * @fieldMandatory
     * @fieldLabel commons_title
     */
    private $strTitle = "";


    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $this->setArrModuleEntry("moduleId", _faqs_module_id_);
        $this->setArrModuleEntry("modul", "faqs");

        //base class
        parent::__construct($strSystemid);
    }

    /**
     * Returns the icon the be used in lists.
     * Please be aware, that only the filename should be returned, the wrapping by getImageAdmin() is
     * done afterwards.
     *
     * @return string the name of the icon, not yet wrapped by getImageAdmin(). Alternatively, you may return an array containing
     *         [the image name, the alt-title]
     */
    public function getStrIcon() {
        return "icon_folderClosed.png";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     *
     * @return string
     */
    public function getStrAdditionalInfo() {
        return "";
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     *
     * @return string
     */
    public function getStrLongDescription() {
        return "";
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     *
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrTitle();
    }


    /**
     * Loads all categories, the given faq is in
     *
     * @param string $strSystemid
     *
     * @return class_module_faqs_category[]
     * @static
     */
    public static function getFaqsMember($strSystemid) {
        $strQuery = "SELECT faqsmem_category as system_id FROM " . _dbprefix_ . "faqs_member
	                   WHERE faqsmem_faq = ? ";
        $arrIds = class_carrier::getInstance()->getObjDB()->getPArray($strQuery, array($strSystemid));
        $arrReturn = array();
        foreach($arrIds as $arrOneId) {
            $arrReturn[] = new class_module_faqs_category($arrOneId["system_id"]);
        }

        return $arrReturn;
    }


    /**
     * Deletes all memberships of the given FAQ
     *
     * @param string $strSystemid FAQ-ID
     *
     * @return bool
     */
    public static function deleteFaqsMemberships($strSystemid) {
        $strQuery = "DELETE FROM " . _dbprefix_ . "faqs_member
	                  WHERE faqsmem_faq = ? ";
        return class_carrier::getInstance()->getObjDB()->_pQuery($strQuery, array($strSystemid));
    }

    public function deleteObject() {

        //start by deleting from members and cat table
        $strQuery = "DELETE FROM " . _dbprefix_ . "faqs_member WHERE faqsmem_category = ? ";

        if($this->objDB->_pQuery($strQuery, array($this->getSystemid()))) {
            return parent::deleteObject();
        }
        return false;
    }

    public function getStrTitle() {
        return $this->strTitle;
    }

    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
    }

}
