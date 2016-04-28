<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
********************************************************************************************************/

namespace Kajona\Portalregistration\Admin\Elements;

use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;
use Kajona\System\Admin\Formentries\FormentryTextrow;
use Kajona\System\System\UserGroup;


/**
 * Class to handle the admin-stuff of the portalregistration-element
 *
 * @author sidler@mulchprod.de
 *
 * @targetTable element_preg.content_id
 */
class ElementPortalregistrationAdmin extends ElementAdmin implements AdminElementInterface
{

    /**
     * @var string
     * @tableColumn element_preg.portalregistration_template
     * @tableColumnDatatype char254
     *
     * @fieldType Kajona\Pages\Admin\Formentries\FormentryTemplate
     * @fieldLabel template
     *
     * @fieldTemplateDir /module_portalregistration
     */
    private $strTemplate;

    /**
     * @var string
     * @tableColumn element_preg.portalregistration_group
     * @tableColumnDatatype char254
     *
     * @fieldType Kajona\System\Admin\Formentries\FormentryDropdown
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

        $arrGroups = UserGroup::getObjectListFiltered();
        $arrGroupsDD = array();
        foreach($arrGroups as $objOneGroup) {
            if($objOneGroup->getStrSubsystem() == "kajona") {
                $arrGroupsDD[$objOneGroup->getSystemid()] = $objOneGroup->getStrName();
            }
        }
        $objForm->getField("group")->setArrKeyValues($arrGroupsDD);

        $objForm->addField(new FormentryTextrow("hint"))->setStrValue($this->getLang("portalregistration_hint"));
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
