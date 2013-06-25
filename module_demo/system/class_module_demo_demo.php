<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: $                                *
********************************************************************************************************/

/**
 * Model for a demo
 * Represents the title
 *
 * @package module_demo
 * @author tim.kiefer@kojikui.de
 * @targetTable demo_demo.demo_id
 */
class class_module_demo_demo extends class_model implements interface_model, interface_admin_listable {

    /**
     * @var string
     * @tableColumn demo_demo.demo_title
     * @fieldType textarea
     * @fieldMandatory
     * @fieldLabel commons_title
     */
    private $strTitle = "";


    /**
     * The demo float
     *
     * @var float
     * @tableColumn demo_demo.demo_float
     * @fieldType float
     */
    private $floatFloat = 0.0;

    /**
     * @var int
     * @tableColumn demo_demo.demo_int
     * @fieldType text
     */
    private $intInt;


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

    public function initObjectInternal() {
        parent::initObjectInternal();
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
        return "icon_news";
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


    public function getStrTitle() {
        return $this->strTitle;
    }

    public function setStrTitle($strTitle) {
        $this->strTitle = $strTitle;
    }
}
