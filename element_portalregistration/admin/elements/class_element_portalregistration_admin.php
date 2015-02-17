<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2015 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                        *
********************************************************************************************************/

/**
 * Class to handle the admin-stuff of the portalregistration-element
 *
 * @package element_portalregistration
 * @author sidler@mulchprod.de
 *
 * @targetTable element_preg.content_id
 */
class class_element_portalregistration_admin extends class_element_admin implements interface_admin_element {

    /**
     * @var string
     * @tableColumn element_preg.portalregistration_template
     * @tableColumnDatatype char254
     *
     * @fieldType template
     * @fieldLabel template
     *
     * @fieldTemplateDir /element_portalregistration
     */
    private $strTemplate;

    /**
     * @var string
     * @tableColumn element_preg.portalregistration_group
     * @tableColumnDatatype char254
     *
     * @fieldType dropdown
     * @fieldLabel portalregistration_group
     */
    private $strGroup;

    /**
     * @var string
     * @tableColumn element_preg.portalregistration_success
     * @tableColumnDatatype char254
     *
     * @fieldType page
     * @fieldLabel commons_page_success
     */
    private $strSuccess;

    public function getAdminForm() {
        $objForm = parent::getAdminForm();

        $arrGroups = class_module_user_group::getObjectList();
        $arrGroupsDD = array();
        foreach($arrGroups as $objOneGroup) {
            if($objOneGroup->getStrSubsystem() == "kajona") {
                $arrGroupsDD[$objOneGroup->getSystemid()] = $objOneGroup->getStrName();
            }
        }
        $objForm->getField("group")->setArrKeyValues($arrGroupsDD);

        $objForm->addField(new class_formentry_textrow("hint"))->setStrValue($this->getLang("portalregistration_hint"));
        $objForm->setFieldToPosition("hint", 1);
        return $objForm;
    }

    /**
     * @param string $strTemplate
     */
    public function setStrTemplate($strTemplate) {
        $this->strTemplate = $strTemplate;
    }

    /**
     * @return string
     */
    public function getStrTemplate() {
        return $this->strTemplate;
    }

    /**
     * @param string $strSuccess
     */
    public function setStrSuccess($strSuccess) {
        $this->strSuccess = $strSuccess;
    }

    /**
     * @return string
     */
    public function getStrSuccess() {
        return $this->strSuccess;
    }

    /**
     * @param string $strGroup
     */
    public function setStrGroup($strGroup) {
        $this->strGroup = $strGroup;
    }

    /**
     * @return string
     */
    public function getStrGroup() {
        return $this->strGroup;
    }




}
