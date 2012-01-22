<?php
/*"******************************************************************************************************
*   (c) 2007-2012 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id: interface_versionable.php 4413 2012-01-03 19:38:11Z sidler $                               *
********************************************************************************************************/

/**
 * The base-class for all form-entries.
 * Holds common values and common method-logic to reduce the amount
 * of own code as much as possible.
 * In addition to extending class_formentry_base, make sure to implement interface_formentry, too.
 *
 * @author sidler@mulchprod.de
 * @since 4.0
 * @package module_system
 */
class class_formentry_base {

    /**
     * @var class_model
     */
    private $objSourceObject = null;
    private $strSourceProperty = null;
    private $strFormName = "";

    /**
     * @var interface_validator
     */
    private $objValidator;


    private $strLabel = null;
    private $strEntryName = null;
    private $bitMandatory = false;




    /**
     * Creates a new instance of the current field.
     *
     * @param $strFormName
     * @param $strSourceProperty
     * @param class_model $objSourceObject
     */
    public function __construct($strFormName, $strSourceProperty, class_model $objSourceObject) {
        $this->strSourceProperty = $strSourceProperty;
        $this->objSourceObject = $strSourceProperty;
        $this->strFormName = $strFormName;

        $this->strLabel = class_carrier::getInstance()->getObjLang()->getLang("form_".$strFormName."_".$strSourceProperty, $objSourceObject->getArrModule("modul"));
        $this->strEntryName = uniStrtolower($strFormName."_".$strSourceProperty);
    }

    /**
     * @param bool $bitMandatory
     * @return class_formentry_base
     */
    public function setBitMandatory($bitMandatory) {
        $this->bitMandatory = $bitMandatory;
        return $this;
    }

    public function getBitMandatory() {
        return $this->bitMandatory;
    }

    /**
     * @param string $strLabel
     * @return class_formentry_base
     */
    public function setStrLabel($strLabel) {
        $this->strLabel = $strLabel;
        return $this;
    }

    public function getStrLabel() {
        return $this->strLabel;
    }

    /**
     * @param \interface_validator $objValidator
     * @return \class_formentry_base
     */
    public function setObjValidator(interface_validator $objValidator) {
        $this->objValidator = $objValidator;
        return $this;
    }

    /**
     * @return \interface_validator
     */
    public function getObjValidator() {
        return $this->objValidator;
    }

    public function setStrFormName($strFormName) {
        $this->strFormName = $strFormName;
    }

    public function getStrFormName() {
        return $this->strFormName;
    }

    public function setStrEntryName($strEntryName) {
        $this->strEntryName = $strEntryName;
    }

    public function getStrEntryName() {
        return $this->strEntryName;
    }


}
