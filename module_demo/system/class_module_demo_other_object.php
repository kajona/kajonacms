<?php
/**
 * Model for a demo other object
 * Represents the title
 *
 * @package module_demo
 * @author tim.kiefer@kojikui.de
 * @targetTable demo_other_object.other_object_id
 *
 * @module demo
 * @moduleId _demo_module_id_
 */
class class_module_demo_other_object extends class_model implements interface_model, interface_admin_listable {

    /**
     * @var string
     * @tableColumn demo_other_object.other_object_title
     *
     * @fieldType textarea
     * @fieldMandatory
     * @fieldLabel commons_title
     */
    private $strTitle = "";

    /**
     * The date of the measurement
     * @var class_date
     * @tableColumn demo_other_object.other_object_date
     *
     * @fieldType date
     */
    private $dateDate;

    /**
     * The weight of the user
     * @var float
     * @tableColumn demo_other_object.other_object_float
     *
     * @fieldType float
     */
    private $floatFloat = 0.0;


    /**
     * @param \class_date $dateDate
     */
    public function setDateDate($dateDate) {
        $this->dateDate = $dateDate;
    }

    /**
     * @return \class_date
     */
    public function getDateDate() {
        return $this->dateDate;
    }

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
     * @param float $floatFloat
     */
    public function setFloatFloat($floatFloat) {
        $this->floatFloat = $floatFloat;
    }

    /**
     * @return float
     */
    public function getFloatFloat() {
        return $this->floatFloat;
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
        return "icon_excel";
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
