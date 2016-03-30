<?php
/*"******************************************************************************************************
*   (c) 2004-2006 by MulchProductions, www.mulchprod.de                                                 *
*   (c) 2007-2016 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*   $Id$                                 *
********************************************************************************************************/

namespace Kajona\Navigation\Admin\Elements;

use Kajona\Navigation\System\NavigationTree;
use Kajona\Pages\Admin\AdminElementInterface;
use Kajona\Pages\Admin\ElementAdmin;
use Kajona\System\Admin\AdminFormgenerator;

/**
 * Admin class of the navigation element
 *
 * @package module_navigation
 * @author sidler@mulchprod.de
 *
 * @targetTable element_navigation.content_id
 */
class ElementNavigationAdmin extends ElementAdmin implements AdminElementInterface {

    /**
     * Legacy support
     *
     * @var string
     * @tableColumn element_navigation.navigation_mode
     * @tableColumnDatatype char254
     * @deprecated
     */
    private $intMode;

    /**
     * @var string
     * @tableColumn element_navigation.navigation_id
     * @tableColumnDatatype char20
     * @fieldType Kajona\System\Admin\Formentries\FormentryDropdown
     * @fieldLabel commons_name
     */
    private $strRepo;

    /**
     * @var string
     * @tableColumn element_navigation.navigation_template
     * @tableColumnDatatype char254
     * @fieldType Kajona\Pages\Admin\Formentries\FormentryTemplate
     * @fieldLabel template
     * @fieldTemplateDir /module_navigation
     */
    private $strTemplate;

    /**
     * @var int
     * @tableColumn element_navigation.navigation_foreign
     * @tableColumnDatatype int
     * @fieldType Kajona\System\Admin\Formentries\FormentryYesno
     * @fieldLabel navigation_foreign
     */
    private $intForeign = 1;


    /**
     * @return AdminFormgenerator|null
     */
    public function getAdminForm() {
        $objForm = parent::getAdminForm();

        $arrNavigationsDropdown = array();
        foreach(NavigationTree::getObjectList() as $objOneNavigation)
            $arrNavigationsDropdown[$objOneNavigation->getSystemid()] = $objOneNavigation->getStrDisplayName();
        $objForm->getField("repo")->setArrKeyValues($arrNavigationsDropdown);

        return $objForm;
    }

    /**
     * @param string $strTemplate
     * @return void
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
     * @param string $strRepo
     * @return void
     */
    public function setStrRepo($strRepo) {
        $this->strRepo = $strRepo;
    }

    /**
     * @return string
     */
    public function getStrRepo() {
        return $this->strRepo;
    }

    /**
     * @param int $intForeign
     * @return void
     */
    public function setIntForeign($intForeign) {
        $this->intForeign = $intForeign;
    }

    /**
     * @return int
     */
    public function getIntForeign() {
        if($this->intForeign === null)
            $this->intForeign = 1;
        return $this->intForeign;
    }

    /**
     * @param string $intMode
     */
    public function setIntMode($intMode) {
        $this->intMode = $intMode;
    }

    /**
     * @return string
     */
    public function getIntMode() {
        return $this->intMode;
    }





}
