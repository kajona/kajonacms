<?php
/**
 * Model for a demo other object
 * Represents the title
 *
 * @package module_demo
 * @author tim.kiefer@kojikui.de
 * @targetTable demo_sub_object.sub_object_id
 */
class class_module_demo_sub_object extends class_model implements interface_model, interface_admin_listable {
    /**
     * Constructor to create a valid object
     *
     * @param string $strSystemid (use "" on new objects)
     */
    public function __construct($strSystemid = "") {
        $this->setArrModuleEntry("moduleId", _demo_module_id_);
        $this->setArrModuleEntry("modul", "demo");
        parent::__construct($strSystemid);
    }

    /**
     * @var string
     * @tableColumn demo_sub_object.sub_object_title
     *
     * @fieldType textarea
     * @fieldMandatory
     * @fieldLabel commons_title
     */
    private $strTitle = "";


    /**
     * @var int
     * @tableColumn demo_demo.demo_int
     *
     * @fieldType text
     */
    private $intInt;


    /**
     * @param string $strTitle
     */
    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
    }

    /**
     * @return string
     */
    public function getStrTitle() {
        return $this->strTitle;
    }

    /**
     * @param int $intInt
     */
    public function setIntInt($intInt) {
        $this->intInt = $intInt;
    }

    /**
     * @return int
     */
    public function getIntInt() {
        return $this->intInt;
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
        return "icon_excel.png";
    }

    /**
     * In nearly all cases, the additional info is rendered left to the action-icons.
     * @return string
     */
    public function getStrAdditionalInfo() {
        // TODO: Implement getStrAdditionalInfo() method.
    }

    /**
     * If not empty, the returned string is rendered below the common title.
     * @return string
     */
    public function getStrLongDescription() {
        // TODO: Implement getStrLongDescription() method.
    }

    /**
     * Returns the name to be used when rendering the current object, e.g. in admin-lists.
     * @return string
     */
    public function getStrDisplayName() {
        return $this->getStrTitle();
    }
}
